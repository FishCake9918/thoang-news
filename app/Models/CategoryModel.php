<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class CategoryModel extends Model
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT id, name
            FROM categories
            WHERE is_active = 1
            ORDER BY parent_id IS NOT NULL, sort_order ASC, id ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function activeTree(): array
    {
        $stmt = $this->db->query("
            SELECT id, name, slug, parent_id
            FROM categories
            WHERE is_active = 1
            ORDER BY parent_id IS NOT NULL, sort_order ASC, id ASC
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $tree = [];

        foreach ($rows as $row) {
            if ($row['parent_id'] === null) {
                $row['children'] = [];
                $tree[(int)$row['id']] = $row;
            }
        }

        foreach ($rows as $row) {
            $parentId = $row['parent_id'] !== null ? (int)$row['parent_id'] : 0;
            if ($parentId > 0 && isset($tree[$parentId])) {
                $tree[$parentId]['children'][] = $row;
            }
        }

        return array_values($tree);
    }

    public function isSelectableForArticle(int $categoryId): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM categories c
            WHERE c.id = ?
              AND c.is_active = 1
              AND NOT EXISTS (
                SELECT 1
                FROM categories child
                WHERE child.parent_id = c.id
                  AND child.is_active = 1
              )
        ");
        $stmt->execute([$categoryId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function ensureOtherCategory(): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO categories (name, slug, color_bg, color_text, sort_order, is_active)
            SELECT 'Khác', 'other', '#E5E7EB', '#374151', 999, 1
            WHERE NOT EXISTS (SELECT 1 FROM categories WHERE slug = 'other')
        ");
        $stmt->execute();
    }
}
