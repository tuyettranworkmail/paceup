<?php

namespace App\Controller\Admin;

use App\Models\Product;

class InventoryController {
    private $productModel;

    public function __construct() {
        $this->requireAdmin();
        $this->productModel = new Product();
    }

    public function index() {
        $variants = $this->productModel->getInventoryOverview();
        $logs = $this->productModel->getInventoryLogs(80);
        $flash = $this->pullFlash();
        require __DIR__ . '/../../Views/admin/inventory/index.php';
    }

    public function update() {
        $variantId = (int)($_POST['variant_id'] ?? 0);
        $quantity = abs((int)($_POST['quantity'] ?? 0));
        $type = $_POST['change_type'] ?? 'in';
        $reason = trim($_POST['reason'] ?? '');

        if ($variantId > 0 && $quantity > 0) {
            $quantityChanged = $type === 'out' ? -$quantity : $quantity;
            $this->productModel->updateStock($variantId, $quantityChanged, $reason ?: 'Manual inventory update');
            $this->setFlash('success', 'Inventory updated.');
        } else {
            $this->setFlash('error', 'Please choose a variant and quantity.');
        }

        $this->redirect('admin/inventory');
    }

    private function requireAdmin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
    }

    private function redirect($path) {
        header('Location: ' . BASE_URL . ltrim($path, '/'));
        exit;
    }

    private function setFlash($type, $message) {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    private function pullFlash() {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }
}
