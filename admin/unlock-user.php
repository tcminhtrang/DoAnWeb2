<?php
session_start();
require_once '../config/database.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $conn->query("UPDATE users SET status='active' WHERE id=$id");
    $_SESSION['success_msg'] = "Đã mở khóa tài khoản thành công!";
}
header("Location: user-management.php");
exit();
?>