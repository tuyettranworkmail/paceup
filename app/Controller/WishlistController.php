<?php
namespace App\Controller;

class WishlistController {
    public function index() {
        require __DIR__ . '/../Views/wishlist.php';
    }
    public function toggle() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
            $productId = $_POST['product_id'];

            if (!isset($_SESSION['wishlist'])) {
                $_SESSION['wishlist'] = [];
            }

            if (isset($_SESSION['wishlist'][$productId])) {
                unset($_SESSION['wishlist'][$productId]); // Có rồi thì bỏ thích
            } else {
                $_SESSION['wishlist'][$productId] = [
                    'id' => $productId,
                    'name' => $_POST['product_name'] ?? 'Sản phẩm yêu thích',
                    'price' => $_POST['product_price'] ?? 0,
                    'image' => $_POST['product_image'] ?? ''
                ]; // Chưa có thì thêm vào
            }
        }
        header("Location: " . BASE_URL . "wishlist");
        exit();
    }

}