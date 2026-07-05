<?php

namespace App\Models;

use PDO;

class Order extends BaseModel {
    
    public function __construct() {
        parent::__construct();
    }

    // --- ORDERS ---
    public function createOrder($data) { return $this->insert('orders', $data); }
    public function getOrder($id) { return $this->getById('orders', $id); }
    
    public function placeOrder($orderData, $cartItems) {
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

            // Tạo đơn hàng (Bảng orders)
            $orderId = $this->createOrder($orderData);
            if (!$orderId) {
                throw new \Exception("Không thể tạo đơn hàng.");
            }

            //Tạo Order Items 
            foreach ($cartItems as $item) {
                $orderItemData = [
               'order_id' => $orderId,
               'variant_id' => $item['variant_id'],
               'quantity' => $item['quantity'],
               'price_at_time' => $item['price']

];
                $this->createOrderItem($orderItemData);
                $stmtStock = $this->db->prepare("
                UPDATE product_variants
                SET stock_quantity = stock_quantity - :qty
                WHERE id = :variant_id
                ");

                $stmtStock->execute([
                'qty' => $item['quantity'],
                'variant_id' => $item['variant_id']
                ]);

                // ăng cột sold_count ở bảng product
                $stmtSold = $this->db->prepare("
                    UPDATE product p
                    JOIN product_variants pv ON p.id = pv.product_id
                    SET sold_count = sold_count + :quantity
                    WHERE pv.id = :variant_id
                ");
                $stmtSold->execute([
                    'quantity' => $item['quantity'],
                    'variant_id' => $item['variant_id']
                ]);
            }
            $paymentData = [
           'order_id' => $orderId,
           'payment_method' => $_POST['payment_method'] ?? 'cod',
           'payment_status' => 0

            ];

            $this->createPayment($paymentData);

            $this->db->commit();
            return ['success' => true, 'order_id' => $orderId, 'message' => 'Đặt hàng thành công.'];

        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Tạo mã đơn hàng độc nhất 
    public function generateUniqueOrderCode() {
        $isUnique = false;
        $orderCode = '';
        
        while (!$isUnique) {
            $randomString = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
            $orderCode = 'ORD-' . date('Ymd') . '-' . $randomString;
            
            $stmt = $this->db->prepare("SELECT id FROM orders WHERE order_code = :order_code");
            $stmt->execute(['order_code' => $orderCode]);
            
            if (!$stmt->fetch()) {
                $isUnique = true;
            }
        }
        
        return $orderCode;
    }

    public function updateOrderStatus($id, $status) { return $this->update('orders', $id, ['status' => $status]); }

    // Hủy đơn hàng
    public function cancelOrder($orderId, $userId = null) {
        $order = $this->getOrder($orderId);
        
        if (!$order) {
            return ['success' => false, 'message' => 'Đơn hàng không tồn tại.'];
        }

        if ($userId && $order['user_id'] != $userId) {
            return ['success' => false, 'message' => 'Bạn không có quyền hủy đơn hàng này.'];
        }

        if ($order['status'] !== 'pending') {
            return ['success' => false, 'message' => 'Chỉ có thể hủy đơn hàng khi đang ở trạng thái chờ xử lý (pending).'];
        }

        $this->updateOrderStatus($orderId, 'canceled');
        return ['success' => true, 'message' => 'Hủy đơn hàng thành công.'];
    }

    public function getOrdersByUserId($userId) {
    $sql = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetchAll();
}

    //ORDER_ITEMS 
    public function createOrderItem($data) { return $this->insert('order_items', $data); }
    public function getOrderItem($id) { return $this->getById('order_items', $id); }

    public function getOrderItems($orderId) {
        $stmt = $this->db->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // PAYMENTS
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

    // Lấy danh sách đơn hàng cho Admin 
    public function getAdminOrders($filters = [], $limit = 10, $offset = 0) {
        $query = "SELECT * FROM orders WHERE 1=1";
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

        $query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($query);

        foreach ($params as $key => $val) {
            $stmt->bindValue(":$key", $val);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBestSellingProducts($limit = 10)
{
    $sql = "
        SELECT
            p.id,
            p.name,
            SUM(oi.quantity) AS total_sold
        FROM order_items oi
        JOIN product_variants pv
            ON oi.variant_id = pv.id
        JOIN product p
            ON pv.product_id = p.id
        GROUP BY p.id, p.name
        ORDER BY total_sold DESC
        LIMIT :limit
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    // Đếm tổng số đơn hàng theo bộ lọc trạng thái
    public function countAdminOrders($filters = []) {
        $query = "SELECT COUNT(id) as total FROM orders WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $query .= " AND LOWER(status) = :status";
            $params['status'] = strtolower($filters['status']);
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

    // Lấy các đơn hàng gần nhất 
    public function getLatestOrders($limit = 5) {
        $stmt = $this->db->prepare("SELECT * FROM orders ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalRevenue()
{
    $sql = "
    SELECT SUM(final_amount) revenue
    FROM orders
    WHERE status IN(
    'processing',
    'shipping',
    'completed',
    'delivered'
    )";
    $stmt = $this->db->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return (float)($row['revenue'] ?? 0);
}

    // Đếm tổng số khách hàng duy nhất đã phát sinh đơn hàng
    public function countUniqueCustomers() {
        $sql = "SELECT COUNT(DISTINCT user_id) as total_users FROM orders WHERE user_id IS NOT NULL";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total_users'] ?? 0);
    }

    public function getConnection()
{
    return $this->db;
}
}
