<?php
require_once '../config/database.php';
$categories = $conn->query("SELECT * FROM categories WHERE status = 'active'");
$all_products_res = $conn->query("SELECT id, product_name, category_id FROM products WHERE status = 'active'");
$products_list = [];
while($row = $all_products_res->fetch_assoc()) {
    $products_list[] = $row;
}

$result_data = null;
$target_date = '';
$selected_cat = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_search'])) {
    $p_id = (int)$_POST['product_id'];
    $target_date = $conn->real_escape_string($_POST['date']);
    $selected_cat = (int)$_POST['category_id'];
    $sql = "SELECT 
            p.product_code, p.product_name, p.unit, c.category_name, p.stock as current_stock,
            (SELECT SUM(id.quantity) FROM import_receipt_details id JOIN import_receipts ir ON id.receipt_id = ir.id 
             WHERE id.product_id = p.id AND ir.import_date <= '$target_date' AND ir.status = 'completed') as total_in,
            (SELECT SUM(od.quantity) FROM order_details od JOIN orders o ON od.order_id = o.id 
             WHERE od.product_id = p.id AND DATE(o.order_date) <= '$target_date' AND o.status = 'delivered') as total_out
            FROM products p 
            JOIN categories c ON p.category_id = c.id
            WHERE p.id = '$p_id'";
    
    $res = $conn->query($sql);
    if ($res && $res->num_rows > 0) {
        $result_data = $res->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="../assets/images/logo-1.png" />
    <title>ChickenJoy Admin | Tra Cứu Tồn Kho</title>
    <link rel="stylesheet" href="../assets/css/admin.css" />
</head>

<body class="admin-body">
    <?php include 'layout/sidebar.php'; ?>

    <main class="main-content">
        <header class="main-header">
            <h1>Tra Cứu Tồn Kho Sản Phẩm</h1>
        </header>

        <a href="inventory.php" class="back-btn">
            <img src="../assets/images/icons/arrow-small-left.png" alt="quay lại"> Quay lại
        </a>

        <div class="inventory-container">
            <div class="search-section">
                <h2>Tra cứu tồn kho</h2>
                <form action="" method="POST" id="searchForm">
                    <div class="form-group">
                        <label for="product-type">Loại sản phẩm:</label>
                        <select id="product-type" name="category_id" onchange="filterProducts()">
                            <option value="">-- Tất cả loại --</option>
                            <?php 
                            $categories->data_seek(0);
                            while($cat = $categories->fetch_assoc()): 
                            ?>
                            <option value="<?php echo $cat['id']; ?>"
                                <?php if($selected_cat == $cat['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="product-name">Tên sản phẩm:</label>
                        <select id="product-name" name="product_id" required>
                            <option value="">-- Chọn sản phẩm --</option>
                            <?php foreach($products_list as $p): ?>
                            <option value="<?php echo $p['id']; ?>" data-category="<?php echo $p['category_id']; ?>"
                                <?php if(isset($_POST['product_id']) && $_POST['product_id'] == $p['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($p['product_name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="date">Tại thời điểm:</label>
                        <input type="date" id="date" name="date" required
                            value="<?php echo $target_date ? htmlspecialchars($target_date) : date('Y-m-d'); ?>" />
                    </div>

                    <button type="submit" name="btn_search" class="btn-primary">Tra cứu tồn kho</button>
                </form>
            </div>

            <div class="result-section">
                <h2>Kết quả tra cứu</h2>
                <div class="result-box">
                    <?php if ($result_data): 
                        $calc_stock = ($result_data['total_in'] ?? 0) - ($result_data['total_out'] ?? 0);
                    ?>
                    <div class="result-row"><span class="label">Mã SP:</span> <span
                            class="value"><?php echo htmlspecialchars($result_data['product_code']); ?></span></div>
                    <div class="result-row"><span class="label">Tên sản phẩm:</span> <span
                            class="value"><?php echo htmlspecialchars($result_data['product_name']); ?></span></div>
                    <div class="result-row"><span class="label">Loại sản phẩm:</span> <span
                            class="value"><?php echo htmlspecialchars($result_data['category_name']); ?></span></div>
                    <div class="result-row"><span class="label">Tồn kho tại ngày
                            <?php echo date('d/m/Y', strtotime($target_date)); ?>:</span>
                        <span class="value"><?php echo number_format($calc_stock); ?>
                            <?php echo htmlspecialchars($result_data['unit']); ?></span>
                    </div>
                    <div class="result-row">
                        <span class="label">Trạng thái:</span>
                        <?php 
        $threshold = 10; 
        
        if ($calc_stock <= 0) {
            echo '<span class="value status cancelled">Hết hàng</span>';
        } elseif ($calc_stock < $threshold) {
            echo '<span class="value status cancelled">Sắp hết hàng</span>';
        } else {
            echo '<span class="value status active">Còn hàng</span>';
        }
    ?>
                    </div>
                    <?php else: ?>
                    <p style="padding: 20px; text-align: center; color: #666;">Vui lòng chọn sản phẩm và ngày để tra
                        cứu.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
    function filterProducts() {
        var categoryId = document.getElementById('product-type').value;
        var productSelect = document.getElementById('product-name');
        var options = productSelect.options;
        productSelect.value = "";

        for (var i = 0; i < options.length; i++) {
            var opt = options[i];
            var productCat = opt.getAttribute('data-category');

            if (categoryId === "" || productCat === categoryId || opt.value === "") {
                opt.style.display = "block";
            } else {
                opt.style.display = "none";
            }
        }
    }
    window.onload = function() {
        if (document.getElementById('product-type').value !== "") {
            filterProducts();
            document.getElementById('product-name').value =
                "<?php echo isset($_POST['product_id']) ? htmlspecialchars($_POST['product_id']) : ''; ?>";
        }
    };
    </script>
</body>

</html>