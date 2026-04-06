<?php
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ma_loai = trim($_POST['category-id']);
    $ten_loai = trim($_POST['category-name']);
    $mo_ta = trim($_POST['category-desc']);
    $trang_thai = $_POST['category-status'];
    $check = $conn->query("SELECT id FROM categories WHERE category_code = '$ma_loai'");
    if($check->num_rows > 0) {
        $error_msg = "Lỗi: Mã loại '$ma_loai' đã tồn tại trong hệ thống! Vui lòng chọn mã khác.";
    } else {
        $sql = "INSERT INTO categories (category_code, category_name, description, status) 
                VALUES ('$ma_loai', '$ten_loai', '$mo_ta', '$trang_thai')";
        if ($conn->query($sql) === TRUE) {
            header("Location: category.php");
            exit();
        } else {
            $error_msg = "Có lỗi xảy ra khi thêm dữ liệu!";
        }
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
      <a href="category.php" class="btn-gray">&larr; Quay lại</a>
    </header>

    <section class="form-section">
      <?php if(isset($error_msg)): ?>
          <p style="color: #e74c3c; font-weight: bold; margin-bottom: 15px;"><img src="../assets/images/icons/triangle-warning.png" style="width:16px; vertical-align:text-bottom;"> <?= $error_msg ?></p>
      <?php endif; ?>

      <form action="" method="POST" class="data-form" id="category-form" novalidate>
        <div class="form-group">
          <label for="category-id">Mã loại:</label>
          <input type="text" id="category-id" name="category-id" class="form-input req-input" placeholder="Ví dụ: L01" value="<?= isset($_POST['category-id']) ? htmlspecialchars($_POST['category-id']) : '' ?>" required autofocus>
        </div>

        <div class="form-group">
          <label for="category-name">Tên loại:</label>
          <input type="text" id="category-name" name="category-name" class="form-input req-input" placeholder="Ví dụ: Gà rán" value="<?= isset($_POST['category-name']) ? htmlspecialchars($_POST['category-name']) : '' ?>" required>
        </div>

        <div class="form-group">
          <label for="category-desc">Mô tả:</label>
          <textarea id="category-desc" name="category-desc" class="form-input" rows="4" placeholder="Mô tả ngắn về loại sản phẩm"><?= isset($_POST['category-desc']) ? htmlspecialchars($_POST['category-desc']) : '' ?></textarea>
        </div>

        <div class="form-group">
          <label for="category-status">Trạng thái:</label>
          <select id="category-status" name="category-status" class="form-input">
            <option value="active" <?= (!isset($_POST['category-status']) || $_POST['category-status'] == 'active') ? 'selected' : '' ?>>Đang hiển thị</option>
            <option value="hidden" <?= (isset($_POST['category-status']) && $_POST['category-status'] == 'hidden') ? 'selected' : '' ?>>Ẩn</option>
          </select>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary">Lưu loại sản phẩm</button>
        </div>
      </form>
    </section>
 </main>

 <script>
    document.querySelectorAll('.req-input').forEach(input => {
        input.addEventListener('input', function() {
            this.style.borderColor = "#ccc";
        });
    });

    document.getElementById('category-form').addEventListener('submit', function(e) {
        let hasError = false;
        
        document.querySelectorAll('.req-input').forEach(el => {
            el.style.borderColor = "#ccc";
            if (el.value.trim() === "") {
                hasError = true;
                el.style.borderColor = "red";
            }
        });

        if (hasError) e.preventDefault(); 
    });
 </script>
</body>
</html>