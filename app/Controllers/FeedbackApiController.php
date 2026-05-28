<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\FeedbackModel;
use PDOException;

class FeedbackApiController extends Controller
{
    private FeedbackModel $feedback;

    public function __construct(FeedbackModel $feedback)
    {
        $this->feedback = $feedback;
    }

    public function submit(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Phương thức không hợp lệ.'], 405);
        }

        if (!Auth::check()) {
            $this->json([
                'success' => false,
                'auth_required' => true,
                'message' => 'Vui lòng đăng nhập hoặc đăng ký để gửi góp ý.',
            ], 401);
        }

        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if ($email === '' || $subject === '' || $message === '') {
            $this->json(['success' => false, 'message' => 'Vui lòng điền đầy đủ tất cả các trường.'], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['success' => false, 'message' => 'Địa chỉ email không hợp lệ.'], 400);
        }

        if (mb_strlen($subject, 'UTF-8') > 200) {
            $this->json(['success' => false, 'message' => 'Tiêu đề không được vượt quá 200 ký tự.'], 400);
        }

        if (mb_strlen($message, 'UTF-8') < 10) {
            $this->json(['success' => false, 'message' => 'Nội dung quá ngắn, tối thiểu 10 ký tự.'], 400);
        }

        try {
            $this->feedback->create(Auth::id(), $email, $subject, $message);
            $this->json(['success' => true, 'message' => 'Cảm ơn! Chúng tôi đã nhận được góp ý của bạn.']);
        } catch (PDOException $e) {
            $this->json(['success' => false, 'message' => 'Lỗi hệ thống. Vui lòng thử lại sau.'], 500);
        }
    }

    public function action(): void
    {
        if (!Auth::isAdmin()) {
            $this->json(['success' => false, 'message' => 'Không có quyền truy cập.'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Phương thức không hợp lệ.'], 405);
        }

        $action = trim($_POST['action'] ?? '');
        $feedbackId = (int)($_POST['feedback_id'] ?? 0);

        if ($feedbackId <= 0) {
            $this->json(['success' => false, 'message' => 'ID không hợp lệ.'], 400);
        }

        try {
            if (!$this->feedback->exists($feedbackId)) {
                $this->json(['success' => false, 'message' => 'Góp ý không tồn tại.'], 404);
            }

            if ($action === 'reply') {
                $reply = trim($_POST['reply'] ?? '');
                if ($reply === '') {
                    $this->json(['success' => false, 'message' => 'Nội dung trả lời không được để trống.'], 400);
                }
                $this->feedback->reply($feedbackId, $reply);
                $this->json(['success' => true, 'message' => 'Đã gửi phản hồi.']);
            }

            if ($action === 'mark_done') {
                $this->feedback->markDone($feedbackId);
                $this->json(['success' => true, 'message' => 'Đã đánh dấu hoàn tất.']);
            }

            if ($action === 'mark_pending') {
                $this->feedback->markPending($feedbackId);
                $this->json(['success' => true, 'message' => 'Đã chuyển về chờ xử lý.']);
            }

            if ($action === 'delete') {
                $this->feedback->delete($feedbackId);
                $this->json(['success' => true, 'message' => 'Đã xoá góp ý.']);
            }

            $this->json(['success' => false, 'message' => 'Action không hợp lệ.'], 400);
        } catch (PDOException $e) {
            $this->json(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()], 500);
        }
    }
}
