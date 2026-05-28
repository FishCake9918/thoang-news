<?php

namespace App\Models;

use App\Core\Model;

class FeedbackModel extends Model
{
    public function create(int $userId, string $email, string $subject, string $message): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO feedback (user_id, sender_email, subject, message) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$userId, $email, $subject, $message]);
    }

    public function exists(int $feedbackId): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM feedback WHERE id = ?");
        $stmt->execute([$feedbackId]);
        return (bool)$stmt->fetchColumn();
    }

    public function reply(int $feedbackId, string $reply): void
    {
        $stmt = $this->db->prepare("UPDATE feedback SET admin_reply = ?, status = 'replied', replied_at = NOW() WHERE id = ?");
        $stmt->execute([$reply, $feedbackId]);
    }

    public function markDone(int $feedbackId): void
    {
        $stmt = $this->db->prepare("UPDATE feedback SET status = 'done' WHERE id = ?");
        $stmt->execute([$feedbackId]);
    }

    public function markPending(int $feedbackId): void
    {
        $stmt = $this->db->prepare("UPDATE feedback SET status = 'pending', admin_reply = NULL, replied_at = NULL WHERE id = ?");
        $stmt->execute([$feedbackId]);
    }

    public function delete(int $feedbackId): void
    {
        $stmt = $this->db->prepare("DELETE FROM feedback WHERE id = ?");
        $stmt->execute([$feedbackId]);
    }
}
