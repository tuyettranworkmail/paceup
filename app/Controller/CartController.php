<?php
namespace App\Controller;

use App\Models\Product; 

class CartController {
    
    // 1. Hiển thị trang giỏ hàng (Chỉ giữ lại 1 hàm index duy nhất!)
    public function index() {
        $cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        
        require_once __DIR__ . '/../Views/cart.php';
    }

    // Thêm sản phẩm vào giỏ hàng
    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = $_POST['product_id'] ?? null;
            $quantity = intval($_POST['quantity'] ?? 1);
            $productName = $_POST['product_name'] ?? '';
            $productPrice = floatval($_POST['product_price'] ?? 0);
            $productImage = $_POST['product_image'] ?? '';
            $variantId = $_POST['variant_id'] ?? null;

            if ($productId) {
                if (!isset($_SESSION['cart'])) {
                    $_SESSION['cart'] = [];
                }

                // Nếu sản phẩm đã có trong giỏ thì tăng số lượng
                if (isset($_SESSION['cart'][$variantId])) {
                    $_SESSION['cart'][$variantId]['quantity'] += $quantity;
                } else {
                    // Nếu chưa có thì thêm mới vào mảng
                    $_SESSION['cart'][$variantId] = [
                        'product_id' => $productId,
                        'variant_id' => $variantId,
                        'name' => $productName,
                        'price' => $productPrice,
                        'image' => $productImage,
                        'quantity' => $quantity
                    ];
                }
            }
        }
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL));
        exit();
    }

    // Cập nhật số lượng sản phẩm trong giỏ hàng
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantities'])) {
            foreach ($_POST['quantities'] as $productId => $quantity) {
                $quantity = intval($quantity);
                if ($quantity <= 0) {
                    unset($_SESSION['cart'][$productId]);
                } else if (isset($_SESSION['cart'][$productId])) {
                    $_SESSION['cart'][$productId]['quantity'] = $quantity;
                }
            }
        }
        header('Location: ' . BASE_URL . 'cart');
        exit();
    }

    // Xóa một sản phẩm khỏi giỏ hàng
    public function delete($productId = null) {
        $id = $productId ?? $_POST['product_id'] ?? null;
        if ($id && isset($_SESSION['cart'][$id])) {
            unset($_SESSION['cart'][$id]);
        }
        header('Location: ' . BASE_URL . 'cart');
        exit();
    }

    // Xóa sạch toàn bộ giỏ hàng
    public function clear() {
        unset($_SESSION['cart']);
        header('Location: ' . BASE_URL . 'cart');
        exit();
    }
}