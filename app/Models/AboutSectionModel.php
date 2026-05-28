<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class AboutSectionModel extends Model
{
    public function findData(string $key): ?string
    {
        $stmt = $this->db->prepare("SELECT section_data FROM about_sections WHERE section_key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['section_data'] ?? null;
    }

    public function save(string $key, string $data, int $userId): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO about_sections (section_key, section_data, updated_by)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE section_data = VALUES(section_data), updated_by = VALUES(updated_by)"
        );
        $stmt->execute([$key, $data, $userId]);
    }
}
