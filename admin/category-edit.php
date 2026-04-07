<?php
require_once 'check_admin.php';
require_once '../config/database.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id']; 
    
    $stmt_get = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt_get->bind_param("i", $id);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc(); 
    } else {
        header("Location: category.php");
        exit();
    }
    $stmt_get->close();
} else {
    header("Location: category.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ten_loai = trim($_POST['category-name']);
    $trang_thai = $_POST['category-status'];
    $mo_ta = isset($_POST['category-desc']) ? trim($_POST['category-desc']) : $row['description'];

    if($ten_loai == "") {
        $error_msg = "Tên loại không được để trống!";
    } else {
        $stmt_update = $conn->prepare("UPDATE categories SET category_name = ?, description = ?, status = ? WHERE id = ?");
        $stmt_update->bind_param("sssi", $ten_loai, $mo_ta, $trang_thai, $id);

        if ($stmt_update->execute()) {            
            header("Location: category.php"); 
            exit();
        } else {
            $error_msg = "Có lỗi xảy ra khi cập nhật!";
        }
        $stmt_update->close();
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