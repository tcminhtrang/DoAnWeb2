<?php
session_start();
header('Content-Type: application/json');

// 1. Kết nối DB bằng file config chung thay vì fix cứng
require_once '../config/database.php';

// Kiểm tra kết nối từ biến $conn của file database.php
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Lỗi kết nối database']);
    exit;
}

// 2. Xử lý POST (phần dưới giữ nguyên)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Truy vấn đúng email và quyền admin
    $stmt = $conn->prepare("SELECT id, fullname, password FROM users WHERE email = ? AND role = 'admin' AND status = 'active' LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // Kiểm tra mật khẩu băm
        if (password_verify($password, $user['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['fullname'];
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Mật khẩu không chính xác!']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Email admin không tồn tại hoặc tài khoản bị khóa!']);
    }
    $stmt->close();
}
?>