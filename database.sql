-- ============================================================
-- Thoáng.vn — Database Schema
-- Import file này vào MySQL, sau đó chạy setup.php 1 lần
-- ============================================================

CREATE DATABASE IF NOT EXISTS thoang_vn
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE thoang_vn;

-- ----------------------------
-- USERS
-- ----------------------------
CREATE TABLE IF NOT EXISTS users (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  username   VARCHAR(50)  UNIQUE NOT NULL,
  email      VARCHAR(100) UNIQUE NOT NULL,
  password   VARCHAR(255) NOT NULL,
  full_name  VARCHAR(100) DEFAULT '',
  role       ENUM('admin','user') DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- ABOUT SECTIONS (JSON per key)
-- ----------------------------
CREATE TABLE IF NOT EXISTS about_sections (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  section_key  VARCHAR(50) UNIQUE NOT NULL,
  section_data LONGTEXT NOT NULL,
  updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  updated_by   INT NULL,
  FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- FEEDBACK / CONTACT MESSAGES
-- ----------------------------
CREATE TABLE IF NOT EXISTS feedback (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  user_id      INT NULL,                      -- NULL = khách vãng lai
  sender_email VARCHAR(100) NOT NULL,
  subject      VARCHAR(200) NOT NULL,
  message      TEXT NOT NULL,
  status       ENUM('pending','replied','done') DEFAULT 'pending',
  admin_reply  TEXT NULL,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  replied_at   TIMESTAMP NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
