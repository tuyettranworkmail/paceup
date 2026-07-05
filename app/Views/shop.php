<?php include __DIR__ . '/partials/header.php'; ?>

<?php
$gender = isset($_GET['gender']) ? $_GET['gender'] : 'all';
$genderLabel = 'Tat ca san pham';
if ($gender === 'men') $genderLabel = 'San pham Nam';
if ($gender === 'women') $genderLabel = 'San pham Nu';

$category = $_GET['category'] ?? 'all';
$sort = $_GET['sort'] ?? 'default';
$priceRange = $_GET['price'] ?? 'all';

function shopUrl(array $overrides): string {
    $params = array_merge($_GET, $overrides);
    return BASE_URL . 'shop?' . http_build_query($params);
}

function productAssetPath($image): string {
    $image = (string)$image;
    if ($image === '') return '';
    return str_starts_with($image, 'uploads/') ? $image : 'assets/images/' . $image;
}
?>

<main>
    <section class="shop-page">
        <div class="shop-topbar">
            <h1><?= htmlspecialchars($genderLabel) ?> (<?= count($products) ?>)</h1>
            <div class="shop-sort">
                <form method="get" action="<?= BASE_URL ?>shop" id="sortForm">
                    <input type="hidden" name="gender" value="<?= htmlspecialchars($gender) ?>">
                    <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
                    <input type="hidden" name="price" value="<?= htmlspecialchars($priceRange) ?>">
                    <label for="sortSel">Sap xep</label>
                    <select name="sort" id="sortSel" onchange="this.form.submit()">
                        <option value="default" <?= $sort === 'default' ? 'selected' : '' ?>>Mac dinh</option>
                        <option value="price-asc" <?= $sort === 'price-asc' ? 'selected' : '' ?>>Gia: Thap den cao</option>
                        <option value="price-desc" <?= $sort === 'price-desc' ? 'selected' : '' ?>>Gia: Cao den thap</option>
                        <option value="name-asc" <?= $sort === 'name-asc' ? 'selected' : '' ?>>Ten: A den Z</option>
                    </select>
                </form>
            </div>
        </div>

        <div class="shop-layout">
            <aside class="shop-sidebar">
                <ul class="filter-cat-list">
                    <li><a href="<?= htmlspecialchars(shopUrl(['category' => 'all'])) ?>" class="<?= $category === 'all' ? 'active' : '' ?>">Tat ca</a></li>
                    <?php foreach ($categories as $c): ?>
                        <li><a href="<?= htmlspecialchars(shopUrl(['category' => $c['name']])) ?>" class="<?= $category === $c['name'] ? 'active' : '' ?>"><?= htmlspecialchars($c['name']) ?></a></li>
                    <?php endforeach; ?>
                </ul>

                <details class="filter-group" <?= $gender !== 'all' ? 'open' : '' ?>>
                    <summary>Gioi tinh</summary>
                    <ul>
                        <li><a href="<?= htmlspecialchars(shopUrl(['gender' => 'all'])) ?>" class="<?= $gender === 'all' ? 'active' : '' ?>">Tat ca</a></li>
                        <li><a href="<?= htmlspecialchars(shopUrl(['gender' => 'men'])) ?>" class="<?= $gender === 'men' ? 'active' : '' ?>">Nam</a></li>
                        <li><a href="<?= htmlspecialchars(shopUrl(['gender' => 'women'])) ?>" class="<?= $gender === 'women' ? 'active' : '' ?>">Nu</a></li>
                    </ul>
                </details>

                <details class="filter-group" <?= $priceRange !== 'all' ? 'open' : '' ?>>
                    <summary>Gia</summary>
                    <ul>
                        <li><a href="<?= htmlspecialchars(shopUrl(['price' => 'all'])) ?>" class="<?= $priceRange === 'all' ? 'active' : '' ?>">Tat ca</a></li>
                        <li><a href="<?= htmlspecialchars(shopUrl(['price' => 'lt3'])) ?>" class="<?= $priceRange === 'lt3' ? 'active' : '' ?>">Duoi 3.000.000 VND</a></li>
                        <li><a href="<?= htmlspecialchars(shopUrl(['price' => '3to5'])) ?>" class="<?= $priceRange === '3to5' ? 'active' : '' ?>">3.000.000 - 5.000.000 VND</a></li>
                        <li><a href="<?= htmlspecialchars(shopUrl(['price' => 'gt5'])) ?>" class="<?= $priceRange === 'gt5' ? 'active' : '' ?>">Tren 5.000.000 VND</a></li>
                    </ul>
                </details>
            </aside>

            <div class="shop-grid">
                <?php foreach ($products as $index => $product): ?>
                    <?php $imagePath = productAssetPath($product['image'] ?? ''); ?>
                    <div class="shop-product-card" data-index="<?= $index ?>">
                        <a href="<?= BASE_URL ?>product?id=<?= (int)$product['id'] ?>" class="product-img-wrapper">
                            <?php if ($imagePath !== ''): ?>
                                <img src="<?= BASE_URL . htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                            <?php endif; ?>
                            <div class="product-actions" onclick="event.preventDefault(); event.stopPropagation()">
                                <button class="btn-add-cart" onclick="addToCart('<?= htmlspecialchars(addslashes($product['name'])) ?>', <?= (float)$product['price'] ?>, '<?= htmlspecialchars($imagePath) ?>')">
                                    Them vao gio
                                </button>
                                <button class="btn-quick-view" onclick="openQuickView(<?= $index ?>)">Xem nhanh</button>
                            </div>
                        </a>
                        <a href="<?= BASE_URL ?>product?id=<?= (int)$product['id'] ?>" class="product-info">
                            <span class="product-name"><?= htmlspecialchars($product['name']) ?></span>
                            <span class="product-type"><?= htmlspecialchars($product['type'] ?? '') ?></span>
                            <span class="product-price"><?= number_format((float)$product['price'], 0, ',', '.') ?> VND</span>
                        </a>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($products)): ?>
                    <p class="shop-empty">Khong co san pham phu hop bo loc.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<div class="cart-overlay" id="cartOverlay" onclick="toggleCart()"></div>
