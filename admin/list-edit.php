<?php
require_once '../config/connect.php';

// 1. LẤY ID TỪ THANH ĐỊA CHỈ & HIỂN THỊ DỮ LIỆU CŨ
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Tìm sản phẩm trong kho
    $sql_get_product = "SELECT * FROM products WHERE id = $id";
    $result_product = $conn->query($sql_get_product);
    
    if ($result_product->num_rows > 0) {
        $product = $result_product->fetch_assoc(); // Biến $product chứa dữ liệu cũ
    } else {
        echo "<script>alert('Không tìm thấy sản phẩm!'); window.location.href='list.php';</script>";
        exit();
    }
} else {
    header("Location: list.php");
    exit();
}

// 2. LẤY DANH SÁCH LOẠI SẢN PHẨM ĐỂ ĐỔ VÀO DROPDOWN
$sql_categories = "SELECT id, category_name FROM categories WHERE status = 'active'";
$result_categories = $conn->query($sql_categories);

// 3. XỬ LÝ KHI BẤM NÚT "LƯU THAY ĐỔI" (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ten_sp = $_POST['product-name'];
    $loai_sp = $_POST['product-category']; // Đây là category_id gửi lên
    $gia_ban = $_POST['product-price'];
    $mo_ta = $_POST['product-desc'];
    $trang_thai = $_POST['product-status'];

    // Lệnh UPDATE ghi đè dữ liệu mới (Mã SP không cho sửa)
    $sql_update = "UPDATE products 
                   SET product_name = '$ten_sp', 
                       category_id = '$loai_sp', 
                       price = '$gia_ban', 
                       description = '$mo_ta',
                       status = '$trang_thai'
                   WHERE id = $id";

    if ($conn->query($sql_update) === TRUE) {
        header("Location: list.php");
        exit();
    } else {
        echo "<script>alert('Lỗi: Có vấn đề khi cập nhật!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/png" href="../assets/images/logo-1.png" />
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
      <form action="" method="POST" class="data-form">
      
      <div class="form-group">
        <label for="product-code">Mã sản phẩm:</label>
        <input type="text" id="product-code" name="product-code" value="<?php echo $product['product_code']; ?>" readonly style="background-color: #eee;">
      </div>

      <div class="form-group">
        <label for="product-name">Tên sản phẩm:</label>
        <input type="text" id="product-name" name="product-name" value="<?php echo $product['product_name']; ?>" required>
      </div>

      <div class="form-group">
        <label for="product-category">Loại sản phẩm:</label>
        <select id="product-category" name="product-category" required>
          <option value="">-- Chọn loại sản phẩm --</option>
          <?php
          if ($result_categories->num_rows > 0) {
              while($cat = $result_categories->fetch_assoc()) {
                  // Cú pháp thần thánh: So sánh ID của Loại này có khớp với ID Loại của Sản phẩm không?
                  // Nếu khớp, in ra chữ 'selected' để nó tự động được chọn.
                  $selected = ($cat['id'] == $product['category_id']) ? 'selected' : '';
                  
                  echo "<option value='" . $cat['id'] . "' $selected>" . $cat['category_name'] . "</option>";
              }
          }
          ?>
        </select>
      </div>

      <div class="form-group">
        <label for="product-price">Giá bán (VNĐ):</label>
        <input type="number" id="product-price" name="product-price" value="<?php echo (int)$product['price']; ?>" required>
      </div>

      <div class="form-group">
        <label for="product-desc">Mô tả:</label>
        <textarea id="product-desc" name="product-desc" rows="4"><?php echo $product['description']; ?></textarea>
      </div>

      <div class="form-group">
        <label for="product-status">Trạng thái:</label>
        <select id="product-status" name="product-status">
          <option value="active" <?php if($product['status'] == 'active') echo 'selected'; ?>>Đang hiển thị</option>
          <option value="hidden" <?php if($product['status'] == 'hidden') echo 'selected'; ?>>Ẩn</option>
        </select>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-primary">Lưu thay đổi</button>
      </div>
      
    </form>
    </section>
  </main>
</body>
</html>

