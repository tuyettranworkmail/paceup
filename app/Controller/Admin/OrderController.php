<?php

namespace App\Controller\Admin;

use App\Models\Order;

class OrderController {
    private $orderModel;

    public function __construct() {
        $this->requireAdmin();
        $this->orderModel = new Order();
        $this->reportModel = new \App\Models\Report();
    }

    // Hiển thị danh sách và thống kê
    public function index() {
        $status = $_GET['status'] ?? 'All';
        $orderCode = trim($_GET['order_code'] ?? '');
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';
        
        $filters = [];
        if ($status !== 'All') {
            $filters['status'] = $status;
        }
        if (!empty($orderCode)) {
            $filters['order_code'] = $orderCode;
        }
        if (!empty($startDate)) {
            $filters['start_date'] = $startDate;
        }
        if (!empty($endDate)) {
            $filters['end_date'] = $endDate;
        }

        $page = (int)($_GET['page'] ?? 1);
        if ($page < 1) $page = 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        // Lấy dữ liệu danh sách đơn hàng
        $orders = $this->orderModel->getAdminOrders($filters, $limit, $offset);
        $totalOrdersCount = $this->orderModel->countAdminOrders($filters);
        $totalPages = ceil($totalOrdersCount / $limit);

        $stats = [
            'total_orders' => $this->orderModel->countAdminOrders([]),
            'revenue'      => $this->orderModel->getTotalRevenue(),
            'pending'      => $this->orderModel->countAdminOrders(['status' => 'pending']),
            'customers'    => $this->orderModel->countUniqueCustomers()
        ];

        require __DIR__ . '/../../Views/admin/orders/index.php';
    }

    public function indexAdmin() {
    // Lấy dữ liệu thống kê
    $stats = [
        'total_orders' => $this->orderModel->countAdminOrders([]),
        'revenue'      => $this->orderModel->getTotalRevenue(),
        'pending'      => $this->orderModel->countAdminOrders(['status' => 'pending']),
        'customers'    => $this->orderModel->countUniqueCustomers()
    ];
    require __DIR__ . '/../Views/admin/orders/index.php';
    }

    //  Cập nhật trạng thái 
    public function updateStatusApi() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? '');

        if ($id > 0 && !empty($status)) {
            $updated = $this->orderModel->updateOrderStatus($id, $status);
            if ($updated) {
                // Ghi nhận lịch sử log đơn hàng
                $this->orderModel->createOrderStatusLog([
                    'order_id' => $id,
                    'status' => $status,
                    'note' => 'Cập nhật trạng thái qua Admin Dashboard',
                    'created_by' => $_SESSION['user_id'] ?? 1
                ]);

                echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái đơn hàng thành công!']);
                exit;
            }
        }

        echo json_encode(['success' => false, 'message' => 'Không thể cập nhật trạng thái đơn hàng.']);
        exit;
    }

    public function myOrders() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "login");
        exit;
    }
    $myOrders = $this->orderModel->getOrdersByUserId($_SESSION['user_id']);
    
    require_once __DIR__ . '/../Views/my-orders.php';
}

    private function requireAdmin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
            exit;
        }
    }

    // Quản lý danh sách & Cập nhật trạng thái đơn hàng
    public function updateStatus()
{
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        header("Location: " . BASE_URL . "admin/orders");
        exit;
    }
    $orderId = (int)($_POST['order_id'] ?? 0);
    $status = $_POST['status'] ?? 'pending';
    if ($orderId <= 0) {
        die("Thiếu Order ID");
    }
    $this->orderModel->updateOrderStatus($orderId, $status);
    $payment = $this->orderModel->getPaymentByOrderId($orderId);
    if ($payment) {
        if ($status == 'completed' || $status == 'delivered') {
            $this->orderModel->updatePaymentStatus(
                $payment['id'],
                'paid'
            );

        }

    }

    header("Location: " . BASE_URL . "admin/orders");
    exit;
}

    // Thống kê báo cáo doanh thu theo khoảng ngày
    public function revenueReport() {
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

        $revenueData = $this->reportModel->getRevenueByPeriod($startDate, $endDate);
        require_once __DIR__ . '/../../Views/admin/reports/revenue.php';
    }

    // Thống kê top 10 sản phẩm bán chạy nhất hệ thống
    public function productsReport() {
        $topProducts = $this->reportModel->getTopSellingProducts(10);
        require_once __DIR__ . '/../../Views/admin/reports/products.php';
    }
}