<div class="cart-sidebar" id="cartSidebar">
    <div class="cart-sidebar-header">
        <h3>Gio hang (<span id="cartCount">0</span>)</h3>
        <button class="cart-close-btn" onclick="toggleCart()">x</button>
    </div>
    <div class="cart-items" id="cartItems"></div>
    <div class="cart-footer">
        <div class="cart-total">
            <span class="label">Tong cong</span>
            <span class="amount" id="cartTotal">0 VND</span>
        </div>
        <button class="btn-checkout" onclick="checkout()">Thanh toan</button>
    </div>
</div>

<div class="modal-overlay" id="modalOverlay" onclick="closeQuickView()">
    <div class="modal-content" onclick="event.stopPropagation()">
        <button class="modal-close" onclick="closeQuickView()">x</button>
        <div class="modal-img">
            <img id="modalImg" src="" alt="">
        </div>
        <div class="modal-details">
            <h2 id="modalName"></h2>
            <p class="modal-category" id="modalCategory"></p>
            <p class="modal-price" id="modalPrice"></p>
            <p class="modal-desc">San pham Nike chinh hang. Cam ket chat luong va bao hanh day du.</p>
            <div class="modal-size-select">
                <label>Chon size</label>
                <div class="size-options">
                    <?php foreach (['38','39','40','41','42','43','44'] as $size): ?>
                        <button class="size-btn" onclick="selectSize(this)"><?= $size ?></button>
                    <?php endforeach; ?>
                </div>
            </div>
            <button class="btn-add-cart-modal" id="modalAddBtn">Them vao gio hang</button>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
