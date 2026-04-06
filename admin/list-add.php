<?php
require_once '../config/database.php';
$sql_categories = "SELECT id, category_name FROM categories WHERE status = 'active'";
$result_categories = $conn->query($sql_categories);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ma_sp = trim($_POST['product-code']);
    $ten_sp = trim($_POST['product-name']);
    $loai_sp = $_POST['product-category'];
    $don_vi = $_POST['product-unit'];
    $mo_ta = trim($_POST['product-desc']);
    $hinh_anh = trim($_POST['product-image']);
    if(empty($hinh_anh)) $hinh_anh = 'default.jpg'; 
    $trang_thai = $_POST['product-status'];

    $ton_dau = (int)$_POST['product-stock'];
    $gia_von = (float)$_POST['product-cost'];
    $loi_nhuan = (float)$_POST['product-profit'] / 100; 
    
    $gia_ban = round($gia_von * (1 + $loi_nhuan));

    $cat_query = $conn->query("SELECT category_name FROM categories WHERE id = $loai_sp");
    $category_text = ($cat_query->num_rows > 0) ? $cat_query->fetch_assoc()['category_name'] : 'Khac';
    
    $sql_check = "SELECT id FROM products WHERE product_code = '$ma_sp'";
    $result_check = $conn->query($sql_check);

    if ($result_check->num_rows > 0) {
        $error_msg = "Lỗi: Mã sản phẩm '$ma_sp' đã tồn tại trong hệ thống! Vui lòng chọn mã khác.";
    } else {
        $sql_insert = "INSERT INTO products (product_code, product_name, category_id, category, description, unit, stock, import_price, profit_rate, price, image, status) 
                       VALUES ('$ma_sp', '$ten_sp', '$loai_sp', '$category_text', '$mo_ta', '$don_vi', $ton_dau, $gia_von, $loi_nhuan, $gia_ban, '$hinh_anh', '$trang_thai')";
    
        if ($conn->query($sql_insert) === TRUE) {
            header("Location: list.php"); 
            exit();
        } else {
            $error_msg = "Lỗi CSDL: Không thể thêm sản phẩm!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <title>ChickenJoy Admin | Thêm sản phẩm</title>
  <link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body class="admin-body">
  <?php include 'layout/sidebar.php'; ?>
  <main class="main-content">
    <header class="main-header">
      <h1>Thêm sản phẩm mới</h1>
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
                <input type="text" name="product-code" class="form-input req-input" value="<?= isset($_POST['product-code']) ? htmlspecialchars($_POST['product-code']) : '' ?>" required>
            </div>
            <div class="form-group" style="flex: 2;">
                <label>Tên sản phẩm:</label>
                <input type="text" name="product-name" class="form-input req-input" value="<?= isset($_POST['product-name']) ? htmlspecialchars($_POST['product-name']) : '' ?>" required>
            </div>
        </div>

        <div style="display:flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
                <label>Loại sản phẩm:</label>
                <select name="product-category" class="form-input req-input" required>
                    <option value="">-- Chọn loại --</option>
                    <?php 
                    $result_categories->data_seek(0);
                    while($cat = $result_categories->fetch_assoc()) {
                        $sel = (isset($_POST['product-category']) && $_POST['product-category'] == $cat['id']) ? 'selected' : '';
                        echo "<option value='" . $cat['id'] . "' $sel>" . $cat['category_name'] . "</option>"; 
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group" style="flex: 1;">
                <label>Đơn vị tính:</label>
                <select name="product-unit" class="form-input">
                    <option value="Phần" <?= (isset($_POST['product-unit']) && $_POST['product-unit'] == 'Phần') ? 'selected' : '' ?>>Phần</option>
                    <option value="Miếng" <?= (isset($_POST['product-unit']) && $_POST['product-unit'] == 'Miếng') ? 'selected' : '' ?>>Miếng</option>
                    <option value="Cái" <?= (isset($_POST['product-unit']) && $_POST['product-unit'] == 'Cái') ? 'selected' : '' ?>>Cái</option>
                    <option value="Ly" <?= (isset($_POST['product-unit']) && $_POST['product-unit'] == 'Ly') ? 'selected' : '' ?>>Ly</option>
                </select>
            </div>
        </div>

        <div style="display:flex; gap: 15px; background: #fffaf5; padding: 15px; border-radius: 8px; border: 1px dashed #ff6b35; margin-bottom: 15px;">
            <div class="form-group" style="flex: 1;">
                <label>Số lượng tồn ban đầu:</label>
                <input type="number" name="product-stock" class="form-input req-number" min="0" value="<?= isset($_POST['product-stock']) ? $_POST['product-stock'] : '0' ?>" required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label>Giá vốn (VNĐ):</label>
                <input type="number" name="product-cost" class="form-input req-number" min="0" value="<?= isset($_POST['product-cost']) ? $_POST['product-cost'] : '0' ?>" required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label>Lợi nhuận mong muốn (%):</label>
                <input type="number" name="product-profit" class="form-input req-number" min="0" value="<?= isset($_POST['product-profit']) ? $_POST['product-profit'] : '20' ?>" required>
            </div>
        </div>

        <div style="display:flex; gap: 15px;">
            <div class="form-group" style="flex: 2;">
                <label>Hình ảnh (Tên file hoặc URL):</label>
                <input type="text" name="product-image" class="form-input" value="<?= isset($_POST['product-image']) ? htmlspecialchars($_POST['product-image']) : '' ?>" placeholder="Ví dụ: ga-ran.jpg (Để trống sẽ dùng mặc định)">
            </div>
            <div class="form-group" style="flex: 1;">
                <label>Trạng thái:</label>
                <select name="product-status" class="form-input">
                    <option value="active" <?= (isset($_POST['product-status']) && $_POST['product-status'] == 'active') ? 'selected' : '' ?>>Hiển thị</option>
                    <option value="hidden" <?= (isset($_POST['product-status']) && $_POST['product-status'] == 'hidden') ? 'selected' : '' ?>>Ẩn</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Mô tả chi tiết:</label>
            <textarea name="product-desc" class="form-input" rows="3"><?= isset($_POST['product-desc']) ? htmlspecialchars($_POST['product-desc']) : '' ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Lưu sản phẩm</button>
        </div>
      </form>
    </section>
  </main>

  <script>
    document.querySelectorAll('.req-input, .req-number').forEach(input => {
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

        document.querySelectorAll('.req-number').forEach(el => {
            el.style.borderColor = "#ccc";
            if (el.value === "" || parseFloat(el.value) < 0) {
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