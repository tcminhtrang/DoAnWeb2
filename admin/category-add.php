<?php
require_once '../config/database.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ma_loai = $_POST['category-id'];
    $ten_loai = $_POST['category-name'];
    $mo_ta = $_POST['category-desc'];
    $trang_thai = $_POST['category-status'];
    $sql = "INSERT INTO categories (category_code, category_name, description, status) 
            VALUES ('$ma_loai', '$ten_loai', '$mo_ta', '$trang_thai')";
    if ($conn->query($sql) === TRUE) {
        header("Location: category.php");
        exit();
    } else {
        echo "<script>alert('Lỗi: Mã loại này đã tồn tại hoặc có lỗi xảy ra!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
 <meta charset="UTF-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1.0" />
 <link rel="icon" type="image/png" href="../assets/images/logo-1.png" />
 <title>ChickenJoy Admin | Thêm Loại sản phẩm</title>
  <link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body class="admin-body">

  <?php include 'layout/sidebar.php'; ?>
  <main class="main-content">
  <header class="main-header">
      <h1>Thêm loại sản phẩm mới</h1>
      <a href="category.php" class="btn-primary" style="background: #6c757d;">Quay lại</a>
    </header>

    <section class="form-section">
      <form action="" method="POST" class="data-form">
        <div class="form-group">
          <label for="category-id">Mã loại:</label>
          <input type="text" id="category-id" name="category-id" placeholder="Ví dụ: L01" required>
        </div>

        <div class="form-group">
          <label for="category-name">Tên loại:</label>
          <input type="text" id="category-name" name="category-name" placeholder="Ví dụ: Gà rán" required>
        </div>

        <div class="form-group">
          <label for="category-desc">Mô tả:</label>
          <textarea id="category-desc" name="category-desc" rows="4" placeholder="Mô tả ngắn về loại sản phẩm"></textarea>
        </div>

        <div class="form-group">
          <label for="category-status">Trạng thái:</label>
          <select id="category-status" name="category-status">
            <option value="active" selected>Đang hiển thị</option>
            <option value="hidden">Ẩn</option>
          </select>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary">Lưu</button>
          <a href="category.php" class="btn-cancel">Hủy</a>
        </div>
      </form>
    </section>
 </main>
</body>
</html>