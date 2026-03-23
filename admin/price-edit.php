<?php
require_once '../config/connect.php';

// 1. LẤY THÔNG TIN SẢN PHẨM CŨ
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql_get = "SELECT * FROM products WHERE id = $id";
    $result = $conn->query($sql_get);
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "<script>alert('Không tìm thấy sản phẩm!'); window.location.href='price.php';</script>";
        exit();
    }
} else {
    header("Location: price.php");
    exit();
}

// 2. LƯU KHI ĐỔI % LỢI NHUẬN
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $loi_nhuan_moi = $_POST['profit_rate'];
    $gia_ban_moi = $_POST['price'];

    $sql_update = "UPDATE products SET profit_rate = $loi_nhuan_moi, price = $gia_ban_moi WHERE id = $id";

    if ($conn->query($sql_update) === TRUE) {
        header("Location: price.php");
        exit();
    } else {
        echo "<script>alert('Lỗi cập nhật giá!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/png" href="../assets/images/logo-1.png" />
  <title>ChickenJoy Admin | Sửa giá sản phẩm</title>
  <link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body class="admin-body">
  <?php include 'layout/sidebar.php'; ?>
  <main class="main-content">
    <header class="main-header">
      <h1>Cập nhật tỷ lệ lợi nhuận</h1>
      <a href="price.php" class="btn-primary" style="background: #6c757d;">Quay lại</a>
    </header>

    <section class="form-section">
      <form action="" method="POST">
        <div class="form-group">
          <label for="prod-code">Mã sản phẩm:</label>
          <input type="text" id="prod-code" value="<?php echo $product['product_code']; ?>" readonly style="background: #eee;">
        </div>

        <div class="form-group">
          <label for="prod-name">Tên sản phẩm:</label>
          <input type="text" id="prod-name" value="<?php echo $product['product_name']; ?>" readonly style="background: #eee;">
        </div>
        
        <div class="form-group">
          <label for="prod-cost">Giá nhập bình quân (Vốn):</label>
          <input type="number" id="prod-cost" name="import_price" value="<?php echo (int)$product['import_price']; ?>" readonly style="background: #eee;">
        </div>

        <hr style="border: 0; border-top: 1px solid #eee; margin: 25px 0;">

        <div class="form-group">
          <label for="prod-profit">Phần trăm lợi nhuận (%):</label>
          <input type="number" id="prod-profit" name="profit_rate" value="<?php echo (int)$product['profit_rate']; ?>" required>
        </div>
        
        <div class="form-group">
          <label for="prod-price">Giá bán (VNĐ):</label>
          <input type="number" id="prod-price" name="price" value="<?php echo (int)$product['price']; ?>" readonly style="background: #eee; font-weight: bold; color: #e74c3c;">
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary">Lưu thay đổi</button>
        </div>
      </form>
    </section>
  </main>

  <script>
    const costInput = document.getElementById('prod-cost');
    const profitInput = document.getElementById('prod-profit');
    const priceInput = document.getElementById('prod-price');

    profitInput.addEventListener('input', function() {
        let cost = parseFloat(costInput.value) || 0;
        let profit = parseFloat(profitInput.value) || 0;
        let newPrice = cost * (1 + (profit / 100));
        priceInput.value = Math.round(newPrice);
    });
  </script>
</body>
</html>