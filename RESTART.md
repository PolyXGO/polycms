# Hướng dẫn Restart Servers

## Thông thường - KHÔNG CẦN RESTART

Vite dev server (`npm run dev`) sẽ tự động hot reload khi:
- Thay đổi Vue components
- Thay đổi TypeScript files
- Thay đổi CSS/Tailwind
- Thêm mới components

Chỉ cần **refresh browser** (F5 hoặc Cmd+R).

## Khi nào CẦN restart

### 1. Thay đổi cấu hình Vite
```bash
# Stop: Ctrl+C
npm run dev
```

### 2. Thêm dependencies mới
```bash
npm install package-name
npm run dev  # Restart Vite
```

### 3. Thay đổi Laravel config
```bash
php artisan config:clear
php artisan cache:clear
# Restart Laravel serve nếu cần
```

### 4. Gặp lỗi "Module not found"
```bash
# Stop cả 2 servers
npm run dev  # Restart Vite
php artisan serve  # Restart Laravel (nếu cần)
```

## Quy trình khởi động đầy đủ

### Terminal 1: Vite Dev Server
```bash
npm run dev
```

### Terminal 2: Laravel Server
```bash
php artisan serve
```

### Browser
- Truy cập: http://localhost:8000/admin
- Hard refresh nếu cần: Ctrl+Shift+R (Windows/Linux) hoặc Cmd+Shift+R (Mac)

## Troubleshooting

### Lỗi "Cannot find module"
1. Stop Vite (Ctrl+C)
2. Xóa node_modules/.vite (nếu có)
3. `npm run dev` lại

### Lỗi "404 on assets"
- Đảm bảo Vite đang chạy
- Hard refresh browser
- Kiểm tra Vite port (thường là 5173)

### Code changes không hiện
- Check browser console có lỗi không
- Hard refresh (Ctrl+Shift+R)
- Restart Vite nếu vẫn không work
