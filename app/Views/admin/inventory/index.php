<?php
require_once __DIR__ . '/../_helpers.php';
adminStart('Inventory', 'inventory', $flash ?? null);
?>

<form class="admin-panel admin-grid" method="post" action="<?= BASE_URL ?>admin/inventory/update">
    <div class="admin-field">
        <label>Variant</label>
        <select name="variant_id" required>
            <option value="">Choose variant</option>
            <?php foreach ($variants as $variant): ?>
                <option value="<?= (int)$variant['id'] ?>">
                    #<?= (int)$variant['id'] ?> - <?= adminE($variant['product_name']) ?> / <?= adminE($variant['size']) ?> / <?= adminE($variant['color']) ?> (stock: <?= (int)$variant['stock_quantity'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="admin-field">
        <label>Type</label>
        <select name="change_type">
            <option value="in">Import stock</option>
            <option value="out">Export stock</option>
        </select>
    </div>
    <div class="admin-field">
        <label>Quantity</label>
        <input type="number" name="quantity" min="1" required>
    </div>
    <div class="admin-field">
        <label>Reason</label>
        <input type="text" name="reason" placeholder="Manual update">
    </div>
    <div class="admin-field" style="align-self:end;">
        <button class="admin-btn" type="submit">Update stock</button>
    </div>
</form>

<section class="admin-panel">
    <h2>Stock overview</h2>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Variant</th>
                <th>Product</th>
                <th>Category</th>
                <th>Size</th>
                <th>Color</th>
                <th>Stock</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($variants as $variant): ?>
                <tr>
                    <td>#<?= (int)$variant['id'] ?></td>
                    <td><?= adminE($variant['product_name']) ?></td>
                    <td><?= adminE($variant['category_name']) ?></td>
                    <td><?= adminE($variant['size']) ?></td>
                    <td><?= adminE($variant['color']) ?></td>
                    <td>
                        <span class="admin-badge <?= (int)$variant['stock_quantity'] > 0 ? 'ok' : 'off' ?>">
                            <?= (int)$variant['stock_quantity'] ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($variants)): ?>
                <tr><td colspan="6">No variants yet. Add variants from product edit page first.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<section class="admin-panel">
    <h2>Inventory logs</h2>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Product</th>
                <th>Variant</th>
                <th>Change</th>
                <th>Reason</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= (int)$log['id'] ?></td>
                    <td><?= adminE($log['product_name']) ?></td>
                    <td><?= adminE($log['size']) ?> / <?= adminE($log['color']) ?></td>
                    <td><?= (int)$log['quantity_changed'] ?></td>
                    <td><?= adminE($log['reason']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($logs)): ?>
                <tr><td colspan="5">No inventory logs yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<?php adminEnd(); ?>
