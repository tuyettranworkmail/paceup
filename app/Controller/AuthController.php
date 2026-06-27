<?php
namespace App\Controller;

use App\Helpers\SessionHelper;
use App\Middleware\AuthMiddleware;
use App\Models\UserModel;
use App\Services\LoggingService;

class AuthController {
    public function login() {
        if (isset($_SESSION['user_id'])) {
            SessionHelper::redirect('/');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if ($email === '' || $password === '') {
                SessionHelper::setFlash('error', 'Vui lòng nhập email và mật khẩu');
                SessionHelper::redirect('/login');
            }

            $userModel = new UserModel();
            $user = $userModel->findByEmail($email);

            if (!$user) {
                SessionHelper::setFlash('error', 'Email không tồn tại');
                SessionHelper::redirect('/login');
            }

            if ((int) $user['status'] === 0) {
                SessionHelper::setFlash('error', 'Tài khoản đã bị khóa');
                SessionHelper::redirect('/login');
            }

            if (!password_verify($password, $user['password'])) {
                SessionHelper::setFlash('error', 'Mật khẩu không đúng');
                SessionHelper::redirect('/login');
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['full_name'];

            LoggingService::write($user['id'], 'login', 'Đăng nhập thành công');

            if ($user['role'] === 'admin') {
                SessionHelper::redirect('/admin');
            }

            SessionHelper::redirect('/');
        }

        require __DIR__ . '/../Views/login.php';
    }

    public function register() {
        if (isset($_SESSION['user_id'])) {
            SessionHelper::redirect('/');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullName = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $confirmPassword = trim($_POST['confirm_password'] ?? '');
            $phone = trim($_POST['phone'] ?? '');

            if ($fullName === '' || $email === '' || $password === '' || $confirmPassword === '') {
                SessionHelper::setFlash('error', 'Vui lòng nhập đầy đủ thông tin');
                SessionHelper::redirect('/register');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                SessionHelper::setFlash('error', 'Email không hợp lệ');
                SessionHelper::redirect('/register');
            }

            if (strlen($password) < 8) {
                SessionHelper::setFlash('error', 'Mật khẩu phải có ít nhất 8 ký tự');
                SessionHelper::redirect('/register');
            }

            if ($password !== $confirmPassword) {
                SessionHelper::setFlash('error', 'Mật khẩu xác nhận không khớp');
                SessionHelper::redirect('/register');
            }

            $userModel = new UserModel();

            if ($userModel->findByEmail($email)) {
                SessionHelper::setFlash('error', 'Email đã được sử dụng');
                SessionHelper::redirect('/register');
            }

            $newId = $userModel->create([
                'full_name' => $fullName,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'phone' => $phone
            ]);

            LoggingService::write($newId, 'register', 'Đăng ký tài khoản mới');
            SessionHelper::setFlash('success', 'Đăng ký thành công, vui lòng đăng nhập');
            SessionHelper::redirect('/login');
        }

        require __DIR__ . '/../Views/register.php';
    }

    public function logout() {
        if (isset($_SESSION['user_id'])) {
            LoggingService::write($_SESSION['user_id'], 'logout', 'Đăng xuất');
        }

        session_destroy();
        SessionHelper::redirect('/login');
    }

    public function changePassword() {
        AuthMiddleware::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            SessionHelper::redirect('/account');
        }

        $currentPassword = trim($_POST['current_password'] ?? '');
        $newPassword = trim($_POST['new_password'] ?? '');
        $confirmNewPassword = trim($_POST['confirm_new_password'] ?? '');

        $userModel = new UserModel();
        $user = $userModel->findById($_SESSION['user_id']);

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            SessionHelper::setFlash('error', 'Mật khẩu hiện tại không đúng');
            SessionHelper::redirect('/account');
        }

        if (strlen($newPassword) < 8) {
            SessionHelper::setFlash('error', 'Mật khẩu mới phải có ít nhất 8 ký tự');
            SessionHelper::redirect('/account');
        }

        if ($newPassword !== $confirmNewPassword) {
            SessionHelper::setFlash('error', 'Mật khẩu mới xác nhận không khớp');
            SessionHelper::redirect('/account');
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $userModel->updatePassword($_SESSION['user_id'], $hash);

        LoggingService::write($_SESSION['user_id'], 'change_password', 'Đổi mật khẩu thành công');
        SessionHelper::setFlash('success', 'Đổi mật khẩu thành công');
        SessionHelper::redirect('/account');
    }

}
