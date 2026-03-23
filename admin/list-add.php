<?php
require_once '../config/database.php';
$sql_categories = "SELECT id, category_name FROM categories WHERE status = 'active'";
$result_categories = $conn->query($sql_categories);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ma_sp = $_POST['product-code'];
    $ten_sp = $_POST['product-name'];
    $loai_sp = $_POST['product-category'];
    $don_vi = $_POST['product-unit'];
    $mo_ta = $_POST['product-desc'];
    $hinh_anh = $_POST['product-image'];
    if(empty($hinh_anh)) $hinh_anh = 'default.jpg'; // Nếu không nhập hình thì dùng hình mặc định
    $trang_thai = $_POST['product-status'];

    // CÁC THÔNG SỐ KHỞI TẠO THEO BAREM
    $ton_dau = $_POST['product-stock'];
    $gia_von = $_POST['product-cost'];
    $loi_nhuan = $_POST['product-profit'] / 100; // Quy đổi 20 -> 0.20
    
    // TỰ ĐỘNG TÍNH GIÁ BÁN TỪ GIÁ VỐN VÀ LỢI NHUẬN
    $gia_ban = round($gia_von * (1 + $loi_nhuan));

    $cat_mapping = [1 => 'GaRan', 2 => 'Hamburger', 3 => 'Combo', 4 => 'MiY', 5 => 'Pizza', 6 => 'KhoaiTay', 7 => 'NuocUong', 8 => 'TrangMieng'];
    $category_text = isset($cat_mapping[$loai_sp]) ? $cat_mapping[$loai_sp] : 'Khac';
    
    $sql_check = "SELECT id FROM products WHERE product_code = '$ma_sp'";
    $result_check = $conn->query($sql_check);

    if ($result_check->num_rows > 0) {
        echo "<script>alert('Lỗi: Mã SP này đã tồn tại!'); window.history.back();</script>";
    } else {
        // Lệnh INSERT đầy đủ nhất để ăn trọn điểm
        $sql_insert = "INSERT INTO products (product_code, product_name, category_id, category, description, unit, stock, import_price, profit_rate, price, image, status) 
                       VALUES ('$ma_sp', '$ten_sp', '$loai_sp', '$category_text', '$mo_ta', '$don_vi', $ton_dau, $gia_von, $loi_nhuan, $gia_ban, '$hinh_anh', '$trang_thai')";
    
        if ($conn->query($sql_insert) === TRUE) {
            header("Location: list.php"); exit();
        } else {
            echo "<script>alert('Lỗi CSDL: Không thể thêm sản phẩm!');</script>";
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
      <a href="list.php" class="btn-primary" style="background: #6c757d;">Quay lại</a>
    </header>

    <section class="form-section">
      <form action="" method="POST">
        <div style="display:flex; gap: 15px;">
            <div class="form-group" style="flex: 1;"><label>Mã sản phẩm:</label><input type="text" name="product-code" required></div>
            <div class="form-group" style="flex: 2;"><label>Tên sản phẩm:</label><input type="text" name="product-name" required></div>
        </div>

        <div style="display:flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
                <label>Loại sản phẩm:</label>
                <select name="product-category" required>
                    <option value="">-- Chọn loại --</option>
                    <?php while($cat = $result_categories->fetch_assoc()) echo "<option value='" . $cat['id'] . "'>" . $cat['category_name'] . "</option>"; ?>
                </select>
            </div>
            <div class="form-group" style="flex: 1;"><label>Đơn vị tính:</label><input type="text" name="product-unit" value="Phần" required></div>
        </div>

        <div style="display:flex; gap: 15px; background: #fffaf5; padding: 15px; border-radius: 8px; border: 1px dashed #ff6b35; margin-bottom: 15px;">
            <div class="form-group" style="flex: 1;"><label>Số lượng tồn ban đầu:</label><input type="number" name="product-stock" value="0" required></div>
            <div class="form-group" style="flex: 1;"><label>Giá vốn (VNĐ):</label><input type="number" name="product-cost" value="0" required></div>
            <div class="form-group" style="flex: 1;"><label>Lợi nhuận mong muốn (%):</label><input type="number" name="product-profit" value="20" required></div>
        </div>

        <div style="display:flex; gap: 15px;">
            <div class="form-group" style="flex: 2;">
                <label>Hình ảnh (Tên file hoặc URL):</label>
                <input type="text" name="product-image" placeholder="Ví dụ: ga-ran.jpg (Để trống sẽ dùng ảnh mặc định)">
            </div>
            <div class="form-group" style="flex: 1;">
                <label>Trạng thái:</label>
                <select name="product-status">
                    <option value="active" selected>Hiển thị</option>
                    <option value="hidden">Ẩn</option>
                </select>
            </div>
        </div>

        <div class="form-group"><label>Mô tả chi tiết:</label><textarea name="product-desc" rows="3"></textarea></div>

        <div class="form-actions"><button type="submit" class="btn-primary">Lưu sản phẩm</button></div>
      </form>
    </section>
  </main>
</body>
</html>