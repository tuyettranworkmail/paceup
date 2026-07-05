<?php

namespace App\Controller;

use App\Models\Coupons;
use App\Models\Order;

class CheckoutController
{
    private $couponModel;
    private $orderModel;

    public function __construct()
    {
        $this->couponModel = new Coupons();
        $this->orderModel = new Order();
    }

    // Hiển thị trang checkout
    public function index()
   {
    require __DIR__ . '/../Views/checkout.php';
   }

   public function success()
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    require __DIR__ . '/../Views/checkout-success.php';
}

    // Xử lý đặt hàng
   public function processOrder()
{
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        header("Location: " . BASE_URL . "checkout");
        exit;
    }

    if (empty($_SESSION['cart'])) {
        die("Giỏ hàng trống.");
    }

    $orderData = [

        'order_code' => $this->orderModel->generateUniqueOrderCode(),
        'user_id' => $_SESSION['user_info']['id'] ?? null,
        'total_amount' => $_POST['total_amount'],
        'coupon_id' => !empty($_POST['coupon_id']) ? $_POST['coupon_id'] : null,
        'final_amount' => $_POST['total_amount'],
        'shipping_name' => $_POST['full_name'],
        'shipping_phone' => $_POST['phone'],
        'shipping_address' => $_POST['address'],
        'shipping_email' => $_POST['email'],
        'status' => 'pending',
        'created_at' => date('Y-m-d H:i:s')

    ];

   $result = $this->orderModel->placeOrder(
    $orderData,
    $_SESSION['cart']
    );
    if (!$result['success']) {
      die($result['message']);
    }

$orderId = $result['order_id'];

$paymentMethod = $_POST['payment_method'];

if ($paymentMethod == "bank" || $paymentMethod == "momo") {

    $amount = (float)$orderData['final_amount'];
    $orderCode = $orderData['order_code'];

    require __DIR__ . '/../Views/partials/payment_qr.php';
    exit;
}

/* COD mới xóa giỏ */
unset($_SESSION['cart']);

header("Location: " . BASE_URL . "checkout-success");
exit;

    header("Location: " . BASE_URL . "checkout-success");

    exit;
}
}