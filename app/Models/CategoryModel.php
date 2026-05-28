<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class CategoryModel extends Model
{
    public function all(): array
    {
        $stmt = $this->db->query("SELECT id, name FROM categories ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function ensureOtherCategory(): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO categories (name, slug, color_bg, color_text)
            SELECT 'Khác', 'other', '#E5E7EB', '#374151'
            WHERE NOT EXISTS (SELECT 1 FROM categories WHERE slug = 'other')
        ");
        $stmt->execute();
    }
}
