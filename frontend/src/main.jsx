import React, { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';
import './style.css';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/webbanhang/api';

function money(value) {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(Number(value || 0));
}

function App() {
  const [products, setProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [brands, setBrands] = useState([]);
  const [loading, setLoading] = useState(true);
  const [q, setQ] = useState('');
  const [sort, setSort] = useState('newest');
  const [error, setError] = useState('');

  async function loadProducts(params = {}) {
    setLoading(true);
    setError('');
    const query = new URLSearchParams({ q, sort, ...params }).toString();

    try {
      const res = await fetch(`${API_URL}/products.php?${query}`, { credentials: 'include' });
      const json = await res.json();
      if (!json.success) throw new Error(json.message || 'Không tải được sản phẩm');
      setProducts(json.data.products || []);
      setCategories(json.data.categories || []);
      setBrands(json.data.brands || []);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    loadProducts();
  }, [sort]);

  function submitSearch(e) {
    e.preventDefault();
    loadProducts({ q });
  }

  return (
    <main className="container">
      <header className="hero">
        <h1>TechShop</h1>
        <p>Frontend Vercel + Backend PHP Render</p>
      </header>

      <form className="toolbar" onSubmit={submitSearch}>
        <input value={q} onChange={(e) => setQ(e.target.value)} placeholder="Tìm sản phẩm..." />
        <select value={sort} onChange={(e) => setSort(e.target.value)}>
          <option value="newest">Mới nhất</option>
          <option value="price_asc">Giá tăng dần</option>
          <option value="price_desc">Giá giảm dần</option>
        </select>
        <button>Tìm</button>
      </form>

      <section className="filters">
        <button onClick={() => loadProducts({ category: 0, brand: 0, q: '' })}>Tất cả</button>
        {categories.map((c) => (
          <button key={c.id_danh_muc} onClick={() => loadProducts({ category: c.id_danh_muc, q: '' })}>
            {c.ten_danh_muc}
          </button>
        ))}
        {brands.map((b) => (
          <button key={b.id_thuong_hieu} onClick={() => loadProducts({ brand: b.id_thuong_hieu, q: '' })}>
            {b.ten_thuong_hieu}
          </button>
        ))}
      </section>

      {loading && <p>Đang tải...</p>}
      {error && <p className="error">{error}</p>}

      <section className="grid">
        {products.map((p) => (
          <article className="card" key={p.id_san_pham}>
            <img src={p.image_url || '/placeholder.png'} alt={p.ten_san_pham} />
            <h3>{p.ten_san_pham}</h3>
            <p>{p.ten_thuong_hieu} · {p.ten_danh_muc}</p>
            <strong>{money(p.gia_giam || p.gia_ban || p.gia)}</strong>
          </article>
        ))}
      </section>
    </main>
  );
}

createRoot(document.getElementById('root')).render(<App />);
