<?php
require_once '../config/connect.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    // Xóa mềm: Đổi trạng thái thành hidden để không ảnh hưởng khóa ngoại
    $conn->query("UPDATE categories SET status = 'hidden' WHERE id = $id");
}
header("Location: category.php");
exit();
?>