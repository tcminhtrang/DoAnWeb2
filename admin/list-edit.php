<?php
require_once 'check_admin.php';
require_once '../config/database.php';

// 1. Lấy thông tin sản phẩm cần sửa
if (isset($_GET['id'])) {
    $id = (int)$_GET['id']; // Ép kiểu an toàn
    $sql_get = "SELECT * FROM products WHERE id = $id";
    $result = $conn->query($sql_get);
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        header("Location: list.php"); exit();
    }
} else { 
    header("Location: list.php"); exit(); 
}

// 2. Lấy danh mục cho dropdown
$result_categories = $conn->query("SELECT id, category_name FROM categories WHERE status = 'active'");

// 3. Xử lý khi nhấn nút Lưu thay đổi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = trim($_POST['product-name']);
    $category_id  = (int)$_POST['product-category'];
    $status       = trim($_POST['product-status']);
    $desc         = trim($_POST['product-desc']);
    
    $image        = trim($_POST['product-image']);
    $remove_img   = isset($_POST['remove-image']) ? true : false;

    // Xử lý logic hình ảnh
    if ($remove_img) {
        $image = 'default.jpg';
    } elseif (empty($image)) {
        $image = $product['image']; // Giữ nguyên ảnh cũ nếu không nhập gì
    }

    // Cập nhật bằng Prepared Statement
    $stmt_update = $conn->prepare("UPDATE products SET product_name = ?, category_id = ?, description = ?, image = ?, status = ? WHERE id = ?");
    $stmt_update->bind_param("sisssi", $product_name, $category_id, $desc, $image, $status, $id);

    if ($stmt_update->execute()) {
        header("Location: list.php"); 
        exit();
    } else {
        $error_msg = "Lỗi CSDL: Không thể cập nhật sản phẩm.";
    }
    $stmt_update->close();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <title>ChickenJoy Admin | Sửa sản phẩm</title>
  <link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body class="admin-body">
  <?php include 'layout/sidebar.php'; ?>
  <main class="main-content">
    <header class="main-header">
      <h1>Sửa thông tin sản phẩm</h1>
      <a href="list.php" class="btn-gray">&larr; Quay lại</a>
    </header>

    <section class="form-section">
      <?php if(isset($error_msg)): ?>
          <p style="color: #e74c3c; font-weight: bold; margin-bottom: 15px;"><img src="../assets/images/icons/triangle-warning.png" style="width:16px; vertical-align:text-bottom;"> <?= $error_msg ?></p>
      <?php endif; ?>

      <form action="" method="POST" id="product-form" novalidate>
      <div style="display:flex; gap: 15px;">
        <div class="form-group" style="flex: 1;">
            <label>Mã sản phẩm:</label>
            <input type="text" class="form-input" value="<?php echo $product['product_code']; ?>" readonly style="background: #eee; cursor: not-allowed;">
        </div>
        <div class="form-group" style="flex: 2;">
            <label>Tên sản phẩm:</label>
            <input type="text" name="product-name" class="form-input req-input" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
        </div>
      </div>

      <div style="display:flex; gap: 15px;">
        <div class="form-group" style="flex: 1;">
            <label>Loại sản phẩm:</label>
            <select name="product-category" class="form-input" required>
            <?php 
            $result_categories->data_seek(0);
            while($cat = $result_categories->fetch_assoc()) {
                $sel = ($cat['id'] == $product['category_id']) ? 'selected' : '';
                echo "<option value='" . $cat['id'] . "' $sel>" . $cat['category_name'] . "</option>";
            } ?>
            </select>
        </div>
        <div class="form-group" style="flex: 1;">
            <label>Trạng thái:</label>
            <select name="product-status" class="form-input">
            <option value="active" <?php if($product['status'] == 'active') echo 'selected'; ?>>Đang hiển thị</option>
            <option value="hidden" <?php if($product['status'] == 'hidden') echo 'selected'; ?>>Ẩn</option>
            </select>
        </div>
      </div>

      <div class="form-group" style="background: #f9f9f9; padding: 15px; border-radius: 8px; border: 1px solid #eee;">
        <label>Đường dẫn hình ảnh mới (Để trống nếu giữ hình cũ):</label>
        <input type="text" name="product-image" class="form-input" placeholder="Hình hiện tại: <?php echo $product['image']; ?>">
        
        <div style="margin-top: 15px; color: #e74c3c; display: flex; align-items: center; gap: 8px;">
            <input type="checkbox" id="remove-img" name="remove-image" value="yes" style="margin: 0; width: 18px; height: 18px; cursor: pointer;">
            <label for="remove-img" style="margin: 0; font-weight: normal; cursor: pointer;">Xóa hình ảnh hiện tại (Sử dụng ảnh mặc định)</label>
        </div>
      </div>

      <div class="form-group">
          <label>Mô tả:</label>
          <textarea name="product-desc" class="form-input" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
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

    document.getElementById('product-form').addEventListener('submit', function(e) {
        let hasError = false;
        
        document.querySelectorAll('.req-input').forEach(el => {
            el.style.borderColor = "#ccc";
            if (el.value.trim() === "") {
                hasError = true;
                el.style.borderColor = "red";
            }
        });

        if (hasError) {
            e.preventDefault();
        }
    });
  </script>
</body>
</html>