<?php
// config/mailer.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../libs/PHPMailer/Exception.php';
require_once __DIR__ . '/../libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer/SMTP.php';

function sendSystemEmail($toEmail, $subject, $bodyContent) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'khanhhoang9918@gmail.com'; // Email Gmail dùng để gửi tin
        $mail->Password   = 'nzbd bucu dqvp ibgn';     // Mật khẩu ứng dụng 16 ký tự tạo từ Google Account
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        
        $mail->setFrom('khanhhoang9918@gmail.com', 'Thoáng.vn');
        $mail->addAddress($toEmail);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $bodyContent;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed. Error: {$mail->ErrorInfo}");
        return false;
    }
}