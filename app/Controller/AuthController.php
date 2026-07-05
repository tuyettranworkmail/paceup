<?php
namespace App\Controller;

class AuthController {
    public function login() {
        require __DIR__ . '/../Views/login.php';
    }

    public function register() {
        require __DIR__ . '/../Views/register.php';
    }

    public function logout() {
        require __DIR__ . '/../Views/logout.php';
    }

}