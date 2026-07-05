<?php
session_start();

// Define Base URL to handle subdirectories in XAMPP
$baseDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
define('BASE_URL', rtrim($baseDir, '/') . '/');

// Simple Autoloader
spl_autoload_register(function ($class) {
    $class = str_replace('App\\', 'app/', $class);
    $class = str_replace('\\', '/', $class);
    $file = __DIR__ . '/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

use App\Core\Router;

$router = new Router();

// Define Routes
$router->add('/', 'HomeController', 'index');
$router->add('/shop', 'ShopController', 'index');
$router->add('/product', 'ProductController', 'show');
$router->add('/cart', 'CartController', 'index');
$router->add('/cart/add', 'CartController', 'add');
$router->add('/cart/update', 'CartController', 'update');
$router->add('/cart/delete', 'CartController', 'delete');
$router->add('/cart/clear', 'CartController', 'clear');
$router->add('/wishlist', 'WishlistController', 'index');
$router->add('/checkout', 'CheckoutController', 'index');
$router->add('/checkout-success', 'CheckoutController', 'success');
$router->add('/login', 'AuthController', 'login');
$router->add('/register', 'AuthController', 'register');
$router->add('/logout', 'AuthController', 'logout');
$router->add('/admin', 'AdminController', 'index');
$router->add('/admin/products', 'Admin\ProductController', 'index');
$router->add('/admin/products/create', 'Admin\ProductController', 'create');
$router->add('/admin/products/edit', 'Admin\ProductController', 'edit');
$router->add('/admin/products/delete', 'Admin\ProductController', 'delete');
$router->add('/admin/products/variants/add', 'Admin\ProductController', 'addVariant');
$router->add('/admin/products/variants/update', 'Admin\ProductController', 'updateVariant');
$router->add('/admin/products/variants/delete', 'Admin\ProductController', 'deleteVariant');
$router->add('/admin/products/images/primary', 'Admin\ProductController', 'setPrimaryImage');
$router->add('/admin/products/images/delete', 'Admin\ProductController', 'deleteImage');
$router->add('/admin/categories', 'Admin\CategoryController', 'index');
$router->add('/admin/categories/create', 'Admin\CategoryController', 'create');
$router->add('/admin/categories/edit', 'Admin\CategoryController', 'edit');
$router->add('/admin/categories/delete', 'Admin\CategoryController', 'delete');
$router->add('/admin/inventory', 'Admin\InventoryController', 'index');
$router->add('/admin/inventory/update', 'Admin\InventoryController', 'update');
$router->add('/admin/dashboard', 'Admin\DashboardController', 'index');
$router->add('/admin/orders', 'Admin\OrderController', 'index');
$router->add('/admin/orders/update-status', 'Admin\OrderController', 'updateStatus');
$router->add('/admin/reports/revenue', 'Admin\OrderController', 'revenueReport');
$router->add('/admin/reports/products', 'Admin\OrderController', 'productsReport');
$router->add('/my-orders', 'OrderController', 'myOrders');
$router->add('/checkout/process', 'CheckoutController', 'processOrder');
$router->add('/checkout/apply-coupon', 'CheckoutController', 'applyCoupon');
$router->add('/payment/add', 'PaymentController', 'add');


$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$basePath = parse_url(BASE_URL, PHP_URL_PATH);

if (strpos($url, $basePath) === 0) {
    $url = substr($url, strlen($basePath));
}

$url = '/' . ltrim($url, '/');

// Redirect logged in users away from auth pages
if (isset($_SESSION['user_id']) && ($url === '/login' || $url === '/register')) {
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: ' . BASE_URL . 'admin');
    } else {
        header('Location: ' . BASE_URL);
    }
    exit;
}

$router->dispatch($url);
