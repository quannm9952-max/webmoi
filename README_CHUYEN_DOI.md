# Web bán hàng: Vercel frontend + Render backend

## Cấu trúc
- `backend/`: PHP thuần chạy trên Render bằng Docker, giữ lại source cũ và thêm API trong thư mục `api/`.
- `frontend/`: React/Vite chạy trên Vercel, gọi API từ Render.

## API đã thêm
- `/api/health.php`
- `/api/products.php`
- `/api/product.php?id=1`

## Biến môi trường backend Render
- `APP_ENV=production`
- `BASE_URL=https://ten-backend-render.onrender.com`
- `FRONTEND_URL=https://ten-frontend-vercel.vercel.app`
- `DB_HOST`
- `DB_PORT=3306`
- `DB_NAME=webbanhang`
- `DB_USER`
- `DB_PASS`
- `DB_CHARSET=utf8mb4`

## Biến môi trường frontend Vercel
- `VITE_API_URL=https://ten-backend-render.onrender.com/api`

## Database
Import file `backend/sql/webbanhang.sql` vào MySQL online, ví dụ Aiven, Railway, Clever Cloud, PlanetScale-compatible MySQL, hoặc dịch vụ MySQL mà Render có thể truy cập.
