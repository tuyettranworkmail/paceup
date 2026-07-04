<?php
namespace App\Controller;

class AccountController {
    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: login');
            exit;
        }

        require_once __DIR__ . '/../../config/db.php';
        
        $user_id = $_SESSION['user_id'];
        
        // Đảm bảo có cột avatar
        try {
            $pdo->exec("ALTER TABLE user ADD COLUMN avatar VARCHAR(255) DEFAULT NULL");
        } catch (\PDOException $e) { }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'update_avatar' && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../public/uploads/avatars/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                // Get extension
                $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                $fileName = time() . '_' . uniqid() . '.' . $ext;
                $targetFile = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile)) {
                    $avatarPath = 'public/uploads/avatars/' . $fileName;
                    $stmt = $pdo->prepare("UPDATE user SET avatar = ? WHERE id = ?");
                    $stmt->execute([$avatarPath, $user_id]);
                    $_SESSION['user_avatar'] = $avatarPath;
                }
                header('Location: account?tab=account&success=1');
                exit;
            } elseif ($action === 'update_account') {
                // Future update logic
            }
        }
        
        // Lấy thông tin user
        $stmt = $pdo->prepare("SELECT * FROM user WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Fetch user addresses if any
        $stmt_addr = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ?");
        $stmt_addr->execute([$user_id]);
        $addresses = $stmt_addr->fetchAll(\PDO::FETCH_ASSOC);
        
        // Fetch orders if any
        $stmt_orders = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
        $stmt_orders->execute([$user_id]);
        $orders = $stmt_orders->fetchAll(\PDO::FETCH_ASSOC);

        require __DIR__ . '/../Views/account.php';
    }
}
