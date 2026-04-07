<?php
require_once 'check_admin.php';
require_once '../config/database.php';

// 1. Lấy thông tin sản phẩm
if (isset($_GET['id'])) {
    $id = (int)$_GET['id']; // FIX BUG PHÁT SINH: Ép kiểu (int) chống SQL Injection
    $sql_get = "SELECT * FROM products WHERE id = $id";
    $result = $conn->query($sql_get);
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        header("Location: price.php");
        exit();
    }
} else {
    header("Location: price.php");
    exit();
}

// 2. Xử lý khi Submit Form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ép kiểu float để an toàn tuyệt đối
    $loi_nhuan_nhap_vao = (float)$_POST['profit_rate'];
    $loi_nhuan_luu_db = $loi_nhuan_nhap_vao / 100; 
    
    // FIX BUG NGHIÊM TRỌNG: Tự tính giá bán tại Server (Bỏ qua $_POST['price'])
    $gia_von = (float)$product['import_price'];
    $gia_ban_moi = round($gia_von * (1 + $loi_nhuan_luu_db));

    // Dùng Prepared Statement để update
    $stmt_update = $conn->prepare("UPDATE products SET profit_rate = ?, price = ? WHERE id = ?");
    $stmt_update->bind_param("ddi", $loi_nhuan_luu_db, $gia_ban_moi, $id);

    if ($stmt_update->execute()) {
        header("Location: price.php");
        exit();
    } else {
        $error_msg = "Lỗi cập nhật giá: " . $conn->error;
    }
    $stmt_update->close();
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
      <a href="price.php" class="btn-gray">&larr; Quay lại</a>
    </header>

    <section class="form-section">
      <?php if(isset($error_msg)): ?>
          <p style="color: #e74c3c; font-weight: bold; margin-bottom: 15px;"><img src="../assets/images/icons/triangle-warning.png" style="width:16px; vertical-align:text-bottom;"> <?= $error_msg ?></p>
      <?php endif; ?>

      <form action="" method="POST" class="data-form">
        <div class="form-group">
          <label for="prod-code">Mã sản phẩm:</label>
          <input type="text" id="prod-code" class="form-input" value="<?php echo $product['product_code']; ?>" readonly style="background: #eee; cursor: not-allowed;">
        </div>

        <div class="form-group">
          <label for="prod-name">Tên sản phẩm:</label>
          <input type="text" id="prod-name" class="form-input" value="<?php echo htmlspecialchars($product['product_name']); ?>" readonly style="background: #eee; cursor: not-allowed;">
        </div>
        
        <div class="form-group">
          <label for="prod-cost">Giá nhập bình quân (Vốn):</label>
          <input type="number" id="prod-cost" class="form-input" name="import_price" value="<?php echo (float)$product['import_price']; ?>" readonly style="background: #eee; cursor: not-allowed;">
        </div>

        <hr style="border: 0; border-top: 1px dashed #ccc; margin: 25px 0;">

        <div class="form-group">
          <label for="prod-profit">Phần trăm lợi nhuận mong muốn (%):</label>
          <input type="number" id="prod-profit" class="form-input" name="profit_rate" step="0.01" value="<?php echo (float)$product['profit_rate'] * 100; ?>" required autofocus>
          <small style="color: #666; display: block; margin-top: 5px;">Mẹo: Nhập 20 cho 20%, 25.5 cho 25.5%</small>
        </div>
        
        <div class="form-group">
          <label for="prod-price">Giá bán đề xuất (VNĐ):</label>
          <input type="number" id="prod-price" class="form-input" name="price" value="<?php echo (int)$product['price']; ?>" readonly style="background: #fff3cd; font-weight: bold; color: #28a745; font-size: 16px; border: 1px solid #28a745;">
          <small style="color: #e74c3c; display: block; margin-top: 5px;">* Giá bán được hệ thống tự động tính: Giá vốn x (100% + % Lợi nhuận)</small>
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