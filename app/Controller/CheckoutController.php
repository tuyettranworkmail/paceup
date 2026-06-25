<?php
namespace App\Controller;

use App\Models\Coupons;

class CheckoutController {
    public function index() {
        require __DIR__ . '/../Views/checkout.php';
    }

    public function success() {
        require __DIR__ . '/../Views/checkout-success.php';
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
