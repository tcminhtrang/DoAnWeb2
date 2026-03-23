<?php
require_once '../config/database.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    // Xóa cứng: Nhờ lúc tạo bảng cậu đã cài ON DELETE CASCADE, nên chỉ cần xóa phiếu cha là chi tiết tự bay màu.
    $conn->query("DELETE FROM import_receipts WHERE id = $id");
}
header("Location: import.php");
exit();
?>