const productsData = <?= json_encode(array_values($products), JSON_UNESCAPED_UNICODE) ?>;
let cart = JSON.parse(localStorage.getItem('paceup_cart')) || [];

function productImagePath(image) {
    if (!image) return '';
    return image.startsWith('uploads/') ? image : 'assets/images/' + image;
}

function openQuickView(index) {
    const product = productsData[index];
    if (!product) return;

    document.getElementById('modalImg').src = BASE_URL + productImagePath(product.image || '');
    document.getElementById('modalImg').alt = product.name;
    document.getElementById('modalName').textContent = product.name;
    document.getElementById('modalCategory').textContent = product.type || '';
    document.getElementById('modalPrice').textContent = formatPrice(product.price);

    document.getElementById('modalAddBtn').onclick = () => {
        addToCart(product.name, product.price, productImagePath(product.image || ''));
        closeQuickView();
    };

    document.getElementById('modalOverlay').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeQuickView() {
    document.getElementById('modalOverlay').classList.remove('active');
    document.body.style.overflow = '';
}

function selectSize(btn) {
    document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
}

function saveCart() {
    localStorage.setItem('paceup_cart', JSON.stringify(cart));
    updateCartUI();
}

function addToCart(name, price, image) {
    const existing = cart.find(item => item.name === name);
    if (existing) existing.qty += 1;
    else cart.push({ name, price, image, qty: 1 });
    saveCart();
    showToast('Da them vao gio hang!');
    toggleCart(true);
}

function removeFromCart(index) {
    cart.splice(index, 1);
    saveCart();
}

function updateQty(index, delta) {
    cart[index].qty += delta;
    if (cart[index].qty <= 0) cart.splice(index, 1);
    saveCart();
}

function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN').format(price) + ' VND';
}

function updateCartUI() {
    const cartItems = document.getElementById('cartItems');
    const cartCount = document.getElementById('cartCount');
    const cartTotal = document.getElementById('cartTotal');
    const totalItems = cart.reduce((sum, item) => sum + item.qty, 0);
    const totalPrice = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);

    cartCount.textContent = totalItems;
    cartTotal.textContent = formatPrice(totalPrice);

    document.querySelectorAll('.cart-badge').forEach(b => {
        b.textContent = totalItems;
        b.style.display = totalItems > 0 ? 'flex' : 'none';
    });

    if (cart.length === 0) {
        cartItems.innerHTML = '<div class="cart-empty"><p>Gio hang trong</p></div>';
        return;
    }

    cartItems.innerHTML = cart.map((item, i) => `
        <div class="cart-item">
            <img src="${item.image.startsWith('http') ? item.image : BASE_URL + item.image}" alt="${item.name}">
            <div class="cart-item-info">
                <div class="item-name">${item.name}</div>
                <div class="item-price">${formatPrice(item.price)}</div>
                <div class="cart-item-qty">
                    <button onclick="updateQty(${i}, -1)">-</button>
                    <span>${item.qty}</span>
                    <button onclick="updateQty(${i}, 1)">+</button>
                </div>
            </div>
            <button class="cart-item-remove" onclick="removeFromCart(${i})">x</button>
        </div>
    `).join('');
}

function toggleCart(forceOpen) {
    const sidebar = document.getElementById('cartSidebar');
    const overlay = document.getElementById('cartOverlay');
    if (forceOpen === true) {
        sidebar.classList.add('active');
        overlay.classList.add('active');
    } else {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }
}

function showToast(message) {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 2500);
}

function checkout() {
    if (cart.length === 0) {
        showToast('Gio hang trong!');
        return;
    }
    window.location.href = BASE_URL + 'checkout';
}

document.addEventListener('DOMContentLoaded', () => {
    updateCartUI();
    const cartIcon = document.querySelector('a[href="<?= BASE_URL ?>cart"]');
    if (cartIcon) {
        cartIcon.addEventListener('click', (e) => {
            e.preventDefault();
            toggleCart();
        });
    }
});
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
