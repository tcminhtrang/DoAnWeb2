<?php
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = strtoupper($conn->real_escape_string($_POST['promo-code'])); // Chuẩn hóa mã viết hoa
    $name = $conn->real_escape_string($_POST['promo-name']);
    $discount = (int)$_POST['promo-discount'];
    $start_date = $_POST['promo-start'];
    $end_date = $_POST['promo-end'];
    $status = $_POST['promo-status'];

    // Kiểm tra xem mã đã tồn tại chưa
    $sql_check = "SELECT id FROM promotions WHERE code = '$code'";
    $result_check = $conn->query($sql_check);

    if ($result_check->num_rows > 0) {
        echo "<script>alert('Lỗi: Mã khuyến mãi này đã tồn tại!'); window.history.back();</script>";
    } elseif ($start_date > $end_date) {
        echo "<script>alert('Lỗi: Ngày bắt đầu không được lớn hơn ngày kết thúc!'); window.history.back();</script>";
    } else {
        $sql_insert = "INSERT INTO promotions (code, name, discount_percent, start_date, end_date, status) 
                       VALUES ('$code', '$name', $discount, '$start_date', '$end_date', '$status')";
    
        if ($conn->query($sql_insert) === TRUE) {
            header("Location: promotion.php"); 
            exit();
        } else {
            echo "<script>alert('Lỗi CSDL: Không thể thêm khuyến mãi!');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <title>ChickenJoy Admin | Thêm Khuyến Mãi</title>
  <link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body class="admin-body">
  <?php include 'layout/sidebar.php'; ?>
  <main class="main-content">
    <header class="main-header">
      <h1>Thêm mã giảm giá mới</h1>
      <a href="promotion.php" class="btn-primary" style="background: #6c757d;">Quay lại</a>
    </header>

    <section class="form-section">
      <form action="" method="POST">
        <div style="display:flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
                <label>Mã giảm giá:</label>
                <input type="text" name="promo-code" placeholder="VD: TET2026" required style="text-transform: uppercase;">
            </div>
            <div class="form-group" style="flex: 2;">
                <label>Tên chương trình:</label>
                <input type="text" name="promo-name" placeholder="VD: Giảm giá Tết Nguyên Đán" required>
            </div>
        </div>

        <div style="display:flex; gap: 15px; background: #fffaf5; padding: 15px; border-radius: 8px; border: 1px dashed #ff6b35; margin-bottom: 15px;">
            <div class="form-group" style="flex: 1;">
                <label>Tỷ lệ giảm (%):</label>
                <input type="number" name="promo-discount" min="1" max="100" value="10" required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label>Ngày bắt đầu:</label>
                <input type="date" name="promo-start" required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label>Ngày kết thúc:</label>
                <input type="date" name="promo-end" required>
            </div>
        </div>

        <div class="form-group" style="width: 30%;">
            <label>Trạng thái:</label>
            <select name="promo-status">
                <option value="active" selected>Hoạt động</option>
                <option value="locked">Khóa</option>
            </select>
        </div>

        <div class="form-actions"><button type="submit" class="btn-primary">Lưu khuyến mãi</button></div>
      </form>
    </section>
  </main>
</body>
</html>