<?php

namespace App\Core;

class DateHelper
{
    public static function vietnameseToday(): string
    {
        $days = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
        return $days[(int)date('w')] . ', ' . date('d') . ' tháng ' . date('m') . ', ' . date('Y');
    }
}
