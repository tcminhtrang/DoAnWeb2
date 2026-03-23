<?php
require_once '../config/database.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $result_product = $conn->query("SELECT * FROM products WHERE id = $id");
    if ($result_product->num_rows > 0) $product = $result_product->fetch_assoc();
    else { echo "<script>alert('Lỗi!'); window.location.href='list.php';</script>"; exit(); }
} else { header("Location: list.php"); exit(); }

$result_categories = $conn->query("SELECT id, category_name FROM categories WHERE status = 'active'");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ten_sp = $_POST['product-name'];
    $loai_sp = $_POST['product-category']; 
    $mo_ta = $_POST['product-desc'];
    $trang_thai = $_POST['product-status'];
    
    // XỬ LÝ SỬA/BỎ HÌNH ẢNH
    $hinh_anh = $_POST['product-image'];
    $xoa_hinh = isset($_POST['remove-image']) ? true : false;
    
    if ($xoa_hinh) {
        $hinh_anh = 'default.jpg'; // Nếu tick vào "Bỏ hình", trả về hình mặc định
    } elseif (empty($hinh_anh)) {
        $hinh_anh = $product['image']; // Nếu không nhập gì, giữ nguyên hình cũ
    }

    $sql_update = "UPDATE products 
                   SET product_name = '$ten_sp', category_id = '$loai_sp', description = '$mo_ta', status = '$trang_thai', image = '$hinh_anh'
                   WHERE id = $id";

    if ($conn->query($sql_update) === TRUE) header("Location: list.php");
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
      <a href="list.php" class="btn-primary" style="background: #6c757d;">Quay lại</a>
    </header>

    <section class="form-section">
      <form action="" method="POST">
      <div style="display:flex; gap: 15px;">
        <div class="form-group" style="flex: 1;"><label>Mã sản phẩm:</label><input type="text" value="<?php echo $product['product_code']; ?>" readonly style="background: #eee;"></div>
        <div class="form-group" style="flex: 2;"><label>Tên sản phẩm:</label><input type="text" name="product-name" value="<?php echo $product['product_name']; ?>" required></div>
      </div>

      <div style="display:flex; gap: 15px;">
        <div class="form-group" style="flex: 1;">
            <label>Loại sản phẩm:</label>
            <select name="product-category" required>
            <?php while($cat = $result_categories->fetch_assoc()) {
                $sel = ($cat['id'] == $product['category_id']) ? 'selected' : '';
                echo "<option value='" . $cat['id'] . "' $sel>" . $cat['category_name'] . "</option>";
            } ?>
            </select>
        </div>
        <div class="form-group" style="flex: 1;">
            <label>Trạng thái:</label>
            <select name="product-status">
            <option value="active" <?php if($product['status'] == 'active') echo 'selected'; ?>>Đang hiển thị</option>
            <option value="hidden" <?php if($product['status'] == 'hidden') echo 'selected'; ?>>Ẩn</option>
            </select>
        </div>
      </div>

      <div class="form-group" style="background: #f9f9f9; padding: 15px; border-radius: 8px;">
        <label>Đường dẫn hình ảnh mới (Để trống nếu giữ hình cũ):</label>
        <input type="text" name="product-image" placeholder="Hình hiện tại: <?php echo $product['image']; ?>">
        <div style="margin-top: 10px; color: #e74c3c;">
            <input type="checkbox" id="remove-img" name="remove-image" value="yes">
            <label for="remove-img" style="display:inline; font-weight: normal; cursor: pointer;">Xóa hình ảnh hiện tại (Sử dụng ảnh mặc định)</label>
        </div>
      </div>

      <div class="form-group"><label>Mô tả:</label><textarea name="product-desc" rows="4"><?php echo $product['description']; ?></textarea></div>
      <div class="form-actions"><button type="submit" class="btn-primary">Lưu thay đổi</button></div>
    </form>
    </section>
  </main>
</body>
</html>