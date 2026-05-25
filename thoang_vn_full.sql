CREATE DATABASE IF NOT EXISTS thoang_vn
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE thoang_vn;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS user_preferences;
DROP TABLE IF EXISTS theme_settings;
DROP TABLE IF EXISTS article_summary;
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
(1, 'AI tạo sinh đang thay đổi cách chúng ta làm việc', 'Trí tuệ nhân tạo đang đi từ công cụ thử nghiệm sang trợ lý quen thuộc trong văn phòng, lớp học và hoạt động sáng tạo.', '<p>AI tạo sinh không còn chỉ là một trào lưu công nghệ. Trong nhiều doanh nghiệp, công cụ này đã được dùng để tóm tắt tài liệu, phác thảo kế hoạch, phân tích dữ liệu khách hàng và hỗ trợ chăm sóc người dùng. Điểm đáng chú ý là AI không thay thế toàn bộ con người, mà thường đảm nhận những phần việc lặp lại để nhân sự có thêm thời gian cho quyết định, kiểm chứng và sáng tạo.</p><p>Ở nhóm lao động tri thức, AI giúp rút ngắn giai đoạn khởi động. Một bản nháp email, một dàn ý bài thuyết trình hoặc một bảng so sánh phương án có thể được tạo trong vài phút. Tuy nhiên, kết quả cuối cùng vẫn cần người dùng hiểu ngữ cảnh, kiểm tra nguồn và điều chỉnh giọng điệu cho phù hợp với mục tiêu cụ thể.</p><figure class="article-inline-image"><img src="https://picsum.photos/seed/thoang-ai-desk/1200/700" alt="Nhân sự sử dụng công cụ AI trong công việc" loading="lazy"><figcaption>AI được dùng như trợ lý hỗ trợ soạn thảo, tổng hợp và gợi ý ý tưởng trong môi trường làm việc.</figcaption></figure><p>Thách thức lớn nhất nằm ở kỹ năng đặt câu hỏi và kiểm chứng thông tin. Nếu người dùng giao việc quá mơ hồ, AI dễ trả lời chung chung hoặc tạo ra nội dung chưa chính xác. Vì vậy, nhiều công ty bắt đầu đào tạo nhân sự cách viết yêu cầu rõ ràng, chia nhỏ nhiệm vụ và luôn đối chiếu thông tin quan trọng với nguồn đáng tin cậy.</p><p>Trong thời gian tới, giá trị của AI sẽ không nằm ở việc tạo ra thật nhiều nội dung, mà nằm ở khả năng kết hợp với quy trình làm việc có trách nhiệm. Người biết dùng AI hiệu quả sẽ có lợi thế, nhưng lợi thế đó chỉ bền vững khi đi cùng tư duy phản biện và hiểu biết chuyên môn.</p><figure class="article-inline-image"><img src="https://picsum.photos/seed/thoang-ai-meeting/1200/700" alt="Cuộc họp thảo luận về ứng dụng AI" loading="lazy"><figcaption>Các nhóm làm việc cần thống nhất nguyên tắc sử dụng AI để bảo đảm chất lượng và tính minh bạch.</figcaption></figure>', 1, 2, 'Tác giả Thoáng.vn', 'AI, Công nghệ, Làm việc', 'https://picsum.photos/seed/thoang-ai-thumb/1200/700', 'Approved', 152, NOW() - INTERVAL 19 DAY, NOW() - INTERVAL 19 DAY, NOW() - INTERVAL 19 DAY),
(2, 'VN-Index tăng mạnh nhờ nhóm ngân hàng', 'Nhóm cổ phiếu ngân hàng và chứng khoán dẫn dắt thị trường trong phiên giao dịch có thanh khoản cải thiện.', '<p>Thị trường chứng khoán trong nước ghi nhận phiên tăng tích cực khi dòng tiền quay lại nhóm vốn hóa lớn. Cổ phiếu ngân hàng đóng vai trò dẫn dắt, trong khi nhóm chứng khoán, bất động sản và bán lẻ cũng có sự phân hóa theo kết quả kinh doanh và kỳ vọng chính sách.</p><p>Điểm tích cực là thanh khoản cải thiện so với các phiên trước, cho thấy nhà đầu tư bắt đầu chủ động hơn thay vì chỉ quan sát. Tuy vậy, xu hướng tăng vẫn cần được kiểm chứng thêm qua nhiều phiên vì áp lực chốt lời thường xuất hiện khi chỉ số tiến gần vùng kháng cự ngắn hạn.</p><figure class="article-inline-image"><img src="https://picsum.photos/seed/thoang-stock-board/1200/700" alt="Bảng điện chứng khoán trong phiên giao dịch" loading="lazy"><figcaption>Nhóm cổ phiếu ngân hàng tiếp tục có ảnh hưởng lớn đến diễn biến chung của chỉ số.</figcaption></figure><p>Các chuyên gia cho rằng nhà đầu tư nên tránh mua đuổi ở những mã đã tăng nóng. Thay vào đó, việc theo dõi nền tích lũy, kết quả kinh doanh và mức độ cải thiện thanh khoản sẽ giúp lựa chọn cơ hội có tỷ lệ rủi ro hợp lý hơn.</p><p>Trong trung hạn, thị trường vẫn phụ thuộc vào mặt bằng lãi suất, sức khỏe lợi nhuận doanh nghiệp và tâm lý của khối ngoại. Nếu các yếu tố này ổn định, nhịp phục hồi có thể lan tỏa sang nhiều nhóm ngành hơn thay vì chỉ tập trung ở vài cổ phiếu trụ.</p><figure class="article-inline-image"><img src="https://picsum.photos/seed/thoang-investor-chart/1200/700" alt="Nhà đầu tư theo dõi biểu đồ thị trường" loading="lazy"><figcaption>Chiến lược giải ngân từng phần giúp nhà đầu tư kiểm soát rủi ro trong giai đoạn thị trường biến động.</figcaption></figure>', 4, 2, 'Tác giả Thoáng.vn', 'Chứng khoán, Ngân hàng, Kinh tế', 'https://picsum.photos/seed/thoang-stock-thumb/1200/700', 'Approved', 98, NOW() - INTERVAL 18 DAY, NOW() - INTERVAL 18 DAY, NOW() - INTERVAL 18 DAY),
(3, 'Đội tuyển Việt Nam công bố danh sách tập trung', 'Ban huấn luyện trao cơ hội cho nhiều gương mặt trẻ trong đợt hội quân mới nhằm thử nghiệm đội hình.', '<p>Danh sách tập trung mới của đội tuyển Việt Nam cho thấy xu hướng kết hợp giữa kinh nghiệm và sức trẻ. Bên cạnh các cầu thủ quen thuộc, nhiều gương mặt đang có phong độ tốt tại giải quốc nội được trao cơ hội để chứng minh năng lực trong môi trường đội tuyển.</p><p>Việc làm mới lực lượng là bước đi cần thiết sau một giai đoạn lịch thi đấu dày đặc. Ban huấn luyện muốn tăng tính cạnh tranh ở từng vị trí, đồng thời tìm thêm phương án cho các sơ đồ chiến thuật khác nhau. Đây cũng là cơ hội để các cầu thủ trẻ làm quen với yêu cầu về cường độ, kỷ luật và khả năng thích nghi.</p><figure class="article-inline-image"><img src="https://picsum.photos/seed/thoang-football-training/1200/700" alt="Buổi tập của đội tuyển bóng đá" loading="lazy"><figcaption>Các buổi tập đầu tiên thường tập trung vào thể lực, phối hợp nhóm và khả năng chuyển trạng thái.</figcaption></figure><p>Người hâm mộ kỳ vọng đội tuyển sẽ trình diễn lối chơi chủ động hơn, đặc biệt ở khả năng kiểm soát bóng và pressing tầm cao. Tuy nhiên, sự thay đổi cần thời gian vì các cầu thủ đến từ nhiều câu lạc bộ khác nhau, có thói quen vận hành và nhịp thi đấu khác nhau.</p><p>Đợt tập trung này không chỉ hướng đến kết quả trước mắt, mà còn là bước chuẩn bị cho các giải đấu quan trọng trong năm. Nếu những nhân tố mới hòa nhập tốt, đội tuyển sẽ có chiều sâu đội hình tốt hơn và nhiều lựa chọn chiến thuật hơn.</p><figure class="article-inline-image"><img src="https://picsum.photos/seed/thoang-stadium-night/1200/700" alt="Sân vận động trong ngày thi đấu" loading="lazy"><figcaption>Sự cổ vũ của khán giả luôn là nguồn động lực lớn cho đội tuyển trong các trận đấu chính thức.</figcaption></figure>', 3, 2, 'Tác giả Thoáng.vn', 'Bóng đá, Đội tuyển, Thể thao', 'https://picsum.photos/seed/thoang-football-thumb/1200/700', 'Approved', 213, NOW() - INTERVAL 17 DAY, NOW() - INTERVAL 17 DAY, NOW() - INTERVAL 17 DAY),
(4, 'Các thành phố châu Á đẩy mạnh giao thông xanh', 'Nhiều đô thị tăng đầu tư xe buýt điện, hạ tầng sạc và chính sách khuyến khích phương tiện phát thải thấp.', '<p>Giao thông xanh đang trở thành ưu tiên trong chiến lược phát triển đô thị của nhiều thành phố châu Á. Áp lực ô nhiễm không khí, ùn tắc và cam kết giảm phát thải khiến chính quyền phải tìm giải pháp mới cho hệ thống di chuyển hằng ngày.</p><p>Xe buýt điện là một trong những bước đi dễ thấy nhất. Khi được triển khai trên các tuyến đông hành khách, loại phương tiện này có thể giảm tiếng ồn, giảm khí thải tại khu vực trung tâm và tạo hình ảnh tích cực cho giao thông công cộng. Tuy nhiên, hiệu quả phụ thuộc nhiều vào chất lượng vận hành, tần suất chuyến và mạng lưới trạm sạc.</p><figure class="article-inline-image"><img src="https://picsum.photos/seed/thoang-electric-bus/1200/700" alt="Xe buýt điện trong đô thị" loading="lazy"><figcaption>Xe buýt điện giúp giảm phát thải tại khu vực đông dân cư nếu được vận hành với tần suất ổn định.</figcaption></figure><p>Bên cạnh phương tiện công cộng, nhiều nơi cũng đầu tư làn xe đạp, phố đi bộ và ứng dụng quản lý giao thông thông minh. Mục tiêu không chỉ là thay đổi loại phương tiện, mà còn thay đổi thói quen di chuyển của người dân theo hướng ngắn hơn, sạch hơn và ít phụ thuộc vào xe cá nhân.</p><p>Thách thức lớn nằm ở chi phí đầu tư ban đầu và khả năng phối hợp giữa các bên. Một mạng lưới giao thông xanh cần quy hoạch đồng bộ, từ điểm đón trả khách, bãi gửi xe, hệ thống thanh toán đến kết nối với khu dân cư và nơi làm việc.</p><figure class="article-inline-image"><img src="https://picsum.photos/seed/thoang-bike-lane/1200/700" alt="Làn xe đạp trong thành phố" loading="lazy"><figcaption>Hạ tầng cho người đi bộ và xe đạp góp phần làm giảm áp lực lên giao thông cá nhân.</figcaption></figure>', 5, 2, 'Tác giả Thoáng.vn', 'Thế giới, Môi trường, Đô thị', 'https://picsum.photos/seed/thoang-green-city-thumb/1200/700', 'Approved', 76, NOW() - INTERVAL 16 DAY, NOW() - INTERVAL 16 DAY, NOW() - INTERVAL 16 DAY),
(5, 'Thói quen đọc sách trở lại trong giới trẻ', 'Các câu lạc bộ đọc sách, hội chợ sách và nội dung review đang giúp sách giấy có thêm sức sống.', '<p>Trong vài năm gần đây, hoạt động đọc sách của người trẻ có dấu hiệu trở lại theo một cách mới. Không chỉ đọc một mình, nhiều bạn chọn tham gia câu lạc bộ, nhóm thảo luận và các buổi trao đổi sách để biến việc đọc thành trải nghiệm cộng đồng.</p><p>Sự phát triển của mạng xã hội cũng góp phần làm sách trở nên gần gũi hơn. Những video review ngắn, danh sách gợi ý theo chủ đề và các thử thách đọc sách giúp người mới bắt đầu dễ tìm được cuốn phù hợp. Điều này tạo ra cầu nối giữa văn hóa đọc truyền thống và thói quen tiếp nhận nội dung nhanh của thế hệ số.</p><figure class="article-inline-image"><img src="https://picsum.photos/seed/thoang-book-cafe/1200/700" alt="Không gian đọc sách tại quán cà phê" loading="lazy"><figcaption>Không gian đọc cộng đồng khiến việc đọc sách trở nên thân thiện và ít cô đơn hơn.</figcaption></figure><p>Điểm đáng mừng là nhiều bạn trẻ không chỉ đọc sách kỹ năng, mà còn quan tâm đến văn học, lịch sử, khoa học thường thức và sách nghiên cứu xã hội. Việc đọc đa dạng giúp mở rộng vốn sống, tăng khả năng tập trung và tạo thói quen suy nghĩ sâu hơn trước các vấn đề.</p><p>Dù vậy, việc duy trì thói quen đọc vẫn cần kỷ luật cá nhân. Một số người bắt đầu bằng mục tiêu nhỏ như đọc 10 trang mỗi ngày, ghi chú lại câu hay hoặc chia sẻ cảm nhận với bạn bè. Những hành động nhỏ này giúp việc đọc bền vững hơn thay vì chỉ bùng lên trong thời gian ngắn.</p><figure class="article-inline-image"><img src="https://picsum.photos/seed/thoang-book-fair/1200/700" alt="Gian hàng tại hội chợ sách" loading="lazy"><figcaption>Hội chợ sách tạo cơ hội để độc giả gặp tác giả, nhà xuất bản và cộng đồng cùng sở thích.</figcaption></figure>', 2, 2, 'Tác giả Thoáng.vn', 'Đời sống, Sách, Giới trẻ', 'https://picsum.photos/seed/thoang-books-thumb/1200/700', 'Approved', 64, NOW() - INTERVAL 15 DAY, NOW() - INTERVAL 15 DAY, NOW() - INTERVAL 15 DAY),
(6, 'Trường đại học mở thêm chương trình học dữ liệu', 'Các ngành phân tích dữ liệu tiếp tục thu hút thí sinh khi nhu cầu nhân lực số tăng ở nhiều lĩnh vực.', '<p>Dữ liệu đang trở thành tài sản quan trọng của doanh nghiệp, cơ quan nhà nước và các tổ chức xã hội. Vì vậy, nhiều trường đại học bắt đầu mở hoặc cập nhật chương trình đào tạo liên quan đến khoa học dữ liệu, phân tích kinh doanh và trí tuệ nhân tạo ứng dụng.</p><p>Điểm mới của các chương trình này là tính liên ngành. Sinh viên không chỉ học lập trình, thống kê và cơ sở dữ liệu, mà còn cần hiểu bài toán kinh doanh, đạo đức dữ liệu và cách trình bày kết quả cho người không chuyên. Đây là những kỹ năng giúp dữ liệu tạo ra quyết định thực tế thay vì chỉ dừng ở biểu đồ.</p><figure class="article-inline-image"><img src="https://picsum.photos/seed/thoang-data-classroom/1200/700" alt="Sinh viên học phân tích dữ liệu" loading="lazy"><figcaption>Các lớp học dữ liệu thường kết hợp giữa lý thuyết, thực hành và dự án theo nhóm.</figcaption></figure><p>Doanh nghiệp hiện cần nhân sự có khả năng làm sạch dữ liệu, xây dựng dashboard, phát hiện xu hướng và hỗ trợ dự báo. Những vị trí này xuất hiện trong ngân hàng, bán lẻ, logistics, giáo dục, y tế và nhiều ngành dịch vụ khác. Điều đó khiến ngành dữ liệu trở thành lựa chọn hấp dẫn với thí sinh yêu thích công nghệ nhưng vẫn muốn gắn với bài toán xã hội.</p><p>Tuy nhiên, sinh viên cần tránh kỳ vọng rằng chỉ học công cụ là đủ. Kiến thức nền về xác suất, tư duy mô hình và khả năng đặt câu hỏi đúng vẫn là yếu tố cốt lõi. Công cụ có thể thay đổi nhanh, nhưng tư duy phân tích sẽ theo người học lâu dài.</p><figure class="article-inline-image"><img src="https://picsum.photos/seed/thoang-data-dashboard/1200/700" alt="Dashboard phân tích dữ liệu" loading="lazy"><figcaption>Trực quan hóa dữ liệu giúp biến bảng số liệu phức tạp thành thông tin dễ hiểu cho người ra quyết định.</figcaption></figure>', 6, 2, 'Tác giả Thoáng.vn', 'Giáo dục, Dữ liệu, Đại học', 'https://picsum.photos/seed/thoang-data-thumb/1200/700', 'Approved', 121, NOW() - INTERVAL 14 DAY, NOW() - INTERVAL 14 DAY, NOW() - INTERVAL 14 DAY),
(7, 'Startup Việt phát triển nền tảng chăm sóc sức khỏe số', 'Ứng dụng mới hỗ trợ đặt lịch khám, quản lý hồ sơ và theo dõi sức khỏe cá nhân trên một nền tảng.', '<p>Chuyển đổi số trong y tế đang mở ra nhiều cơ hội cho các startup Việt. Thay vì chỉ tập trung vào đặt lịch khám, các nền tảng mới hướng đến việc quản lý hồ sơ sức khỏe, nhắc lịch tái khám, lưu kết quả xét nghiệm và kết nối người dùng với cơ sở y tế phù hợp.</p><p>Với người dùng, lợi ích lớn nhất là giảm thời gian chờ đợi và giảm tình trạng thất lạc thông tin. Khi dữ liệu được lưu có hệ thống, bác sĩ có thể theo dõi lịch sử điều trị tốt hơn, còn bệnh nhân dễ chủ động trong việc chăm sóc sức khỏe hằng ngày.</p><figure class="article-inline-image"><img src="https://picsum.photos/seed/thoang-health-app/1200/700" alt="Ứng dụng chăm sóc sức khỏe trên điện thoại" loading="lazy"><figcaption>Nền tảng sức khỏe số giúp người dùng theo dõi lịch khám và thông tin y tế cá nhân thuận tiện hơn.</figcaption></figure><p>Dù tiềm năng lớn, lĩnh vực này có yêu cầu rất cao về bảo mật và độ tin cậy. Dữ liệu y tế là dữ liệu nhạy cảm, vì vậy nền tảng phải có cơ chế phân quyền, mã hóa và quy trình xử lý sự cố rõ ràng. Trải nghiệm người dùng cũng cần đơn giản, đặc biệt với người lớn tuổi hoặc người ít quen dùng công nghệ.</p><p>Các chuyên gia cho rằng startup y tế số sẽ phát triển bền vững hơn nếu hợp tác chặt với bệnh viện, phòng khám và chuyên gia chuyên môn. Công nghệ chỉ là một phần; niềm tin của người dùng mới là yếu tố quyết định khả năng mở rộng.</p><figure class="article-inline-image"><img src="https://picsum.photos/seed/thoang-digital-health/1200/700" alt="Bác sĩ trao đổi với bệnh nhân qua nền tảng số" loading="lazy"><figcaption>Sự kết hợp giữa chuyên môn y tế và công nghệ là điều kiện quan trọng để dịch vụ sức khỏe số tạo giá trị thật.</figcaption></figure>', 1, 2, 'Tác giả Thoáng.vn', 'Startup, Y tế số, Công nghệ', 'https://picsum.photos/seed/thoang-health-tech-thumb/1200/700', 'Approved', 87, NOW() - INTERVAL 13 DAY, NOW() - INTERVAL 13 DAY, NOW() - INTERVAL 13 DAY),
(8, 'Giá vàng trong nước biến động nhẹ', 'Thị trường vàng điều chỉnh sau nhiều phiên tăng, nhà đầu tư thận trọng hơn trước chênh lệch mua bán.', '<p>Giá vàng trong nước biến động nhẹ sau chuỗi phiên tăng mạnh, phản ánh tâm lý thận trọng của cả người mua và người bán. Chênh lệch giữa giá mua vào và bán ra vẫn ở mức đáng chú ý, khiến nhà đầu tư ngắn hạn phải cân nhắc kỹ trước khi giao dịch.</p><p>Trên thị trường quốc tế, giá vàng thường chịu tác động từ lãi suất, tỷ giá, lạm phát và nhu cầu trú ẩn an toàn. Khi các tín hiệu kinh tế toàn cầu chưa rõ ràng, vàng có thể tiếp tục dao động trong biên độ rộng, nhất là vào thời điểm có thông tin mới từ ngân hàng trung ương lớn.</p><figure class="article-inline-image"><img src="https://picsum.photos/seed/thoang-gold-shop/1200/700" alt="Cửa hàng vàng trong giờ giao dịch" loading="lazy"><figcaption>Người mua vàng cần chú ý chênh lệch mua bán vì đây là yếu tố ảnh hưởng trực tiếp đến lợi nhuận ngắn hạn.</figcaption></figure><p>Đối với người tích lũy dài hạn, vàng vẫn được xem là một phần của danh mục phòng thủ. Tuy nhiên, việc phân bổ tỷ trọng quá lớn có thể khiến danh mục thiếu linh hoạt. Nhà đầu tư nên xác định mục tiêu rõ ràng: tích lũy tài sản, phòng ngừa rủi ro hay giao dịch ngắn hạn.</p><p>Chuyên gia khuyến nghị không nên vay mượn để mua vàng trong giai đoạn biến động. Với tài sản có biên độ giá lớn, quyết định dựa trên cảm xúc thường dẫn đến rủi ro cao hơn so với kế hoạch mua từng phần và theo dõi thị trường định kỳ.</p><figure class="article-inline-image"><img src="https://picsum.photos/seed/thoang-gold-chart/1200/700" alt="Biểu đồ giá vàng" loading="lazy"><figcaption>Biểu đồ giá giúp nhà đầu tư nhận diện xu hướng, nhưng không thể thay thế quản trị rủi ro.</figcaption></figure>', 4, 2, 'Tác giả Thoáng.vn', 'Vàng, Tài chính, Kinh tế', 'https://picsum.photos/seed/thoang-gold-thumb/1200/700', 'Approved', 144, NOW() - INTERVAL 12 DAY, NOW() - INTERVAL 12 DAY, NOW() - INTERVAL 12 DAY),
(9, 'Giải chạy cộng đồng thu hút hơn 5.000 người', 'Sự kiện thể thao cuối tuần lan tỏa thói quen vận động và gây quỹ cho các hoạt động xã hội.', '<p>Một giải chạy cộng đồng cuối tuần đã thu hút hơn 5.000 người tham gia ở nhiều cự ly khác nhau. Sự kiện không chỉ là hoạt động thể thao, mà còn trở thành dịp để gia đình, nhóm bạn và doanh nghiệp cùng lan tỏa lối sống năng động.</p><p>Các cự ly được thiết kế từ ngắn đến dài giúp người mới bắt đầu cũng có thể tham gia. Ban tổ chức bố trí trạm nước, đội hỗ trợ y tế và khu vực phục hồi sau khi hoàn thành đường chạy. Điều này giúp trải nghiệm của vận động viên phong trào an toàn và thân thiện hơn.</p><figure class="article-inline-image"><img src="https://picsum.photos/seed/thoang-running-start/1200/700" alt="Người tham gia xuất phát tại giải chạy" loading="lazy"><figcaption>Không khí xuất phát là khoảnh khắc tạo nhiều cảm hứng nhất trong các giải chạy cộng đồng.</figcaption></figure><p>Điểm nổi bật của giải năm nay là hoạt động gây quỹ cho chương trình hỗ trợ trẻ em có hoàn cảnh khó khăn. Mỗi lượt đăng ký đóng góp một phần kinh phí cho quỹ, đồng thời giúp người tham gia cảm thấy hành trình của mình có thêm ý nghĩa xã hội.</p><p>Sau đại dịch, nhu cầu vận động ngoài trời tăng rõ rệt. Chạy bộ trở thành lựa chọn phổ biến vì chi phí thấp, dễ bắt đầu và phù hợp với nhiều độ tuổi. Các huấn luyện viên khuyến nghị người mới nên tăng cự ly từ từ, khởi động kỹ và nghỉ ngơi đủ để tránh chấn thương.</p><figure class="article-inline-image"><img src="https://picsum.photos/seed/thoang-running-finish/1200/700" alt="Người chạy về đích" loading="lazy"><figcaption>Hoàn thành đường chạy giúp người tham gia có thêm động lực duy trì thói quen vận động thường xuyên.</figcaption></figure>', 3, 2, 'Tác giả Thoáng.vn', 'Running, Cộng đồng, Thể thao', 'https://picsum.photos/seed/thoang-running-thumb/1200/700', 'Approved', 72, NOW() - INTERVAL 11 DAY, NOW() - INTERVAL 11 DAY, NOW() - INTERVAL 11 DAY),
(10, 'Công nghệ pin mới hứa hẹn tăng thời lượng thiết bị', 'Các phòng thí nghiệm đang thử nghiệm vật liệu pin an toàn hơn, bền hơn và có khả năng sạc nhanh hơn.', '<p>Thời lượng pin luôn là một trong những yếu tố người dùng quan tâm nhất khi chọn điện thoại, laptop hoặc thiết bị đeo. Các nghiên cứu mới về vật liệu pin đang hướng đến mục tiêu tăng mật độ năng lượng, giảm nguy cơ quá nhiệt và kéo dài tuổi thọ sau nhiều chu kỳ sạc.</p><p>Một số nhóm nghiên cứu tập trung vào pin thể rắn, trong khi các doanh nghiệp khác cải thiện cấu trúc điện cực và thuật toán quản lý sạc. Nếu được thương mại hóa thành công, công nghệ mới có thể giúp thiết bị mỏng hơn nhưng vẫn dùng được lâu hơn.</p><figure class="article-inline-image"><img src="https://picsum.photos/seed/thoang-battery-lab/1200/700" alt="Phòng thí nghiệm nghiên cứu pin" loading="lazy"><figcaption>Các thử nghiệm trong phòng lab là bước đầu trước khi công nghệ pin mới được đưa vào sản xuất đại trà.</figcaption></figure><p>Tuy vậy, con đường từ phòng thí nghiệm đến sản phẩm thương mại thường kéo dài. Nhà sản xuất cần chứng minh độ ổn định, chi phí sản xuất, khả năng mở rộng và mức độ an toàn trong nhiều điều kiện sử dụng khác nhau. Một công nghệ tốt trên mẫu thử chưa chắc đã phù hợp với hàng triệu thiết bị.</p><p>Trong lúc chờ các đột phá lớn, người dùng vẫn có thể kéo dài tuổi thọ pin bằng thói quen sạc hợp lý, tránh nhiệt độ quá cao và cập nhật phần mềm quản lý năng lượng. Những thay đổi nhỏ này giúp thiết bị hoạt động ổn định hơn trong thời gian dài.</p><figure class="article-inline-image"><img src="https://picsum.photos/seed/thoang-phone-charging/1200/700" alt="Thiết bị di động đang sạc" loading="lazy"><figcaption>Quản lý sạc thông minh giúp cân bằng giữa tốc độ sạc và tuổi thọ pin của thiết bị.</figcaption></figure>', 1, 2, 'Tác giả Thoáng.vn', 'Pin, Công nghệ, Thiết bị', 'https://picsum.photos/seed/thoang-battery-thumb/1200/700', 'Approved', 191, NOW() - INTERVAL 10 DAY, NOW() - INTERVAL 10 DAY, NOW() - INTERVAL 10 DAY),
(11, 'Bài chờ duyệt về triển lãm công nghệ', 'Bài viết này dùng để kiểm tra trạng thái chờ duyệt trong dashboard admin.', '<p>Nội dung đang chờ admin xem xét. Người đọc thông thường sẽ không thấy bài này ở trang chủ cho đến khi bài được duyệt.</p>', 1, 2, 'Tác giả Thoáng.vn', 'Chờ duyệt, Công nghệ', NULL, 'request', 0, NOW() - INTERVAL 2 DAY, NOW() - INTERVAL 2 DAY, NULL),
(12, 'Bài mẫu bị từ chối', 'Bài viết này minh họa trạng thái bị từ chối trong dashboard writer.', '<p>Trạng thái bị từ chối giúp writer phân biệt với bài đang chờ duyệt hoặc đã xuất bản.</p>', 7, 2, 'Tác giả Thoáng.vn', 'Từ chối, Mẫu', NULL, 'disapproved', 0, NOW(), NOW(), NULL);

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
('gioi-thieu', '{"title":"Về hệ thống Thoáng","content":"Nền tảng đọc tin tức nhanh, gọn và dễ theo dõi."}', 1),
('lien-he', '{"email":"contact@thoang.vn","hotline":"1900 1000","address":"TP.HCM"}', 1);

INSERT INTO feedback (user_id, sender_email, subject, message, status) VALUES
(3, 'user@thoang.vn', 'Góp ý giao diện', 'Trang đọc tin hoạt động tốt, mong có thêm nhiều danh mục hơn.', 'pending');

INSERT INTO user_preferences (user_id, font_size, theme) VALUES
(3, 15, 'light');

COMMIT;
