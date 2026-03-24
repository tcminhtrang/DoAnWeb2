<?php
require_once '../config/database.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Xóa cứng khỏi bảng promotions
    $sql_delete = "DELETE FROM promotions WHERE id = $id";
    
    if ($conn->query($sql_delete) === TRUE) {
        // Có thể thêm thông báo thành công bằng session nếu muốn
    } else {
        echo "<script>alert('Lỗi: Không thể xóa mã khuyến mãi này!');</script>";
    }
}

// Chuyển hướng về trang danh sách
header("Location: promotion.php");
exit();
?>