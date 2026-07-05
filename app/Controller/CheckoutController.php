<?php
namespace App\Controller;

use App\Models\Coupons;
use App\Models\Order;

class CheckoutController {
    public function index() {
        require __DIR__ . '/../Views/checkout.php';
    }

    public function success() {
        require __DIR__ . '/../Views/checkout-success.php';
    }

    public function placeOrder() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để đặt hàng.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $items = $input['items'] ?? [];

        if (empty($items)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Giỏ hàng đang trống.']);
            return;
        }

        $shippingName = trim($input['shipping_name'] ?? '');
        $shippingPhone = trim($input['shipping_phone'] ?? '');
        $shippingAddress = trim($input['shipping_address'] ?? '');
        $shippingEmail = trim($input['shipping_email'] ?? '');
        $couponId = isset($input['coupon_id']) && $input['coupon_id'] !== '' ? (int) $input['coupon_id'] : null;
        $discount = max(0, (float) ($input['discount'] ?? 0));

        if ($shippingName === '' || $shippingPhone === '' || $shippingAddress === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin giao hàng.']);
            return;
        }

        $totalAmount = 0;
        foreach ($items as $item) {
            $quantity = max(0, (int) ($item['qty'] ?? 0));
            $price = max(0, (float) ($item['price'] ?? 0));
            $totalAmount += $quantity * $price;
        }

        if ($totalAmount <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Tổng đơn hàng không hợp lệ.']);
            return;
        }

        $finalAmount = max(0, $totalAmount - $discount);
        $orderModel = new Order();
        $orderId = $orderModel->createOrder([
            'order_code' => $orderModel->generateUniqueOrderCode(),
            'user_id' => $_SESSION['user_id'],
            'total_amount' => $totalAmount,
            'coupon_id' => $couponId,
            'final_amount' => $finalAmount,
            'shipping_name' => $shippingName,
            'shipping_phone' => $shippingPhone,
            'shipping_address' => $shippingAddress,
            'shipping_email' => $shippingEmail !== '' ? $shippingEmail : null,
            'status' => 'pending'
        ]);

        foreach ($items as $item) {
            $quantity = max(0, (int) ($item['qty'] ?? 0));
            $price = max(0, (float) ($item['price'] ?? 0));

            if ($quantity > 0 && $price >= 0) {
                $orderModel->createOrderItem([
                    'order_id' => $orderId,
                    'variant_id' => null,
                    'quantity' => $quantity,
                    'price_at_time' => $price
                ]);
            }
        }

        echo json_encode(['success' => true, 'order_id' => $orderId]);
    }

    public function applyCoupon() {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $code = trim($input['code'] ?? '');
        $orderTotal = floatval($input['order_total'] ?? 0);

        if (!$code) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã giảm giá.']);
            return;
        }

        $model = new Coupons();
        $result = $model->validateCoupon($code, $orderTotal);

        if (!$result['is_valid']) {
            echo json_encode(['success' => false, 'message' => $result['message']]);
            return;
        }

        $coupon = $result['data'];
        $discount = $orderTotal * ($coupon['discount_percent'] / 100);
        if ($coupon['max_discount'] && $discount > $coupon['max_discount']) {
            $discount = floatval($coupon['max_discount']);
        }

        echo json_encode([
            'success' => true,
            'discount' => $discount,
            'discount_percent' => floatval($coupon['discount_percent']),
            'code' => $coupon['code'],
            'coupon_id' => $coupon['id']
        ]);
    }
}
