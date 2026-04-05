<?php
session_start();
require_once '../config/database.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $default_password = password_hash('123456', PASSWORD_DEFAULT);
    
    $sql = "UPDATE users SET password='$default_password' WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['success_msg'] = "Đã khởi tạo lại mật khẩu thành [123456] thành công!";
    }
}
header("Location: user-management.php");
exit();
?>