<?php
session_start();
// Chỉ cần kiểm tra session, bỏ phần xử lý POST đi
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin/dashboard.php");
    exit();
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

        <form id="loginForm" class="login-form">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" placeholder="Nhập email..." required autofocus />
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" id="password" placeholder="Nhập mật khẩu..." required />
            </div>

            <p id="errorMsg" style="display: none; color: #e74c3c; font-weight: bold; text-align: center; margin-bottom: 15px; font-size: 14px;"></p>

            <button type="submit" class="btn-primary">Đăng nhập</button>
        </form>

        <footer>
            <p>© 2026 ChickenJoy Admin</p>
        </footer>
    </div>
    
    <script src="assets/js/login.js"></script> 
</body>
</html>