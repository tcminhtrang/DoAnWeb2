<?php
session_start();
require_once 'config/database.php';
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin/dashboard.php");
    exit();
}

$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT id, fullname, password FROM users WHERE email = ? AND role = 'admin' AND status = 'active' LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['fullname'];
            header("Location: admin/dashboard.php");
            exit();
        } else {
            $error_msg = "Mật khẩu không chính xác!";
        }
    } else {
        $error_msg = "Email quản trị không tồn tại hoặc tài khoản đã bị khóa!";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="assets/images/logo-1.png" />
    <title>ChickenJoy Admin | Đăng nhập</title>
    <link rel="stylesheet" href="assets/css/admin.css" />
</head>
<body class="login-page">
    <div class="login-box">
        <div class="login-header">
            <img src="assets/images/logo.png" alt="ChickenJoy Logo" class="logo" />
            <h2>Đăng nhập quản trị</h2>
        </div>

        <form method="POST" action="" class="login-form">
            <div class="form-group">
                <label for="email">Email/Tên đăng nhập</label>
                <input type="email" id="email" name="email" placeholder="Nhập email..." value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required autofocus />
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" placeholder="Nhập mật khẩu..." required />
            </div>

            <?php if($error_msg != ""): ?>
                <p class="error-msg" style="color: #e74c3c; font-weight: bold; text-align: center; margin-bottom: 15px; font-size: 14px;">
                    <?= $error_msg ?>
                </p>
            <?php endif; ?>

            <button type="submit" class="btn-primary">Đăng nhập</button>
        </form>

        <footer>
            <p>© 2026 ChickenJoy Admin</p>
        </footer>
    </div>
</body>
</html>