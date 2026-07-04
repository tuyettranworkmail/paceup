<?php

if (!function_exists('adminE')) {
    function adminE($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('adminMoney')) {
    function adminMoney($value) {
        return number_format((float)$value, 0, ',', '.') . ' VND';
    }
}

if (!function_exists('adminImageUrl')) {
    function adminImageUrl($image) {
        $image = (string)$image;
        if ($image === '') {
            return '';
        }

        if (preg_match('/^uploads\//', $image)) {
            return BASE_URL . $image;
        }

        return BASE_URL . 'assets/images/' . $image;
    }
}

if (!function_exists('adminStart')) {
    function adminStart($title, $active, $flash = null) {
        include __DIR__ . '/../partials/header.php';
        ?>
        <style>
            .admin-shell { display: grid; grid-template-columns: 230px 1fr; min-height: calc(100vh - 80px); background: #f6f7f9; }
            .admin-side { background: #111; padding: 1.5rem 1rem; }
            .admin-side a { display: block; color: #ddd; text-decoration: none; padding: .75rem 1rem; border-radius: 6px; margin-bottom: .35rem; font-family: var(--font-ui); font-weight: 600; }
            .admin-side a.active, .admin-side a:hover { background: #fff; color: #111; }
            .admin-main { padding: 2rem; }
            .admin-title { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem; }
            .admin-title h1 { font-family: var(--font-ui); font-size: 1.8rem; margin: 0; }
            .admin-panel { background: #fff; border: 1px solid #e8e8e8; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; }
            .admin-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: .75rem; }
            .admin-field label { display: block; font: 600 .85rem var(--font-ui); margin-bottom: .35rem; color: #333; }
            .admin-field input, .admin-field select, .admin-field textarea { width: 100%; padding: .7rem; border: 1px solid #d8d8d8; border-radius: 6px; font-family: var(--font-ui); }
            .admin-table { width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #e8e8e8; }
            .admin-table th, .admin-table td { padding: .85rem; border-bottom: 1px solid #eee; text-align: left; vertical-align: middle; font-family: var(--font-ui); font-size: .92rem; }
            .admin-table th { background: #fafafa; font-size: .78rem; text-transform: uppercase; color: #555; }
            .admin-actions { display: flex; flex-wrap: wrap; gap: .45rem; align-items: center; }
            .admin-btn { display: inline-flex; align-items: center; justify-content: center; border: 0; border-radius: 6px; padding: .65rem .9rem; background: #111; color: #fff; text-decoration: none; font: 700 .86rem var(--font-ui); cursor: pointer; }
            .admin-btn.light { background: #eee; color: #111; }
            .admin-btn.danger { background: #d73535; color: #fff; }
            .admin-btn.ok { background: #17803d; color: #fff; }
            .admin-badge { display: inline-flex; padding: .25rem .55rem; border-radius: 999px; background: #eee; font: 700 .78rem var(--font-ui); }
            .admin-badge.ok { background: #e7f6ec; color: #17803d; }
            .admin-badge.off { background: #feecec; color: #b3261e; }
            .admin-flash { margin-bottom: 1rem; padding: .8rem 1rem; border-radius: 6px; font-family: var(--font-ui); background: #eef6ff; color: #164b7a; }
            .admin-flash.error { background: #feecec; color: #9d1c1c; }
            .admin-flash.success { background: #e7f6ec; color: #176b35; }
            .admin-thumb { width: 64px; height: 64px; object-fit: contain; background: #f3f3f3; border-radius: 6px; border: 1px solid #eee; }
            @media (max-width: 800px) { .admin-shell { grid-template-columns: 1fr; } .admin-side { display: flex; overflow-x: auto; gap: .35rem; } .admin-side a { white-space: nowrap; } }
        </style>
        <div class="admin-shell">
            <aside class="admin-side">
                <a href="<?= BASE_URL ?>admin">Dashboard</a>
                <a href="<?= BASE_URL ?>admin/products" class="<?= $active === 'products' ? 'active' : '' ?>">Products</a>
                <a href="<?= BASE_URL ?>admin/categories" class="<?= $active === 'categories' ? 'active' : '' ?>">Categories</a>
                <a href="<?= BASE_URL ?>admin/inventory" class="<?= $active === 'inventory' ? 'active' : '' ?>">Inventory</a>
                <a href="<?= BASE_URL ?>admin/reviews" class="<?= $active === 'reviews' ? 'active' : '' ?>">Reviews</a>
            </aside>
            <main class="admin-main">
                <div class="admin-title">
                    <h1><?= adminE($title) ?></h1>
                </div>
                <?php if ($flash): ?>
                    <div class="admin-flash <?= adminE($flash['type'] ?? '') ?>"><?= adminE($flash['message'] ?? '') ?></div>
                <?php endif; ?>
        <?php
    }
}

if (!function_exists('adminEnd')) {
    function adminEnd() {
        ?>
            </main>
        </div>
        <?php include __DIR__ . '/../partials/footer.php';
    }
}
