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
$router->add('/wishlist', 'WishlistController', 'index');
$router->add('/checkout', 'CheckoutController', 'index');
$router->add('/checkout-success', 'CheckoutController', 'success');
$router->add('/login', 'AuthController', 'login');
$router->add('/register', 'AuthController', 'register');
$router->add('/logout', 'AuthController', 'logout');
$router->add('/admin', 'AdminController', 'index');
$router->add('/account', 'AccountController', 'index');
$router->add('/apply-coupon', 'CheckoutController', 'applyCoupon');

// Parse URL
$url = $_SERVER['REQUEST_URI'];
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