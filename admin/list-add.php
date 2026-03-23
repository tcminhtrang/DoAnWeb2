<?php
require_once '../config/connect.php';
$sql_categories = "SELECT id, category_name FROM categories WHERE status = 'active'";
$result_categories = $conn->query($sql_categories);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ma_sp = $_POST['product-code'];
    $ten_sp = $_POST['product-name'];
    $loai_sp = $_POST['product-category'];
    $gia_ban = $_POST['product-price'];
    $mo_ta = $_POST['product-desc'];
    $sql_check = "SELECT id FROM products WHERE product_code = '$ma_sp'";
    $result_check = $conn->query($sql_check);

    if ($result_check->num_rows > 0) {
        echo "<script>alert('Lỗi: Mã sản phẩm [$ma_sp] này đã từng tồn tại trong lịch sử (có thể đang bị ẩn). Vui lòng sử dụng mã khác!'); window.history.back();</script>";
    } else {
        $sql_insert = "INSERT INTO products (product_code, product_name, category_id, price, description) 
                   VALUES ('$ma_sp', '$ten_sp', '$loai_sp', '$gia_ban', '$mo_ta')";
    
        if ($conn->query($sql_insert) === TRUE) {
            header("Location: list.php");
            exit();
        } else {
            echo "<script>alert('Lỗi: Không thể thêm sản phẩm!');</script>";
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
  <title>ChickenJoy Admin | Thêm sản phẩm</title>
  <link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body class="admin-body">
  <?php include 'layout/sidebar.php'; ?>

  <main class="main-content">
    <header class="main-header">
      <h1>Thêm sản phẩm mới</h1>
      <a href="list.html" class="btn-primary" style="background: #6c757d;">Quay lại</a>
    </header>

    <section class="form-section">
      <form action="#" method="get">
        <div class="form-group">
          <label for="prod-category">Loại sản phẩm:</label>
          <div class="form-group">
        <label for="product-category">Loại sản phẩm:</label>
        <select id="product-category" name="product-category" required>
          <option value="">-- Chọn loại sản phẩm --</option>
          
          <?php
          if ($result_categories->num_rows > 0) {
              while($cat = $result_categories->fetch_assoc()) {
                  echo "<option value='" . $cat['id'] . "'>" . $cat['category_name'] . "</option>";
              }
          }
          ?>
          
        </select>
      </div>
        </div>
        
        <div class="form-group">
          <label for="prod-code">Mã sản phẩm:</label>
          <input type="text" id="prod-code" placeholder="Ví dụ: SP001">
        </div>

        <div class="form-group">
          <label for="prod-name">Tên sản phẩm:</label>
          <input type="text" id="prod-name" placeholder="Ví dụ: Gà rán giòn tan">
        </div>

        <div class="form-group">
          <label for="prod-image">Hình ảnh (URL):</label>
          <input type="text" id="prod-image" placeholder="Nhập đường dẫn URL của hình ảnh...">
        </div>

        <div class="form-group">
          <label for="prod-desc">Mô tả:</label>
          <textarea id="prod-desc" rows="4" placeholder="Mô tả ngắn về sản phẩm..."></textarea>
        </div>

        <div class="form-actions">
          <a href="list.html" class="btn-primary">Lưu lại</a>
          <a href="list.html" class="btn-cancel">Hủy bỏ</a>
        </div>
      </form>
    </section>
  </main>
</body>
</html>