CREATE DATABASE IF NOT EXISTS thoang_vn
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

SET NAMES utf8mb4;

USE thoang_vn;

START TRANSACTION;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS comments;
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
  is_verified TINYINT(1) NOT NULL DEFAULT 0,
  verify_token VARCHAR(255) DEFAULT NULL,
  reset_token VARCHAR(255) DEFAULT NULL,
  reset_expires DATETIME DEFAULT NULL,
  theme_preference ENUM('light','dark') NOT NULL DEFAULT 'light',
  article_font_size TINYINT UNSIGNED NOT NULL DEFAULT 16,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(100) NOT NULL UNIQUE,
  parent_id INT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  color_bg VARCHAR(20) DEFAULT '#EEEDFE',
  color_text VARCHAR(20) DEFAULT '#534AB7',
  KEY idx_categories_parent (parent_id),
  KEY idx_categories_active_order (is_active, sort_order),
  CONSTRAINT fk_categories_parent
    FOREIGN KEY (parent_id) REFERENCES categories(id)
    ON DELETE SET NULL ON UPDATE CASCADE
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
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_articles_author
    FOREIGN KEY (author_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  article_id INT NOT NULL,
  user_id INT NOT NULL,
  content TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_comments_article (article_id, created_at),
  KEY idx_comments_user (user_id),
  CONSTRAINT fk_comments_articles
    FOREIGN KEY (article_id) REFERENCES articles(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_comments_users
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE
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

INSERT INTO users (id, username, email, password, full_name, role, is_verified, verify_token, reset_token, reset_expires, theme_preference, article_font_size) VALUES
(1, 'admin123', 'admin@thoang.vn', '$2y$10$Jop12xzbuAPxGhyldwVDgeJmTBx4ZGQsGIdgbhEDGIHrgFVMgx1lW', 'Quản trị viên', 'admin', 1, NULL, NULL, NULL, 'light', 16),
(2, 'writer01', 'writer@thoang.vn', '$2y$10$Jop12xzbuAPxGhyldwVDgeJmTBx4ZGQsGIdgbhEDGIHrgFVMgx1lW', 'Tác giả Thoáng.vn', 'writer', 1, NULL, NULL, NULL, 'dark', 17),
(3, 'user01', 'user@thoang.vn', '$2y$10$Jop12xzbuAPxGhyldwVDgeJmTBx4ZGQsGIdgbhEDGIHrgFVMgx1lW', 'Người đọc mẫu', 'user', 1, NULL, NULL, NULL, 'light', 16);

ALTER TABLE users AUTO_INCREMENT = 4;

INSERT INTO categories (id, name, slug, parent_id, sort_order, is_active, color_bg, color_text) VALUES
(1, 'Công nghệ', 'tech', NULL, 5, 1, '#EEEDFE', '#534AB7'),
(2, 'Đời sống', 'life', NULL, 6, 1, '#D4EDDA', '#155724'),
(3, 'Thể thao', 'sport', NULL, 4, 1, '#F8D7DA', '#721c24'),
(4, 'Kinh tế', 'biz', NULL, 3, 1, '#FFF3CD', '#856404'),
(5, 'Thế giới', 'world', NULL, 2, 1, '#D1ECF1', '#0c5460'),
(6, 'Giáo dục', 'edu', NULL, 7, 1, '#E2E3E5', '#383d41'),
(7, 'Khác', 'other', NULL, 8, 1, '#E5E7EB', '#374151'),
(8, 'Chính trị', 'politics', NULL, 1, 1, '#EAF1FF', '#2f5fa8'),
(9, 'Sự kiện', 'su-kien', 8, 1, 1, '#EAF1FF', '#2f5fa8'),
(10, 'Xây dựng Đảng', 'xay-dung-dang', 8, 2, 1, '#EAF1FF', '#2f5fa8'),
(11, 'Đối ngoại', 'doi-ngoai', 8, 3, 1, '#EAF1FF', '#2f5fa8'),
(12, 'Bàn luận', 'ban-luan', 8, 4, 1, '#EAF1FF', '#2f5fa8'),
(13, 'Quốc phòng', 'quoc-phong', 8, 5, 1, '#EAF1FF', '#2f5fa8'),
(14, 'Quốc hội', 'quoc-hoi', 8, 6, 1, '#EAF1FF', '#2f5fa8');

ALTER TABLE categories AUTO_INCREMENT = 15;

INSERT INTO articles
  (id, title, summary, content, category_id, author_id, source, tags, image_url, status, view_count, created_at, updated_at, published_at)
VALUES
(1, 'Doanh nghiệp Việt tăng tốc ứng dụng AI trong vận hành', 'Trí tuệ nhân tạo đang được nhiều doanh nghiệp dùng để xử lý dữ liệu, chăm sóc khách hàng và hỗ trợ nhân sự ra quyết định nhanh hơn.', '<p>Trong vài năm gần đây, AI không còn chỉ xuất hiện trong phòng nghiên cứu mà đã bước vào hoạt động hằng ngày của doanh nghiệp. Các bộ phận bán hàng dùng chatbot để phản hồi câu hỏi phổ biến, nhóm marketing dùng công cụ phân tích hành vi khách hàng, còn bộ phận vận hành dùng mô hình dự báo để tối ưu tồn kho và lịch giao hàng.</p><p>Điểm khác biệt nằm ở cách triển khai. Những đơn vị đạt hiệu quả thường không bắt đầu bằng các dự án quá lớn, mà chọn một quy trình cụ thể có dữ liệu rõ ràng, đo được thời gian tiết kiệm và có người chịu trách nhiệm kiểm chứng kết quả.</p><figure class="article-inline-image"><img src="https://images.unsplash.com/photo-1485827404703-89b55fcc595e?auto=format&fit=crop&w=1200&q=80" alt="Nhân sự văn phòng sử dụng công cụ AI trong công việc" loading="lazy"><figcaption>AI đang được dùng để hỗ trợ các tác vụ văn phòng như tổng hợp dữ liệu, soạn thảo và chăm sóc khách hàng.</figcaption></figure><p>Dù vậy, rủi ro về dữ liệu và độ chính xác vẫn là vấn đề cần được quản trị. Doanh nghiệp cần quy định loại dữ liệu nào được đưa vào hệ thống, ai được quyền phê duyệt kết quả và khi nào bắt buộc có con người kiểm tra trước khi sử dụng.</p><p>Các chuyên gia cho rằng lợi thế bền vững không đến từ việc chạy theo công cụ mới nhất, mà đến từ khả năng kết hợp AI với quy trình làm việc rõ ràng. Khi nhân sự hiểu mục tiêu, dữ liệu được chuẩn hóa và kết quả được giám sát, AI mới tạo ra giá trị thực.</p><figure class="article-inline-image"><img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1200&q=80" alt="Màn hình phân tích dữ liệu trong doanh nghiệp" loading="lazy"><figcaption>Dữ liệu sạch và quy trình kiểm chứng là nền tảng để ứng dụng AI hiệu quả.</figcaption></figure>', 1, 2, 'Thoáng.vn tổng hợp', 'AI, Doanh nghiệp, Chuyển đổi số', 'https://images.unsplash.com/photo-1677442136019-21780ecad995?auto=format&fit=crop&w=1200&q=80', 'Approved', 186, NOW() - INTERVAL 19 DAY, NOW() - INTERVAL 19 DAY, NOW() - INTERVAL 19 DAY),
(2, 'VN-Index giằng co khi dòng tiền phân hóa giữa các nhóm ngành', 'Thị trường chứng khoán ghi nhận trạng thái thận trọng khi nhà đầu tư chọn lọc cổ phiếu theo kết quả kinh doanh và triển vọng ngành.', '<p>Sau giai đoạn tăng mạnh, thị trường chứng khoán bước vào nhịp giằng co với thanh khoản thay đổi theo từng phiên. Nhóm ngân hàng, chứng khoán và bán lẻ vẫn thu hút sự chú ý, trong khi bất động sản và vật liệu xây dựng phân hóa rõ hơn theo sức khỏe tài chính của từng doanh nghiệp.</p><p>Diễn biến này cho thấy dòng tiền không rời bỏ thị trường nhưng đã thận trọng hơn. Thay vì mua rộng toàn ngành, nhà đầu tư ưu tiên cổ phiếu có báo cáo kinh doanh tích cực, nợ vay ở mức kiểm soát và câu chuyện tăng trưởng đủ rõ trong vài quý tới.</p><figure class="article-inline-image"><img src="https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?auto=format&fit=crop&w=1200&q=80" alt="Bảng điện chứng khoán trong phiên giao dịch" loading="lazy"><figcaption>Thanh khoản và độ rộng thị trường là hai tín hiệu được nhà đầu tư theo dõi sát trong các nhịp giằng co.</figcaption></figure><p>Các công ty chứng khoán khuyến nghị nhà đầu tư hạn chế mua đuổi khi chỉ số tiến gần vùng kháng cự. Việc giải ngân từng phần, giữ tỷ trọng tiền mặt hợp lý và đặt ngưỡng cắt lỗ rõ ràng giúp giảm rủi ro khi thị trường biến động mạnh.</p><p>Trong trung hạn, xu hướng của VN-Index vẫn phụ thuộc vào mặt bằng lãi suất, lợi nhuận doanh nghiệp và hoạt động của khối ngoại. Nếu các yếu tố vĩ mô ổn định, cơ hội có thể lan sang nhiều nhóm ngành hơn thay vì chỉ tập trung ở cổ phiếu vốn hóa lớn.</p><figure class="article-inline-image"><img src="https://images.unsplash.com/photo-1642790106117-e829e14a795f?auto=format&fit=crop&w=1200&q=80" alt="Biểu đồ tài chính trên màn hình giao dịch" loading="lazy"><figcaption>Nhà đầu tư cần kết hợp phân tích kỹ thuật với dữ liệu cơ bản của doanh nghiệp.</figcaption></figure>', 4, 2, 'Thoáng.vn tài chính', 'Chứng khoán, VN-Index, Đầu tư', 'https://images.unsplash.com/photo-1535320903710-d993d3d77d29?auto=format&fit=crop&w=1200&q=80', 'Approved', 132, NOW() - INTERVAL 18 DAY, NOW() - INTERVAL 18 DAY, NOW() - INTERVAL 18 DAY),
(3, 'Bóng đá Việt Nam đặt niềm tin vào lứa cầu thủ trẻ', 'Các đội tuyển trẻ và câu lạc bộ trong nước đang trao thêm cơ hội thi đấu cho cầu thủ mới nhằm chuẩn bị lực lượng dài hạn.', '<p>Bóng đá Việt Nam bước vào giai đoạn chuyển tiếp khi nhiều cầu thủ trẻ được trao cơ hội ở giải vô địch quốc gia và các đợt tập trung đội tuyển. Sự xuất hiện của lớp cầu thủ mới giúp ban huấn luyện có thêm phương án về tốc độ, sức bền và khả năng pressing.</p><p>Điểm tích cực là nhiều câu lạc bộ đã mạnh dạn sử dụng cầu thủ dưới 23 tuổi ở các vị trí quan trọng. Việc được thi đấu thường xuyên giúp cầu thủ trẻ trưởng thành nhanh hơn so với chỉ tập luyện cùng đội một.</p><figure class="article-inline-image"><img src="https://images.unsplash.com/photo-1431324155629-1a6deb1dec8d?auto=format&fit=crop&w=1200&q=80" alt="Cầu thủ trẻ tập luyện trên sân bóng" loading="lazy"><figcaption>Thời lượng thi đấu thực tế là điều kiện quan trọng để cầu thủ trẻ tích lũy kinh nghiệm.</figcaption></figure><p>Tuy nhiên, quá trình trẻ hóa không thể diễn ra vội vàng. Các cầu thủ mới cần được hỗ trợ về thể lực, tâm lý thi đấu và khả năng ra quyết định dưới áp lực. Bên cạnh đó, vai trò của các đàn anh giàu kinh nghiệm vẫn rất quan trọng trong phòng thay đồ.</p><p>Nếu được xây dựng theo lộ trình ổn định, thế hệ cầu thủ trẻ có thể tạo chiều sâu cho đội tuyển trong các giải đấu khu vực và vòng loại châu lục. Điều người hâm mộ chờ đợi không chỉ là kết quả tức thời mà là một nền tảng bền vững hơn.</p><figure class="article-inline-image"><img src="https://images.unsplash.com/photo-1517927033932-b3d18e61fb3a?auto=format&fit=crop&w=1200&q=80" alt="Sân vận động trong ngày thi đấu bóng đá" loading="lazy"><figcaption>Sự ủng hộ của khán giả là động lực lớn cho quá trình làm mới đội hình.</figcaption></figure>', 3, 2, 'Thoáng.vn thể thao', 'Bóng đá Việt Nam, Cầu thủ trẻ, V-League', 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=1200&q=80', 'Approved', 224, NOW() - INTERVAL 17 DAY, NOW() - INTERVAL 17 DAY, NOW() - INTERVAL 17 DAY),
(4, 'Xe buýt điện mở rộng lộ trình trong các đô thị lớn', 'Nhiều thành phố đẩy mạnh phương tiện công cộng phát thải thấp để giảm ô nhiễm không khí và cải thiện trải nghiệm đi lại.', '<p>Giao thông xanh đang trở thành ưu tiên của nhiều đô thị lớn khi áp lực ô nhiễm và ùn tắc ngày càng rõ. Xe buýt điện được xem là một giải pháp có thể triển khai theo từng tuyến, giúp giảm tiếng ồn và khí thải tại khu vực đông dân cư.</p><p>So với xe dùng nhiên liệu truyền thống, xe buýt điện mang lại trải nghiệm êm hơn và chi phí vận hành có thể tối ưu nếu hạ tầng sạc được bố trí hợp lý. Tuy nhiên, hiệu quả phụ thuộc nhiều vào quy hoạch tuyến, tần suất chuyến và khả năng kết nối với metro, bãi gửi xe và khu dân cư.</p><figure class="article-inline-image"><img src="https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&w=1200&q=80" alt="Xe buýt điện chạy trong thành phố" loading="lazy"><figcaption>Xe buýt điện giúp cải thiện chất lượng không khí nếu được vận hành ổn định trên các tuyến đông khách.</figcaption></figure><p>Một số chuyên gia giao thông cho rằng phương tiện sạch chỉ là một phần của bài toán. Để người dân chuyển từ xe cá nhân sang giao thông công cộng, thành phố cần bảo đảm thời gian chờ ngắn, thông tin chuyến rõ ràng và điểm dừng thuận tiện cho người đi bộ.</p><p>Trong dài hạn, mạng lưới giao thông xanh cần kết hợp xe buýt điện, đường sắt đô thị, làn xe đạp và không gian đi bộ an toàn. Khi các phương thức di chuyển liên thông tốt, thói quen đi lại của người dân mới có thể thay đổi bền vững.</p><figure class="article-inline-image"><img src="https://images.unsplash.com/photo-1519583272095-6433daf26b6e?auto=format&fit=crop&w=1200&q=80" alt="Làn xe đạp trong khu đô thị" loading="lazy"><figcaption>Hạ tầng đi bộ và xe đạp giúp giảm phụ thuộc vào xe cá nhân trong các chuyến đi ngắn.</figcaption></figure>', 5, 2, 'Thoáng.vn đô thị', 'Giao thông xanh, Xe buýt điện, Đô thị', 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80', 'Approved', 91, NOW() - INTERVAL 16 DAY, NOW() - INTERVAL 16 DAY, NOW() - INTERVAL 16 DAY),
(5, 'Không gian đọc sách cộng đồng thu hút người trẻ trở lại với sách giấy', 'Các quán cà phê sách, câu lạc bộ đọc và hội chợ xuất bản đang tạo thêm lý do để người trẻ đọc sách thường xuyên hơn.', '<p>Giữa nhịp sống nhiều màn hình, sách giấy vẫn tìm được chỗ đứng nhờ các không gian đọc cộng đồng. Nhiều bạn trẻ chọn quán cà phê sách, thư viện mở hoặc câu lạc bộ đọc như một cách tách khỏi thông báo điện thoại và tập trung lâu hơn vào một chủ đề.</p><p>Điều làm xu hướng này đáng chú ý là đọc sách không còn là hoạt động hoàn toàn cá nhân. Người đọc có thể tham gia buổi thảo luận, trao đổi sách cũ hoặc nghe tác giả chia sẻ về quá trình viết. Tính cộng đồng giúp việc đọc trở nên bền hơn.</p><figure class="article-inline-image"><img src="https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=1200&q=80" alt="Kệ sách trong một không gian đọc cộng đồng" loading="lazy"><figcaption>Không gian đọc thân thiện giúp người trẻ dễ bắt đầu và duy trì thói quen đọc.</figcaption></figure><p>Các nền tảng mạng xã hội cũng góp phần đưa sách đến gần độc giả mới. Những video giới thiệu ngắn, danh sách sách theo chủ đề và thử thách đọc mỗi tháng giúp người mới không bị lạc giữa quá nhiều lựa chọn.</p><p>Dù vậy, chuyên gia giáo dục khuyến nghị người đọc nên cân bằng giữa nội dung ngắn trên mạng và việc đọc sâu. Một cuốn sách được đọc kỹ vẫn giúp rèn khả năng tập trung, tư duy phản biện và vốn ngôn ngữ tốt hơn nhiều dạng nội dung lướt nhanh.</p><figure class="article-inline-image"><img src="https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&w=1200&q=80" alt="Sinh viên đọc sách trong thư viện" loading="lazy"><figcaption>Thư viện và câu lạc bộ đọc tạo môi trường để thói quen đọc phát triển tự nhiên.</figcaption></figure>', 2, 2, 'Thoáng.vn đời sống', 'Sách, Văn hóa đọc, Giới trẻ', 'https://images.unsplash.com/photo-1519682337058-a94d519337bc?auto=format&fit=crop&w=1200&q=80', 'Approved', 78, NOW() - INTERVAL 15 DAY, NOW() - INTERVAL 15 DAY, NOW() - INTERVAL 15 DAY),
(6, 'Ngành dữ liệu và AI trở thành lựa chọn nổi bật trong tuyển sinh', 'Nhiều trường đại học mở hoặc cập nhật chương trình về khoa học dữ liệu, trí tuệ nhân tạo và phân tích kinh doanh.', '<p>Nhu cầu nhân lực số khiến các ngành liên quan đến dữ liệu và AI được nhiều thí sinh quan tâm. Không chỉ các trường kỹ thuật, nhiều trường kinh tế và quản trị cũng đưa phân tích dữ liệu, trực quan hóa và ứng dụng AI vào chương trình đào tạo.</p><p>Điểm mới của các chương trình này là tính liên ngành. Sinh viên không chỉ học lập trình, thống kê và cơ sở dữ liệu, mà còn phải hiểu bối cảnh kinh doanh, đạo đức dữ liệu và cách trình bày kết quả cho người ra quyết định.</p><figure class="article-inline-image"><img src="https://images.unsplash.com/photo-1523580846011-d3a5bc25702b?auto=format&fit=crop&w=1200&q=80" alt="Sinh viên học phân tích dữ liệu trong lớp" loading="lazy"><figcaption>Các chương trình dữ liệu hiện nay kết hợp kiến thức công nghệ với bài toán thực tế của doanh nghiệp.</figcaption></figure><p>Doanh nghiệp đang cần nhân sự biết làm sạch dữ liệu, xây dựng dashboard, phát hiện xu hướng và hỗ trợ dự báo. Những vị trí này xuất hiện trong ngân hàng, bán lẻ, logistics, giáo dục, y tế và cả khu vực công.</p><p>Tuy nhiên, giảng viên khuyến nghị thí sinh không nên chọn ngành chỉ vì tên gọi đang nổi. Người học cần có nền tảng toán, tư duy logic và khả năng tự cập nhật công cụ mới. Công nghệ thay đổi nhanh, nhưng tư duy phân tích là năng lực theo người học lâu dài.</p><figure class="article-inline-image"><img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1200&q=80" alt="Dashboard phân tích dữ liệu trên màn hình" loading="lazy"><figcaption>Trực quan hóa dữ liệu giúp biến số liệu phức tạp thành thông tin dễ hiểu.</figcaption></figure>', 6, 2, 'Thoáng.vn giáo dục', 'Tuyển sinh, Dữ liệu, AI', 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1200&q=80', 'Approved', 149, NOW() - INTERVAL 14 DAY, NOW() - INTERVAL 14 DAY, NOW() - INTERVAL 14 DAY),
(7, 'Y tế số giúp người bệnh quản lý lịch khám và hồ sơ cá nhân', 'Các nền tảng đặt lịch, tư vấn từ xa và lưu trữ hồ sơ sức khỏe đang làm thay đổi cách người dân tiếp cận dịch vụ y tế.', '<p>Y tế số phát triển nhanh khi người dân quen hơn với việc đặt lịch khám, nhận kết quả xét nghiệm và theo dõi lịch tái khám qua ứng dụng. Với các bệnh mạn tính, việc lưu trữ hồ sơ theo thời gian giúp bác sĩ có thêm dữ liệu để đánh giá tình trạng của bệnh nhân.</p><p>Lợi ích dễ thấy nhất là giảm thời gian chờ và hạn chế thất lạc giấy tờ. Người bệnh có thể xem lại đơn thuốc, lịch sử khám và lời dặn của bác sĩ, trong khi cơ sở y tế giảm tải cho quầy tiếp nhận.</p><figure class="article-inline-image"><img src="https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=1200&q=80" alt="Bác sĩ sử dụng máy tính bảng trong bệnh viện" loading="lazy"><figcaption>Hồ sơ sức khỏe điện tử giúp quá trình khám chữa bệnh liền mạch hơn.</figcaption></figure><p>Dù vậy, y tế số đòi hỏi tiêu chuẩn bảo mật cao. Dữ liệu sức khỏe là thông tin nhạy cảm, vì vậy nền tảng cần phân quyền chặt, mã hóa dữ liệu và có quy trình xử lý khi xảy ra sự cố.</p><p>Các chuyên gia cho rằng công nghệ chỉ phát huy hiệu quả khi được tích hợp với quy trình chuyên môn. Ứng dụng tốt không thay thế bác sĩ, mà giúp bác sĩ và bệnh nhân giao tiếp, theo dõi và ra quyết định thuận tiện hơn.</p><figure class="article-inline-image"><img src="https://images.unsplash.com/photo-1584982751601-97dcc096659c?auto=format&fit=crop&w=1200&q=80" alt="Tư vấn y tế từ xa qua thiết bị số" loading="lazy"><figcaption>Tư vấn từ xa phù hợp với theo dõi sau khám và các tình huống không cần cấp cứu.</figcaption></figure>', 1, 2, 'Thoáng.vn sức khỏe', 'Y tế số, Hồ sơ sức khỏe, Công nghệ', 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=1200&q=80', 'Approved', 104, NOW() - INTERVAL 13 DAY, NOW() - INTERVAL 13 DAY, NOW() - INTERVAL 13 DAY),
(8, 'Giá vàng biến động mạnh khi nhà đầu tư tìm kênh trú ẩn', 'Thị trường vàng chịu tác động từ kỳ vọng lãi suất, tỷ giá và tâm lý phòng thủ trước biến động kinh tế toàn cầu.', '<p>Giá vàng thường tăng khi nhà đầu tư lo ngại về rủi ro kinh tế hoặc địa chính trị. Trong bối cảnh lãi suất, tỷ giá và lạm phát liên tục được theo dõi, vàng vẫn được xem là kênh trú ẩn quen thuộc của nhiều người.</p><p>Tại thị trường trong nước, chênh lệch giữa giá mua vào và bán ra là yếu tố người mua cần chú ý. Khi khoảng cách này lớn, nhà đầu tư ngắn hạn có thể chịu rủi ro ngay cả khi giá niêm yết tăng nhẹ.</p><figure class="article-inline-image"><img src="https://images.unsplash.com/photo-1610375461246-83df859d849d?auto=format&fit=crop&w=1200&q=80" alt="Thỏi vàng và đồng xu vàng trên bàn" loading="lazy"><figcaption>Vàng thường được xem là tài sản phòng thủ trong giai đoạn thị trường biến động.</figcaption></figure><p>Các chuyên gia tài chính khuyến nghị người dân không nên dùng đòn bẩy hoặc vay mượn để mua vàng. Với tài sản có biên độ dao động lớn, kế hoạch mua từng phần và tỷ trọng hợp lý trong danh mục quan trọng hơn quyết định theo cảm xúc.</p><p>Trong dài hạn, vàng có thể đóng vai trò bảo toàn giá trị, nhưng không tạo dòng tiền như cổ phiếu hoặc trái phiếu. Vì vậy, người đầu tư cần xác định rõ mục tiêu là tích lũy, phòng thủ hay giao dịch ngắn hạn.</p><figure class="article-inline-image"><img src="https://images.unsplash.com/photo-1642790106117-e829e14a795f?auto=format&fit=crop&w=1200&q=80" alt="Biểu đồ tài chính liên quan đến giá vàng" loading="lazy"><figcaption>Theo dõi xu hướng giá cần đi cùng quản trị rủi ro và kế hoạch phân bổ vốn.</figcaption></figure>', 4, 2, 'Thoáng.vn tài chính', 'Vàng, Tài chính cá nhân, Kinh tế', 'https://images.unsplash.com/photo-1610375461246-83df859d849d?auto=format&fit=crop&w=1200&q=80', 'Approved', 163, NOW() - INTERVAL 12 DAY, NOW() - INTERVAL 12 DAY, NOW() - INTERVAL 12 DAY),
(9, 'Giải chạy cộng đồng lan tỏa thói quen vận động cuối tuần', 'Các sự kiện chạy bộ phong trào ngày càng thu hút gia đình, nhóm bạn và doanh nghiệp tham gia cùng mục tiêu sức khỏe.', '<p>Chạy bộ phong trào đã trở thành hoạt động quen thuộc tại nhiều đô thị. Không chỉ là cuộc đua thành tích, các giải chạy cộng đồng còn tạo không gian để người tham gia gặp gỡ, rèn luyện sức khỏe và đóng góp cho các hoạt động xã hội.</p><p>Các cự ly ngắn giúp người mới bắt đầu dễ tham gia, trong khi cự ly dài hơn dành cho người đã tập luyện ổn định. Ban tổ chức thường bố trí trạm nước, đội y tế và khu phục hồi để bảo đảm an toàn cho vận động viên.</p><figure class="article-inline-image"><img src="https://images.unsplash.com/photo-1552674605-db6ffd4facb5?auto=format&fit=crop&w=1200&q=80" alt="Đám đông xuất phát tại một giải chạy" loading="lazy"><figcaption>Không khí xuất phát là điểm nhấn tạo cảm hứng trong các giải chạy cộng đồng.</figcaption></figure><p>Nhiều doanh nghiệp cũng xem giải chạy là hoạt động gắn kết nhân sự. Khi đồng nghiệp cùng tập luyện và hoàn thành mục tiêu, tinh thần đội nhóm được cải thiện theo cách tự nhiên hơn các sự kiện trong phòng họp.</p><p>Huấn luyện viên khuyến nghị người mới nên tăng cự ly từ từ, khởi động kỹ và nghỉ ngơi đủ. Sự bền vững của thói quen vận động quan trọng hơn việc cố gắng đạt thành tích cao trong một thời gian ngắn.</p><figure class="article-inline-image"><img src="https://images.unsplash.com/photo-1461896836934-ffe607ba8211?auto=format&fit=crop&w=1200&q=80" alt="Vận động viên chạy về đích" loading="lazy"><figcaption>Hoàn thành đường chạy giúp người tham gia có thêm động lực duy trì lối sống năng động.</figcaption></figure>', 3, 2, 'Thoáng.vn thể thao', 'Chạy bộ, Sức khỏe, Cộng đồng', 'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?auto=format&fit=crop&w=1200&q=80', 'Approved', 89, NOW() - INTERVAL 11 DAY, NOW() - INTERVAL 11 DAY, NOW() - INTERVAL 11 DAY),
(10, 'Pin thể rắn mở ra kỳ vọng mới cho xe điện và thiết bị di động', 'Công nghệ pin mới hướng đến mật độ năng lượng cao hơn, thời gian sạc ngắn hơn và mức độ an toàn tốt hơn.', '<p>Pin thể rắn được nhiều hãng công nghệ và ô tô nghiên cứu vì có tiềm năng cải thiện đáng kể thời lượng sử dụng. Thay vì chất điện phân lỏng như pin lithium-ion phổ biến, pin thể rắn dùng vật liệu rắn để giảm nguy cơ rò rỉ và quá nhiệt.</p><p>Nếu được thương mại hóa thành công, công nghệ này có thể giúp xe điện đi xa hơn sau mỗi lần sạc, đồng thời rút ngắn thời gian chờ tại trạm sạc. Với điện thoại và laptop, lợi ích nằm ở dung lượng cao hơn trong thiết kế mỏng nhẹ.</p><figure class="article-inline-image"><img src="https://images.unsplash.com/photo-1581092160562-40aa08e78837?auto=format&fit=crop&w=1200&q=80" alt="Phòng thí nghiệm nghiên cứu pin" loading="lazy"><figcaption>Pin thể rắn vẫn cần nhiều thử nghiệm về độ bền, chi phí và khả năng sản xuất hàng loạt.</figcaption></figure><p>Thách thức lớn nhất là đưa công nghệ từ phòng thí nghiệm ra dây chuyền sản xuất. Doanh nghiệp cần chứng minh pin hoạt động ổn định qua nhiều chu kỳ, an toàn trong các điều kiện nhiệt độ khác nhau và có giá thành đủ cạnh tranh.</p><p>Trong khi chờ các thế hệ pin mới, người dùng vẫn có thể kéo dài tuổi thọ thiết bị bằng cách tránh nhiệt độ cao, không để pin cạn quá lâu và sử dụng bộ sạc đạt chuẩn. Những thói quen nhỏ này giúp pin hiện tại hoạt động ổn định hơn.</p><figure class="article-inline-image"><img src="https://images.unsplash.com/photo-1593941707882-a5bac6861d75?auto=format&fit=crop&w=1200&q=80" alt="Xe điện đang sạc tại trạm" loading="lazy"><figcaption>Nhu cầu xe điện là động lực lớn thúc đẩy nghiên cứu pin an toàn và sạc nhanh hơn.</figcaption></figure>', 1, 2, 'Thoáng.vn công nghệ', 'Pin thể rắn, Xe điện, Thiết bị', 'https://images.unsplash.com/photo-1603791440384-56cd371ee9a7?auto=format&fit=crop&w=1200&q=80', 'Approved', 205, NOW() - INTERVAL 10 DAY, NOW() - INTERVAL 10 DAY, NOW() - INTERVAL 10 DAY),
(11, 'Triển lãm đổi mới sáng tạo giới thiệu nhiều giải pháp công nghệ Việt', 'Các startup mang đến sản phẩm về AI, giáo dục số, nông nghiệp thông minh và thương mại điện tử tại một sự kiện công nghệ.', '<p>Một triển lãm đổi mới sáng tạo quy tụ nhiều nhóm startup và doanh nghiệp công nghệ trong nước. Các gian hàng tập trung vào giải pháp AI cho doanh nghiệp, nền tảng học trực tuyến, thiết bị nông nghiệp thông minh và công cụ hỗ trợ bán hàng đa kênh.</p><p>Điểm đáng chú ý là nhiều sản phẩm không chỉ dừng ở ý tưởng mà đã có khách hàng thử nghiệm. Đại diện một số startup cho biết họ ưu tiên giải quyết các bài toán nhỏ nhưng rõ ràng, từ quản lý kho, tưới tiêu tự động đến chăm sóc khách hàng.</p><figure class="article-inline-image"><img src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=1200&q=80" alt="Khách tham quan tại triển lãm công nghệ" loading="lazy"><figcaption>Triển lãm công nghệ là cơ hội để startup kiểm chứng sản phẩm với người dùng thực tế.</figcaption></figure><p>Các nhà đầu tư quan tâm nhiều hơn đến khả năng tạo doanh thu, chất lượng đội ngũ và mức độ hiểu thị trường. Một sản phẩm có giao diện tốt nhưng thiếu mô hình kinh doanh rõ ràng sẽ khó đi xa sau giai đoạn thử nghiệm.</p><p>Bài viết này đang ở trạng thái chờ duyệt để admin kiểm tra trước khi xuất hiện trên trang chủ. Nội dung có thể dùng để thử quy trình gửi bài, duyệt bài và chuyển trạng thái trong dashboard quản trị.</p><figure class="article-inline-image"><img src="https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=1200&q=80" alt="Gian hàng startup tại sự kiện đổi mới sáng tạo" loading="lazy"><figcaption>Startup cần chứng minh sản phẩm giải quyết được vấn đề cụ thể của khách hàng.</figcaption></figure>', 1, 2, 'Thoáng.vn công nghệ', 'Startup, Đổi mới sáng tạo, Công nghệ Việt', 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=1200&q=80', 'request', 0, NOW() - INTERVAL 2 DAY, NOW() - INTERVAL 2 DAY, NULL),
(12, 'Tin lan truyền trên mạng xã hội cần được kiểm chứng trước khi chia sẻ', 'Một số nội dung gây chú ý trên mạng có thể thiếu nguồn xác thực, dễ tạo hiểu nhầm nếu người dùng chia sẻ quá nhanh.', '<p>Mạng xã hội giúp thông tin lan truyền nhanh nhưng cũng làm tăng nguy cơ hiểu sai khi nội dung thiếu nguồn rõ ràng. Một hình ảnh cũ, một đoạn video cắt ngắn hoặc một tiêu đề giật mạnh có thể khiến người đọc đưa ra kết luận vội vàng.</p><p>Người dùng nên kiểm tra ngày đăng, nguồn phát hành ban đầu và đối chiếu với các kênh chính thức trước khi chia sẻ. Với các thông tin liên quan đến sức khỏe, tài chính hoặc an toàn cộng đồng, việc kiểm chứng càng quan trọng.</p><figure class="article-inline-image"><img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80" alt="Người dùng đọc tin trên điện thoại" loading="lazy"><figcaption>Thông tin lan truyền nhanh trên mạng xã hội cần được đọc với thái độ thận trọng.</figcaption></figure><p>Bài viết này được đặt ở trạng thái không duyệt vì phần nguồn tham khảo trong bản nháp chưa đủ rõ. Đây là dữ liệu mẫu để writer thấy được trạng thái bài bị từ chối trong dashboard và có thể chỉnh sửa trước khi gửi lại.</p><p>Một bài viết về kiểm chứng thông tin cần nêu ví dụ cụ thể, nguồn xác minh và hướng dẫn rõ ràng cho người đọc. Khi các yếu tố này thiếu, nội dung rất dễ trở thành lời khuyên chung chung thay vì giúp người dùng hành động đúng.</p><figure class="article-inline-image"><img src="https://images.unsplash.com/photo-1504711434969-e33886168f5c?auto=format&fit=crop&w=1200&q=80" alt="Phóng viên kiểm chứng thông tin trong phòng tin tức" loading="lazy"><figcaption>Kiểm chứng nguồn là bước cần thiết trước khi biến một nội dung lan truyền thành bài viết chính thức.</figcaption></figure>', 7, 2, 'Thoáng.vn kiểm chứng', 'Tin giả, Mạng xã hội, Kiểm chứng', 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?auto=format&fit=crop&w=1200&q=80', 'disapproved', 0, NOW() - INTERVAL 1 DAY, NOW() - INTERVAL 1 DAY, NULL);

ALTER TABLE articles AUTO_INCREMENT = 13;

INSERT INTO comments (article_id, user_id, content, created_at) VALUES
(1, 3, 'Bài viết dễ hiểu, nhất là phần nhấn mạnh việc kiểm chứng thông tin khi dùng AI.', NOW() - INTERVAL 4 DAY),
(1, 2, 'Mình nghĩ các công ty nên có quy định nội bộ rõ ràng hơn khi dùng AI cho dữ liệu khách hàng.', NOW() - INTERVAL 3 DAY),
(3, 3, 'Hy vọng các cầu thủ trẻ sẽ được trao thêm thời gian thi đấu để thể hiện năng lực.', NOW() - INTERVAL 2 DAY),
(5, 3, 'Mình cũng bắt đầu đọc lại sách giấy gần đây, cảm giác tập trung hơn đọc trên điện thoại.', NOW() - INTERVAL 1 DAY);

INSERT INTO bookmarks (session_id, article_id, user_id, created_at) VALUES
('sample-session', 1, NULL, NOW() - INTERVAL 5 DAY),
('sample-session', 5, NULL, NOW() - INTERVAL 4 DAY),
('user-session', 3, 3, NOW() - INTERVAL 3 DAY);

INSERT INTO about_sections (section_key, section_data, updated_by) VALUES
('hero', '{"eyebrow":"Về chúng tôi","title":"Tin tức thời <em>thoáng qua,</em><br>kiến thức ở lại.","lead":"Thoáng.vn là ứng dụng đọc tin hiện đại, giúp bạn nắm bắt thông tin quan trọng trong khoảng 30 giây."}', 1),
('stats', '[{"num":"30","unit":"s","label":"Thời gian đọc mỗi tin"},{"num":"7","unit":"+","label":"Chủ đề tin tức"},{"num":"100","unit":"%","label":"Tối ưu cho đọc nhanh"},{"num":"0","unit":"đ","label":"Hoàn toàn miễn phí"}]', 1),
('mission', '{"quote":"Mỗi người xứng đáng được tiếp cận thông tin rõ ràng, trung thực và nhanh chóng.","body":"Thoáng.vn ra đời để giúp người đọc nắm điều cốt lõi của tin tức nhanh hơn, rõ hơn và ít bị phân tâm hơn."}', 1),
('values', '[{"icon":"bi-lightning-charge","title":"Nhanh mà không nông","desc":"Cô đọng nhưng vẫn giữ ngữ cảnh cần thiết."},{"icon":"bi-shield-check","title":"Trung thực trước tiên","desc":"Tin tức có nguồn rõ ràng và được trình bày có trách nhiệm."},{"icon":"bi-person-heart","title":"Người dùng làm trọng tâm","desc":"Trải nghiệm đọc nhẹ nhàng, dễ lướt và dễ lưu lại."}]', 1),
('story', '[{"title":"Bắt đầu từ nhu cầu đọc nhanh","desc":"Nhóm xây dựng một cách đọc tin phù hợp với nhịp sống bận rộn."},{"title":"Ý tưởng về thoáng","desc":"Tin tức được trình bày theo cách dễ lướt, dễ hiểu và dễ lưu lại."},{"title":"Xây dựng cho người Việt","desc":"Tối ưu cho thói quen đọc tin trên điện thoại và các khoảng thời gian ngắn."}]', 1),
('features', '[{"icon":"bi-hand-index","text":"Lướt để đọc tin nhanh"},{"icon":"bi-clock","text":"Đọc trong 30 giây"},{"icon":"bi-funnel","text":"Lọc theo chủ đề"},{"icon":"bi-bookmark-check","text":"Lưu tin quan trọng"}]', 1),
('team', '[{"initials":"NK","name":"Nguyễn Khánh Hoàng","role":"Frontend Developer"},{"initials":"HN","name":"Hứa Đức Nghĩa","role":"Backend Developer"},{"initials":"LV","name":"Lê Hoàng Việt","role":"UI/UX Designer"},{"initials":"MT","name":"Nguyễn Kiều Minh Trí","role":"Content & SEO"}]', 1),
('cta', '{"title":"Thoáng qua là nắm ngay.","desc":"Hãy thử một lần đọc tin theo cách khác: nhanh hơn, rõ hơn, ít nhiễu hơn.","btn1_text":"Đọc tin ngay","btn1_url":"index.php","btn2_text":"Gửi góp ý","btn2_url":"#"}', 1);
INSERT INTO feedback (user_id, sender_email, subject, message, status) VALUES
(3, 'user@thoang.vn', 'Góp ý giao diện', 'Trang đọc tin hoạt động tốt, mong có thêm nhiều danh mục hơn.', 'pending');


COMMIT;
