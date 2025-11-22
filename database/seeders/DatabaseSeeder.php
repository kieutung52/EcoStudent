<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\University;
use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 0. Seed Rules
        $this->call(RuleSeeder::class);

        // 1. Tạo Universities
        $universities = [
            ['name' => 'Đại học Bách Khoa Hà Nội', 'code' => 'BKHN', 'address' => 'Số 1 Đại Cồ Việt, Hai Bà Trưng, Hà Nội'],
            ['name' => 'Đại học Kinh tế Quốc dân', 'code' => 'NEU', 'address' => '207 Giải Phóng, Đồng Tâm, Hai Bà Trưng, Hà Nội'],
            ['name' => 'Đại học FPT', 'code' => 'FPT', 'address' => 'Khu Công nghệ cao Hòa Lạc, Thạch Thất, Hà Nội'],
            ['name' => 'Đại học Quốc gia Hà Nội', 'code' => 'VNU', 'address' => '144 Xuân Thủy, Cầu Giấy, Hà Nội'],
            ['name' => 'Đại học Ngoại thương', 'code' => 'FTU', 'address' => '91 Chùa Láng, Đống Đa, Hà Nội'],
        ];

        foreach ($universities as $uni) {
            University::create($uni);
        }

        // 2. Tạo Categories
        $categories = [
            ['name' => 'Sách giáo khoa', 'slug' => 'sach-giao-khoa'],
            ['name' => 'Đồ điện tử', 'slug' => 'do-dien-tu'],
            ['name' => 'Quần áo', 'slug' => 'quan-ao'],
            ['name' => 'Đồ gia dụng', 'slug' => 'do-gia-dung'],
            ['name' => 'Xe đạp', 'slug' => 'xe-dap'],
            ['name' => 'Đồ dùng học tập', 'slug' => 'do-dung-hoc-tap'],
            ['name' => 'Thể thao', 'slug' => 'the-thao'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        // 3. Tạo Users (1 admin, 5 users)
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@ecostudent.com',
            'password' => Hash::make('password123'),
            'phone' => '0123456789',
            'university_id' => 1,
            'role' => 'ADMIN',
            'status' => 'ACTIVE',
            'is_active' => true,
        ]);

        $user1 = User::create([
            'name' => 'Nguyễn Văn An',
            'email' => 'an@example.com',
            'password' => Hash::make('password123'),
            'phone' => '0987654321',
            'university_id' => 1,
            'role' => 'USER',
            'status' => 'ACTIVE',
            'is_active' => true,
        ]);

        $user2 = User::create([
            'name' => 'Trần Thị Bình',
            'email' => 'binh@example.com',
            'password' => Hash::make('password123'),
            'phone' => '0912345678',
            'university_id' => 2,
            'role' => 'USER',
            'status' => 'ACTIVE',
            'is_active' => true,
        ]);

        $user3 = User::create([
            'name' => 'Lê Văn Cường',
            'email' => 'cuong@example.com',
            'password' => Hash::make('password123'),
            'phone' => '0923456789',
            'university_id' => 3,
            'role' => 'USER',
            'status' => 'ACTIVE',
            'is_active' => true,
        ]);

        $user4 = User::create([
            'name' => 'Phạm Thị Dung',
            'email' => 'dung@example.com',
            'password' => Hash::make('password123'),
            'phone' => '0934567890',
            'university_id' => 4,
            'role' => 'USER',
            'status' => 'ACTIVE',
            'is_active' => true,
        ]);

        $user5 = User::create([
            'name' => 'Hoàng Văn Em',
            'email' => 'em@example.com',
            'password' => Hash::make('password123'),
            'phone' => '0945678901',
            'university_id' => 5,
            'role' => 'USER',
            'status' => 'ACTIVE',
            'is_active' => true,
        ]);

        // 4. Tạo Posts với nhiều sản phẩm (mỗi post trên 6 sản phẩm)
        
        // Post 1: Bộ sách giáo khoa đầy đủ
        $post1 = Post::create([
            'user_id' => $user1->id,
            'university_id' => 1,
            'title' => 'Bán bộ sách giáo khoa lớp 12 đầy đủ - Tất cả các môn',
            'content' => 'Mình đã tốt nghiệp nên bán lại bộ sách giáo khoa lớp 12. Sách còn mới, không viết vẽ gì. Ai cần thì liên hệ nhé!',
            'status' => 'approved',
            'view_count' => 45,
        ]);

        $post1Products = [
            ['name' => 'Sách Toán 12', 'price' => 50000, 'quantity' => 2, 'category_id' => 1, 'description' => 'Sách Toán lớp 12, còn mới'],
            ['name' => 'Sách Văn 12', 'price' => 45000, 'quantity' => 2, 'category_id' => 1, 'description' => 'Sách Ngữ Văn lớp 12'],
            ['name' => 'Sách Anh 12', 'price' => 48000, 'quantity' => 2, 'category_id' => 1, 'description' => 'Sách Tiếng Anh lớp 12'],
            ['name' => 'Sách Lý 12', 'price' => 52000, 'quantity' => 2, 'category_id' => 1, 'description' => 'Sách Vật Lý lớp 12'],
            ['name' => 'Sách Hóa 12', 'price' => 51000, 'quantity' => 2, 'category_id' => 1, 'description' => 'Sách Hóa Học lớp 12'],
            ['name' => 'Sách Sinh 12', 'price' => 49000, 'quantity' => 2, 'category_id' => 1, 'description' => 'Sách Sinh Học lớp 12'],
            ['name' => 'Sách Sử 12', 'price' => 47000, 'quantity' => 2, 'category_id' => 1, 'description' => 'Sách Lịch Sử lớp 12'],
            ['name' => 'Sách Địa 12', 'price' => 46000, 'quantity' => 2, 'category_id' => 1, 'description' => 'Sách Địa Lý lớp 12'],
        ];

        foreach ($post1Products as $prod) {
            Product::create(array_merge($prod, ['post_id' => $post1->id, 'is_sold' => false]));
        }

        // Post 2: Đồ điện tử và phụ kiện
        $post2 = Post::create([
            'user_id' => $user2->id,
            'university_id' => 2,
            'title' => 'Thanh lý đồ điện tử - Laptop, chuột, bàn phím, tai nghe',
            'content' => 'Laptop còn dùng tốt, mình đổi máy mới nên bán lại. Máy chạy mượt, pin còn ổn. Có thể test trước khi mua.',
            'status' => 'approved',
            'view_count' => 128,
        ]);

        $post2Products = [
            ['name' => 'Laptop Dell Inspiron 15', 'price' => 5000000, 'quantity' => 1, 'category_id' => 2, 'description' => 'CPU: Intel i5, RAM: 8GB, HDD: 500GB'],
            ['name' => 'Chuột không dây Logitech', 'price' => 200000, 'quantity' => 1, 'category_id' => 2, 'description' => 'Chuột không dây, còn mới'],
            ['name' => 'Bàn phím cơ Logitech', 'price' => 800000, 'quantity' => 1, 'category_id' => 2, 'description' => 'Bàn phím cơ RGB, switch blue'],
            ['name' => 'Tai nghe Sony WH-1000XM4', 'price' => 3500000, 'quantity' => 1, 'category_id' => 2, 'description' => 'Tai nghe chống ồn, còn bảo hành'],
            ['name' => 'Webcam Logitech C920', 'price' => 1200000, 'quantity' => 1, 'category_id' => 2, 'description' => 'Webcam Full HD, còn mới'],
            ['name' => 'Ổ cứng SSD 256GB', 'price' => 600000, 'quantity' => 1, 'category_id' => 2, 'description' => 'SSD Samsung, còn bảo hành'],
            ['name' => 'USB 3.0 64GB', 'price' => 150000, 'quantity' => 3, 'category_id' => 2, 'description' => 'USB tốc độ cao, còn mới'],
            ['name' => 'Cáp sạc MacBook', 'price' => 300000, 'quantity' => 2, 'category_id' => 2, 'description' => 'Cáp sạc chính hãng Apple'],
        ];

        foreach ($post2Products as $prod) {
            Product::create(array_merge($prod, ['post_id' => $post2->id, 'is_sold' => false]));
        }

        // Post 3: Quần áo và đồ gia dụng
        $post3 = Post::create([
            'user_id' => $user3->id,
            'university_id' => 3,
            'title' => 'Thanh lý đồ dùng sinh viên - Quần áo, chăn gối, bàn học, đồ dùng',
            'content' => 'Mình ra trường nên thanh lý hết đồ. Tất cả đều còn dùng tốt, giá rẻ cho sinh viên.',
            'status' => 'approved',
            'view_count' => 67,
        ]);

        $post3Products = [
            ['name' => 'Áo khoác mùa đông', 'price' => 150000, 'quantity' => 2, 'category_id' => 3, 'description' => 'Áo khoác ấm, size M và L'],
            ['name' => 'Quần jean', 'price' => 100000, 'quantity' => 3, 'category_id' => 3, 'description' => 'Quần jean còn mới, size 28-30'],
            ['name' => 'Áo thun', 'price' => 50000, 'quantity' => 5, 'category_id' => 3, 'description' => 'Áo thun cotton, nhiều màu'],
            ['name' => 'Bàn học gấp', 'price' => 300000, 'quantity' => 1, 'category_id' => 4, 'description' => 'Bàn học gấp gọn, tiết kiệm không gian'],
            ['name' => 'Chăn gối bộ', 'price' => 200000, 'quantity' => 1, 'category_id' => 4, 'description' => 'Bộ chăn gối đầy đủ, sạch sẽ'],
            ['name' => 'Giá treo quần áo', 'price' => 50000, 'quantity' => 2, 'category_id' => 4, 'description' => 'Giá treo quần áo inox, chắc chắn'],
            ['name' => 'Bóng đèn LED', 'price' => 30000, 'quantity' => 5, 'category_id' => 4, 'description' => 'Bóng đèn LED tiết kiệm điện'],
            ['name' => 'Quạt điện mini', 'price' => 180000, 'quantity' => 1, 'category_id' => 4, 'description' => 'Quạt điện nhỏ gọn, tiết kiệm điện'],
        ];

        foreach ($post3Products as $prod) {
            Product::create(array_merge($prod, ['post_id' => $post3->id, 'is_sold' => false]));
        }

        // Post 4: Đồ dùng học tập
        $post4 = Post::create([
            'user_id' => $user4->id,
            'university_id' => 4,
            'title' => 'Bán đồ dùng học tập - Bút, vở, sổ, balo, máy tính',
            'content' => 'Mình mua nhiều nhưng không dùng hết, bán lại cho các bạn sinh viên. Tất cả đều còn mới.',
            'status' => 'approved',
            'view_count' => 89,
        ]);

        $post4Products = [
            ['name' => 'Bút bi Pilot', 'price' => 10000, 'quantity' => 20, 'category_id' => 6, 'description' => 'Bút bi Pilot, nhiều màu'],
            ['name' => 'Vở học sinh 200 trang', 'price' => 25000, 'quantity' => 10, 'category_id' => 6, 'description' => 'Vở kẻ ngang, giấy tốt'],
            ['name' => 'Sổ tay A5', 'price' => 35000, 'quantity' => 8, 'category_id' => 6, 'description' => 'Sổ tay bìa cứng, giấy trắng'],
            ['name' => 'Balo laptop', 'price' => 250000, 'quantity' => 1, 'category_id' => 6, 'description' => 'Balo chống sốc, ngăn riêng laptop'],
            ['name' => 'Máy tính Casio fx-580VN X', 'price' => 450000, 'quantity' => 1, 'category_id' => 6, 'description' => 'Máy tính khoa học, còn bảo hành'],
            ['name' => 'Thước kẻ bộ', 'price' => 30000, 'quantity' => 3, 'category_id' => 6, 'description' => 'Bộ thước kẻ đầy đủ'],
            ['name' => 'Bút highlight', 'price' => 15000, 'quantity' => 12, 'category_id' => 6, 'description' => 'Bút highlight nhiều màu'],
            ['name' => 'Kẹp giấy', 'price' => 20000, 'quantity' => 5, 'category_id' => 6, 'description' => 'Hộp kẹp giấy các loại'],
        ];

        foreach ($post4Products as $prod) {
            Product::create(array_merge($prod, ['post_id' => $post4->id, 'is_sold' => false]));
        }

        // Post 5: Xe đạp và đồ thể thao
        $post5 = Post::create([
            'user_id' => $user5->id,
            'university_id' => 5,
            'title' => 'Bán xe đạp và đồ thể thao - Xe đạp, giày, quần áo thể thao',
            'content' => 'Mình chuyển nhà nên bán lại xe đạp và đồ thể thao. Tất cả đều còn dùng tốt.',
            'status' => 'approved',
            'view_count' => 156,
        ]);

        $post5Products = [
            ['name' => 'Xe đạp địa hình', 'price' => 1500000, 'quantity' => 1, 'category_id' => 5, 'description' => 'Xe đạp địa hình, phanh đĩa'],
            ['name' => 'Giày thể thao Nike', 'price' => 800000, 'quantity' => 1, 'category_id' => 7, 'description' => 'Giày chạy bộ, size 42'],
            ['name' => 'Quần áo thể thao', 'price' => 120000, 'quantity' => 3, 'category_id' => 7, 'description' => 'Bộ quần áo thể thao, co giãn tốt'],
            ['name' => 'Bóng đá', 'price' => 150000, 'quantity' => 1, 'category_id' => 7, 'description' => 'Bóng đá size 5, còn mới'],
            ['name' => 'Vợt cầu lông', 'price' => 200000, 'quantity' => 2, 'category_id' => 7, 'description' => 'Vợt cầu lông Yonex'],
            ['name' => 'Túi đựng vợt', 'price' => 80000, 'quantity' => 1, 'category_id' => 7, 'description' => 'Túi đựng vợt cầu lông'],
            ['name' => 'Bình nước thể thao', 'price' => 50000, 'quantity' => 2, 'category_id' => 7, 'description' => 'Bình nước 750ml, chống rò rỉ'],
            ['name' => 'Khăn thể thao', 'price' => 30000, 'quantity' => 5, 'category_id' => 7, 'description' => 'Khăn thể thao thấm hút tốt'],
        ];

        foreach ($post5Products as $prod) {
            Product::create(array_merge($prod, ['post_id' => $post5->id, 'is_sold' => false]));
        }

        // Post 6: Sách tham khảo và tài liệu
        $post6 = Post::create([
            'user_id' => $user1->id,
            'university_id' => 1,
            'title' => 'Bán sách tham khảo và tài liệu học tập - Sách chuyên ngành',
            'content' => 'Bán lại sách tham khảo và tài liệu học tập. Sách còn mới, không viết vẽ.',
            'status' => 'approved',
            'view_count' => 92,
        ]);

        $post6Products = [
            ['name' => 'Sách Lập trình C++', 'price' => 120000, 'quantity' => 1, 'category_id' => 1, 'description' => 'Sách lập trình C++ cơ bản và nâng cao'],
            ['name' => 'Sách Cấu trúc dữ liệu', 'price' => 110000, 'quantity' => 1, 'category_id' => 1, 'description' => 'Sách cấu trúc dữ liệu và giải thuật'],
            ['name' => 'Sách Database', 'price' => 130000, 'quantity' => 1, 'category_id' => 1, 'description' => 'Sách hệ quản trị cơ sở dữ liệu'],
            ['name' => 'Sách Mạng máy tính', 'price' => 125000, 'quantity' => 1, 'category_id' => 1, 'description' => 'Sách mạng máy tính và internet'],
            ['name' => 'Sách Kinh tế vi mô', 'price' => 100000, 'quantity' => 1, 'category_id' => 1, 'description' => 'Sách kinh tế vi mô'],
            ['name' => 'Sách Kinh tế vĩ mô', 'price' => 105000, 'quantity' => 1, 'category_id' => 1, 'description' => 'Sách kinh tế vĩ mô'],
            ['name' => 'Tài liệu ôn thi', 'price' => 50000, 'quantity' => 5, 'category_id' => 1, 'description' => 'Tài liệu ôn thi các môn'],
            ['name' => 'Sách tiếng Anh chuyên ngành', 'price' => 90000, 'quantity' => 2, 'category_id' => 1, 'description' => 'Sách tiếng Anh chuyên ngành IT'],
        ];

        foreach ($post6Products as $prod) {
            Product::create(array_merge($prod, ['post_id' => $post6->id, 'is_sold' => false]));
        }

        // Post 7: Đồ điện tử phụ kiện
        $post7 = Post::create([
            'user_id' => $user2->id,
            'university_id' => 2,
            'title' => 'Bán phụ kiện điện tử - Sạc, cáp, ốp lưng, tai nghe',
            'content' => 'Bán lại các phụ kiện điện tử không dùng đến. Tất cả đều còn mới hoặc dùng ít.',
            'status' => 'approved',
            'view_count' => 73,
        ]);

        $post7Products = [
            ['name' => 'Sạc nhanh 20W', 'price' => 150000, 'quantity' => 2, 'category_id' => 2, 'description' => 'Sạc nhanh USB-C, hỗ trợ PD'],
            ['name' => 'Cáp USB-C 2m', 'price' => 80000, 'quantity' => 3, 'category_id' => 2, 'description' => 'Cáp USB-C dài 2m, sạc nhanh'],
            ['name' => 'Ốp lưng iPhone', 'price' => 100000, 'quantity' => 2, 'category_id' => 2, 'description' => 'Ốp lưng trong suốt, chống sốc'],
            ['name' => 'Tai nghe có dây', 'price' => 200000, 'quantity' => 1, 'category_id' => 2, 'description' => 'Tai nghe có dây, âm thanh tốt'],
            ['name' => 'Loa Bluetooth', 'price' => 400000, 'quantity' => 1, 'category_id' => 2, 'description' => 'Loa Bluetooth JBL, pin lâu'],
            ['name' => 'Pin dự phòng 10000mAh', 'price' => 300000, 'quantity' => 1, 'category_id' => 2, 'description' => 'Pin dự phòng sạc nhanh'],
            ['name' => 'Đế tản nhiệt laptop', 'price' => 180000, 'quantity' => 1, 'category_id' => 2, 'description' => 'Đế tản nhiệt có quạt'],
            ['name' => 'Hub USB-C', 'price' => 250000, 'quantity' => 1, 'category_id' => 2, 'description' => 'Hub USB-C 7 trong 1'],
        ];

        foreach ($post7Products as $prod) {
            Product::create(array_merge($prod, ['post_id' => $post7->id, 'is_sold' => false]));
        }

        // Post 8: Đồ gia dụng đầy đủ
        $post8 = Post::create([
            'user_id' => $user3->id,
            'university_id' => 3,
            'title' => 'Thanh lý đồ gia dụng - Nồi, chảo, bát đĩa, đồ dùng nhà bếp',
            'content' => 'Mình chuyển về quê nên thanh lý hết đồ gia dụng. Tất cả đều còn dùng tốt.',
            'status' => 'approved',
            'view_count' => 54,
        ]);

        $post8Products = [
            ['name' => 'Nồi cơm điện', 'price' => 400000, 'quantity' => 1, 'category_id' => 4, 'description' => 'Nồi cơm điện 1.8L, còn mới'],
            ['name' => 'Chảo chống dính', 'price' => 120000, 'quantity' => 2, 'category_id' => 4, 'description' => 'Chảo chống dính, đáy dày'],
            ['name' => 'Bộ bát đĩa', 'price' => 150000, 'quantity' => 1, 'category_id' => 4, 'description' => 'Bộ bát đĩa 6 người'],
            ['name' => 'Bình đun nước', 'price' => 200000, 'quantity' => 1, 'category_id' => 4, 'description' => 'Bình đun nước điện, 1.7L'],
            ['name' => 'Dao thớt bộ', 'price' => 80000, 'quantity' => 1, 'category_id' => 4, 'description' => 'Bộ dao thớt đầy đủ'],
            ['name' => 'Hộp đựng thức ăn', 'price' => 50000, 'quantity' => 6, 'category_id' => 4, 'description' => 'Hộp đựng thức ăn có nắp'],
            ['name' => 'Thìa đũa bộ', 'price' => 40000, 'quantity' => 2, 'category_id' => 4, 'description' => 'Bộ thìa đũa inox'],
            ['name' => 'Rổ rá nhựa', 'price' => 30000, 'quantity' => 3, 'category_id' => 4, 'description' => 'Rổ rá nhựa các kích cỡ'],
        ];

        foreach ($post8Products as $prod) {
            Product::create(array_merge($prod, ['post_id' => $post8->id, 'is_sold' => false]));
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin: admin@ecostudent.com / password123');
        $this->command->info('Users: an@example.com, binh@example.com, cuong@example.com, dung@example.com, em@example.com / password123');
    }
}
