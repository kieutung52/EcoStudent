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
        // 1. Tạo Universities
        $universities = [
            ['name' => 'Đại học Bách Khoa Hà Nội', 'code' => 'BKHN', 'address' => 'Số 1 Đại Cồ Việt, Hai Bà Trưng, Hà Nội'],
            ['name' => 'Đại học Kinh tế Quốc dân', 'code' => 'NEU', 'address' => '207 Giải Phóng, Đồng Tâm, Hai Bà Trưng, Hà Nội'],
            ['name' => 'Đại học FPT', 'code' => 'FPT', 'address' => 'Khu Công nghệ cao Hòa Lạc, Thạch Thất, Hà Nội'],
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
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        // 3. Tạo Users (4 users: 1 admin, 3 users)
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

        // 4. Tạo Posts với Products
        // Post 1: Sách giáo khoa
        $post1 = Post::create([
            'user_id' => $user1->id,
            'university_id' => 1,
            'title' => 'Bán sách giáo khoa lớp 12 - Tất cả các môn',
            'content' => 'Mình đã tốt nghiệp nên bán lại bộ sách giáo khoa lớp 12. Sách còn mới, không viết vẽ gì. Ai cần thì liên hệ nhé!',
            'status' => 'approved',
            'view_count' => 45,
        ]);

        Product::create([
            'post_id' => $post1->id,
            'category_id' => 1,
            'name' => 'Sách Toán 12',
            'price' => 50000,
            'quantity' => 2,
            'description' => 'Sách Toán lớp 12, còn mới, không viết vẽ',
            'is_sold' => false,
        ]);

        Product::create([
            'post_id' => $post1->id,
            'category_id' => 1,
            'name' => 'Sách Văn 12',
            'price' => 45000,
            'quantity' => 2,
            'description' => 'Sách Ngữ Văn lớp 12',
            'is_sold' => false,
        ]);

        Product::create([
            'post_id' => $post1->id,
            'category_id' => 1,
            'name' => 'Sách Anh 12',
            'price' => 48000,
            'quantity' => 2,
            'description' => 'Sách Tiếng Anh lớp 12',
            'is_sold' => false,
        ]);

        // Post 2: Đồ điện tử
        $post2 = Post::create([
            'user_id' => $user2->id,
            'university_id' => 2,
            'title' => 'Bán laptop cũ - Dell Inspiron 15',
            'content' => 'Laptop còn dùng tốt, mình đổi máy mới nên bán lại. Máy chạy mượt, pin còn ổn. Có thể test trước khi mua.',
            'status' => 'approved',
            'view_count' => 128,
        ]);

        Product::create([
            'post_id' => $post2->id,
            'category_id' => 2,
            'name' => 'Laptop Dell Inspiron 15',
            'price' => 5000000,
            'quantity' => 1,
            'description' => 'CPU: Intel i5, RAM: 8GB, HDD: 500GB, Màn hình 15.6 inch. Đã dùng 2 năm, còn bảo hành 6 tháng.',
            'is_sold' => false,
        ]);

        Product::create([
            'post_id' => $post2->id,
            'category_id' => 2,
            'name' => 'Chuột không dây Logitech',
            'price' => 200000,
            'quantity' => 1,
            'description' => 'Chuột không dây, còn mới, chưa dùng',
            'is_sold' => false,
        ]);

        // Post 3: Quần áo và đồ gia dụng
        $post3 = Post::create([
            'user_id' => $user3->id,
            'university_id' => 3,
            'title' => 'Thanh lý đồ dùng sinh viên - Quần áo, chăn gối, bàn học',
            'content' => 'Mình ra trường nên thanh lý hết đồ. Tất cả đều còn dùng tốt, giá rẻ cho sinh viên.',
            'status' => 'approved',
            'view_count' => 67,
        ]);

        Product::create([
            'post_id' => $post3->id,
            'category_id' => 3,
            'name' => 'Áo khoác mùa đông',
            'price' => 150000,
            'quantity' => 2,
            'description' => 'Áo khoác ấm, size M và L',
            'is_sold' => false,
        ]);

        Product::create([
            'post_id' => $post3->id,
            'category_id' => 3,
            'name' => 'Quần jean',
            'price' => 100000,
            'quantity' => 3,
            'description' => 'Quần jean còn mới, size 28-30',
            'is_sold' => false,
        ]);

        Product::create([
            'post_id' => $post3->id,
            'category_id' => 4,
            'name' => 'Bàn học gấp',
            'price' => 300000,
            'quantity' => 1,
            'description' => 'Bàn học gấp gọn, tiết kiệm không gian',
            'is_sold' => false,
        ]);

        Product::create([
            'post_id' => $post3->id,
            'category_id' => 4,
            'name' => 'Chăn gối bộ',
            'price' => 200000,
            'quantity' => 1,
            'description' => 'Bộ chăn gối đầy đủ, sạch sẽ',
            'is_sold' => false,
        ]);

        Product::create([
            'post_id' => $post3->id,
            'category_id' => 4,
            'name' => 'Giá treo quần áo',
            'price' => 50000,
            'quantity' => 2,
            'description' => 'Giá treo quần áo inox, chắc chắn',
            'is_sold' => false,
        ]);

        Product::create([
            'post_id' => $post3->id,
            'category_id' => 4,
            'name' => 'Bóng đèn LED',
            'price' => 30000,
            'quantity' => 5,
            'description' => 'Bóng đèn LED tiết kiệm điện, còn mới',
            'is_sold' => false,
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin: admin@ecostudent.com / password123');
        $this->command->info('Users: an@example.com, binh@example.com, cuong@example.com / password123');
    }
}
