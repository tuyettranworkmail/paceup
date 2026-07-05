<?php
namespace App\Controller;

class AdminController {
    public function index() {
        require __DIR__ . '/../Views/admin.php';
    }
    public function dashboard() {
    $bestSellers = $this->orderModel->getBestSellingProducts(5);
    require __DIR__ . '/../Views/admin/dashboard.php';
}

}