<?php

namespace App\Models;

use PDO;

class Order extends BaseModel {
    public function __construct() {
        parent::__construct();
        // Xử lý logic cho các bảng: orders, order_items, order_status_logs, payments
    }

    // --- ORDERS ---
    public function createOrder($data) { return $this->insert('orders', $data); }
    public function getOrder($id) { return $this->getById('orders', $id); }
    
    // Logic Đặt hàng: Kiểm tra tồn kho -> Trừ kho, Tăng Sold -> Lưu DB
    public function placeOrder($orderData, $cartItems) {
        // 1. Kiểm tra tồn kho trước khi lưu (Return false nếu không đủ)
        foreach ($cartItems as $item) {
            $stmt = $this->db->prepare("SELECT stock_quantity FROM product_variants WHERE id = :variant_id");
            $stmt->execute(['variant_id' => $item['variant_id']]);
            $variant = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$variant || $variant['stock_quantity'] < $item['quantity']) {
                return [
                    'success' => false, 
                    'message' => 'Sản phẩm với ID biến thể '.$item['variant_id'].' không đủ số lượng trong kho.'
                ];
            }
        }

        try {
            $this->db->beginTransaction();

            // 2. Tạo đơn hàng (Bảng orders)
            $orderId = $this->createOrder($orderData);
            if (!$orderId) {
                throw new \Exception("Không thể tạo đơn hàng.");
            }

            // 3. Tạo Order Items (Lưu ý: trigger ở MySQL sẽ tự động trừ stock_quantity)
            foreach ($cartItems as $item) {
                $orderItemData = [
                    'order_id' => $orderId,
                    'variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity'],
                    'price_at_time' => $item['price'] ?? 0 // Cần map giá hiện tại
                ];
                $this->createOrderItem($orderItemData);

                // 4. Tăng cột sold_count ở bảng product
                $stmtSold = $this->db->prepare("
                    UPDATE product p
                    JOIN product_variants pv ON p.id = pv.product_id
                    SET p.sold_count = p.sold_count + :quantity
                    WHERE pv.id = :variant_id
                ");
                $stmtSold->execute([
                    'quantity' => $item['quantity'],
                    'variant_id' => $item['variant_id']
                ]);
            }

            $this->db->commit();
            return ['success' => true, 'order_id' => $orderId, 'message' => 'Đặt hàng thành công.'];

        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Tạo mã đơn hàng độc nhất (Ví dụ: ORD-20260621-XXXXXX)
    public function generateUniqueOrderCode() {
        $isUnique = false;
        $orderCode = '';
        
        while (!$isUnique) {
            $randomString = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
            $orderCode = 'ORD-' . date('Ymd') . '-' . $randomString;
            
            // Kiểm tra xem đã tồn tại trong DB chưa
            $stmt = $this->db->prepare("SELECT id FROM orders WHERE order_code = :order_code");
            $stmt->execute(['order_code' => $orderCode]);
            
            if (!$stmt->fetch()) {
                $isUnique = true;
            }
        }
        
        return $orderCode;
    }
    public function updateOrderStatus($id, $status) { return $this->update('orders', $id, ['status' => $status]); }

    // Hủy đơn hàng (Chỉ cho phép khi status = 'pending')
    public function cancelOrder($orderId, $userId = null) {
        $order = $this->getOrder($orderId);
        
        if (!$order) {
            return ['success' => false, 'message' => 'Đơn hàng không tồn tại.'];
        }

        // Nếu có truyền userId, kiểm tra xem đơn hàng có thuộc về user này không
        if ($userId && $order['user_id'] != $userId) {
            return ['success' => false, 'message' => 'Bạn không có quyền hủy đơn hàng này.'];
        }

        // Chỉ cho phép hủy khi trạng thái là pending
        if ($order['status'] !== 'pending') {
            return ['success' => false, 'message' => 'Chỉ có thể hủy đơn hàng khi đang ở trạng thái chờ xử lý (pending).'];
        }

        // Cập nhật trạng thái
        $this->updateOrderStatus($orderId, 'canceled');
        
        // Lưu ý: Database đã có sẵn Trigger (trg_after_order_canceled) 
        // để tự động hoàn trả số lượng kho (inventory) khi status đổi thành 'canceled'
        
        return ['success' => true, 'message' => 'Hủy đơn hàng thành công.'];
    }

    public function getOrdersByUserId($userId, $status = 'all') {
        $statusMap = [
            'pending' => 'pending',
            'confirmed' => 'confirmed',
            'shipping' => 'shipping',
            'delivered' => 'delivered',
            'cancelled' => 'canceled',
            'canceled' => 'canceled'
        ];
        $normalizedStatus = strtolower((string) $status);

        $sql = "SELECT * FROM orders WHERE user_id = :user_id";
        $params = ['user_id' => $userId];

        if ($normalizedStatus !== 'all' && isset($statusMap[$normalizedStatus])) {
            $sql .= " AND status = :status";
            $params['status'] = $statusMap[$normalizedStatus];
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- ORDER_ITEMS ---
    public function createOrderItem($data) { return $this->insert('order_items', $data); }
    public function getOrderItem($id) { return $this->getById('order_items', $id); }

    public function getOrderItems($orderId) {
        $stmt = $this->db->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- PAYMENTS ---
    public function createPayment($data) { return $this->insert('payments', $data); }
    public function getPayment($id) { return $this->getById('payments', $id); }
    public function updatePaymentStatus($id, $status) { return $this->update('payments', $id, ['payment_status' => $status]); }

    public function getPaymentByOrderId($orderId) {
        $stmt = $this->db->prepare("SELECT * FROM payments WHERE order_id = :order_id");
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // --- ORDER_STATUS_LOGS ---
    public function createOrderStatusLog($data) { return $this->insert('order_status_logs', $data); }
    public function getOrderStatusLog($id) { return $this->getById('order_status_logs', $id); }

    public function getStatusLogsByOrder($orderId) {
        $stmt = $this->db->prepare("SELECT * FROM order_status_logs WHERE order_id = :order_id ORDER BY created_at ASC");
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- ADMIN DASHBOARD & QUẢN LÝ ---

    // 1. Lấy danh sách đơn hàng cho Admin (có Filter và Phân trang)
    public function getAdminOrders($filters = [], $limit = 10, $offset = 0) {
        $query = "SELECT * FROM orders WHERE 1=1";
        $params = [];

        // Lọc theo trạng thái đơn hàng
        if (!empty($filters['status'])) {
            $query .= " AND status = :status";
            $params['status'] = $filters['status'];
        }

        // Lọc theo khoảng thời gian (Từ ngày - Đến ngày)
        if (!empty($filters['start_date'])) {
            $query .= " AND DATE(created_at) >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $query .= " AND DATE(created_at) <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }

        // Tìm kiếm theo mã đơn hàng
        if (!empty($filters['order_code'])) {
            $query .= " AND order_code LIKE :order_code";
            $params['order_code'] = "%" . $filters['order_code'] . "%";
        }

        // Sắp xếp mới nhất
        $query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($query);

        // Bind params thủ công để xử lý phân trang (LIMIT/OFFSET phải là INT)
        foreach ($params as $key => $val) {
            $stmt->bindValue(":$key", $val);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. Đếm tổng số đơn hàng theo Filter (Để tính tổng số trang)
    public function countAdminOrders($filters = []) {
        $query = "SELECT COUNT(id) as total FROM orders WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $query .= " AND status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['start_date'])) {
            $query .= " AND DATE(created_at) >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $query .= " AND DATE(created_at) <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }
        if (!empty($filters['order_code'])) {
            $query .= " AND order_code LIKE :order_code";
            $params['order_code'] = "%" . $filters['order_code'] . "%";
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['total'] : 0;
    }

    // 3. Lấy các đơn hàng gần nhất (Dùng cho Widget Dashboard)
    public function getLatestOrders($limit = 5) {
        $stmt = $this->db->prepare("SELECT * FROM orders ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
