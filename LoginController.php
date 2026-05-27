<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_id = trim($_POST['login_id'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($login_id) || empty($password)) {
        $error = 'Vui lòng điền đầy đủ thông tin.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
            $stmt->execute([$login_id, $login_id]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                
                // KIỂM TRA TRẠNG THÁI XÁC THỰC EMAIL
                if (isset($user['is_verified']) && $user['is_verified'] == 0) {
                    $error = 'Tài khoản của bạn chưa kích hoạt. Vui lòng kiểm tra email để xác thực trước khi đăng nhập.';
                } else {
                    $old_session_id = session_id();
                    session_regenerate_id(true);
                    $new_session_id = session_id();

                    $update_bookmarks = $pdo->prepare("UPDATE IGNORE bookmarks SET user_id = ? WHERE session_id = ? AND user_id IS NULL");
                    $update_bookmarks->execute([$user['id'], $old_session_id]);
                    
                    $sync_user_bookmarks = $pdo->prepare("UPDATE IGNORE bookmarks SET session_id = ? WHERE user_id = ?");
                    $sync_user_bookmarks->execute([$new_session_id, $user['id']]);

                    $delete_duplicates = $pdo->prepare("DELETE FROM bookmarks WHERE user_id = ? AND session_id != ?");
                    $delete_duplicates->execute([$user['id'], $new_session_id]);

                    $delete_old = $pdo->prepare("DELETE FROM bookmarks WHERE session_id = ? AND user_id IS NULL");
                    $delete_old->execute([$old_session_id]);

                    $_SESSION['user_id']  = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email']    = $user['email'];
                    $_SESSION['full_name']= $user['full_name'];
                    $_SESSION['role']     = $user['role'];

                    $redirect = 'index.php';
                    if ($user['role'] === 'admin') $redirect = 'dashboard.php';
                    elseif ($user['role'] === 'writer') $redirect = 'dashboard_writer.php';
                    
                    header("Location: " . ($_GET['redirect'] ?? $redirect));
                    exit;
                }
            } else {
                $error = 'Tên đăng nhập hoặc mật khẩu không đúng.';
            }
        } catch (PDOException $e) {
            $error = 'Có lỗi xảy ra. Vui lòng thử lại.';
        }
    }
}