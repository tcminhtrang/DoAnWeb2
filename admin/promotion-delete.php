<?php
require_once 'check_admin.php';
require_once '../config/database.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql_delete = "DELETE FROM promotions WHERE id = $id";
    if ($conn->query($sql_delete) === TRUE) {
    } else {
        echo "<script>alert('Lỗi: Không thể xóa mã khuyến mãi này!');</script>";
    }
}
header("Location: promotion.php");
exit();
?>