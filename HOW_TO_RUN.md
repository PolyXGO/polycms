# Hướng dẫn Chạy và Test PolyCMS

## 📋 Yêu cầu

- PHP 8.3+
- Node.js 20.19+ hoặc 22.12+ (hoặc dùng Node.js 18 cho dev mode)
- Composer
- MySQL/PostgreSQL/SQLite

## 🚀 Bước 1: Setup ban đầu (nếu chưa làm)

```bash
# 1. Install dependencies
composer install
npm install

# 2. Copy .env file
cp .env.example .env

# 3. Generate app key
php artisan key:generate

# 4. Cấu hình database trong .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=polycms
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Chạy migrations và seeders
php artisan migrate --seed

# 6. Tạo storage link
php artisan storage:link
```

## 🎯 Bước 2: Chạy Development Servers

### Terminal 1: Laravel Server
```bash
php artisan serve
```
Server sẽ chạy tại: http://localhost:8000

### Terminal 2: Vite Dev Server
```bash
npm run dev
```
Vite sẽ chạy tại: http://localhost:5173

## 🔐 Bước 3: Truy cập Admin Panel

1. Mở browser: http://localhost:8000/admin
2. Đăng nhập với tài khoản đã seed:
   - **Admin:** admin@example.com / password
   - **Editor:** editor@example.com / password
   - **Author:** author@example.com / password

## ✅ Bước 4: Test các tính năng

### Test Posts Management
1. Click "Posts" trong sidebar
2. Click "+ New Post" để tạo post mới
3. Điền thông tin:
   - Title: "My First Post"
   - Slug: "my-first-post"
   - Type: Post
   - Status: Draft
4. Sử dụng Block Editor để thêm content
5. Click "Save Post"
6. Kiểm tra post đã được tạo trong danh sách

### Test API
```bash
# Get all posts
curl http://localhost:8000/api/v1/posts

# Login và get token
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# Tạo post mới (thay TOKEN bằng token từ login)
curl -X POST http://localhost:8000/api/v1/posts \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Test Post","slug":"test-post","type":"post","status":"draft"}'
```

## 🐛 Troubleshooting

### Lỗi: "Could not resolve CSS"
- Đảm bảo đã chạy `npm install`
- Xóa cache: `rm -rf node_modules/.vite .vite`

### Lỗi: "Module not found"
- Stop Vite (Ctrl+C)
- Chạy lại: `npm run dev`

### Lỗi: "419 Page Expired"
- Clear cache: `php artisan cache:clear`
- Check session driver trong .env

### Lỗi: Database connection
- Kiểm tra database credentials trong .env
- Đảm bảo database đã được tạo
- Chạy lại: `php artisan migrate`

## 📝 Test Checklist

- [ ] Admin login/logout
- [ ] Posts CRUD (Create, Read, Update, Delete)
- [ ] Block Editor hoạt động
- [ ] Products management (khi implement)
- [ ] Categories management (khi implement)
- [ ] Media upload (khi implement)
- [ ] Widgets configuration (khi implement)

## 🔧 Quick Commands

```bash
# Clear all caches
php artisan optimize:clear

# Reset database và seed lại
php artisan migrate:fresh --seed

# Build production (cần Node.js 20+)
npm run build

# Chạy tests
php artisan test
```
