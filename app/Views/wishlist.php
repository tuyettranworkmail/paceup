<?php include __DIR__ . '/partials/header.php'; ?>

<main style="min-height: 70vh; padding: 2rem; max-width: 1200px; margin: 0 auto; font-family: var(--font-body);">
    <h1 style="font-family: var(--font-ui); font-size: 2rem; margin-bottom: 2rem;">Danh sách yêu thích</h1>
    
    <div id="wishlist-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 2rem;">
        <!-- JS will populate this -->
    </div>
    
    <div id="wishlist-empty" style="display: none; text-align: center; padding: 5rem 0;">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 1rem; color: #ccc;"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
        <h2 style="font-family: var(--font-ui); margin-bottom: 1rem;">Danh sách yêu thích trống</h2>
        <p style="color: #666; margin-bottom: 2rem;">Bạn chưa lưu sản phẩm nào vào danh sách yêu thích.</p>
        <a href="<?= BASE_URL ?>shop" style="display: inline-block; padding: 1rem 2rem; background: #111; color: #fff; text-decoration: none; border-radius: 100px; font-weight: 500;">Tiếp tục mua sắm</a>
    </div>
</main>

<!-- Toast -->
<div class="toast" id="toast"></div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    renderWishlist();
});

function renderWishlist() {
    const container = document.getElementById('wishlist-container');
    const emptyMsg = document.getElementById('wishlist-empty');
    let favourites = JSON.parse(localStorage.getItem('paceup_favs')) || [];
    
    if (favourites.length === 0) {
        container.style.display = 'none';
        emptyMsg.style.display = 'block';
        return;
    }
    
    container.style.display = 'grid';
    emptyMsg.style.display = 'none';
    
    container.innerHTML = favourites.map((item, index) => `
        <div style="border: 1px solid #eee; border-radius: 8px; overflow: hidden; display: flex; flex-direction: column;">
            <div style="background: #f5f5f5; aspect-ratio: 1; display: flex; align-items: center; justify-content: center; position: relative;">
                <img src="${item.image.startsWith('http') ? item.image : BASE_URL + item.image}" alt="${item.name}" style="width: 100%; height: 100%; object-fit: contain; padding: 1rem;">
                <button onclick="removeFromWishlist(${index})" style="position: absolute; top: 10px; right: 10px; background: #fff; border: none; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                    ✕
                </button>
            </div>
            <div style="padding: 1rem; display: flex; flex-direction: column; flex: 1;">
                <h3 style="font-size: 1rem; font-weight: 500; margin-bottom: 0.5rem;">${item.name}</h3>
                <div style="font-weight: 600; margin-bottom: 1rem; margin-top: auto;">${new Intl.NumberFormat('vi-VN').format(item.price)} ₫</div>
                <button onclick="addToCartFromWishlist('${item.name.replace(/'/g, "\\'")}', ${item.price}, '${item.image}')" style="width: 100%; padding: 0.8rem; background: #111; color: #fff; border: none; border-radius: 100px; font-weight: 500; cursor: pointer;">
                    Thêm vào giỏ
                </button>
            </div>
        </div>
    `).join('');
}

function removeFromWishlist(index) {
    let favourites = JSON.parse(localStorage.getItem('paceup_favs')) || [];
    favourites.splice(index, 1);
    localStorage.setItem('paceup_favs', JSON.stringify(favourites));
    renderWishlist();
    showToast('Đã xóa khỏi danh sách yêu thích');
}

function showToast(message) {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 2500);
}

// Minimal cart logic just for adding from wishlist page
let cart = JSON.parse(localStorage.getItem('paceup_cart')) || [];
function addToCartFromWishlist(name, price, image) {
    const existing = cart.find(item => item.name === name);
    if (existing) existing.qty += 1;
    else cart.push({ name, price, image, qty: 1 });
    localStorage.setItem('paceup_cart', JSON.stringify(cart));
    showToast('Đã thêm vào giỏ hàng!');
    if (typeof window.updateBadgeGlobal === 'function') window.updateBadgeGlobal();
}
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>