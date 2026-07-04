<?php

namespace App\Controller\Admin;

use App\Models\Review;

class ReviewController {
    private $reviewModel;

    public function __construct() {
        $this->requireAdmin();
        $this->reviewModel = new Review();
    }

    public function index() {
        $status = $_GET['status'] ?? '';
        $reviews = $this->reviewModel->getAllReviews($status);
        $flash = $this->pullFlash();
        require __DIR__ . '/../../Views/admin/reviews/index.php';
    }

    public function approve() {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->reviewModel->approve($id);
            $this->setFlash('success', 'Review approved.');
        }
        $this->redirect('admin/reviews');
    }

    public function hide() {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->reviewModel->hide($id);
            $this->setFlash('success', 'Review hidden.');
        }
        $this->redirect('admin/reviews');
    }

    public function delete() {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->reviewModel->delete($id);
            $this->setFlash('success', 'Review deleted.');
        }
        $this->redirect('admin/reviews');
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
