TÀI LIỆU PHÂN TÍCH VÀ THIẾT KẾ HỆ THỐNG SOCIAL MARKETPLACE CHO SINH VIÊN

PHẦN 1: HOÀN THIỆN USE CASE (CHỨC NĂNG)

Dựa trên yêu cầu ban đầu, để hệ thống hoạt động trơn tru theo hướng Social và Marketplace, chúng ta cần bổ sung các module về Tương tác (Social), Đánh giá tín nhiệm (Trust) và Quản lý đơn hàng (Order Flow).

1. Actor: Guest (Khách vãng lai)

Xem danh sách bài đăng (Newsfeed).

Tìm kiếm/Lọc bài đăng (Theo tên sản phẩm, theo trường Đại học, theo khoảng giá).

Xem chi tiết bài đăng và sản phẩm.

Đăng ký / Đăng nhập.

2. Actor: Authenticated User (Sinh viên)

Nhóm chức năng Tài khoản:

Cập nhật hồ sơ (Avatar, thông tin cá nhân, xác thực thẻ sinh viên - Optional để tăng uy tín).

Đổi mật khẩu.

Xem lịch sử giao dịch.

Nhóm chức năng Người bán (Seller):

Đăng bài (Create Post):

Nhập tiêu đề, mô tả chung.

Chọn địa điểm giao dịch (Dropdown danh sách các trường ĐH tại Hà Nội).

Thêm nhiều sản phẩm trong 1 bài (Product Cards): Tên, Ảnh, Giá, Số lượng tồn kho.

Quản lý bài đăng: Sửa, Xóa, Ẩn bài, Đánh dấu "Đã bán hết".

Quản lý đơn bán: Xác nhận đơn hàng, Cập nhật trạng thái (Đang giao, Hoàn thành, Hủy).

Nhóm chức năng Người mua (Buyer):

Tương tác Social:

Like/React bài đăng.

Comment (Bình luận) dưới bài đăng để hỏi giá/tình trạng.

Chat (Messaging): Chat trực tiếp với người bán để mặc cả hoặc chốt địa điểm (Rất quan trọng với đồ cũ).

Report (Báo cáo) bài đăng vi phạm.

Mua hàng:

Thêm sản phẩm vào giỏ hàng.

Checkout (Đặt hàng): Điền địa chỉ/SĐT, chọn phương thức thanh toán (COD).

Đánh giá (Review): Rate sao và viết nhận xét cho người bán sau khi đơn hàng hoàn thành (Xây dựng uy tín).

Nhóm chức năng Thông báo (Notification):

Nhận thông báo khi có người comment, có đơn hàng mới, đơn hàng thay đổi trạng thái.

3. Actor: Admin

Dashboard: Thống kê số lượng User mới, Bài đăng mới, Doanh thu (nếu có thu phí), Top người bán uy tín.

Quản lý User: Khóa/Mở khóa tài khoản, Xác minh sinh viên.

Kiểm duyệt bài đăng (Moderation):

Duyệt bài (nếu bật chế độ duyệt trước khi hiện).

Xử lý các bài bị Report.

Quản lý danh mục: CRUD các trường Đại học, CRUD danh mục sản phẩm (Sách, Quần áo, Đồ điện tử...).

Quản lý Rule: CRUD các quy định đăng bài.

PHẦN 2: THIẾT KẾ CƠ SỞ DỮ LIỆU (DATABASE SCHEMA)

Dưới đây là thiết kế các bảng (Tables) và mối quan hệ (Relationships) tối ưu cho Laravel.

1. Bảng người dùng & Phân quyền

users

id: BigInt, PK

name: String (Tên hiển thị)

email: String, Unique

password: String

phone: String (SĐT liên hệ)

avatar: String (URL ảnh đại diện)

university_id: BigInt, FK (Trường đang theo học - tham khảo)

role: Enum (‘USER‘, ‘ADMIN’) 

status: Enum (‘ACTIVE‘, ‘BANNED’,’WARNING’,’SHUT_DOWN’) — Trạng thái shut down là để cho các chức năng như xóa tài khoản thay vì xóa bản ghi thì sẽ đổi lại status là shut down và chuyển các thông tin như tên thành 1 vài tên cố định như nguwoifu dugnf hệ thống. để có thể giữ là id để không ảnh hưởng tới các bnar ghi khác

is_active: Boolean (Trạng thái hoạt động)

remember_token, timestamps

2. Bảng dữ liệu danh mục (Master Data)

universities (Danh sách trường ĐH)

id: BigInt, PK

name: String (Ví dụ: ĐH Bách Khoa, NEU, FPT...)

code: String (Mã trường)

address: String

timestamps

categories (Loại sản phẩm: Giáo trình, Đồ gia dụng...)

