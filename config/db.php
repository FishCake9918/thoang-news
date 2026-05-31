<?php
// config/db.php

$host     = 'thoang-vn-thoangvn83.l.aivencloud.com'; // ĐIỀN HOST CỦA AIVEN
$port     = '17230';                                // ĐIỀN PORT CỦA AIVEN (Không phải 3306)
$dbname   = 'thoang_vn';                            // ĐIỀN TÊN DATABASE
$username = 'avnadmin';                             // ĐIỀN USER
$password = 'AVNS_oFGKYeg7hFr-minw_zb';                   // ĐIỀN MẬT KHẨU

try {
    // Tùy chọn cấu hình PDO (Bắt buộc phải có dòng SSL_VERIFY_SERVER_CERT = false để dev với Aiven)
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, 
    ];

    // Tạo kết nối
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password, $options);
    
} catch(PDOException $e) {
    die("Kết nối Database đám mây thất bại: " . $e->getMessage());
}
?>