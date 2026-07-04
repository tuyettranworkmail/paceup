<?php
require_once __DIR__ . '/../_helpers.php';
adminStart('Reviews', 'reviews', $flash ?? null);
?>

<div class="admin-panel">
    <form method="get" action="<?= BASE_URL ?>admin/reviews" class="admin-actions">
        <select name="status">
            <option value="" <?= ($_GET['status'] ?? '') === '' ? 'selected' : '' ?>>All</option>
            <option value="1" <?= (string)($_GET['status'] ?? '') === '1' ? 'selected' : '' ?>>Approved</option>
            <option value="0" <?= (string)($_GET['status'] ?? '') === '0' ? 'selected' : '' ?>>Hidden</option>
        </select>
        <button class="admin-btn" type="submit">Filter</button>
    </form>
</div>

<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Product</th>
            <th>User</th>
            <th>Rating</th>
            <th>Comment</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($reviews as $review): ?>
            <tr>
                <td><?= (int)$review['id'] ?></td>
                <td><?= adminE($review['product_name']) ?></td>
                <td><?= adminE($review['user_name'] ?? 'Guest') ?></td>
                <td><?= (int)$review['rating'] ?>/5</td>
                <td><?= adminE($review['comment']) ?></td>
                <td>
                    <span class="admin-badge <?= (int)$review['status'] === 1 ? 'ok' : 'off' ?>">
                        <?= (int)$review['status'] === 1 ? 'Approved' : 'Hidden' ?>
                    </span>
                </td>
                <td>
                    <div class="admin-actions">
                        <form method="post" action="<?= BASE_URL ?>admin/reviews/approve">
                            <input type="hidden" name="id" value="<?= (int)$review['id'] ?>">
                            <button class="admin-btn ok" type="submit">Approve</button>
                        </form>
                        <form method="post" action="<?= BASE_URL ?>admin/reviews/hide">
                            <input type="hidden" name="id" value="<?= (int)$review['id'] ?>">
                            <button class="admin-btn light" type="submit">Hide</button>
                        </form>
                        <form method="post" action="<?= BASE_URL ?>admin/reviews/delete" onsubmit="return confirm('Delete this review?')">
                            <input type="hidden" name="id" value="<?= (int)$review['id'] ?>">
                            <button class="admin-btn danger" type="submit">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($reviews)): ?>
            <tr><td colspan="7">No reviews found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php adminEnd(); ?>
