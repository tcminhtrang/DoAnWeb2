<?php
require_once '../config/database.php';
$sql_products = "SELECT id, product_name, product_code FROM products WHERE status = 'active'";
$result_products = $conn->query($sql_products);
$products_list = [];
while($p = $result_products->fetch_assoc()) {
    $products_list[] = $p;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (!isset($_POST['product_id']) || empty(array_filter($_POST['product_id']))) {
        echo "<script>alert('Lỗi: Phiếu nhập trống hoặc sản phẩm không hợp lệ!'); window.location.href='import-add.php';</script>";
        exit();
    }

    $receipt_code = mysqli_real_escape_string($conn, $_POST['receipt_code']);
    $import_date = mysqli_real_escape_string($conn, $_POST['import_date']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // BẮT ĐẦU TRANSACTION
    mysqli_begin_transaction($conn);
    
    try {
        $sql_receipt = "INSERT INTO import_receipts (receipt_code, import_date, status, total_amount) VALUES ('$receipt_code', '$import_date', '$status', 0)";
        
        if (!$conn->query($sql_receipt)) {
            throw new Exception("Không thể tạo phiếu nhập.");
        }
        
        $receipt_id = $conn->insert_id;
        $total_all = 0;
        
        $product_ids = $_POST['product_id'];
        $quantities = $_POST['quantity'];
        $prices = $_POST['import_price'];

        for ($i = 0; $i < count($product_ids); $i++) {
            $p_id = (int)$product_ids[$i];
            $qty = (int)$quantities[$i];
            $price = (float)$prices[$i];
            
            if (empty($p_id) || $qty <= 0 || $price < 1000) continue;

            $subtotal = $qty * $price;
            $total_all += $subtotal;
            
            $sql_detail = "INSERT INTO import_receipt_details (receipt_id, product_id, quantity, import_price) VALUES ($receipt_id, $p_id, $qty, $price)";
            if (!$conn->query($sql_detail)) {
                throw new Exception("Lỗi thêm chi tiết sản phẩm.");
            }
            
            // Xử lý tính toán giá vốn khi phiếu đã "hoàn thành"
            if ($status == 'completed') {
                $res_prod = $conn->query("SELECT stock, import_price, profit_rate FROM products WHERE id = $p_id");
                if ($res_prod->num_rows > 0) {
                    $prod = $res_prod->fetch_assoc();
                    
                    $ton_hien_tai = (int)$prod['stock'];
                    $gia_von_hien_tai = (float)$prod['import_price'];
                    $ty_le_loi_nhuan = (float)$prod['profit_rate'];
                    
                    $tu_so = ($ton_hien_tai * $gia_von_hien_tai) + ($qty * $price);
                    $mau_so = $ton_hien_tai + $qty;
                    $gia_von_moi = $mau_so > 0 ? round($tu_so / $mau_so) : 0; 
                    $gia_ban_moi = round($gia_von_moi * (1 + $ty_le_loi_nhuan));
                    $ton_moi = $ton_hien_tai + $qty;
                    
                    $sql_update_prod = "UPDATE products SET import_price = $gia_von_moi, price = $gia_ban_moi, stock = $ton_moi WHERE id = $p_id";
                    if (!$conn->query($sql_update_prod)) {
                        throw new Exception("Lỗi cập nhật kho sản phẩm.");
                    }
                }
            }
        }
        
        $sql_update_receipt = "UPDATE import_receipts SET total_amount = $total_all WHERE id = $receipt_id";
        if (!$conn->query($sql_update_receipt)) {
            throw new Exception("Lỗi cập nhật tổng tiền.");
        }
        
        // HOÀN TẤT GIAO DỊCH (LƯU VÀO DB)
        mysqli_commit($conn);
        header("Location: import.php"); 
        exit();

    } catch (Exception $e) {
        // CÓ LỖI XẢY RA -> HỦY BỎ TOÀN BỘ CÁC BƯỚC ĐÃ LÀM Ở TRÊN
        mysqli_rollback($conn);
        echo "<script>alert('Lỗi hệ thống: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
        exit();
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