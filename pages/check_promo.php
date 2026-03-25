<?php
include '../config/database.php';
$code = mysqli_real_escape_string($conn, $_GET['code'] ?? '');
$date_now = date('Y-m-d');

// 1. Kiểm tra mã có tồn tại không
$sql_check = "SELECT * FROM promotions WHERE code = '$code'";
$res_check = mysqli_query($conn, $sql_check);

if (mysqli_num_rows($res_check) == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Mã khuyến mãi không tồn tại!']);
    exit();
}

$promo = mysqli_fetch_assoc($res_check);

// 2. Kiểm tra trạng thái mã (active hay locked)
if ($promo['status'] !== 'active') {
    echo json_encode(['status' => 'error', 'message' => 'Mã này hiện đang bị khóa!']);
    exit();
}

// 3. Kiểm tra thời hạn sử dụng
if ($date_now < $promo['start_date']) {
    echo json_encode(['status' => 'error', 'message' => 'Mã này chưa đến ngày sử dụng!']);
    exit();
}
if ($date_now > $promo['end_date']) {
    echo json_encode(['status' => 'error', 'message' => 'Mã khuyến mãi đã hết hạn!']);
    exit();
}

// 4. Nếu mọi thứ OK
echo json_encode([
    'status' => 'success', 
    'percent' => $promo['discount_percent'],
    'message' => 'Áp dụng mã thành công!'
]);
?>