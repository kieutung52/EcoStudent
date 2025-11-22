<?php

namespace Database\Seeders;

use App\Models\Rule;
use Illuminate\Database\Seeder;

class RuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rules = [
            [
                'title' => 'Nội dung phù hợp',
                'content' => 'Bài đăng phải có nội dung phù hợp, không được chứa thông tin sai sự thật, lừa đảo, hoặc vi phạm pháp luật.',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Hình ảnh sản phẩm',
                'content' => 'Hình ảnh sản phẩm phải là ảnh thật, rõ ràng, không được sử dụng ảnh từ nguồn khác hoặc ảnh mẫu.',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Thông tin giá cả',
                'content' => 'Giá sản phẩm phải chính xác, không được đăng giá sai hoặc cố tình đánh lừa người mua.',
                'order' => 3,
                'is_active' => true,
            ],
            [
                'title' => 'Mô tả sản phẩm',
                'content' => 'Mô tả sản phẩm phải trung thực về tình trạng, không được che giấu khuyết điểm hoặc thông tin quan trọng.',
                'order' => 4,
                'is_active' => true,
            ],
            [
                'title' => 'Sản phẩm cấm',
                'content' => 'Không được đăng các sản phẩm cấm như hàng giả, hàng nhái, chất cấm, hoặc các sản phẩm vi phạm pháp luật.',
                'order' => 5,
                'is_active' => true,
            ],
            [
                'title' => 'Spam và lặp lại',
                'content' => 'Không được đăng bài spam, đăng lặp lại nhiều lần, hoặc đăng bài không liên quan đến mục đích của hệ thống.',
                'order' => 6,
                'is_active' => true,
            ],
            [
                'title' => 'Thông tin liên hệ',
                'content' => 'Phải cung cấp thông tin liên hệ chính xác, không được sử dụng thông tin giả mạo hoặc của người khác.',
                'order' => 7,
                'is_active' => true,
            ],
        ];

        foreach ($rules as $rule) {
            Rule::create($rule);
        }
    }
}

