<?php

namespace App\Controllers;

use App\Models\AboutSectionModel;
use Exception;

class AboutController
{
    private AboutSectionModel $sections;

    public function __construct(AboutSectionModel $sections)
    {
        $this->sections = $sections;
    }

    public function index(): array
    {
        $defaults = $this->defaults();
        $sections = [];

        foreach ($defaults as $key => $defaultValue) {
            try {
                $rawData = $this->sections->findData($key);
                $decoded = $rawData ? json_decode($rawData, true) : null;
                $sections[$key] = is_array($decoded) ? $decoded : $defaultValue;
            } catch (Exception $e) {
                $sections[$key] = $defaultValue;
            }
        }

        return $sections;
    }

    private function defaults(): array
    {
        return [
            'hero' => [
                'eyebrow' => 'Về chúng tôi',
                'title' => 'Tin tức thời <em>thoáng qua,</em><br>kiến thức ở lại.',
                'lead' => 'Thoáng.vn là ứng dụng đọc tin hiện đại, giúp bạn nắm bắt thông tin quan trọng trong khoảng 30 giây.',
            ],
            'stats' => [
                ['num' => '30', 'unit' => 's', 'label' => 'Thời gian đọc mỗi tin'],
                ['num' => '5', 'unit' => '+', 'label' => 'Chủ đề tin tức'],
                ['num' => '100', 'unit' => '%', 'label' => 'Không quảng cáo xâm phạm'],
                ['num' => '0', 'unit' => 'đ', 'label' => 'Hoàn toàn miễn phí'],
            ],
            'mission' => [
                'quote' => '"Mỗi người xứng đáng được tiếp cận thông tin rõ ràng, trung thực và nhanh chóng."',
                'body' => 'Thoáng.vn ra đời để giúp người đọc nắm điều cốt lõi của tin tức nhanh hơn, rõ hơn và ít bị phân tâm hơn.',
            ],
            'values' => [
                ['icon' => 'bi-lightning-charge', 'title' => 'Nhanh mà không nông', 'desc' => 'Cô đọng nhưng vẫn giữ ngữ cảnh cần thiết.'],
                ['icon' => 'bi-shield-check', 'title' => 'Trung thực trước tiên', 'desc' => 'Tin tức có nguồn rõ ràng và được trình bày có trách nhiệm.'],
                ['icon' => 'bi-person-heart', 'title' => 'Người dùng làm trọng', 'desc' => 'Trải nghiệm đọc nhẹ nhàng, không gây nghiện.'],
            ],
            'story' => [
                ['title' => 'Bắt đầu từ nhu cầu đọc nhanh', 'desc' => 'Nhóm xây dựng một cách đọc tin phù hợp với nhịp sống bận rộn.'],
                ['title' => 'Ý tưởng về "thoáng"', 'desc' => 'Tin tức được trình bày theo cách dễ lướt, dễ hiểu và dễ lưu lại.'],
                ['title' => 'Xây dựng cho người Việt', 'desc' => 'Tối ưu cho thói quen đọc tin trên điện thoại và các khoảng thời gian ngắn.'],
            ],
            'features' => [
                ['icon' => 'bi-hand-index', 'text' => 'Swipe để lưu / bỏ qua'],
                ['icon' => 'bi-clock', 'text' => 'Đọc trong 30 giây'],
                ['icon' => 'bi-funnel', 'text' => 'Lọc theo chủ đề'],
                ['icon' => 'bi-bookmark-check', 'text' => 'Lưu tin quan trọng'],
            ],
            'team' => [
                ['initials' => 'NK', 'name' => 'Nguyễn Khánh Hoàng', 'role' => 'Frontend Developer'],
                ['initials' => 'HN', 'name' => 'Hứa Đức Nghĩa', 'role' => 'Backend Developer'],
                ['initials' => 'LV', 'name' => 'Lê Hoàng Việt', 'role' => 'UI/UX Designer'],
                ['initials' => 'MT', 'name' => 'Nguyễn Kiều Minh Trí', 'role' => 'Content & SEO'],
            ],
            'cta' => [
                'title' => 'Thoáng qua là nắm ngay.',
                'desc' => 'Hãy thử một lần đọc tin theo cách khác: nhanh hơn, rõ hơn, ít nhiễu hơn.',
                'btn1_text' => 'Đọc tin ngay',
                'btn1_url' => 'index.php',
                'btn2_text' => 'Gửi góp ý',
                'btn2_url' => '#',
            ],
        ];
    }
}
