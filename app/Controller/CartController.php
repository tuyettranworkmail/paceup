<?php
namespace App\Controller;

use App\Middleware\AuthMiddleware;

class CartController {
    public function index() {
        AuthMiddleware::requireUser();
        require __DIR__ . '/../Views/cart.php';
    }

}