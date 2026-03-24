<?php
require_once '../config/database.php';

// 1. Lấy thông tin khuyến mãi hiện tại để hiển thị lên form
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql_get = "SELECT * FROM promotions WHERE id = $id";
    $result = $conn->query($sql_get);
    
    if ($result->num_rows > 0) {
        $promo = $result->fetch_assoc();
    } else {
        echo "<script>alert('Không tìm thấy mã khuyến mãi!'); window.location.href='promotion.php';</script>";
        exit();
    }
} else {
    header("Location: promotion.php");
    exit();
}

// 2. Xử lý khi submit form cập nhật
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = strtoupper($conn->real_escape_string($_POST['promo-code']));
    $name = $conn->real_escape_string($_POST['promo-name']);
    $discount = (int)$_POST['promo-discount'];
    $start_date = $_POST['promo-start'];
    $end_date = $_POST['promo-end'];
    $status = $_POST['promo-status'];

    // Kiểm tra xem mã mới sửa có bị trùng với mã của chương trình khác không
    $sql_check = "SELECT id FROM promotions WHERE code = '$code' AND id != $id";
    $result_check = $conn->query($sql_check);

    if ($result_check->num_rows > 0) {
        echo "<script>alert('Lỗi: Mã khuyến mãi này đã được sử dụng cho chương trình khác!'); window.history.back();</script>";
    } elseif ($start_date > $end_date) {
        echo "<script>alert('Lỗi: Ngày bắt đầu không được lớn hơn ngày kết thúc!'); window.history.back();</script>";
    } else {
        $sql_update = "UPDATE promotions SET 
                        code = '$code', 
                        name = '$name', 
                        discount_percent = $discount, 
                        start_date = '$start_date', 
                        end_date = '$end_date', 
                        status = '$status' 
                       WHERE id = $id";
    
        if ($conn->query($sql_update) === TRUE) {
            header("Location: promotion.php"); 
            exit();
        } else {
            echo "<script>alert('Lỗi CSDL: Không thể cập nhật khuyến mãi!');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <title>ChickenJoy Admin | Cập nhật Khuyến Mãi</title>
  <link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body class="admin-body">
  <?php include 'layout/sidebar.php'; ?>
  <main class="main-content">
    <header class="main-header">
      <h1>Cập nhật mã giảm giá</h1>
      <a href="promotion.php" class="btn-primary" style="background: #6c757d;">Quay lại</a>
    </header>

    <section class="form-section">
      <form action="" method="POST">
        <div style="display:flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
                <label>Mã giảm giá (Code):</label>
                <input type="text" name="promo-code" value="<?php echo $promo['code']; ?>" required style="text-transform: uppercase;">
            </div>
            <div class="form-group" style="flex: 2;">
                <label>Tên chương trình:</label>
                <input type="text" name="promo-name" value="<?php echo $promo['name']; ?>" required>
            </div>
        </div>

        <div style="display:flex; gap: 15px; background: #fffaf5; padding: 15px; border-radius: 8px; border: 1px dashed #ff6b35; margin-bottom: 15px;">
            <div class="form-group" style="flex: 1;">
                <label>Tỷ lệ giảm (%):</label>
                <input type="number" name="promo-discount" min="1" max="100" value="<?php echo $promo['discount_percent']; ?>" required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label>Ngày bắt đầu:</label>
                <input type="date" name="promo-start" value="<?php echo $promo['start_date']; ?>" required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label>Ngày kết thúc:</label>
                <input type="date" name="promo-end" value="<?php echo $promo['end_date']; ?>" required>
            </div>
        </div>

        <div class="form-group" style="width: 30%;">
            <label>Trạng thái:</label>
            <select name="promo-status">
                <option value="active" <?php if($promo['status'] == 'active') echo 'selected'; ?>>Hoạt động</option>
                <option value="locked" <?php if($promo['status'] == 'locked') echo 'selected'; ?>>Khóa</option>
            </select>
        </div>

        <div class="form-actions"><button type="submit" class="btn-primary">Lưu thay đổi</button></div>
      </form>
    </section>
  </main>
</body>
</html>