<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role'];
        
        if ($user['role'] === 'admin') {
            header('Location: admin.php');
        } else {
            header('Location: index.php');
        }
        exit;
    } else {
        $error = 'Email hoặc mật khẩu không đúng.';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - PaceUp</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Inter:wght@400;500;600&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>

<main class="auth-page">
    <div class="auth-form-wrapper">
        <div class="auth-form">
            <div style="text-align: center; margin-bottom: 2rem;">
                <h2>Welcome</h2>
                <p class="subtitle">Please enter your details.</p>
            </div>
            
            <?php if ($error): ?>
                <div style="background: #fee; color: #c00; padding: 10px; border-radius: 8px; margin-bottom: 1rem; text-align: center; font-size: 14px;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form action="<?= BASE_URL ?>login" method="POST">
                <div class="form-group">
                    <input type="text" id="email" name="email" placeholder="Email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <input type="password" id="password" name="password" placeholder="Password" required>
                </div>
                
                <div class="auth-options">
                    <label>
                        <input type="checkbox" name="remember"> Remember for 30 days
                    </label>
                    <a href="#">Forgot password?</a>
                </div>

                <button type="submit" class="btn-login">Login</button>

                <p style="text-align: center; margin-top: 1.5rem; font-size: 14px; color: #888;">
                    Don't have an account? <a href="<?= BASE_URL ?>register" style="color: #000; font-weight: 600;">Sign up</a>
                </p>
            </form>
        </div>
    </div>
    <div class="auth-image"></div>
</main>

</body>
</html>