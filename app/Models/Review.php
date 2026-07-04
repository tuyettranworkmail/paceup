<?php

namespace App\Models;

use PDO;

class Review extends BaseModel {
    public function __construct() {
        parent::__construct();
    }

    public function getAllReviews($status = '') {
        $sql = "SELECT r.*, p.name AS product_name, u.full_name AS user_name
                FROM reviews r
                LEFT JOIN product p ON r.product_id = p.id
                LEFT JOIN user u ON r.user_id = u.id
                WHERE 1=1";
        $params = [];

        if ($status !== '') {
            $sql .= " AND r.status = :status";
            $params['status'] = (int)$status;
        }

        $sql .= " ORDER BY r.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByProduct($productId) {
        $stmt = $this->db->prepare("SELECT r.*, u.full_name AS user_name
                                    FROM reviews r
                                    LEFT JOIN user u ON r.user_id = u.id
                                    WHERE r.product_id = :product_id AND r.status = 1
                                    ORDER BY r.id DESC");
        $stmt->execute(['product_id' => $productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        return $this->insert('reviews', $data);
    }

    public function approve($id) {
        return $this->update('reviews', $id, ['status' => 1]);
    }

    public function hide($id) {
        return $this->update('reviews', $id, ['status' => 0]);
    }

    public function delete($id) {
        return parent::delete('reviews', $id);
    }
}
