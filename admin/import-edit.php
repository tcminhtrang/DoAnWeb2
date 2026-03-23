<?php
require_once '../config/connect.php';

// 1. LẤY ID VÀ HIỂN THỊ DỮ LIỆU CŨ
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql_get = "SELECT * FROM import_receipts WHERE id = $id";
    $result = $conn->query($sql_get);
    
    if ($result->num_rows > 0) {
        $receipt = $result->fetch_assoc();
    } else {
        echo "<script>alert('Không tìm thấy phiếu nhập!'); window.location.href='import.php';</script>";
        exit();
    }
} else {
    header("Location: import.php");
    exit();
}

// 2. XỬ LÝ LƯU (Chỉ cho phép sửa ngày và trạng thái)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ngay_nhap = $_POST['import_date'];
    $trang_thai = $_POST['status'];

    $sql_update = "UPDATE import_receipts SET import_date = '$ngay_nhap', status = '$trang_thai' WHERE id = $id";

    if ($conn->query($sql_update) === TRUE) {
        header("Location: import.php");
        exit();
    } else {
        echo "<script>alert('Lỗi cập nhật!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/png" href="../assets/images/logo-1.png" />
  <title>ChickenJoy Admin | Cập nhật phiếu nhập</title>
  <link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body class="admin-body">
  <?php include 'layout/sidebar.php'; ?>
  <main class="main-content">
    <header class="main-header">
      <h1>Cập nhật trạng thái phiếu nhập</h1>
      <a href="import.php" class="btn-primary" style="background: #6c757d;">Quay lại</a>
    </header>

    <section class="form-section">
      <form action="" method="POST">
        <div class="form-group">
          <label>Mã phiếu nhập:</label>
          <input type="text" value="<?php echo $receipt['receipt_code']; ?>" readonly style="background: #eee;">
        </div>

        <div class="form-group">
          <label>Tổng tiền (VNĐ):</label>
          <input type="text" value="<?php echo number_format($receipt['total_amount'], 0, ',', '.'); ?>đ" readonly style="background: #eee; font-weight: bold; color: #e74c3c;">
        </div>

        <div class="form-group">
          <label>Ngày nhập:</label>
          <input type="date" name="import_date" value="<?php echo date('Y-m-d', strtotime($receipt['import_date'])); ?>" required>
        </div>

        <div class="form-group">
          <label>Trạng thái:</label>
          <select name="status">
            <option value="pending" <?php if($receipt['status'] == 'pending') echo 'selected'; ?>>Đang xử lý</option>
            <option value="completed" <?php if($receipt['status'] == 'completed') echo 'selected'; ?>>Hoàn thành</option>
          </select>
        </div>

        <div class="form-actions" style="margin-top: 20px;">
          <button type="submit" class="btn-primary">Lưu thay đổi</button>
        </div>
      </form>
    </section>
  </main>
</body>
</html>