id: BigInt, PK

name: String

timestamps

3. Bảng Bài đăng & Sản phẩm (Core Feature)

Thiết kế theo mô hình: 1 Bài đăng (Post) chứa nhiều Sản phẩm (Products).

posts (Bài viết dạng Social)

id: BigInt, PK

user_id: BigInt, FK (Người đăng)

university_id: BigInt, FK (Địa điểm giao dịch/Pass đồ - chọn từ bảng universities)

title: String (Tiêu đề bài viết)

content: Text (Mô tả chung, tâm sự, lý do pass...)

status: Enum ('pending', 'approved', 'rejected', 'hidden', 'sold_out')

view_count: Integer (Đếm lượt xem)

timestamps

deleted_at (Soft delete)

products (Sản phẩm cụ thể trong bài đăng)

id: BigInt, PK

post_id: BigInt, FK

category_id: BigInt, FK (Optional)

name: String (Tên món đồ)

price: Decimal/Double

quantity: Integer (Số lượng)

image: String (URL ảnh sản phẩm)

description: Text (Mô tả chi tiết tình trạng: mới 99%, hỏng nút...)

is_sold: Boolean (Đã bán hay chưa)

timestamps

4. Bảng Tương tác (Social)

post_likes

id: BigInt, PK

user_id: BigInt, FK

post_id: BigInt, FK

created_at

comments

id: BigInt, PK

user_id: BigInt, FK

post_id: BigInt, FK

content: Text

parent_id: BigInt (Để trả lời comment - nested comments)

timestamps

reports (Báo cáo vi phạm)

id: BigInt, PK

user_id: BigInt, FK (Người báo cáo)

post_id: BigInt, FK (Bài bị báo cáo)

reason: Text (Lý do: Lừa đảo, hàng cấm...)

status: Enum ('pending', 'resolved')

timestamps

5. Bảng Giỏ hàng & Đơn hàng (E-commerce)

carts (Có thể dùng Redis hoặc Session, nhưng dùng DB để lưu lâu dài)

id: BigInt, PK

user_id: BigInt, FK

product_id: BigInt, FK

quantity: Integer

timestamps

orders

id: BigInt, PK

user_id: BigInt, FK (Người mua)

seller_id: BigInt, FK (Người bán - Lưu ý: Nên tách đơn theo người bán)

total_amount: Decimal

payment_method: Enum ('COD', 'Banking') - Hiện tại tập trung COD

shipping_address: String

phone_number: String

status: Enum ('pending', 'confirmed', 'shipping', 'completed', 'cancelled')

note: Text

timestamps

order_items

id: BigInt, PK

order_id: BigInt, FK

product_id: BigInt, FK

product_name: String (Snapshot tên lúc mua)

product_price: Decimal (Snapshot giá lúc mua)

quantity: Integer

timestamps

6. Bảng Đánh giá & Chat (Mở rộng)

reviews

id: BigInt, PK

order_id: BigInt, FK

reviewer_id: BigInt, FK (Người mua)

reviewed_user_id: BigInt, FK (Người bán)

rating: Integer (1-5 sao)

comment: Text

timestamps

conversations (Chat)

id: BigInt, PK

user_one: BigInt

user_two: BigInt

timestamps

messages

id: BigInt, PK

conversation_id: BigInt, FK

sender_id: BigInt, FK

content: Text

is_read: Boolean

timestampsg

PHẦN 3: MỘT SỐ LƯU Ý KHI CODE LARAVEL

JWT Authentication:

Cài đặt gói tymon/jwt-auth hoặc sử dụng Laravel Sanctum (Sanctum dễ cấu hình hơn và hỗ trợ tốt cả SPA lẫn Mobile App). Với quy mô sinh viên, Sanctum là đủ và nhẹ.

Middleware Phân quyền:

Tạo Middleware CheckAdmin để bảo vệ các route /admin.

Dùng Policy (Laravel Policies) để check quyền sở hữu bài viết: User A không được sửa/xóa bài của User B.

Xử lý Post & Product:

Khi User tạo Post, sử dụng Database Transaction (DB::transaction) để đảm bảo cả Post và các Product bên trong được tạo thành công cùng lúc. Nếu lỗi 1 trong 2 thì rollback.

Logic Giỏ hàng (Multi-seller):

Vì đây là sàn C2C (Customer to Customer), nếu người mua chọn 2 sản phẩm của 2 người bán khác nhau, hệ thống nên tách thành 2 Đơn hàng (Orders) riêng biệt khi checkout để mỗi người bán quản lý đơn của mình.

Real-time (Tùy chọn nâng cao):

Sử dụng Laravel Reverb (mới trong Laravel 11) hoặc Pusher để làm tính năng Chat và Thông báo real-time.