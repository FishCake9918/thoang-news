-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 30, 2026 at 08:58 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `thoang_vn`
--

CREATE DATABASE IF NOT EXISTS `thoang_vn`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `thoang_vn`;

DELIMITER $$
--
-- Procedures
--
CREATE PROCEDURE `proc_check_login` (IN `in_login` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci)   BEGIN
    SELECT 
        id,
        username,
        email,
        password,
        full_name,
        avatar,
        role,
        is_verified
    FROM users
    WHERE username COLLATE utf8mb4_unicode_ci = in_login
       OR email COLLATE utf8mb4_unicode_ci = in_login
    LIMIT 1;
END$$

CREATE PROCEDURE `proc_get_articles_by_category` (IN `in_category_id` INT)   BEGIN
    SELECT 
        a.id,
        a.title,
        a.summary,
        a.image_url,
        a.view_count,
        a.created_at,
        c.name AS category_name
    FROM articles a
    JOIN categories c ON a.category_id = c.id
    WHERE a.category_id = in_category_id
    ORDER BY a.created_at DESC;
END$$

CREATE PROCEDURE `proc_get_saved_articles_by_user` (IN `in_user_id` INT)   BEGIN
    SELECT 
        b.id AS bookmark_id,
        a.id AS article_id,
        a.title,
        a.summary,
        a.image_url,
        a.created_at,
        b.created_at AS saved_at
    FROM bookmarks b
    JOIN articles a ON b.article_id = a.id
    WHERE b.user_id = in_user_id
    ORDER BY b.created_at DESC;
END$$

CREATE PROCEDURE `proc_update_article_status` (IN `in_article_id` INT, IN `in_status` VARCHAR(50))   BEGIN
    UPDATE articles
    SET 
        status = in_status,
        published_at = CASE 
            WHEN in_status = 'Approved' THEN NOW()
            ELSE published_at
        END,
        updated_at = NOW()
    WHERE id = in_article_id;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `about_sections`
--

