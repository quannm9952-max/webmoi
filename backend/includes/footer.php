<footer class="site-footer mt-5">
    <div class="container">
        <div class="footer-cta">
            <div>
                <h4>TechShop đồng hành cùng bạn</h4>
                <p>Nhận tư vấn thiết bị công nghệ phù hợp học tập, làm việc và gaming.</p>
            </div>
            <a href="<?= BASE_URL ?>/shop.php" class="btn btn-light rounded-pill px-4 fw-700">
                <i class="bi bi-bag me-2"></i>Mua sắm ngay
            </a>
        </div>

        <div class="row g-4 footer-main">
            <div class="col-lg-4">
                <a href="<?= BASE_URL ?>/shop.php" class="brand-logo">
                    <span class="brand-icon">T</span>
                    <span>Tech<span>Shop</span></span>
                </a>
                <p class="text-muted mt-3" style="font-size:14px;line-height:1.7">
                    Thiết bị điện tử chính hãng, bảo hành rõ ràng, giao hàng nhanh toàn quốc.
                </p>
                <div class="socials">
                    <a href="#" title="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" title="YouTube"><i class="bi bi-youtube"></i></a>
                    <a href="#" title="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#" title="TikTok"><i class="bi bi-tiktok"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <h6>Mua hàng</h6>
                <a href="<?= BASE_URL ?>/shop.php"><i class="bi bi-shop me-2 text-primary"></i>Sản phẩm</a>
                <a href="<?= BASE_URL ?>/cart.php"><i class="bi bi-cart me-2 text-primary"></i>Giỏ hàng</a>
                <a href="<?= BASE_URL ?>/checkout.php"><i class="bi bi-credit-card me-2 text-primary"></i>Thanh toán</a>
                <a href="<?= BASE_URL ?>/favorites.php"><i class="bi bi-heart me-2 text-primary"></i>Yêu thích</a>
            </div>
            <div class="col-lg-2 col-6">
                <h6>Tài khoản</h6>
                <a href="<?= BASE_URL ?>/account.php"><i class="bi bi-person me-2 text-primary"></i>Thông tin</a>
                <a href="<?= BASE_URL ?>/my_orders.php"><i class="bi bi-box-seam me-2 text-primary"></i>Đơn hàng</a>
                <a href="<?= BASE_URL ?>/login.php"><i class="bi bi-box-arrow-in-right me-2 text-primary"></i>Đăng nhập</a>
                <a href="<?= BASE_URL ?>/register.php"><i class="bi bi-person-plus me-2 text-primary"></i>Đăng ký</a>
            </div>
            <div class="col-lg-4">
                <h6>Liên hệ</h6>
                <p><i class="bi bi-geo-alt-fill text-primary me-2"></i>123 Nguyễn Văn Linh, Q.7, TP.HCM</p>
                <p><i class="bi bi-telephone-fill text-primary me-2"></i>1900 1234 (8:00–22:00)</p>
                <p><i class="bi bi-envelope-fill text-primary me-2"></i>cskh@techshop.vn</p>
                <p><i class="bi bi-shield-check-fill text-success me-2"></i>Bảo hành 12–24 tháng chính hãng</p>
            </div>
        </div>

        <div class="footer-bottom">
            <span>&copy; <?= date('Y') ?> TechShop. All rights reserved.</span>
            <span>
               <a href="<?= BASE_URL ?>/privacy.php" class="text-muted text-decoration-none me-3">Chính sách bảo mật</a>
                <a href="<?= BASE_URL ?>/terms.php" class="text-muted text-decoration-none">Điều khoản sử dụng</a>
            </span>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>

function showToast(message, type = 'success') {
    const icons = { success: 'bi-check-circle-fill', error: 'bi-x-circle-fill', warning: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill' };
    const container = document.getElementById('toast-container');
    const el = document.createElement('div');
    el.className = `toast-msg toast-${type}`;
    el.innerHTML = `<i class="bi ${icons[type] || icons.info} toast-icon"></i><span>${message}</span>`;
    container.appendChild(el);
    setTimeout(() => {
        el.classList.add('toast-out');
        el.addEventListener('animationend', () => el.remove());
    }, 3200);
}

document.addEventListener('DOMContentLoaded', function () {
    // Add to cart
    document.querySelectorAll('.ajax-cart-btn').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault(); e.stopPropagation();
            const qty = parseInt(document.getElementById('qty-input')?.value || btn.dataset.qty || '1');
            const fd = new FormData();
            fd.append('id_san_pham', btn.dataset.id);
            fd.append('so_luong', qty);

            btn.classList.add('loading');
            const origText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang thêm...';

            fetch('<?= BASE_URL ?>/ajax/cart.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(d => {
                    showToast(d.message, d.success ? 'success' : 'error');
                    if (d.success) {
                        // Update cart badge
                        const badge = document.querySelector('.cart-badge');
                        if (d.cart_count !== undefined) {
                            if (badge) {
                                badge.textContent = d.cart_count;
                            } else {
                                const cartIcon = document.querySelector('a[href*="cart.php"]');
                                if (cartIcon) {
                                    const b = document.createElement('span');
                                    b.className = 'cart-badge';
                                    b.textContent = d.cart_count;
                                    cartIcon.appendChild(b);
                                }
                            }
                        }
                    }
                })
                .catch(() => showToast('Lỗi kết nối, vui lòng thử lại.', 'error'))
                .finally(() => {
                    btn.classList.remove('loading');
                    btn.innerHTML = origText;
                });
        });
    });

    // Buy Now
    document.querySelectorAll('.ajax-buy-now-btn').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault(); e.stopPropagation();
            const qty = parseInt(document.getElementById('qty-input')?.value || btn.dataset.qty || '1');
            const fd = new FormData();
            fd.append('id_san_pham', btn.dataset.id);
            fd.append('so_luong', qty);

            btn.classList.add('loading');
            const origText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';

            fetch('<?= BASE_URL ?>/ajax/cart.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        window.location.href = '<?= BASE_URL ?>/checkout.php';
                    } else {
                        showToast(d.message, 'error');
                        btn.classList.remove('loading');
                        btn.innerHTML = origText;
                    }
                })
                .catch(() => {
                    showToast('Lỗi kết nối, vui lòng thử lại.', 'error');
                    btn.classList.remove('loading');
                    btn.innerHTML = origText;
                });
        });
    });


    document.querySelectorAll('.ajax-favorite-btn').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault(); e.stopPropagation();
            const fd = new FormData();
            fd.append('id_san_pham', btn.dataset.id);
            fetch('<?= BASE_URL ?>/ajax/favorite.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(d => {
                    showToast(d.message, d.success ? 'success' : 'error');
                    if (d.success) {
                        if (d.action === 'added') {
                            btn.classList.add('active');
                            const icon = btn.querySelector('i');
                            if (icon) icon.classList.replace('bi-heart', 'bi-heart-fill');
                        } else {
                            btn.classList.remove('active');
                            const icon = btn.querySelector('i');
                            if (icon) icon.classList.replace('bi-heart-fill', 'bi-heart');
                            if (location.pathname.endsWith('/favorites.php')) {
                                btn.closest('.col')?.remove();
                            }
                        }
                    }
                })
                .catch(() => showToast('Lỗi kết nối, vui lòng thử lại.', 'error'));
        });
    });


    document.querySelectorAll('.qty-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = document.getElementById('qty-input');
            if (!input) return;
            const max = parseInt(input.max || '999');
            let val = parseInt(input.value || '1');
            val = btn.dataset.dir === 'up' ? Math.min(val + 1, max) : Math.max(val - 1, 1);
            input.value = val;
        });
    });
});
</script>
