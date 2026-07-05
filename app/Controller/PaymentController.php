<?php

namespace App\Controller;

class PaymentController
{
    public function add()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Xóa giỏ hàng sau khi khách xác nhận thanh toán
        unset($_SESSION['cart']);

        header("Location: " . BASE_URL . "checkout-success");
        exit;
    }
}