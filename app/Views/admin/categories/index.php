<?php
require_once __DIR__ . '/../_helpers.php';
adminStart('Categories', 'categories', $flash ?? null);
?>

<form class="admin-panel admin-grid" method="post" action="<?= BASE_URL ?>admin/categories/create">
    <div class="admin-field">
        <label>Name</label>
        <input type="text" name="name" required>
    </div>
    <div class="admin-field">
        <label>Slug</label>
        <input type="text" name="slug" placeholder="Auto from name">
    </div>
    <div class="admin-field">
        <label>Status</label>
        <select name="status">
            <option value="1">Active</option>
            <option value="0">Hidden</option>
        </select>
    </div>
    <div class="admin-field" style="align-self:end;">
        <button class="admin-btn" type="submit">Add category</button>
    </div>
</form>

<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Slug</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($categories as $category): ?>
            <?php $categoryFormId = 'category-edit-' . (int)$category['id']; ?>
            <tr>
                <td><?= (int)$category['id'] ?></td>
                <td><input form="<?= $categoryFormId ?>" type="text" name="name" value="<?= adminE($category['name']) ?>" required></td>
                <td><input form="<?= $categoryFormId ?>" type="text" name="slug" value="<?= adminE($category['slug']) ?>"></td>
                <td>
                    <select form="<?= $categoryFormId ?>" name="status">
                        <option value="1" <?= (int)$category['status'] === 1 ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= (int)$category['status'] === 0 ? 'selected' : '' ?>>Hidden</option>
                    </select>
                </td>
                <td class="admin-actions">
                    <form id="<?= $categoryFormId ?>" method="post" action="<?= BASE_URL ?>admin/categories/edit">
                        <input type="hidden" name="id" value="<?= (int)$category['id'] ?>">
                    </form>
                    <button class="admin-btn light" form="<?= $categoryFormId ?>" type="submit">Save</button>
                    <form method="post" action="<?= BASE_URL ?>admin/categories/delete" onsubmit="return confirm('Hide this category?')">
                        <input type="hidden" name="id" value="<?= (int)$category['id'] ?>">
                        <button class="admin-btn danger" type="submit">Hide</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php adminEnd(); ?>
