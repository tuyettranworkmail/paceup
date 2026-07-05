<?php
namespace App\Models;

use PDO; 

class Wishlist {
    private $db;

    public function __construct() {
        parent::__construct();
        $this->db = $conn;
    }

    // Lấy toàn bộ sản phẩm yêu thích của 1 User
    public function getWishlistByUser($userId) {
        $sql = "SELECT w.id as wishlist_id, p.* FROM wishlist w 
                JOIN products p ON w.product_id = p.id 
                WHERE w.user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Thêm sản phẩm vào danh sách yêu thích
    public function add($userId, $productId) {
        // Kiểm tra xem sản phẩm đã được thích trước đó chưa
        $sqlCheck = "SELECT id FROM wishlist WHERE user_id = :user_id AND product_id = :product_id";
        $stmtCheck = $this->db->prepare($sqlCheck);
        $stmtCheck->execute(['user_id' => $userId, 'product_id' => $productId]);
        
        if ($stmtCheck->rowCount() == 0) {
            $sql = "INSERT INTO wishlist (user_id, product_id, created_at) VALUES (:user_id, :product_id, NOW())";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['user_id' => $userId, 'product_id' => $productId]);
        }
        return false;
    }

    // Xóa sản phẩm khỏi danh sách yêu thích
    public function remove($userId, $productId) {
        $sql = "DELETE FROM wishlist WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['user_id' => $userId, 'product_id' => $productId]);
    }
}