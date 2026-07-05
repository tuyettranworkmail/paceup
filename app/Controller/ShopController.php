<?php
namespace App\Controller;

use App\Models\Product;

class ShopController {
    public function index() {
        $productModel = new Product();
        $gender = isset($_GET['gender']) ? $_GET['gender'] : 'all';
        $category = $_GET['category'] ?? 'all';
        $sort = $_GET['sort'] ?? 'default';
        $priceRange = $_GET['price'] ?? 'all';

        $products = $productModel->getProductsByFilter([
            'gender' => $gender,
            'category' => $category,
            'price' => $priceRange,
            'sort' => $sort
        ]);

        $categories = $productModel->getActiveCategories();

        require __DIR__ . '/../Views/shop.php';
    }

}