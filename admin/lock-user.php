<?php
require_once 'check_admin.php';
require_once '../config/database.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $conn->query("UPDATE users SET status='locked' WHERE id=$id");
    $_SESSION['success_msg'] = "Đã khóa tài khoản thành công!";
}
header("Location: user-management.php");
exit();
?>