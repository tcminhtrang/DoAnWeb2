<?php
require_once '../config/connect.php';

// Lấy danh sách sản phẩm để đổ vào danh sách chọn (dropdown)
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
    
    // 1. Lưu thông tin chung vào bảng import_receipts
    $sql_receipt = "INSERT INTO import_receipts (receipt_code, import_date, status, total_amount) 
                    VALUES ('$receipt_code', '$import_date', '$status', 0)";
    
    if ($conn->query($sql_receipt) === TRUE) {
        $receipt_id = $conn->insert_id; // Lấy ID vừa tự động tạo
        $total_all = 0;

        // Kiểm tra xem có thêm sản phẩm nào không
        if(isset($_POST['product_id'])) {
            $product_ids = $_POST['product_id'];
            $quantities = $_POST['quantity'];
            $prices = $_POST['import_price'];

            for ($i = 0; $i < count($product_ids); $i++) {
                $p_id = $product_ids[$i];
                $qty = $quantities[$i]; // Số lượng nhập
                $price = $prices[$i];   // Giá nhập mới
                $subtotal = $qty * $price;
                $total_all += $subtotal;

                // Lưu vào bảng chi tiết
                $conn->query("INSERT INTO import_receipt_details (receipt_id, product_id, quantity, import_price) 
                              VALUES ($receipt_id, $p_id, $qty, $price)");

                // ==========================================
                // THUẬT TOÁN TÍNH GIÁ VỐN & CẬP NHẬT KHO
                // ==========================================
                $res_prod = $conn->query("SELECT stock, import_price, profit_rate FROM products WHERE id = $p_id");
                $prod = $res_prod->fetch_assoc();

                $ton_hien_tai = $prod['stock'];
                $gia_von_hien_tai = $prod['import_price'];
                $ty_le_loi_nhuan = $prod['profit_rate'];

                // Tính vốn bình quân
                $tu_so = ($ton_hien_tai * $gia_von_hien_tai) + ($qty * $price);
                $mau_so = $ton_hien_tai + $qty;
                $gia_von_moi = round($tu_so / $mau_so); 

                // Tính giá bán mới
                $gia_ban_moi = round($gia_von_moi * (1 + ($ty_le_loi_nhuan / 100)));
                $ton_moi = $ton_hien_tai + $qty;

                // Cập nhật lại Product
                $conn->query("UPDATE products 
                              SET import_price = $gia_von_moi, price = $gia_ban_moi, stock = $ton_moi 
                              WHERE id = $p_id");
            }
        }

        // Cập nhật lại tổng tiền thực tế
        $conn->query("UPDATE import_receipts SET total_amount = $total_all WHERE id = $receipt_id");

        header("Location: import.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/png" href="../assets/images/logo-1.png" />
  <title>ChickenJoy Admin | Thêm phiếu nhập</title>
  <link rel="stylesheet" href="../assets/css/admin.css" />
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
                    <option value="pending">Đang xử lý</option>
                    <option value="completed">Hoàn thành</option>
                </select>
            </div>
        </div>

        <table class="data-table" id="product-table">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th style="width: 15%;">Số lượng</th>
                    <th style="width: 25%;">Giá nhập (VNĐ)</th>
                    <th>Thành tiền</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <tr class="product-row">
                    <td>
                        <select name="product_id[]" required style="width: 100%; padding: 8px;">
                            <option value="">-- Chọn Sản Phẩm --</option>
                            <?php foreach($products_list as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= $p['product_code'] ?> - <?= $p['product_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input type="number" name="quantity[]" class="qty" min="1" value="1" style="width: 100%; padding: 8px;"></td>
                    <td><input type="number" name="import_price[]" class="price" min="0" value="0" style="width: 100%; padding: 8px;"></td>
                    <td class="subtotal" style="font-weight: bold;">0đ</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <div class="form-actions" style="margin-top: 25px; display: flex; justify-content: space-between;">
            <button type="button" id="add-row" class="btn-edit" style="background: #2ecc71; color: white; border: none;">+ Thêm dòng sản phẩm</button>
            <button type="submit" class="btn-primary">Lưu phiếu nhập</button>
        </div>
      </form>
    </section>
  </main>

  <script>
    document.getElementById('add-row').addEventListener('click', function() {
        const tbody = document.querySelector('#product-table tbody');
        const newRow = tbody.querySelector('.product-row').cloneNode(true);
        
        // Reset dữ liệu dòng mới
        newRow.querySelectorAll('input').forEach(input => input.value = input.classList.contains('qty') ? 1 : 0);
        newRow.querySelector('.subtotal').innerText = '0đ';
        newRow.querySelector('select').value = "";
        
        // Thêm nút xóa
        const actionTd = newRow.lastElementChild;
        actionTd.innerHTML = '<button type="button" class="btn-delete" onclick="this.parentElement.parentElement.remove()">Xóa</button>';
        
        tbody.appendChild(newRow);
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