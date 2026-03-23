<?php
require_once '../config/database.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // 1. Kiểm tra xem sản phẩm này ĐÃ TỪNG ĐƯỢC NHẬP HÀNG chưa?
    $sql_check = "SELECT COUNT(*) as total FROM import_receipt_details WHERE product_id = $id";
    $result_check = $conn->query($sql_check);
    $row = $result_check->fetch_assoc();

    if ($row['total'] > 0) {
        // Đã từng nhập hàng -> XÓA MỀM (Chỉ ẩn đi để giữ lịch sử)
        $conn->query("UPDATE products SET status = 'hidden' WHERE id = $id");
    } else {
        // Chưa từng nhập hàng (Sản phẩm mới tạo thử) -> XÓA CỨNG
        $conn->query("DELETE FROM products WHERE id = $id");
    }
}
header("Location: list.php");
exit();
?>