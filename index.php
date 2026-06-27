<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
$router->add('/change-password', 'AuthController', 'changePassword');
$router->add('/forgot-password', 'AuthController', 'forgotPassword');
$router->add('/verify-otp', 'AuthController', 'verifyOtp');
$router->add('/reset-password', 'AuthController', 'resetPassword');
$router->add('/admin', 'AdminController', 'index');
$router->add('/apply-coupon', 'CheckoutController', 'applyCoupon');

// Parse URL
$url = $_SERVER['REQUEST_URI'];
$basePath = parse_url(BASE_URL, PHP_URL_PATH);
if (strpos($url, $basePath) === 0) {
    $url = substr($url, strlen($basePath));
}
$url = '/' . ltrim($url, '/');

$router->dispatch($url);
