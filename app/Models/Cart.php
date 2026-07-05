<?php

namespace App\Models;

use PDO;

class Cart extends BaseModel {
    public function __construct() {
        parent::__construct();
        // Xử lý logic cho các bảng: cart, wishlist
    }

    // --- CART ---
    public function createCartItem($data) { return $this->insert('cart', $data); }
    public function getCartItem($id) { return $this->getById('cart', $id); }
    public function updateCartQuantity($id, $quantity) { return $this->update('cart', $id, ['quantity' => $quantity]); }
    public function deleteCartItem($id) { return $this->delete('cart', $id); }

    public function getCartByUserId($userId) {
        $stmt = $this->db->prepare("SELECT * FROM cart WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCartBySessionId($sessionId) {
        $stmt = $this->db->prepare("SELECT * FROM cart WHERE session_id = :session_id");
        $stmt->execute(['session_id' => $sessionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Đếm tổng số lượng sản phẩm trong giỏ hàng (dùng cho badge trên navbar)
    public function countCartItems($userId = null, $sessionId = null) {
        if ($userId) {
            $stmt = $this->db->prepare("SELECT SUM(quantity) as total_quantity FROM cart WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $userId]);
        } elseif ($sessionId) {
            $stmt = $this->db->prepare("SELECT SUM(quantity) as total_quantity FROM cart WHERE session_id = :session_id");
            $stmt->execute(['session_id' => $sessionId]);
        } else {
            return 0;
        }

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total_quantity'] ? (int)$result['total_quantity'] : 0;
    }

    public function mergeGuestCartIntoUser($sessionId, $userId) {
        $guestCartItems = $this->getCartBySessionId($sessionId);
        
        if (empty($guestCartItems)) {
            return false;
        }

        foreach ($guestCartItems as $guestItem) {
            $variantId = $guestItem['variant_id'];
            $guestQuantity = $guestItem['quantity'];

            // Kiểm tra xem user đã có sản phẩm này trong giỏ hàng chưa
            $stmt = $this->db->prepare("SELECT * FROM cart WHERE user_id = :user_id AND variant_id = :variant_id");
            $stmt->execute(['user_id' => $userId, 'variant_id' => $variantId]);
            $userItem = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($userItem) {
                // Nếu đã có, cộng dồn số lượng
                $newQuantity = $userItem['quantity'] + $guestQuantity;
                $this->updateCartQuantity($userItem['id'], $newQuantity);
                // Xóa item của session
                $this->deleteCartItem($guestItem['id']);
            } else {
                // Nếu chưa có, chuyển item của session sang cho user
                $stmtUpdate = $this->db->prepare("UPDATE cart SET user_id = :user_id, session_id = NULL WHERE id = :id");
                $stmtUpdate->execute(['user_id' => $userId, 'id' => $guestItem['id']]);
            }
        }
        return true;
    }

    // Xóa toàn bộ giỏ hàng (sau khi đặt hàng thành công)
    public function clearCart($userId = null, $sessionId = null) {
        if ($userId) {
            $stmt = $this->db->prepare("DELETE FROM cart WHERE user_id = :user_id");
            return $stmt->execute(['user_id' => $userId]);
        } elseif ($sessionId) {
            $stmt = $this->db->prepare("DELETE FROM cart WHERE session_id = :session_id");
            return $stmt->execute(['session_id' => $sessionId]);
        }
        return false;
    }

    // --- WISHLIST ---
    public function createWishlistItem($data) { return $this->insert('wishlist', $data); }
    public function getWishlistItem($id) { return $this->getById('wishlist', $id); }
    public function deleteWishlistItem($id) { return $this->delete('wishlist', $id); }

    public function getWishlistByUser($userId) {
        $stmt = $this->db->prepare("SELECT * FROM wishlist WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
