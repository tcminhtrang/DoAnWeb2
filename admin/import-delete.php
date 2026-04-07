<?php
require_once '../config/database.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $check = $conn->query("SELECT status FROM import_receipts WHERE id = $id");
    if ($check->num_rows > 0) {
        $status = $check->fetch_assoc()['status'];
        
        if ($status == 'completed') {
            echo "<script>alert('Lỗi: Không thể xóa phiếu nhập ĐÃ HOÀN THÀNH vì sẽ làm sai lệch tồn kho!'); window.location.href='import.php';</script>";
            exit();
        } else {
            $conn->query("DELETE FROM import_receipts WHERE id = $id");
        }
    }
}
header("Location: import.php");
exit();
?>