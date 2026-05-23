CREATE DATABASE IF NOT EXISTS thoang_vn
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE thoang_vn;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS user_preferences;
DROP TABLE IF EXISTS theme_settings;
DROP TABLE IF EXISTS article_summary;
DROP TABLE IF EXISTS bookmarks;
DROP TABLE IF EXISTS feedback;
DROP TABLE IF EXISTS articles;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS about_sections;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  full_name VARCHAR(100) DEFAULT '',
  role ENUM('admin','writer','user') DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(100) NOT NULL UNIQUE,
  color_bg VARCHAR(20) DEFAULT '#EEEDFE',
  color_text VARCHAR(20) DEFAULT '#534AB7'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE articles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  summary TEXT NOT NULL,
  content LONGTEXT DEFAULT NULL,
  category_id INT NOT NULL,
  author_id INT NULL,
  source VARCHAR(100) DEFAULT NULL,
  tags VARCHAR(255) DEFAULT NULL,
  image_url VARCHAR(500) DEFAULT NULL,
  status VARCHAR(50) NOT NULL DEFAULT 'request',
  view_count INT DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  published_at TIMESTAMP NULL DEFAULT NULL,
  KEY idx_articles_category (category_id),
  KEY idx_articles_author_status (author_id, status),
  CONSTRAINT fk_articles_categories
    FOREIGN KEY (category_id) REFERENCES categories(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_articles_author
    FOREIGN KEY (author_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE bookmarks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  article_id INT NOT NULL,
  session_id VARCHAR(255) NOT NULL,
  user_id INT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_session_article (session_id, article_id),
  KEY idx_bookmarks_article (article_id),
  CONSTRAINT fk_bookmarks_articles
    FOREIGN KEY (article_id) REFERENCES articles(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_bookmarks_users
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE feedback (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  sender_email VARCHAR(100) NOT NULL,
  subject VARCHAR(200) NOT NULL,
  message TEXT NOT NULL,
  status ENUM('pending','replied','done') DEFAULT 'pending',
  admin_reply TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  replied_at TIMESTAMP NULL DEFAULT NULL,
  KEY idx_feedback_user (user_id),
  CONSTRAINT fk_feedback_users
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE about_sections (
  id INT AUTO_INCREMENT PRIMARY KEY,
  section_key VARCHAR(50) NOT NULL UNIQUE,
  section_data LONGTEXT NOT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  updated_by INT NULL,
  KEY idx_about_updated_by (updated_by),
  CONSTRAINT fk_about_users
    FOREIGN KEY (updated_by) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE article_summary (
  id INT AUTO_INCREMENT PRIMARY KEY,
  article_id INT NOT NULL,
  is_summary_visible TINYINT(1) DEFAULT 0,
  summary_text TEXT DEFAULT NULL,
  KEY idx_summary_article (article_id),
  CONSTRAINT fk_summary_articles
    FOREIGN KEY (article_id) REFERENCES articles(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE theme_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  article_id INT NOT NULL,
  theme VARCHAR(20) DEFAULT 'light',
  KEY idx_theme_user (user_id),
  KEY idx_theme_article (article_id),
  CONSTRAINT fk_theme_users
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_theme_articles
    FOREIGN KEY (article_id) REFERENCES articles(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_preferences (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  font_size INT(3) DEFAULT 15,
  theme VARCHAR(20) DEFAULT 'light',
  KEY idx_prefs_user (user_id),
  CONSTRAINT fk_prefs_users
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (id, username, email, password, full_name, role) VALUES
(1, 'admin123', 'admin@thoang.vn', '$2y$10$Jop12xzbuAPxGhyldwVDgeJmTBx4ZGQsGIdgbhEDGIHrgFVMgx1lW', 'Quản trị viên', 'admin'),
(2, 'writer01', 'writer@thoang.vn', '$2y$10$Jop12xzbuAPxGhyldwVDgeJmTBx4ZGQsGIdgbhEDGIHrgFVMgx1lW', 'Tác giả Thoáng.vn', 'writer'),
(3, 'user01', 'user@thoang.vn', '$2y$10$Jop12xzbuAPxGhyldwVDgeJmTBx4ZGQsGIdgbhEDGIHrgFVMgx1lW', 'Người đọc mẫu', 'user');

INSERT INTO categories (id, name, slug, color_bg, color_text) VALUES
(1, 'Công nghệ', 'tech', '#EEEDFE', '#534AB7'),
(2, 'Đời sống', 'life', '#D4EDDA', '#155724'),
(3, 'Thể thao', 'sport', '#F8D7DA', '#721c24'),
(4, 'Kinh tế', 'biz', '#FFF3CD', '#856404'),
(5, 'Thế giới', 'world', '#D1ECF1', '#0c5460'),
(6, 'Giáo dục', 'edu', '#E2E3E5', '#383d41'),
(7, 'Khác', 'other', '#E5E7EB', '#374151');

INSERT INTO articles
  (id, title, summary, content, category_id, author_id, source, tags, image_url, status, view_count, created_at, updated_at, published_at)
VALUES
(1, 'AI tạo sinh đang thay đổi cách chúng ta làm việc', 'Trí tuệ nhân tạo đang tham gia sâu hơn vào các quy trình làm việc hằng ngày.', '<p>AI tạo sinh giúp rút ngắn thời gian xử lý tài liệu, lập kế hoạch và xây dựng ý tưởng. Người dùng cần biết cách kiểm chứng thông tin để sử dụng công cụ này hiệu quả.</p>', 1, 2, 'Tác giả Thoáng.vn', 'AI, Công nghệ, Làm việc', 'https://picsum.photos/seed/thoang-ai/1200/700', 'published', 152, NOW() - INTERVAL 19 DAY, NOW() - INTERVAL 19 DAY, NOW() - INTERVAL 19 DAY),
(2, 'VN-Index tăng mạnh nhờ nhóm ngân hàng', 'Nhóm cổ phiếu ngân hàng dẫn dắt thị trường trong phiên giao dịch sôi động.', '<p>Dòng tiền quay lại nhóm vốn hóa lớn giúp chỉ số duy trì sắc xanh. Thanh khoản cải thiện cho thấy tâm lý nhà đầu tư tích cực hơn.</p>', 4, 2, 'Tác giả Thoáng.vn', 'Chứng khoán, Ngân hàng', 'https://picsum.photos/seed/thoang-stock/1200/700', 'published', 98, NOW() - INTERVAL 18 DAY, NOW() - INTERVAL 18 DAY, NOW() - INTERVAL 18 DAY),
(3, 'Đội tuyển Việt Nam công bố danh sách tập trung', 'Huấn luyện viên trưởng trao cơ hội cho nhiều gương mặt trẻ ở đợt hội quân mới.', '<p>Danh sách mới cân bằng giữa kinh nghiệm và sức trẻ. Ban huấn luyện kỳ vọng đội hình có thêm phương án chiến thuật linh hoạt.</p>', 3, 2, 'Tác giả Thoáng.vn', 'Bóng đá, Đội tuyển', 'https://picsum.photos/seed/thoang-football/1200/700', 'published', 213, NOW() - INTERVAL 17 DAY, NOW() - INTERVAL 17 DAY, NOW() - INTERVAL 17 DAY),
(4, 'Các thành phố châu Á đẩy mạnh giao thông xanh', 'Nhiều đô thị tăng đầu tư xe buýt điện và hạ tầng sạc công cộng.', '<p>Giao thông xanh trở thành ưu tiên trong quy hoạch đô thị. Việc mở rộng phương tiện điện giúp giảm phát thải và cải thiện chất lượng không khí.</p>', 5, 2, 'Tác giả Thoáng.vn', 'Thế giới, Môi trường', 'https://picsum.photos/seed/thoang-green-city/1200/700', 'published', 76, NOW() - INTERVAL 16 DAY, NOW() - INTERVAL 16 DAY, NOW() - INTERVAL 16 DAY),
(5, 'Thói quen đọc sách trở lại trong giới trẻ', 'Các câu lạc bộ đọc sách và hội chợ sách ghi nhận lượng tham gia tăng.', '<p>Không gian đọc cộng đồng giúp sách giấy có thêm sức sống. Nhiều bạn trẻ xem đọc sách là cách nghỉ ngơi khỏi nhịp sống số.</p>', 2, 2, 'Tác giả Thoáng.vn', 'Đời sống, Sách', 'https://picsum.photos/seed/thoang-books/1200/700', 'published', 64, NOW() - INTERVAL 15 DAY, NOW() - INTERVAL 15 DAY, NOW() - INTERVAL 15 DAY),
(6, 'Trường đại học mở thêm chương trình học dữ liệu', 'Các ngành phân tích dữ liệu tiếp tục thu hút thí sinh quan tâm.', '<p>Nhu cầu nhân lực dữ liệu tăng khiến nhiều trường cập nhật chương trình đào tạo. Sinh viên được học thêm kỹ năng phân tích, trực quan hóa và đạo đức dữ liệu.</p>', 6, 2, 'Tác giả Thoáng.vn', 'Giáo dục, Dữ liệu', 'https://picsum.photos/seed/thoang-data/1200/700', 'published', 121, NOW() - INTERVAL 14 DAY, NOW() - INTERVAL 14 DAY, NOW() - INTERVAL 14 DAY),
(7, 'Startup Việt phát triển nền tảng chăm sóc sức khỏe số', 'Ứng dụng mới hỗ trợ đặt lịch khám và theo dõi hồ sơ sức khỏe cá nhân.', '<p>Chuyển đổi số trong y tế giúp người dùng tiếp cận dịch vụ thuận tiện hơn. Các nền tảng cần bảo đảm an toàn dữ liệu và trải nghiệm dễ dùng.</p>', 1, 2, 'Tác giả Thoáng.vn', 'Startup, Y tế số', 'https://picsum.photos/seed/thoang-health-tech/1200/700', 'published', 87, NOW() - INTERVAL 13 DAY, NOW() - INTERVAL 13 DAY, NOW() - INTERVAL 13 DAY),
(8, 'Giá vàng trong nước biến động nhẹ', 'Thị trường vàng điều chỉnh sau nhiều phiên tăng liên tiếp.', '<p>Nhà đầu tư thận trọng hơn khi chênh lệch giá mua bán mở rộng. Chuyên gia khuyến nghị theo dõi xu hướng lãi suất và tỷ giá.</p>', 4, 2, 'Tác giả Thoáng.vn', 'Vàng, Tài chính', 'https://picsum.photos/seed/thoang-gold/1200/700', 'published', 144, NOW() - INTERVAL 12 DAY, NOW() - INTERVAL 12 DAY, NOW() - INTERVAL 12 DAY),
(9, 'Giải chạy cộng đồng thu hút hơn 5.000 người', 'Sự kiện thể thao cuối tuần lan tỏa thói quen vận động lành mạnh.', '<p>Người tham gia có nhiều lựa chọn cự ly phù hợp thể lực. Ban tổ chức dành một phần kinh phí cho hoạt động thiện nguyện.</p>', 3, 2, 'Tác giả Thoáng.vn', 'Running, Cộng đồng', 'https://picsum.photos/seed/thoang-running/1200/700', 'published', 72, NOW() - INTERVAL 11 DAY, NOW() - INTERVAL 11 DAY, NOW() - INTERVAL 11 DAY),
(10, 'Công nghệ pin mới hứa hẹn tăng thời lượng thiết bị', 'Các phòng thí nghiệm đang thử nghiệm vật liệu pin an toàn và bền hơn.', '<p>Pin thế hệ mới có thể cải thiện thời gian sử dụng thiết bị di động. Tuy vậy, quá trình thương mại hóa vẫn cần thêm kiểm chứng về chi phí và độ ổn định.</p>', 1, 2, 'Tác giả Thoáng.vn', 'Pin, Công nghệ', 'https://picsum.photos/seed/thoang-battery/1200/700', 'published', 191, NOW() - INTERVAL 10 DAY, NOW() - INTERVAL 10 DAY, NOW() - INTERVAL 10 DAY),
(11, 'Du lịch nội địa bước vào mùa cao điểm', 'Các điểm đến biển đảo ghi nhận lượng đặt phòng tăng trong kỳ nghỉ hè.', '<p>Doanh nghiệp du lịch tung nhiều gói ưu đãi để kích cầu. Du khách ưu tiên lịch trình ngắn, chi phí hợp lý và trải nghiệm địa phương.</p>', 2, 2, 'Tác giả Thoáng.vn', 'Du lịch, Hè', 'https://picsum.photos/seed/thoang-travel/1200/700', 'published', 58, NOW() - INTERVAL 9 DAY, NOW() - INTERVAL 9 DAY, NOW() - INTERVAL 9 DAY),
(12, 'Hội nghị khí hậu kêu gọi tăng tốc giảm phát thải', 'Các quốc gia thảo luận mục tiêu tài chính khí hậu và năng lượng tái tạo.', '<p>Đàm phán khí hậu tiếp tục nhấn mạnh vai trò của công nghệ sạch. Nhiều cam kết cần được chuyển thành kế hoạch triển khai cụ thể.</p>', 5, 2, 'Tác giả Thoáng.vn', 'Khí hậu, Thế giới', 'https://picsum.photos/seed/thoang-climate/1200/700', 'published', 136, NOW() - INTERVAL 8 DAY, NOW() - INTERVAL 8 DAY, NOW() - INTERVAL 8 DAY),
(13, 'Nhiều gia đình chọn bữa cơm tối không thiết bị số', 'Xu hướng tạm rời màn hình giúp cải thiện kết nối trong gia đình.', '<p>Các chuyên gia khuyến khích tạo khoảng thời gian không điện thoại trong ngày. Bữa cơm tối là điểm khởi đầu dễ thực hiện.</p>', 2, 2, 'Tác giả Thoáng.vn', 'Gia đình, Đời sống', 'https://picsum.photos/seed/thoang-family/1200/700', 'published', 49, NOW() - INTERVAL 7 DAY, NOW() - INTERVAL 7 DAY, NOW() - INTERVAL 7 DAY),
(14, 'Nền tảng học trực tuyến tích hợp trợ lý AI', 'Người học có thể nhận gợi ý lộ trình và bài tập cá nhân hóa.', '<p>Trợ lý AI trong giáo dục giúp người học ôn tập chủ động. Giáo viên vẫn giữ vai trò định hướng và đánh giá chất lượng học tập.</p>', 6, 2, 'Tác giả Thoáng.vn', 'AI, Giáo dục', 'https://picsum.photos/seed/thoang-elearning/1200/700', 'published', 112, NOW() - INTERVAL 6 DAY, NOW() - INTERVAL 6 DAY, NOW() - INTERVAL 6 DAY),
(15, 'Doanh nghiệp nhỏ tăng bán hàng qua mạng xã hội', 'Kênh livestream và nội dung ngắn tiếp tục là công cụ bán hàng hiệu quả.', '<p>Mạng xã hội giúp doanh nghiệp tiếp cận khách hàng với chi phí thấp. Khâu chăm sóc sau bán và vận hành kho vẫn là thách thức lớn.</p>', 4, 2, 'Tác giả Thoáng.vn', 'Kinh doanh, Mạng xã hội', 'https://picsum.photos/seed/thoang-social-commerce/1200/700', 'published', 167, NOW() - INTERVAL 5 DAY, NOW() - INTERVAL 5 DAY, NOW() - INTERVAL 5 DAY),
(16, 'Câu lạc bộ trẻ tạo bất ngờ tại giải quốc nội', 'Lối chơi tốc độ giúp đội bóng trẻ liên tục giành điểm trước đối thủ mạnh.', '<p>Thành tích tích cực đến từ chiến lược đào tạo dài hạn. Người hâm mộ kỳ vọng đội bóng duy trì phong độ trong phần còn lại mùa giải.</p>', 3, 2, 'Tác giả Thoáng.vn', 'Thể thao, Bóng đá', 'https://picsum.photos/seed/thoang-club/1200/700', 'published', 93, NOW() - INTERVAL 4 DAY, NOW() - INTERVAL 4 DAY, NOW() - INTERVAL 4 DAY),
(17, 'Không gian làm việc linh hoạt được ưa chuộng', 'Nhiều công ty kết hợp văn phòng truyền thống với lịch làm việc từ xa.', '<p>Mô hình linh hoạt giúp nhân sự cân bằng tốt hơn giữa công việc và đời sống. Doanh nghiệp cần thiết kế quy trình phối hợp rõ ràng.</p>', 7, 2, 'Tác giả Thoáng.vn', 'Làm việc, Khác', 'https://picsum.photos/seed/thoang-workspace/1200/700', 'published', 81, NOW() - INTERVAL 3 DAY, NOW() - INTERVAL 3 DAY, NOW() - INTERVAL 3 DAY),
(18, 'Bài chờ duyệt về triển lãm công nghệ', 'Bài viết này dùng để kiểm tra trạng thái chờ duyệt trong dashboard admin.', '<p>Nội dung đang chờ admin xem xét. Người đọc thường sẽ không thấy bài này ở trang chủ.</p>', 1, 2, 'Tác giả Thoáng.vn', 'Chờ duyệt, Công nghệ', NULL, 'request', 0, NOW() - INTERVAL 2 DAY, NOW() - INTERVAL 2 DAY, NULL),
(19, 'Bài chờ duyệt về giáo dục kỹ năng số', 'Bài viết mẫu thứ hai ở trạng thái chờ duyệt.', '<p>Writer có thể chỉnh sửa bài viết này. Admin có thể xem trước và chuyển trạng thái xuất bản.</p>', 6, 2, 'Tác giả Thoáng.vn', 'Chờ duyệt, Giáo dục', NULL, 'request', 0, NOW() - INTERVAL 1 DAY, NOW() - INTERVAL 1 DAY, NULL),
(20, 'Bài mẫu bị từ chối', 'Bài viết này minh họa trạng thái bị từ chối trong dashboard writer.', '<p>Trạng thái bị từ chối giúp writer phân biệt với bài đang chờ duyệt hoặc đã xuất bản.</p>', 7, 2, 'Tác giả Thoáng.vn', 'Từ chối, Mẫu', NULL, 'disapproved', 0, NOW(), NOW(), NULL);

INSERT INTO about_sections (section_key, section_data, updated_by) VALUES
('gioi-thieu', '{"title":"Về hệ thống Thoáng","content":"Nền tảng đọc tin tức nhanh, gọn và dễ theo dõi."}', 1),
('lien-he', '{"email":"contact@thoang.vn","hotline":"1900 1000","address":"TP.HCM"}', 1);

INSERT INTO feedback (user_id, sender_email, subject, message, status) VALUES
(3, 'user@thoang.vn', 'Góp ý giao diện', 'Trang đọc tin hoạt động tốt, mong có thêm nhiều danh mục hơn.', 'pending');

INSERT INTO user_preferences (user_id, font_size, theme) VALUES
(3, 15, 'light');

COMMIT;