CREATE TABLE `about_sections` (
  `id` int(11) NOT NULL,
  `section_key` varchar(50) NOT NULL,
  `section_data` longtext NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `about_sections`
--

INSERT INTO `about_sections` (`id`, `section_key`, `section_data`, `updated_at`, `updated_by`) VALUES
(1, 'gioi-thieu', '{\"title\":\"Về hệ thống Thoáng\",\"content\":\"Nền tảng đọc tin tức nhanh, gọn và dễ theo dõi.\"}', '2026-05-25 17:32:16', 1),
(2, 'lien-he', '{\"email\":\"contact@thoang.vn\",\"hotline\":\"1900 1000\",\"address\":\"TP.HCM\"}', '2026-05-25 17:32:16', 1);

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--

CREATE TABLE `articles` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `summary` text NOT NULL,
  `content` longtext DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `author_id` int(11) DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'request',
  `view_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `published_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `articles`
--

INSERT INTO `articles` (`id`, `title`, `summary`, `content`, `category_id`, `author_id`, `source`, `tags`, `image_url`, `status`, `view_count`, `created_at`, `updated_at`, `published_at`) VALUES
(1, 'AI tạo sinh đang thay đổi cách chúng ta làm việc', 'Trí tuệ nhân tạo đang đi từ công cụ thử nghiệm sang trợ lý quen thuộc trong văn phòng, lớp học và hoạt động sáng tạo.', '<p>AI tạo sinh không còn chỉ là một trào lưu công nghệ. Trong nhiều doanh nghiệp, công cụ này đã được dùng để tóm tắt tài liệu, phác thảo kế hoạch, phân tích dữ liệu khách hàng và hỗ trợ chăm sóc người dùng. Điểm đáng chú ý là AI không thay thế toàn bộ con người, mà thường đảm nhận những phần việc lặp lại để nhân sự có thêm thời gian cho quyết định, kiểm chứng và sáng tạo.</p><p>Ở nhóm lao động tri thức, AI giúp rút ngắn giai đoạn khởi động. Một bản nháp email, một dàn ý bài thuyết trình hoặc một bảng so sánh phương án có thể được tạo trong vài phút. Tuy nhiên, kết quả cuối cùng vẫn cần người dùng hiểu ngữ cảnh, kiểm tra nguồn và điều chỉnh giọng điệu cho phù hợp với mục tiêu cụ thể.</p><figure class=\"article-inline-image\"><img src=\"https://picsum.photos/seed/thoang-ai-desk/1200/700\" alt=\"Nhân sự sử dụng công cụ AI trong công việc\" loading=\"lazy\"><figcaption>AI được dùng như trợ lý hỗ trợ soạn thảo, tổng hợp và gợi ý ý tưởng trong môi trường làm việc.</figcaption></figure><p>Thách thức lớn nhất nằm ở kỹ năng đặt câu hỏi và kiểm chứng thông tin. Nếu người dùng giao việc quá mơ hồ, AI dễ trả lời chung chung hoặc tạo ra nội dung chưa chính xác. Vì vậy, nhiều công ty bắt đầu đào tạo nhân sự cách viết yêu cầu rõ ràng, chia nhỏ nhiệm vụ và luôn đối chiếu thông tin quan trọng với nguồn đáng tin cậy.</p><p>Trong thời gian tới, giá trị của AI sẽ không nằm ở việc tạo ra thật nhiều nội dung, mà nằm ở khả năng kết hợp với quy trình làm việc có trách nhiệm. Người biết dùng AI hiệu quả sẽ có lợi thế, nhưng lợi thế đó chỉ bền vững khi đi cùng tư duy phản biện và hiểu biết chuyên môn.</p><figure class=\"article-inline-image\"><img src=\"https://picsum.photos/seed/thoang-ai-meeting/1200/700\" alt=\"Cuộc họp thảo luận về ứng dụng AI\" loading=\"lazy\"><figcaption>Các nhóm làm việc cần thống nhất nguyên tắc sử dụng AI để bảo đảm chất lượng và tính minh bạch.</figcaption></figure>', 1, 2, 'Tác giả Thoáng.vn', 'AI, Công nghệ, Làm việc', 'https://picsum.photos/seed/thoang-ai-thumb/1200/700', 'Approved', 165, '2026-05-06 17:32:16', '2026-05-28 10:13:47', '2026-05-28 10:13:47'),
(2, 'VN-Index tăng mạnh nhờ nhóm ngân hàng', 'Nhóm cổ phiếu ngân hàng và chứng khoán dẫn dắt thị trường trong phiên giao dịch có thanh khoản cải thiện.', '<p>Thị trường chứng khoán trong nước ghi nhận phiên tăng tích cực khi dòng tiền quay lại nhóm vốn hóa lớn. Cổ phiếu ngân hàng đóng vai trò dẫn dắt, trong khi nhóm chứng khoán, bất động sản và bán lẻ cũng có sự phân hóa theo kết quả kinh doanh và kỳ vọng chính sách.</p><p>Điểm tích cực là thanh khoản cải thiện so với các phiên trước, cho thấy nhà đầu tư bắt đầu chủ động hơn thay vì chỉ quan sát. Tuy vậy, xu hướng tăng vẫn cần được kiểm chứng thêm qua nhiều phiên vì áp lực chốt lời thường xuất hiện khi chỉ số tiến gần vùng kháng cự ngắn hạn.</p><figure class=\"article-inline-image\"><img src=\"https://picsum.photos/seed/thoang-stock-board/1200/700\" alt=\"Bảng điện chứng khoán trong phiên giao dịch\" loading=\"lazy\"><figcaption>Nhóm cổ phiếu ngân hàng tiếp tục có ảnh hưởng lớn đến diễn biến chung của chỉ số.</figcaption></figure><p>Các chuyên gia cho rằng nhà đầu tư nên tránh mua đuổi ở những mã đã tăng nóng. Thay vào đó, việc theo dõi nền tích lũy, kết quả kinh doanh và mức độ cải thiện thanh khoản sẽ giúp lựa chọn cơ hội có tỷ lệ rủi ro hợp lý hơn.</p><p>Trong trung hạn, thị trường vẫn phụ thuộc vào mặt bằng lãi suất, sức khỏe lợi nhuận doanh nghiệp và tâm lý của khối ngoại. Nếu các yếu tố này ổn định, nhịp phục hồi có thể lan tỏa sang nhiều nhóm ngành hơn thay vì chỉ tập trung ở vài cổ phiếu trụ.</p><figure class=\"article-inline-image\"><img src=\"https://picsum.photos/seed/thoang-investor-chart/1200/700\" alt=\"Nhà đầu tư theo dõi biểu đồ thị trường\" loading=\"lazy\"><figcaption>Chiến lược giải ngân từng phần giúp nhà đầu tư kiểm soát rủi ro trong giai đoạn thị trường biến động.</figcaption></figure>', 4, 2, 'Tác giả Thoáng.vn', 'Chứng khoán, Ngân hàng, Kinh tế', 'https://picsum.photos/seed/thoang-stock-thumb/1200/700', 'Approved', 105, '2026-05-07 17:32:16', '2026-05-30 05:18:32', '2026-05-07 17:32:16'),
(3, 'Đội tuyển Việt Nam công bố danh sách tập trung', 'Ban huấn luyện trao cơ hội cho nhiều gương mặt trẻ trong đợt hội quân mới nhằm thử nghiệm đội hình.', '<p>Danh sách tập trung mới của đội tuyển Việt Nam cho thấy xu hướng kết hợp giữa kinh nghiệm và sức trẻ. Bên cạnh các cầu thủ quen thuộc, nhiều gương mặt đang có phong độ tốt tại giải quốc nội được trao cơ hội để chứng minh năng lực trong môi trường đội tuyển.</p><p>Việc làm mới lực lượng là bước đi cần thiết sau một giai đoạn lịch thi đấu dày đặc. Ban huấn luyện muốn tăng tính cạnh tranh ở từng vị trí, đồng thời tìm thêm phương án cho các sơ đồ chiến thuật khác nhau. Đây cũng là cơ hội để các cầu thủ trẻ làm quen với yêu cầu về cường độ, kỷ luật và khả năng thích nghi.</p><figure class=\"article-inline-image\"><img src=\"https://picsum.photos/seed/thoang-football-training/1200/700\" alt=\"Buổi tập của đội tuyển bóng đá\" loading=\"lazy\"><figcaption>Các buổi tập đầu tiên thường tập trung vào thể lực, phối hợp nhóm và khả năng chuyển trạng thái.</figcaption></figure><p>Người hâm mộ kỳ vọng đội tuyển sẽ trình diễn lối chơi chủ động hơn, đặc biệt ở khả năng kiểm soát bóng và pressing tầm cao. Tuy nhiên, sự thay đổi cần thời gian vì các cầu thủ đến từ nhiều câu lạc bộ khác nhau, có thói quen vận hành và nhịp thi đấu khác nhau.</p><p>Đợt tập trung này không chỉ hướng đến kết quả trước mắt, mà còn là bước chuẩn bị cho các giải đấu quan trọng trong năm. Nếu những nhân tố mới hòa nhập tốt, đội tuyển sẽ có chiều sâu đội hình tốt hơn và nhiều lựa chọn chiến thuật hơn.</p><figure class=\"article-inline-image\"><img src=\"https://picsum.photos/seed/thoang-stadium-night/1200/700\" alt=\"Sân vận động trong ngày thi đấu\" loading=\"lazy\"><figcaption>Sự cổ vũ của khán giả luôn là nguồn động lực lớn cho đội tuyển trong các trận đấu chính thức.</figcaption></figure>', 3, 2, 'Tác giả Thoáng.vn', 'Bóng đá, Đội tuyển, Thể thao', 'https://picsum.photos/seed/thoang-football-thumb/1200/700', 'Approved', 218, '2026-05-08 17:32:16', '2026-05-30 05:19:04', '2026-05-08 17:32:16'),
(4, 'Các thành phố châu Á đẩy mạnh giao thông xanh', 'Nhiều đô thị tăng đầu tư xe buýt điện, hạ tầng sạc và chính sách khuyến khích phương tiện phát thải thấp.', '<p>Giao thông xanh đang trở thành ưu tiên trong chiến lược phát triển đô thị của nhiều thành phố châu Á. Áp lực ô nhiễm không khí, ùn tắc và cam kết giảm phát thải khiến chính quyền phải tìm giải pháp mới cho hệ thống di chuyển hằng ngày.</p><p>Xe buýt điện là một trong những bước đi dễ thấy nhất. Khi được triển khai trên các tuyến đông hành khách, loại phương tiện này có thể giảm tiếng ồn, giảm khí thải tại khu vực trung tâm và tạo hình ảnh tích cực cho giao thông công cộng. Tuy nhiên, hiệu quả phụ thuộc nhiều vào chất lượng vận hành, tần suất chuyến và mạng lưới trạm sạc.</p><figure class=\"article-inline-image\"><img src=\"https://picsum.photos/seed/thoang-electric-bus/1200/700\" alt=\"Xe buýt điện trong đô thị\" loading=\"lazy\"><figcaption>Xe buýt điện giúp giảm phát thải tại khu vực đông dân cư nếu được vận hành với tần suất ổn định.</figcaption></figure><p>Bên cạnh phương tiện công cộng, nhiều nơi cũng đầu tư làn xe đạp, phố đi bộ và ứng dụng quản lý giao thông thông minh. Mục tiêu không chỉ là thay đổi loại phương tiện, mà còn thay đổi thói quen di chuyển của người dân theo hướng ngắn hơn, sạch hơn và ít phụ thuộc vào xe cá nhân.</p><p>Thách thức lớn nằm ở chi phí đầu tư ban đầu và khả năng phối hợp giữa các bên. Một mạng lưới giao thông xanh cần quy hoạch đồng bộ, từ điểm đón trả khách, bãi gửi xe, hệ thống thanh toán đến kết nối với khu dân cư và nơi làm việc.</p><figure class=\"article-inline-image\"><img src=\"https://picsum.photos/seed/thoang-bike-lane/1200/700\" alt=\"Làn xe đạp trong thành phố\" loading=\"lazy\"><figcaption>Hạ tầng cho người đi bộ và xe đạp góp phần làm giảm áp lực lên giao thông cá nhân.</figcaption></figure>', 5, 2, 'Tác giả Thoáng.vn', 'Thế giới, Môi trường, Đô thị', 'https://picsum.photos/seed/thoang-green-city-thumb/1200/700', 'Approved', 81, '2026-05-09 17:32:16', '2026-05-30 05:19:36', '2026-05-09 17:32:16'),
(5, 'Thói quen đọc sách trở lại trong giới trẻ', 'Các câu lạc bộ đọc sách, hội chợ sách và nội dung review đang giúp sách giấy có thêm sức sống.', '<p>Trong vài năm gần đây, hoạt động đọc sách của người trẻ có dấu hiệu trở lại theo một cách mới. Không chỉ đọc một mình, nhiều bạn chọn tham gia câu lạc bộ, nhóm thảo luận và các buổi trao đổi sách để biến việc đọc thành trải nghiệm cộng đồng.</p><p>Sự phát triển của mạng xã hội cũng góp phần làm sách trở nên gần gũi hơn. Những video review ngắn, danh sách gợi ý theo chủ đề và các thử thách đọc sách giúp người mới bắt đầu dễ tìm được cuốn phù hợp. Điều này tạo ra cầu nối giữa văn hóa đọc truyền thống và thói quen tiếp nhận nội dung nhanh của thế hệ số.</p><figure class=\"article-inline-image\"><img src=\"https://picsum.photos/seed/thoang-book-cafe/1200/700\" alt=\"Không gian đọc sách tại quán cà phê\" loading=\"lazy\"><figcaption>Không gian đọc cộng đồng khiến việc đọc sách trở nên thân thiện và ít cô đơn hơn.</figcaption></figure><p>Điểm đáng mừng là nhiều bạn trẻ không chỉ đọc sách kỹ năng, mà còn quan tâm đến văn học, lịch sử, khoa học thường thức và sách nghiên cứu xã hội. Việc đọc đa dạng giúp mở rộng vốn sống, tăng khả năng tập trung và tạo thói quen suy nghĩ sâu hơn trước các vấn đề.</p><p>Dù vậy, việc duy trì thói quen đọc vẫn cần kỷ luật cá nhân. Một số người bắt đầu bằng mục tiêu nhỏ như đọc 10 trang mỗi ngày, ghi chú lại câu hay hoặc chia sẻ cảm nhận với bạn bè. Những hành động nhỏ này giúp việc đọc bền vững hơn thay vì chỉ bùng lên trong thời gian ngắn.</p><figure class=\"article-inline-image\"><img src=\"https://picsum.photos/seed/thoang-book-fair/1200/700\" alt=\"Gian hàng tại hội chợ sách\" loading=\"lazy\"><figcaption>Hội chợ sách tạo cơ hội để độc giả gặp tác giả, nhà xuất bản và cộng đồng cùng sở thích.</figcaption></figure>', 2, 2, 'Tác giả Thoáng.vn', 'Đời sống, Sách, Giới trẻ', 'https://picsum.photos/seed/thoang-books-thumb/1200/700', 'Approved', 73, '2026-05-10 17:32:16', '2026-05-30 05:20:36', '2026-05-10 17:32:16'),
(6, 'Trường đại học mở thêm chương trình học dữ liệu', 'Các ngành phân tích dữ liệu tiếp tục thu hút thí sinh khi nhu cầu nhân lực số tăng ở nhiều lĩnh vực.', '<p>Dữ liệu đang trở thành tài sản quan trọng của doanh nghiệp, cơ quan nhà nước và các tổ chức xã hội. Vì vậy, nhiều trường đại học bắt đầu mở hoặc cập nhật chương trình đào tạo liên quan đến khoa học dữ liệu, phân tích kinh doanh và trí tuệ nhân tạo ứng dụng.</p><p>Điểm mới của các chương trình này là tính liên ngành. Sinh viên không chỉ học lập trình, thống kê và cơ sở dữ liệu, mà còn cần hiểu bài toán kinh doanh, đạo đức dữ liệu và cách trình bày kết quả cho người không chuyên. Đây là những kỹ năng giúp dữ liệu tạo ra quyết định thực tế thay vì chỉ dừng ở biểu đồ.</p><figure class=\"article-inline-image\"><img src=\"https://picsum.photos/seed/thoang-data-classroom/1200/700\" alt=\"Sinh viên học phân tích dữ liệu\" loading=\"lazy\"><figcaption>Các lớp học dữ liệu thường kết hợp giữa lý thuyết, thực hành và dự án theo nhóm.</figcaption></figure><p>Doanh nghiệp hiện cần nhân sự có khả năng làm sạch dữ liệu, xây dựng dashboard, phát hiện xu hướng và hỗ trợ dự báo. Những vị trí này xuất hiện trong ngân hàng, bán lẻ, logistics, giáo dục, y tế và nhiều ngành dịch vụ khác. Điều đó khiến ngành dữ liệu trở thành lựa chọn hấp dẫn với thí sinh yêu thích công nghệ nhưng vẫn muốn gắn với bài toán xã hội.</p><p>Tuy nhiên, sinh viên cần tránh kỳ vọng rằng chỉ học công cụ là đủ. Kiến thức nền về xác suất, tư duy mô hình và khả năng đặt câu hỏi đúng vẫn là yếu tố cốt lõi. Công cụ có thể thay đổi nhanh, nhưng tư duy phân tích sẽ theo người học lâu dài.</p><figure class=\"article-inline-image\"><img src=\"https://picsum.photos/seed/thoang-data-dashboard/1200/700\" alt=\"Dashboard phân tích dữ liệu\" loading=\"lazy\"><figcaption>Trực quan hóa dữ liệu giúp biến bảng số liệu phức tạp thành thông tin dễ hiểu cho người ra quyết định.</figcaption></figure>', 6, 2, 'Tác giả Thoáng.vn', 'Giáo dục, Dữ liệu, Đại học', 'https://picsum.photos/seed/thoang-data-thumb/1200/700', 'Approved', 123, '2026-05-11 17:32:16', '2026-05-30 05:13:12', '2026-05-11 17:32:16'),
(7, 'Startup Việt phát triển nền tảng chăm sóc sức khỏe số', 'Ứng dụng mới hỗ trợ đặt lịch khám, quản lý hồ sơ và theo dõi sức khỏe cá nhân trên một nền tảng.', '<p>Chuyển đổi số trong y tế đang mở ra nhiều cơ hội cho các startup Việt. Thay vì chỉ tập trung vào đặt lịch khám, các nền tảng mới hướng đến việc quản lý hồ sơ sức khỏe, nhắc lịch tái khám, lưu kết quả xét nghiệm và kết nối người dùng với cơ sở y tế phù hợp.</p><p>Với người dùng, lợi ích lớn nhất là giảm thời gian chờ đợi và giảm tình trạng thất lạc thông tin. Khi dữ liệu được lưu có hệ thống, bác sĩ có thể theo dõi lịch sử điều trị tốt hơn, còn bệnh nhân dễ chủ động trong việc chăm sóc sức khỏe hằng ngày.</p><figure class=\"article-inline-image\"><img src=\"https://picsum.photos/seed/thoang-health-app/1200/700\" alt=\"Ứng dụng chăm sóc sức khỏe trên điện thoại\" loading=\"lazy\"><figcaption>Nền tảng sức khỏe số giúp người dùng theo dõi lịch khám và thông tin y tế cá nhân thuận tiện hơn.</figcaption></figure><p>Dù tiềm năng lớn, lĩnh vực này có yêu cầu rất cao về bảo mật và độ tin cậy. Dữ liệu y tế là dữ liệu nhạy cảm, vì vậy nền tảng phải có cơ chế phân quyền, mã hóa và quy trình xử lý sự cố rõ ràng. Trải nghiệm người dùng cũng cần đơn giản, đặc biệt với người lớn tuổi hoặc người ít quen dùng công nghệ.</p><p>Các chuyên gia cho rằng startup y tế số sẽ phát triển bền vững hơn nếu hợp tác chặt với bệnh viện, phòng khám và chuyên gia chuyên môn. Công nghệ chỉ là một phần; niềm tin của người dùng mới là yếu tố quyết định khả năng mở rộng.</p><figure class=\"article-inline-image\"><img src=\"https://picsum.photos/seed/thoang-digital-health/1200/700\" alt=\"Bác sĩ trao đổi với bệnh nhân qua nền tảng số\" loading=\"lazy\"><figcaption>Sự kết hợp giữa chuyên môn y tế và công nghệ là điều kiện quan trọng để dịch vụ sức khỏe số tạo giá trị thật.</figcaption></figure>', 1, 2, 'Tác giả Thoáng.vn', 'Startup, Y tế số, Công nghệ', 'https://picsum.photos/seed/thoang-health-tech-thumb/1200/700', 'Approved', 89, '2026-05-12 17:32:16', '2026-05-30 05:13:44', '2026-05-12 17:32:16'),
(8, 'Giá vàng trong nước biến động nhẹ', 'Thị trường vàng điều chỉnh sau nhiều phiên tăng, nhà đầu tư thận trọng hơn trước chênh lệch mua bán.', '<p>Giá vàng trong nước biến động nhẹ sau chuỗi phiên tăng mạnh, phản ánh tâm lý thận trọng của cả người mua và người bán. Chênh lệch giữa giá mua vào và bán ra vẫn ở mức đáng chú ý, khiến nhà đầu tư ngắn hạn phải cân nhắc kỹ trước khi giao dịch.</p><p>Trên thị trường quốc tế, giá vàng thường chịu tác động từ lãi suất, tỷ giá, lạm phát và nhu cầu trú ẩn an toàn. Khi các tín hiệu kinh tế toàn cầu chưa rõ ràng, vàng có thể tiếp tục dao động trong biên độ rộng, nhất là vào thời điểm có thông tin mới từ ngân hàng trung ương lớn.</p><figure class=\"article-inline-image\"><img src=\"https://picsum.photos/seed/thoang-gold-shop/1200/700\" alt=\"Cửa hàng vàng trong giờ giao dịch\" loading=\"lazy\"><figcaption>Người mua vàng cần chú ý chênh lệch mua bán vì đây là yếu tố ảnh hưởng trực tiếp đến lợi nhuận ngắn hạn.</figcaption></figure><p>Đối với người tích lũy dài hạn, vàng vẫn được xem là một phần của danh mục phòng thủ. Tuy nhiên, việc phân bổ tỷ trọng quá lớn có thể khiến danh mục thiếu linh hoạt. Nhà đầu tư nên xác định mục tiêu rõ ràng: tích lũy tài sản, phòng ngừa rủi ro hay giao dịch ngắn hạn.</p><p>Chuyên gia khuyến nghị không nên vay mượn để mua vàng trong giai đoạn biến động. Với tài sản có biên độ giá lớn, quyết định dựa trên cảm xúc thường dẫn đến rủi ro cao hơn so với kế hoạch mua từng phần và theo dõi thị trường định kỳ.</p><figure class=\"article-inline-image\"><img src=\"https://picsum.photos/seed/thoang-gold-chart/1200/700\" alt=\"Biểu đồ giá vàng\" loading=\"lazy\"><figcaption>Biểu đồ giá giúp nhà đầu tư nhận diện xu hướng, nhưng không thể thay thế quản trị rủi ro.</figcaption></figure>', 4, 2, 'Tác giả Thoáng.vn', 'Vàng, Tài chính, Kinh tế', 'https://picsum.photos/seed/thoang-gold-thumb/1200/700', 'Approved', 150, '2026-05-13 17:32:16', '2026-05-30 05:14:16', '2026-05-13 17:32:16'),
(9, 'Giải chạy cộng đồng thu hút hơn 5.000 người', 'Sự kiện thể thao cuối tuần lan tỏa thói quen vận động và gây quỹ cho các hoạt động xã hội.', '<p>Một giải chạy cộng đồng cuối tuần đã thu hút hơn 5.000 người tham gia ở nhiều cự ly khác nhau. Sự kiện không chỉ là hoạt động thể thao, mà còn trở thành dịp để gia đình, nhóm bạn và doanh nghiệp cùng lan tỏa lối sống năng động.</p><p>Các cự ly được thiết kế từ ngắn đến dài giúp người mới bắt đầu cũng có thể tham gia. Ban tổ chức bố trí trạm nước, đội hỗ trợ y tế và khu vực phục hồi sau khi hoàn thành đường chạy. Điều này giúp trải nghiệm của vận động viên phong trào an toàn và thân thiện hơn.</p><figure class=\"article-inline-image\"><img src=\"https://picsum.photos/seed/thoang-running-start/1200/700\" alt=\"Người tham gia xuất phát tại giải chạy\" loading=\"lazy\"><figcaption>Không khí xuất phát là khoảnh khắc tạo nhiều cảm hứng nhất trong các giải chạy cộng đồng.</figcaption></figure><p>Điểm nổi bật của giải năm nay là hoạt động gây quỹ cho chương trình hỗ trợ trẻ em có hoàn cảnh khó khăn. Mỗi lượt đăng ký đóng góp một phần kinh phí cho quỹ, đồng thời giúp người tham gia cảm thấy hành trình của mình có thêm ý nghĩa xã hội.</p><p>Sau đại dịch, nhu cầu vận động ngoài trời tăng rõ rệt. Chạy bộ trở thành lựa chọn phổ biến vì chi phí thấp, dễ bắt đầu và phù hợp với nhiều độ tuổi. Các huấn luyện viên khuyến nghị người mới nên tăng cự ly từ từ, khởi động kỹ và nghỉ ngơi đủ để tránh chấn thương.</p><figure class=\"article-inline-image\"><img src=\"https://picsum.photos/seed/thoang-running-finish/1200/700\" alt=\"Người chạy về đích\" loading=\"lazy\"><figcaption>Hoàn thành đường chạy giúp người tham gia có thêm động lực duy trì thói quen vận động thường xuyên.</figcaption></figure>', 3, 2, 'Tác giả Thoáng.vn', 'Running, Cộng đồng, Thể thao', 'https://picsum.photos/seed/thoang-running-thumb/1200/700', 'Approved', 75, '2026-05-14 17:32:16', '2026-05-30 05:14:48', '2026-05-14 17:32:16'),
(10, 'Công nghệ pin mới hứa hẹn tăng thời lượng thiết bị', 'Các phòng thí nghiệm đang thử nghiệm vật liệu pin an toàn hơn, bền hơn và có khả năng sạc nhanh hơn.', '<p>Thời lượng pin luôn là một trong những yếu tố người dùng quan tâm nhất khi chọn điện thoại, laptop hoặc thiết bị đeo. Các nghiên cứu mới về vật liệu pin đang hướng đến mục tiêu tăng mật độ năng lượng, giảm nguy cơ quá nhiệt và kéo dài tuổi thọ sau nhiều chu kỳ sạc.</p><p>Một số nhóm nghiên cứu tập trung vào pin thể rắn, trong khi các doanh nghiệp khác cải thiện cấu trúc điện cực và thuật toán quản lý sạc. Nếu được thương mại hóa thành công, công nghệ mới có thể giúp thiết bị mỏng hơn nhưng vẫn dùng được lâu hơn.</p><figure class=\"article-inline-image\"><img src=\"https://picsum.photos/seed/thoang-battery-lab/1200/700\" alt=\"Phòng thí nghiệm nghiên cứu pin\" loading=\"lazy\"><figcaption>Các thử nghiệm trong phòng lab là bước đầu trước khi công nghệ pin mới được đưa vào sản xuất đại trà.</figcaption></figure><p>Tuy vậy, con đường từ phòng thí nghiệm đến sản phẩm thương mại thường kéo dài. Nhà sản xuất cần chứng minh độ ổn định, chi phí sản xuất, khả năng mở rộng và mức độ an toàn trong nhiều điều kiện sử dụng khác nhau. Một công nghệ tốt trên mẫu thử chưa chắc đã phù hợp với hàng triệu thiết bị.</p><p>Trong lúc chờ các đột phá lớn, người dùng vẫn có thể kéo dài tuổi thọ pin bằng thói quen sạc hợp lý, tránh nhiệt độ quá cao và cập nhật phần mềm quản lý năng lượng. Những thay đổi nhỏ này giúp thiết bị hoạt động ổn định hơn trong thời gian dài.</p><figure class=\"article-inline-image\"><img src=\"https://picsum.photos/seed/thoang-phone-charging/1200/700\" alt=\"Thiết bị di động đang sạc\" loading=\"lazy\"><figcaption>Quản lý sạc thông minh giúp cân bằng giữa tốc độ sạc và tuổi thọ pin của thiết bị.</figcaption></figure>', 1, 2, 'Tác giả Thoáng.vn', 'Pin, Công nghệ, Thiết bị', 'https://picsum.photos/seed/thoang-battery-thumb/1200/700', 'Approved', 194, '2026-05-15 17:32:16', '2026-05-30 05:15:20', '2026-05-15 17:32:16'),
(11, 'Bài chờ duyệt về triển lãm công nghệ', 'Bài viết này dùng để kiểm tra trạng thái chờ duyệt trong dashboard admin.', '<p>Nội dung đang chờ admin xem xét. Người đọc thông thường sẽ không thấy bài này ở trang chủ cho đến khi bài được duyệt.</p>', 1, 2, 'Tác giả Thoáng.vn', 'Chờ duyệt, Công nghệ', NULL, 'request', 0, '2026-05-23 17:32:16', '2026-05-23 17:32:16', NULL),
(12, 'Bài mẫu bị từ chối', 'Bài viết này minh họa trạng thái bị từ chối trong dashboard writer.', '<p>Trạng thái bị từ chối giúp writer phân biệt với bài đang chờ duyệt hoặc đã xuất bản.</p>', 7, 2, 'Tác giả Thoáng.vn', 'Từ chối, Mẫu', NULL, 'disapproved', 0, '2026-05-25 17:32:16', '2026-05-25 17:32:16', NULL),
(13, 'Tin nóng: Phát hiện mới về vũ trụ', 'Các nhà khoa học vừa công bố một khám phá đột phá về hố đen siêu khối lượng ở trung tâm dải ngân hà.', '<p>Khám phá này mở ra nhiều hướng nghiên cứu mới về cách các thiên hà hình thành và phát triển. Theo nhóm nghiên cứu, hố đen này có khối lượng gấp hàng tỷ lần mặt trời và đang trong giai đoạn hoạt động mạnh nhất từng được ghi nhận.</p>', 1, 2, 'Tác giả Thoáng.vn', 'Vũ trụ, Khoa học, Hot', 'https://picsum.photos/seed/thoang-space/1200/700', 'Approved', 1528, '2026-05-26 03:44:38', '2026-05-30 05:15:52', '2026-05-26 04:44:38'),
(14, 'Nóng: Thay đổi lớn trong kỳ thi năm nay', 'Bộ Giáo dục vừa ban hành quy chế mới với nhiều điểm điều chỉnh quan trọng cho kỳ thi sắp tới.', '<p>Theo đó, cấu trúc đề thi sẽ có sự phân hóa cao hơn nhằm đánh giá chính xác năng lực thực tế của học sinh. Các trường đại học cũng được tự chủ nhiều hơn trong việc xét tuyển dựa trên kết quả kỳ thi này kết hợp với hồ sơ học bạ.</p>', 6, 2, 'Tác giả Thoáng.vn', 'Giáo dục, Tuyển sinh, Hot', 'https://picsum.photos/seed/thoang-exam/1200/700', 'Approved', 2344, '2026-05-26 00:44:38', '2026-05-30 05:16:24', '2026-05-26 02:44:38'),
(15, 'Công nghệ mới giúp giảm thiểu rác thải nhựa đại dương', 'Một hệ thống lưới thông minh tự động thu gom rác thải vừa được triển khai thử nghiệm tại các vùng biển lớn.', '<p>Dự án này sử dụng AI để nhận diện dòng chảy và mật độ rác, từ đó triển khai lưới thu gom một cách tối ưu nhất. Khả năng tự động hóa giúp giảm bớt sức lao động và tăng đáng kể hiệu suất thu gom rác thải nhựa trên diện rộng.</p>', 5, 2, 'Tác giả Thoáng.vn', 'Môi trường, Công nghệ, Hot', 'https://picsum.photos/seed/thoang-ocean/1200/700', 'Approved', 893, '2026-05-26 04:44:38', '2026-05-30 05:16:56', '2026-05-26 05:14:38'),
(16, 'Thị trường bất động sản có dấu hiệu khởi sắc cuối năm', 'Lãi suất cho vay giảm và các chính sách hỗ trợ đang tạo động lực mới cho giao dịch bất động sản.', '<p>Nhiều dự án mới bắt đầu rục rịch mở bán, ghi nhận lượng quan tâm tăng đáng kể từ người mua nhà ở thực. Nguồn cung dự kiến sẽ dồi dào hơn khi nhiều nhà phát triển quyết định tung ra các sản phẩm chiến lược sau thời gian dài quan sát thị trường.</p>', 4, 2, 'Tác giả Thoáng.vn', 'Kinh tế, Bất động sản', 'https://picsum.photos/seed/thoang-realestate/1200/700', 'Approved', 1249, '2026-05-26 00:44:38', '2026-05-30 05:17:28', '2026-05-26 01:44:38'),
(17, 'Khai mạc giải đấu thể thao điện tử quy mô lớn nhất khu vực', 'Hàng trăm đội tuyển chuyên nghiệp đã quy tụ về sự kiện eSports lớn nhất năm để tranh tài.', '<p>Sự kiện dự kiến kéo dài trong hai tuần với tổng giải thưởng lên đến hàng triệu đô la, thu hút đông đảo người hâm mộ theo dõi. Giải đấu không chỉ là cơ hội để các đội tuyển cọ xát mà còn là dịp để cộng đồng yêu thích eSports hội ngộ và giao lưu.</p>', 3, 2, 'Tác giả Thoáng.vn', 'Thể thao, eSports, Game', 'https://picsum.photos/seed/thoang-esport/1200/700', 'Approved', 3518, '2026-05-25 22:44:38', '2026-05-30 05:18:00', '2026-05-25 23:44:38');

-- --------------------------------------------------------

--
-- Table structure for table `bookmarks`
--

CREATE TABLE `bookmarks` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookmarks`
--

INSERT INTO `bookmarks` (`id`, `article_id`, `session_id`, `user_id`, `created_at`) VALUES
(1, 1, 'sample-session', NULL, '2026-05-20 17:32:16'),
(2, 5, 'sample-session', NULL, '2026-05-21 17:32:16'),
(3, 3, '6crnqq1j2aokv58rk244j8cj8a', 3, '2026-05-22 17:32:16'),
(4, 15, 'or8r288pm20ljplk9m0meg7sku', 2, '2026-05-26 07:48:01');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `color_bg` varchar(20) DEFAULT '#EEEDFE',
  `color_text` varchar(20) DEFAULT '#534AB7',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `color_bg`, `color_text`) VALUES
(1, 'Công nghệ', 'tech', '#EEEDFE', '#534AB7'),
(2, 'Đời sống', 'life', '#D4EDDA', '#155724'),
(3, 'Thể thao', 'sport', '#F8D7DA', '#721c24'),
(4, 'Kinh tế', 'biz', '#FFF3CD', '#856404'),
(5, 'Thế giới', 'world', '#D1ECF1', '#0c5460'),
(6, 'Giáo dục', 'edu', '#E2E3E5', '#383d41'),
(7, 'Khác', 'other', '#E5E7EB', '#374151');

UPDATE `categories`
SET `sort_order` = `id`
WHERE `sort_order` = 0;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `article_id`, `user_id`, `content`, `created_at`) VALUES
(1, 1, 3, 'Bài viết dễ hiểu, nhất là phần nhấn mạnh việc kiểm chứng thông tin khi dùng AI.', '2026-05-21 17:32:16'),
(2, 1, 2, 'Mình nghĩ các công ty nên có quy định nội bộ rõ ràng hơn khi dùng AI cho dữ liệu khách hàng.', '2026-05-22 17:32:16'),
(3, 3, 3, 'Hy vọng các cầu thủ trẻ sẽ được trao thêm thời gian thi đấu để thể hiện năng lực.', '2026-05-23 17:32:16'),
(4, 5, 3, 'Mình cũng bắt đầu đọc lại sách giấy gần đây, cảm giác tập trung hơn đọc trên điện thoại.', '2026-05-24 17:32:16');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `sender_email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `status` enum('pending','replied','done') DEFAULT 'pending',
  `admin_reply` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `replied_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `sender_email`, `subject`, `message`, `status`, `admin_reply`, `created_at`, `replied_at`) VALUES
(1, 3, 'user@thoang.vn', 'Góp ý giao diện', 'Trang đọc tin hoạt động tốt, mong có thêm nhiều danh mục hơn.', 'pending', NULL, '2026-05-25 17:32:16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT '',
  `avatar` varchar(120) DEFAULT 'images/avatars/avatar-01.svg',
  `role` enum('admin','writer','user') DEFAULT 'user',
  `is_verified` tinyint(1) DEFAULT 0,
  `verify_token` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `role`, `is_verified`, `verify_token`, `reset_token`, `reset_expires`, `created_at`) VALUES
(1, 'admin123', 'admin@thoang.vn', '$2y$10$Jop12xzbuAPxGhyldwVDgeJmTBx4ZGQsGIdgbhEDGIHrgFVMgx1lW', 'Quản trị viên', 'admin', 1, NULL, NULL, NULL, '2026-05-25 17:32:16'),
(2, 'writer01', 'writer@thoang.vn', '$2y$10$Jop12xzbuAPxGhyldwVDgeJmTBx4ZGQsGIdgbhEDGIHrgFVMgx1lW', 'Tác giả Thoáng.vn', 'writer', 1, NULL, NULL, NULL, '2026-05-25 17:32:16'),
(3, 'user01', 'user@thoang.vn', '$2y$10$Jop12xzbuAPxGhyldwVDgeJmTBx4ZGQsGIdgbhEDGIHrgFVMgx1lW', 'Người đọc mẫu', 'user', 1, NULL, NULL, NULL, '2026-05-25 17:32:16');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `about_sections`
--
ALTER TABLE `about_sections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `section_key` (`section_key`),
  ADD KEY `idx_about_updated_by` (`updated_by`);

--
-- Indexes for table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_articles_category` (`category_id`),
  ADD KEY `idx_articles_author_status` (`author_id`,`status`);

--
-- Indexes for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_session_article` (`session_id`,`article_id`),
  ADD KEY `idx_bookmarks_article` (`article_id`),
  ADD KEY `fk_bookmarks_users` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_categories_parent` (`parent_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_comments_article` (`article_id`,`created_at`),
  ADD KEY `idx_comments_user` (`user_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_feedback_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `about_sections`
--
ALTER TABLE `about_sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `bookmarks`
--
ALTER TABLE `bookmarks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `about_sections`
--
ALTER TABLE `about_sections`
  ADD CONSTRAINT `fk_about_users` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `fk_articles_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_articles_categories` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD CONSTRAINT `fk_bookmarks_articles` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bookmarks_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `fk_comments_articles` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_comments_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `fk_feedback_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
