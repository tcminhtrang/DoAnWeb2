<?php
require_once '../config/database.php';
$sql_products = "SELECT id, product_name, product_code FROM products WHERE status = 'active'";
$result_products = $conn->query($sql_products);
$products_list = [];
while($p = $result_products->fetch_assoc()) {
    $products_list[] = $p;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $receipt_code = $_POST['receipt_code'];
    $import_date = $_POST['import_date'];
    $status = $_POST['status'];
    
    $sql_receipt = "INSERT INTO import_receipts (receipt_code, import_date, status, total_amount) VALUES ('$receipt_code', '$import_date', '$status', 0)";
    
    if ($conn->query($sql_receipt) === TRUE) {
        $receipt_id = $conn->insert_id;
        $total_all = 0;
        if(isset($_POST['product_id'])) {
            $product_ids = $_POST['product_id'];
            $quantities = $_POST['quantity'];
            $prices = $_POST['import_price'];

            for ($i = 0; $i < count($product_ids); $i++) {
                $p_id = $product_ids[$i];
                $qty = $quantities[$i];
                $price = $prices[$i];
                $subtotal = $qty * $price;
                $total_all += $subtotal;
                $conn->query("INSERT INTO import_receipt_details (receipt_id, product_id, quantity, import_price) VALUES ($receipt_id, $p_id, $qty, $price)");
                
                // CHỈ TÍNH TOÁN NẾU TẠO PHIẾU HOÀN THÀNH LUÔN
                if ($status == 'completed') {
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
        }
        $conn->query("UPDATE import_receipts SET total_amount = $total_all WHERE id = $receipt_id");
        header("Location: import.php"); exit();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <title>ChickenJoy Admin | Thêm phiếu nhập</title>
  <link rel="stylesheet" href="../assets/css/admin.css" />
  
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <style>
      .select2-container .select2-selection--single { height: 38px; padding-top: 5px; border-radius: 8px; border: 1px solid #ccc; }
  </style>
</head>
<body class="admin-body">
  <?php include 'layout/sidebar.php'; ?>
  <main class="main-content">
    <header class="main-header">
      <h1>Tạo phiếu nhập hàng mới</h1>
      <a href="import.php" class="btn-primary" style="background: #6c757d;">Quay lại</a>
    </header>
    <section class="form-section">
      <form action="" method="POST" id="import-form">
        <div style="display: flex; gap: 20px; margin-bottom: 20px;">
            <div class="form-group" style="flex: 1;">
                <label>Mã phiếu:</label>
                <input type="text" name="receipt_code" value="PN<?php echo time(); ?>" readonly style="background: #eee;">
            </div>
            <div class="form-group" style="flex: 1;">
                <label>Ngày nhập:</label>
                <input type="date" name="import_date" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label>Trạng thái:</label>
                <select name="status">
                    <option value="pending">Đang xử lý (Lưu nháp)</option>
                    <option value="completed">Hoàn thành (Cộng kho luôn)</option>
                </select>
            </div>
        </div>

        <table class="data-table" id="product-table">
            <thead>
                <tr>
                    <th>Sản phẩm (Gõ để tìm kiếm)</th>
                    <th style="width: 15%;">Số lượng</th>
                    <th style="width: 25%;">Giá nhập (VNĐ)</th>
                    <th>Thành tiền</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <tr class="product-row">
                    <td>
                        <select name="product_id[]" class="select-search" required style="width: 100%;">
                            <option value="">-- Chọn Sản Phẩm --</option>
                            <?php foreach($products_list as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= $p['product_code'] ?> - <?= $p['product_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input type="number" name="quantity[]" class="qty" min="1" value="1" style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #ccc;"></td>
                    <td><input type="number" name="import_price[]" class="price" min="0" value="0" style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #ccc;"></td>
                    <td class="subtotal" style="font-weight: bold;">0đ</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        <div class="form-actions" style="margin-top: 25px; display: flex; justify-content: space-between;">
            <button type="button" id="add-row" class="btn-primary">+ Thêm dòng sản phẩm</button>
            <button type="submit" class="btn-primary">Lưu phiếu nhập</button>
        </div>
      </form>
    </section>
  </main>

  <script>
    $(document).ready(function() {
        $('.select-search').select2();
    });

    document.getElementById('add-row').addEventListener('click', function() {
        $('.select-search').select2('destroy');
        
        const tbody = document.querySelector('#product-table tbody');
        const newRow = tbody.querySelector('.product-row').cloneNode(true);
        newRow.querySelectorAll('input').forEach(input => input.value = input.classList.contains('qty') ? 1 : 0);
        newRow.querySelector('.subtotal').innerText = '0đ';
        newRow.querySelector('select').value = "";
        
        const actionTd = newRow.lastElementChild;
        actionTd.innerHTML = '<button type="button" class="btn-action-gray" onclick="this.parentElement.parentElement.remove()">Xóa</button>';
        
        tbody.appendChild(newRow);
        
        $('.select-search').select2();
    });

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('qty') || e.target.classList.contains('price')) {
            const row = e.target.closest('tr');
            const qty = parseFloat(row.querySelector('.qty').value) || 0;
            const price = parseFloat(row.querySelector('.price').value) || 0;
            const subtotal = qty * price;
            row.querySelector('.subtotal').innerText = new Intl.NumberFormat('vi-VN').format(subtotal) + 'đ';
        }
    });
  </script>
</body>
</html>