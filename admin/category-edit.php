<?php
require_once '../config/database.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql_get = "SELECT * FROM categories WHERE id = $id";
    $result = $conn->query($sql_get);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc(); 
    } else {
        header("Location: category.php");
        exit();
    }
} else {
    header("Location: category.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ten_loai = trim($_POST['category-name']);
    $mo_ta = trim($_POST['category-desc']);
    $trang_thai = $_POST['category-status'];

    if($ten_loai == "") {
        $error_msg = "Tên loại không được để trống!";
    } else {
        $sql_update = "UPDATE categories 
                       SET category_name = '$ten_loai', description = '$mo_ta', status = '$trang_thai' 
                       WHERE id = $id";

        if ($conn->query($sql_update) === TRUE) {
            $conn->query("UPDATE products SET category = '$ten_loai' WHERE category_id = $id");

            header("Location: category.php"); 
            exit();
        } else {
            $error_msg = "Có lỗi xảy ra khi cập nhật!";
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
 <title>ChickenJoy Admin | Sửa Loại sản phẩm</title>
  <link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body class="admin-body">
  <?php include 'layout/sidebar.php'; ?>
  <main class="main-content">
  <header class="main-header">
   <h1>Sửa loại sản phẩm</h1>
   <a href="category.php" class="btn-gray">&larr; Quay lại</a>
  </header>

  <section class="form-section">
      <?php if(isset($error_msg)): ?>
          <p style="color: #e74c3c; font-weight: bold; margin-bottom: 15px;"><img src="../assets/images/icons/triangle-warning.png" style="width:16px; vertical-align:text-bottom;"> <?= $error_msg ?></p>
      <?php endif; ?>

      <form action="" method="POST" class="data-form" id="category-form" novalidate>
    
      <div class="form-group">
        <label for="category-id">Mã loại:</label>
        <input type="text" id="category-id" name="category-id" class="form-input" value="<?php echo $row['category_code']; ?>" readonly style="background-color: #eee; cursor: not-allowed;">
      </div>

      <div class="form-group">
        <label for="category-name">Tên loại:</label>
        <input type="text" id="category-name" name="category-name" class="form-input req-input" value="<?php echo htmlspecialchars($row['category_name']); ?>" required>
      </div>

      <div class="form-group">
        <label for="category-status">Trạng thái:</label>
        <select id="category-status" name="category-status" class="form-input">
          <option value="active" <?php if($row['status'] == 'active') echo 'selected'; ?>>Đang hiển thị</option>
          <option value="hidden" <?php if($row['status'] == 'hidden') echo 'selected'; ?>>Ẩn</option>
        </select>
      </div>
      
      <div class="form-actions">
        <button type="submit" class="btn-primary">Lưu thay đổi</button>
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