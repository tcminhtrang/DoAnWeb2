<?php
require_once 'check_admin.php';
require_once '../config/database.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $stmt_get = $conn->prepare("SELECT * FROM import_receipts WHERE id = ?");
    $stmt_get->bind_param("i", $id);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    
    if ($result->num_rows > 0) {
        $receipt = $result->fetch_assoc();
        if($receipt['status'] == 'completed') {
            echo "<script>alert('Phiếu nhập đã hoàn thành, không thể sửa đổi!'); window.location.href='import.php';</script>";
            exit();
        }
    } else {
        header("Location: import.php"); exit();
    }
    $stmt_get->close();
} else { 
    header("Location: import.php"); exit(); 
}

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

    $ngay_nhap = $_POST['import_date'];
    $trang_thai = $_POST['status'];
    
    $total_all = 0;
    $product_ids = $_POST['product_id'];
    $quantities = $_POST['quantity'];
    $prices = $_POST['import_price'];
    mysqli_begin_transaction($conn);

    try {
        $stmt_del = $conn->prepare("DELETE FROM import_receipt_details WHERE receipt_id = ?");
        $stmt_del->bind_param("i", $id);
        $stmt_del->execute();
        $stmt_insert_detail = $conn->prepare("INSERT INTO import_receipt_details (receipt_id, product_id, quantity, import_price) VALUES (?, ?, ?, ?)");
        $stmt_update_prod = $conn->prepare("UPDATE products SET import_price = ?, price = ?, stock = ? WHERE id = ?");

        for ($i = 0; $i < count($product_ids); $i++) {
            $p_id = (int)$product_ids[$i];
            $qty = (int)$quantities[$i];
            $price = (float)$prices[$i];

            if (empty($p_id) || $qty <= 0 || $price < 1000) continue;

            $subtotal = $qty * $price;
            $total_all += $subtotal;
            $stmt_insert_detail->bind_param("iiid", $id, $p_id, $qty, $price);
            $stmt_insert_detail->execute();

            if ($trang_thai == 'completed' && $receipt['status'] == 'pending') {
                $res_prod = $conn->query("SELECT stock, import_price, profit_rate FROM products WHERE id = $p_id FOR UPDATE");
                $prod = $res_prod->fetch_assoc();

                $ton_hien_tai = $prod['stock'];
                $gia_von_hien_tai = $prod['import_price'];
                $ty_le_loi_nhuan = $prod['profit_rate']; 
                
                $tu_so = ($ton_hien_tai * $gia_von_hien_tai) + ($qty * $price);
                $mau_so = $ton_hien_tai + $qty;
                $gia_von_moi = $mau_so > 0 ? round($tu_so / $mau_so) : 0; 
                $gia_ban_moi = round($gia_von_moi * (1 + $ty_le_loi_nhuan));
                $ton_moi = $ton_hien_tai + $qty;
                $stmt_update_prod->bind_param("ddii", $gia_von_moi, $gia_ban_moi, $ton_moi, $p_id);
                $stmt_update_prod->execute();
            }
        }
        $stmt_update_receipt = $conn->prepare("UPDATE import_receipts SET import_date = ?, status = ?, total_amount = ? WHERE id = ?");
        $stmt_update_receipt->bind_param("ssdi", $ngay_nhap, $trang_thai, $total_all, $id);
        $stmt_update_receipt->execute();
        mysqli_commit($conn);
        header("Location: import.php"); 
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<script>alert('Lỗi hệ thống: Không thể lưu cập nhật phiếu nhập!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <title>ChickenJoy Admin | Chỉnh sửa phiếu nhập</title>
  <link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body class="admin-body">
  <?php include 'layout/sidebar.php'; ?>
  <main class="main-content">
    <header class="main-header">
      <h1>Chỉnh sửa phiếu nhập hàng</h1>
      <a href="import.php" class="btn-primary">Quay lại</a>
    </header>
    <section class="form-section">
      <form action="" method="POST" id="import-form" novalidate>
        <div style="display: flex; gap: 20px; margin-bottom: 20px;">
            <div class="form-group" style="flex: 1;">
                <label>Mã phiếu:</label>
                <input type="text" value="<?php echo $receipt['receipt_code']; ?>" readonly style="background: #eee;">
            </div>
            <div class="form-group" style="flex: 1;">
                <label>Ngày nhập:</label>
                <input type="date" name="import_date" value="<?php echo date('Y-m-d', strtotime($receipt['import_date'])); ?>" required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label>Trạng thái:</label>
                <select name="status">
                    <option value="pending" selected>Đang xử lý</option>
                    <option value="completed">Hoàn thành</option>
                </select>
            </div>
        </div>

        <datalist id="product_list">
            <?php foreach($products_list as $p): ?>
                <option data-id="<?= $p['id'] ?>" value="<?= $p['product_code'] ?> - <?= $p['product_name'] ?>"></option>
            <?php endforeach; ?>
        </datalist>

        <table class="data-table" id="product-table">
            <thead>
                <tr>
                    <th style="width: 35%;">Sản phẩm</th>
                    <th style="width: 15%;">Số lượng</th>
                    <th style="width: 20%;">Giá nhập (VNĐ)</th>
                    <th>Thành tiền</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if(count($current_details) == 0): 
                ?>
                <tr class="product-row">
                    <td>
                        <input type="text" list="product_list" class="combo-input" placeholder="Gõ tên hoặc mã SP..." onchange="updateProductId(this)" required>
                        <input type="hidden" name="product_id[]" class="hidden-id" required>
                    </td>
                    <td><input type="number" name="quantity[]" class="qty" min="0" value="1" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc; outline: none;" required></td>
                    <td><input type="number" name="import_price[]" class="price" min="0" step="100" value="1000" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc; outline: none;" required></td>
                    <td class="subtotal" style="font-weight: bold;">1.000đ</td>
                    <td><button type="button" class="btn-gray" onclick="removeRow(this)">Xóa</button></td>
                </tr>
                <?php 
                else: 
                    foreach($current_details as $detail): 
                        $current_text = "";
                        foreach($products_list as $p) {
                            if ($p['id'] == $detail['product_id']) {
                                $current_text = $p['product_code'] . " - " . $p['product_name'];
                                break;
                            }
                        }
                ?>
                <tr class="product-row">
                    <td>
                        <input type="text" list="product_list" class="combo-input" placeholder="Gõ tên hoặc mã SP..." value="<?= $current_text ?>" onchange="updateProductId(this)" required>
                        <input type="hidden" name="product_id[]" class="hidden-id" value="<?= $detail['product_id'] ?>" required>
                    </td>
                    <td><input type="number" name="quantity[]" class="qty" min="0" value="<?= $detail['quantity'] ?>" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc; outline: none;" required></td>
                    <td><input type="number" name="import_price[]" class="price" min="0" step="100" value="<?= $detail['import_price'] ?>" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc; outline: none;" required></td>
                    <td class="subtotal" style="font-weight: bold;"><?= number_format($detail['quantity'] * $detail['import_price'], 0, ',', '.') ?>đ</td>
                    <td><button type="button" class="btn-gray" onclick="removeRow(this)">Xóa</button></td>
                </tr>
                <?php 
                    endforeach; 
                endif; 
                ?>
            </tbody>
        </table>
        
        <div class="form-actions" style="margin-top: 25px; display: flex; justify-content: space-between;">
            <button type="button" id="add-row" class="btn-primary">+ Thêm dòng sản phẩm</button>
            <button type="submit" class="btn-primary">Lưu thay đổi</button>
        </div>
      </form>
    </section>
  </main>

  <script>
    function updateProductId(inputElement) {
        inputElement.style.borderColor = "#ccc";
        const datalist = document.getElementById('product_list');
        const options = datalist.options;
        const hiddenInput = inputElement.nextElementSibling;
        let foundId = "";
        
        if (inputElement.value.trim() === "") {
            hiddenInput.value = ""; return;
        }

        for (let i = 0; i < options.length; i++) {
            if (options[i].value === inputElement.value) {
                foundId = options[i].getAttribute('data-id');
                break;
            }
        }
        
        if (foundId !== "") {
            hiddenInput.value = foundId;
        } else {
            inputElement.style.borderColor = "red";
            inputElement.value = "";
            hiddenInput.value = "";
            inputElement.focus();
        }
    }

    function removeRow(btn) {
        const tbody = document.querySelector('#product-table tbody');
        if (tbody.querySelectorAll('.product-row').length <= 1) {
            alert("Bạn đã xóa hết sản phẩm. Hệ thống sẽ tự động hủy phiếu và quay về danh sách!");
            window.location.href = "import.php";
        } else {
            btn.closest('tr').remove();
        }
    }

    document.getElementById('add-row').addEventListener('click', function() {
        const tbody = document.querySelector('#product-table tbody');
        const newRow = tbody.querySelector('.product-row').cloneNode(true);
        
        newRow.querySelectorAll('input').forEach(input => {
            input.style.borderColor = "#ccc"; 
            if(input.classList.contains('qty')) {
                input.value = 1;
            } else if(input.classList.contains('price')) {
                input.value = 1000;
            }
        });
        
        newRow.querySelector('.combo-input').value = "";
        newRow.querySelector('.hidden-id').value = "";
        newRow.querySelector('.subtotal').innerText = '1.000đ';
        
        tbody.appendChild(newRow);
    });

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('price')) {
            let val = parseFloat(e.target.value);
            if (val < 1000 || isNaN(val)) {
                e.target.style.borderColor = "red";
            }
        }
        if (e.target.classList.contains('qty')) {
            let val = parseInt(e.target.value);
            if (val <= 0 || isNaN(val)) {
                e.target.style.borderColor = "red";
            }
        }
    });

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('qty') || e.target.classList.contains('price') || e.target.classList.contains('combo-input')) {
            e.target.style.borderColor = "#ccc"; 
            
            if (e.target.classList.contains('qty') || e.target.classList.contains('price')) {
                const row = e.target.closest('tr');
                const qty = parseFloat(row.querySelector('.qty').value) || 0;
                const price = parseFloat(row.querySelector('.price').value) || 0;
                const subtotal = qty * price;
                row.querySelector('.subtotal').innerText = new Intl.NumberFormat('vi-VN').format(subtotal) + 'đ';
            }
        }
    });

    document.getElementById('import-form').addEventListener('submit', function(e) {
        let hasError = false;
        let priceError = false;

        const productIds = document.querySelectorAll('.hidden-id');
        const prices = document.querySelectorAll('.price');
        const quantities = document.querySelectorAll('.qty');

        document.querySelectorAll('.combo-input, .price, .qty').forEach(el => el.style.borderColor = "#ccc");

        for (let i = 0; i < productIds.length; i++) {
            if (!productIds[i].value) {
                hasError = true;
                productIds[i].previousElementSibling.style.borderColor = "red";
            }
            if (parseFloat(prices[i].value) < 1000 || isNaN(parseFloat(prices[i].value))) {
                hasError = true;
                priceError = true;
                prices[i].style.borderColor = "red";
            }
            if (parseInt(quantities[i].value) <= 0 || isNaN(parseInt(quantities[i].value))) {
                hasError = true;
                quantities[i].style.borderColor = "red";
            }
        }

        if (hasError) {
            e.preventDefault();
            if (priceError) {
                alert("Vui lòng kiểm tra lại: Giá nhập hàng tối thiểu phải từ 1.000 VNĐ!");
            }
        }
    });
  </script>
</body>
</html>