<?php
require_once '../config/database.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ngay_nhap = $_POST['import_date'];
    $trang_thai = $_POST['status'];
    if ($trang_thai == 'completed' && $receipt['status'] == 'pending') {
        $sql_details = "SELECT product_id, quantity, import_price FROM import_receipt_details WHERE receipt_id = $id";
        $res_details = $conn->query($sql_details);
        
        while ($d = $res_details->fetch_assoc()) {
            $p_id = $d['product_id'];
            $qty = $d['quantity'];
            $price = $d['import_price'];

            $res_prod = $conn->query("SELECT stock, import_price, profit_rate FROM products WHERE id = $p_id");
            $prod = $res_prod->fetch_assoc();

            $ton_hien_tai = $prod['stock'];
            $gia_von_hien_tai = $prod['import_price'];
            $ty_le_loi_nhuan = $prod['profit_rate']; 

            $tu_so = ($ton_hien_tai * $gia_von_hien_tai) + ($qty * $price);
            $mau_so = $ton_hien_tai + $qty;
            $gia_von_moi = round($tu_so / $mau_so); 
            $gia_ban_moi = round($gia_von_moi * (1 + $ty_le_loi_nhuan));
            $ton_moi = $ton_hien_tai + $qty;

            $conn->query("UPDATE products SET import_price = $gia_von_moi, price = $gia_ban_moi, stock = $ton_moi WHERE id = $p_id");
        }
    }

    $sql_update = "UPDATE import_receipts SET import_date = '$ngay_nhap', status = '$trang_thai' WHERE id = $id";
    if ($conn->query($sql_update) === TRUE) {
        header("Location: import.php"); exit();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <title>ChickenJoy Admin | Chốt phiếu nhập</title>
  <link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body class="admin-body">
  <?php include 'layout/sidebar.php'; ?>
  <main class="main-content">
    <header class="main-header">
      <h1>Cập nhật trạng thái phiếu nhập</h1>
      <a href="import.php" class="btn-primary" style="background: #6c757d;">Quay lại</a>
    </header>
    <section class="form-section">
      <form action="" method="POST">
        <div class="form-group"><label>Mã phiếu nhập:</label>
          <input type="text" value="<?php echo $receipt['receipt_code']; ?>" readonly style="background: #eee;">
        </div>
        <div class="form-group"><label>Tổng tiền (VNĐ):</label>
          <input type="text" value="<?php echo number_format($receipt['total_amount'], 0, ',', '.'); ?>đ" readonly style="background: #eee; font-weight: bold; color: #e74c3c;">
        </div>
        <div class="form-group"><label>Ngày nhập:</label>
          <input type="date" name="import_date" value="<?php echo date('Y-m-d', strtotime($receipt['import_date'])); ?>" required>
        </div>
        <div class="form-group"><label>Trạng thái:</label>
          <select name="status">
            <option value="pending" selected>Đang xử lý</option>
            <option value="completed">Hoàn thành (Chốt kho)</option>
          </select>
        </div>
        <div class="form-actions" style="margin-top: 20px;">
          <button type="submit" class="btn-primary">Lưu thay đổi</button>
        </div>
      </form>
    </section>
  </main>
</body>
</html>