# Hướng dẫn Seeding Database

## Chạy Seeder

```bash
php artisan db:seed
```

Hoặc nếu muốn fresh migration và seed:

```bash
php artisan migrate:fresh --seed
```

## Dữ liệu được tạo

### Users (4 users)
1. **Admin**
   - Email: `admin@ecostudent.com`
   - Password: `password123`
   - Role: ADMIN

2. **User 1**
   - Email: `an@example.com`
   - Password: `password123`
   - Role: USER
   - Trường: Đại học Bách Khoa Hà Nội

3. **User 2**
   - Email: `binh@example.com`
   - Password: `password123`
   - Role: USER
   - Trường: Đại học Kinh tế Quốc dân

4. **User 3**
   - Email: `cuong@example.com`
   - Password: `password123`
   - Role: USER
   - Trường: Đại học FPT

### Universities (3 trường)
- Đại học Bách Khoa Hà Nội
- Đại học Kinh tế Quốc dân
- Đại học FPT

### Categories (5 danh mục)
- Sách giáo khoa
- Đồ điện tử
- Quần áo
- Đồ gia dụng
- Xe đạp

### Posts (3 bài viết)

1. **Post 1** - Sách giáo khoa (User: an@example.com)
   - 3 sản phẩm: Sách Toán, Văn, Anh

2. **Post 2** - Đồ điện tử (User: binh@example.com)
   - 2 sản phẩm: Laptop Dell, Chuột không dây

3. **Post 3** - Quần áo và đồ gia dụng (User: cuong@example.com)
   - 6 sản phẩm: Áo khoác, Quần jean, Bàn học, Chăn gối, Giá treo, Bóng đèn

## Test các chức năng

1. **Đăng nhập**: Sử dụng các tài khoản trên
2. **Xem newsfeed**: Trang chủ hiển thị 3 bài viết
3. **Like/Comment**: Click vào nút like hoặc comment
4. **Xem sản phẩm**: Click vào ảnh sản phẩm để xem chi tiết
5. **Thêm vào giỏ hàng**: Từ modal sản phẩm
6. **Đăng bài mới**: Menu "Đăng bài"
7. **Xem giỏ hàng**: Menu "Giỏ hàng"
8. **Thanh toán**: Từ giỏ hàng
9. **Xem đơn hàng**: Menu "Đơn hàng" và "Đơn bán"
10. **Cập nhật profile**: Menu "Hồ sơ"

