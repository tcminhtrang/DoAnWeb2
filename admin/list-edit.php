<?php
require_once '../config/database.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql_get = "SELECT * FROM import_receipts WHERE id = $id";
    $result = $conn->query($sql_get);
    if ($result->num_rows > 0) {
        $receipt = $result->fetch_assoc();
        if($receipt['status'] == 'completed') {
            echo "<script>alert('Phiếu nhập đã hoàn thành, không thể sửa đổi!'); window.location.href='import.php';</script>";
            exit();
        }
    } else {
        header("Location: import.php"); exit();
    }
} else { header("Location: import.php"); exit(); }

$sql_products = "SELECT id, product_name, product_code FROM products WHERE status = 'active'";
$result_products = $conn->query($sql_products);
$products_list = [];
while($p = $result_products->fetch_assoc()) {
    $products_list[] = $p;
}

$sql_details = "SELECT * FROM import_receipt_details WHERE receipt_id = $id";
$result_details = $conn->query($sql_details);
$current_details = [];
while($d = $result_details->fetch_assoc()) {
    $current_details[] = $d;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (!isset($_POST['product_id']) || empty(array_filter($_POST['product_id']))) {
        echo "<script>alert('Lỗi: Phiếu nhập trống hoặc sản phẩm không hợp lệ!'); window.location.href='import-edit.php?id=$id';</script>";
        exit();
    }

    $ngay_nhap = mysqli_real_escape_string($conn, $_POST['import_date']);
    $trang_thai = mysqli_real_escape_string($conn, $_POST['status']);
    
    $total_all = 0;
    $product_ids = $_POST['product_id'];
    $quantities = $_POST['quantity'];
    $prices = $_POST['import_price'];

    // BẮT ĐẦU TRANSACTION
    mysqli_begin_transaction($conn);

    try {
        if (!$conn->query("DELETE FROM import_receipt_details WHERE receipt_id = $id")) {
            throw new Exception("Lỗi xóa chi tiết cũ.");
        }

        for ($i = 0; $i < count($product_ids); $i++) {
            $p_id = (int)$product_ids[$i];
            $qty = (int)$quantities[$i];
            $price = (float)$prices[$i];

            if (empty($p_id) || $qty <= 0 || $price < 1000) continue;

            $subtotal = $qty * $price;
            $total_all += $subtotal;

            $sql_detail = "INSERT INTO import_receipt_details (receipt_id, product_id, quantity, import_price) VALUES ($id, $p_id, $qty, $price)";
            if (!$conn->query($sql_detail)) {
                throw new Exception("Lỗi thêm chi tiết mới.");
            }

            if ($trang_thai == 'completed' && $receipt['status'] == 'pending') {
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

        $sql_update_receipt = "UPDATE import_receipts SET import_date = '$ngay_nhap', status = '$trang_thai', total_amount = $total_all WHERE id = $id";
        if (!$conn->query($sql_update_receipt)) {
            throw new Exception("Lỗi cập nhật phiếu nhập.");
        }

        // HOÀN TẤT GIAO DỊCH
        mysqli_commit($conn);
        header("Location: import.php"); 
        exit();

    } catch (Exception $e) {
        // CÓ LỖI XẢY RA -> HỦY BỎ
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