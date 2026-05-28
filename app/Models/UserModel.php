<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class UserModel extends Model
{
    public function findByLogin(string $login): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$login, $login]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function usernameOrEmailExists(string $username, string $email): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$username, $email]);
        return (bool)$stmt->fetchColumn();
    }

    public function createUser(string $username, string $email, string $passwordHash, string $fullName, string $verifyToken): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password, full_name, role, is_verified, verify_token)
            VALUES (?, ?, ?, ?, 'user', 0, ?)
        ");
        return $stmt->execute([$username, $email, $passwordHash, $fullName, $verifyToken]);
    }

    public function mergeGuestBookmarks(int $userId, string $oldSessionId, string $newSessionId): void
    {
        $stmt = $this->db->prepare("UPDATE IGNORE bookmarks SET user_id = ? WHERE session_id = ? AND user_id IS NULL");
        $stmt->execute([$userId, $oldSessionId]);

        $stmt = $this->db->prepare("UPDATE IGNORE bookmarks SET session_id = ? WHERE user_id = ?");
        $stmt->execute([$newSessionId, $userId]);

        $stmt = $this->db->prepare("DELETE FROM bookmarks WHERE user_id = ? AND session_id != ?");
        $stmt->execute([$userId, $newSessionId]);

        $stmt = $this->db->prepare("DELETE FROM bookmarks WHERE session_id = ? AND user_id IS NULL");
        $stmt->execute([$oldSessionId]);
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT id, full_name FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function setResetToken(int $userId, string $token, string $expires): void
    {
        $stmt = $this->db->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
        $stmt->execute([$token, $expires, $userId]);
    }

    public function findIdByValidResetToken(string $token): int
    {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1");
        $stmt->execute([$token]);
        return (int)($stmt->fetchColumn() ?: 0);
    }

    public function resetPassword(int $userId, string $passwordHash): bool
    {
        $stmt = $this->db->prepare("
            UPDATE users
            SET password = ?, reset_token = NULL, reset_expires = NULL
            WHERE id = ?
        ");
        return $stmt->execute([$passwordHash, $userId]);
    }

    public function findVerificationState(string $token): ?array
    {
        $stmt = $this->db->prepare("SELECT id, is_verified FROM users WHERE verify_token = ? LIMIT 1");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function markVerified(int $userId): bool
    {
        $stmt = $this->db->prepare("UPDATE users SET is_verified = 1, verify_token = NULL WHERE id = ?");
        return $stmt->execute([$userId]);
    }
}
