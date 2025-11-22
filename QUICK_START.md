# Hướng dẫn nhanh

## Cài đặt và chạy

### 1. Cài đặt dependencies
```bash
npm install
```

### 2. Build assets (cho production)
```bash
npm run build
```

### 3. Hoặc chạy dev mode (tự động reload)
```bash
npm run dev
```

### 4. Chạy migrations và seed database
```bash
php artisan migrate:fresh --seed
```

### 5. Chạy server Laravel
```bash
php artisan serve
```

### 6. Truy cập ứng dụng
Mở trình duyệt: `http://localhost:8000`

## Test Accounts

**Admin:**
- Email: `admin@ecostudent.com`
- Password: `password123`

**Users:**
- Email: `an@example.com` / Password: `password123`
- Email: `binh@example.com` / Password: `password123`
- Email: `cuong@example.com` / Password: `password123`

## Lưu ý

- Nếu gặp lỗi với Node.js version, đảm bảo bạn đang dùng Node.js 18+ (đã được cấu hình tương thích)
- Assets đã được build sẵn trong `public/build/`, bạn có thể chạy server ngay mà không cần `npm run dev`
- Để development với hot reload, chạy `npm run dev` trong terminal riêng

