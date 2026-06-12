import React, { useEffect, useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';
import './style.css';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/webbanhang/api';
const BACKEND_URL = API_URL.replace(/\/api\/?$/, '');

function money(value) {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(Number(value || 0));
}

function cleanText(value) {
  return String(value ?? '').trim();
}

function App() {
  const [products, setProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [brands, setBrands] = useState([]);
  const [loading, setLoading] = useState(true);
  const [q, setQ] = useState('');
  const [sort, setSort] = useState('newest');
  const [activeCategory, setActiveCategory] = useState(0);
  const [activeBrand, setActiveBrand] = useState(0);
  const [error, setError] = useState('');

  const validCategories = useMemo(
    () => (categories || []).filter((c) => cleanText(c.ten_danh_muc)),
    [categories]
  );

  const validBrands = useMemo(
    () => (brands || []).filter((b) => cleanText(b.name || b.ten_thuong_hieu)),
    [brands]
  );

  const totalCount = validCategories.reduce((sum, c) => sum + Number(c.so_luong || 0), 0) || products.length;

  async function loadProducts(params = {}) {
    setLoading(true);
    setError('');

    const nextCategory = params.category ?? activeCategory;
    const nextBrand = params.brand ?? activeBrand;
    const nextQ = params.q ?? q;
    const nextSort = params.sort ?? sort;

    const query = new URLSearchParams();
    if (nextQ) query.set('q', nextQ);
    if (Number(nextCategory) > 0) query.set('category', nextCategory);
    if (Number(nextBrand) > 0) query.set('brand', nextBrand);
    query.set('sort', nextSort);

    try {
      const res = await fetch(`${API_URL}/products.php?${query.toString()}`, { credentials: 'include' });
      const json = await res.json();
      if (!json.success) throw new Error(json.message || 'Không tải được sản phẩm');
      setProducts(json.data.products || []);
      setCategories(json.data.categories || []);
      setBrands(json.data.brands || []);
      setActiveCategory(Number(nextCategory) || 0);
      setActiveBrand(Number(nextBrand) || 0);
      setQ(nextQ || '');
      setSort(nextSort || 'newest');
    } catch (err) {
      setError(err.message || 'Lỗi kết nối');
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    loadProducts({ category: 0, brand: 0, q: '', sort: 'newest' });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  function submitSearch(e) {
    e.preventDefault();
    loadProducts({ q, category: 0, brand: 0, sort });
  }

  function addCartToast() {
    alert('Bản React demo đang hiển thị giao diện giống web cũ. Chức năng giỏ hàng cần thêm API đăng nhập/giỏ hàng để dùng đầy đủ.');
  }

  return (
    <>
      <div className="top-strip">
        <div className="container top-strip-inner">
          <span><i className="bi bi-lightning-charge-fill text-warning"></i> TechShop Flash Sale — Giảm đến 40%</span>
          <span><i className="bi bi-telephone-fill me-1"></i>1900 1234 &nbsp;|&nbsp; <i className="bi bi-clock me-1"></i>8:00–22:00</span>
        </div>
      </div>

      <header className="main-header sticky-top">
        <div className="container header-row">
          <a href="#shop-content" className="brand-logo">
            <span className="brand-icon">T</span>
            <span>Tech<span>Shop</span></span>
          </a>

          <form className="search-box desktop-search" onSubmit={submitSearch}>
            <input value={q} onChange={(e) => setQ(e.target.value)} placeholder="Tìm kiếm sản phẩm, thương hiệu..." />
            <button type="submit"><i className="bi bi-search"></i></button>
          </form>

          <nav className="header-actions">
            <a href={`${BACKEND_URL}/favorites.php`} className="header-icon-btn" title="Yêu thích"><i className="bi bi-heart"></i></a>
            <a href={`${BACKEND_URL}/cart.php`} className="header-icon-btn" title="Giỏ hàng"><i className="bi bi-cart3"></i></a>
            <a href={`${BACKEND_URL}/login.php`} className="login-btn"><i className="bi bi-person me-1"></i>Đăng nhập</a>
          </nav>
        </div>
      </header>

      <div className="mobile-search-bar">
        <div className="container">
          <form className="search-box" onSubmit={submitSearch}>
            <input value={q} onChange={(e) => setQ(e.target.value)} placeholder="Tìm kiếm sản phẩm..." />
            <button type="submit"><i className="bi bi-search"></i></button>
          </form>
        </div>
      </div>

      <main className="container main-wrap">
        <section className="hero-slider">
          <div className="hero-text">
            <span className="subtitle">TECHSHOP MEGA SALE</span>
            <h2>Thiết bị công nghệ chính hãng cho mọi nhu cầu</h2>
            <p>Laptop, màn hình, phụ kiện và linh kiện PC với giá tốt.</p>
            <a href="#shop-content" className="btn-buy">Khám phá ngay</a>
          </div>
          <div className="hero-art">
            <img src="https://res.cloudinary.com/daro9erbh/image/upload/v1777205633/D%E1%BB%B1_%C3%A1n_m%E1%BB%9Bi_6_eahyjq.png" alt="TechShop Banner" />
          </div>
        </section>

        <div className="shop-layout" id="shop-content">
          <aside className="filter-sidebar">
            <h6>Danh mục</h6>
            <ul className="filter-list">
              <li>
                <button className={!activeCategory ? 'active' : ''} onClick={() => loadProducts({ category: 0, q: '', brand: activeBrand })}>
                  Tất cả sản phẩm <span className="filter-badge">{totalCount}</span>
                </button>
              </li>
              {validCategories.map((c) => (
                <li key={c.id_danh_muc}>
                  <button className={activeCategory === Number(c.id_danh_muc) ? 'active' : ''} onClick={() => loadProducts({ category: c.id_danh_muc, q: '' })}>
                    {c.ten_danh_muc}<span className="filter-badge">{c.so_luong || 0}</span>
                  </button>
                </li>
              ))}
            </ul>

            <h6>Thương hiệu</h6>
            <ul className="filter-list">
              {validBrands.map((b) => {
                const id = b.id || b.id_thuong_hieu;
                const name = b.name || b.ten_thuong_hieu;
                return (
                  <li key={id}>
                    <button className={activeBrand === Number(id) ? 'active' : ''} onClick={() => loadProducts({ brand: id, q: '' })}>{name}</button>
                  </li>
                );
              })}
            </ul>
          </aside>

          <section className="products-area">
            <div className="sort-bar">
              <div>
                <span className="section-heading">{q ? `Kết quả cho "${q}"` : activeCategory || activeBrand ? 'Sản phẩm đã lọc' : 'Tất cả sản phẩm'}</span>
                <small className="muted">({products.length} sản phẩm)</small>
              </div>
              <select className="sort-select" value={sort} onChange={(e) => loadProducts({ sort: e.target.value })}>
                <option value="newest">Mới nhất</option>
                <option value="price_asc">Giá thấp → cao</option>
                <option value="price_desc">Giá cao → thấp</option>
              </select>
            </div>

            {loading && <div className="empty-state"><i className="bi bi-arrow-repeat spinner"></i><h5>Đang tải sản phẩm...</h5></div>}
            {error && <div className="empty-state error"><i className="bi bi-wifi-off"></i><h5>{error}</h5></div>}

            {!loading && !error && products.length === 0 && (
              <div className="empty-state"><i className="bi bi-search"></i><h5>Không tìm thấy sản phẩm nào</h5><p>Hãy thử tìm kiếm với từ khóa khác.</p></div>
            )}

            <div className="products-grid">
              {products.map((p) => {
                const price = Number(p.gia_giam && Number(p.gia_giam) < Number(p.gia_ban) ? p.gia_giam : p.gia_ban || p.gia);
                const hasDiscount = p.gia_giam && Number(p.gia_giam) < Number(p.gia_ban);
                const discountPct = hasDiscount ? Math.round((1 - Number(p.gia_giam) / Number(p.gia_ban)) * 100) : 0;
                return (
                  <article className="product-card" key={p.id_san_pham}>
                    {hasDiscount && <span className="p-badge">-{discountPct}%</span>}
                    <a href={`${BACKEND_URL}/product.php?id=${p.id_san_pham}`} className="p-img-box">
                      <img src={p.image_url || p.hinh_anh || p.hinh_anh_chinh || '/placeholder.png'} alt={p.ten_san_pham} loading="lazy" />
                    </a>
                    <div className="p-content">
                      <div className="p-brand">{p.ten_thuong_hieu || ''}</div>
                      <a href={`${BACKEND_URL}/product.php?id=${p.id_san_pham}`} className="p-title">{p.ten_san_pham}</a>
                      <div className="mt-auto">
                        <span className="p-price">{money(price)}</span>
                        {hasDiscount && <span className="p-old-price">{money(p.gia_ban)}</span>}
                      </div>
                      <div className="p-actions">
                        <button className="btn-add-cart" onClick={addCartToast}><i className="bi bi-cart-plus"></i> Thêm vào giỏ</button>
                        <button className="btn-fav" onClick={addCartToast} title="Yêu thích"><i className="bi bi-heart"></i></button>
                      </div>
                    </div>
                  </article>
                );
              })}
            </div>
          </section>
        </div>
      </main>

      <footer className="site-footer">
        <div className="container">
          <div className="footer-cta">
            <div><h4>TechShop đồng hành cùng bạn</h4><p>Nhận tư vấn thiết bị công nghệ phù hợp học tập, làm việc và gaming.</p></div>
            <a href="#shop-content" className="btn-light"><i className="bi bi-bag me-2"></i>Mua sắm ngay</a>
          </div>
          <div className="footer-main">
            <div className="footer-col brand-col">
              <a href="#shop-content" className="brand-logo"><span className="brand-icon">T</span><span>Tech<span>Shop</span></span></a>
              <p>Thiết bị điện tử chính hãng, bảo hành rõ ràng, giao hàng nhanh toàn quốc.</p>
              <div className="socials"><a><i className="bi bi-facebook"></i></a><a><i className="bi bi-youtube"></i></a><a><i className="bi bi-instagram"></i></a><a><i className="bi bi-tiktok"></i></a></div>
            </div>
            <div className="footer-col"><h6>Mua hàng</h6><a>Sản phẩm</a><a>Giỏ hàng</a><a>Thanh toán</a><a>Yêu thích</a></div>
            <div className="footer-col"><h6>Tài khoản</h6><a>Thông tin</a><a>Đơn hàng</a><a>Đăng nhập</a><a>Đăng ký</a></div>
            <div className="footer-col contact-col"><h6>Liên hệ</h6><p><i className="bi bi-geo-alt-fill"></i>123 Nguyễn Văn Linh, Q.7, TP.HCM</p><p><i className="bi bi-telephone-fill"></i>1900 1234</p><p><i className="bi bi-envelope-fill"></i>cskh@techshop.vn</p></div>
          </div>
          <div className="footer-bottom"><span>© 2026 TechShop. All rights reserved.</span><span>Chính sách bảo mật · Điều khoản sử dụng</span></div>
        </div>
      </footer>
    </>
  );
}

createRoot(document.getElementById('root')).render(<App />);
