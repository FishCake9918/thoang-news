USE thoang_vn;

ALTER TABLE users
  ADD COLUMN avatar VARCHAR(120) DEFAULT 'images/avatars/avatar-01.svg' AFTER full_name;

ALTER TABLE categories
  ADD COLUMN parent_id INT NULL AFTER slug,
  ADD COLUMN sort_order INT NOT NULL DEFAULT 0 AFTER color_text,
  ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER sort_order;

ALTER TABLE categories
  ADD INDEX idx_categories_parent (parent_id),
  ADD CONSTRAINT fk_categories_parent
    FOREIGN KEY (parent_id) REFERENCES categories(id)
    ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE categories
SET sort_order = id
WHERE sort_order = 0;

UPDATE articles
SET status = 'Approved'
WHERE status = 'published';
