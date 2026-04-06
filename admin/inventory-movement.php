<?php
require_once '../config/database.php';

$all_products = $conn->query("SELECT id, product_name FROM products WHERE status = 'active'");
$movement_result = null;
$f_date = $t_date = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_movement'])) {
    $p_id = (int)$_POST['product_id']; 
    $f_date = $conn->real_escape_string($_POST['from_date']);
    $t_date = $conn->real_escape_string($_POST['to_date']);

    $sql = "SELECT 
            p.product_code, p.product_name, p.unit, c.category_name,
            -- Tồn đầu kỳ: Nhập - Xuất trước ngày 'Từ ngày'
            ((SELECT IFNULL(SUM(quantity),0) FROM import_receipt_details id JOIN import_receipts ir ON id.receipt_id = ir.id WHERE id.product_id = p.id AND ir.import_date < '$f_date' AND ir.status = 'completed') - 
             (SELECT IFNULL(SUM(quantity),0) FROM order_details od JOIN orders o ON od.order_id = o.id WHERE od.product_id = p.id AND DATE(o.order_date) < '$f_date' AND o.status = 'delivered')) as opening_stock,
            -- Nhập trong kỳ
            (SELECT IFNULL(SUM(quantity),0) FROM import_receipt_details id JOIN import_receipts ir ON id.receipt_id = ir.id WHERE id.product_id = p.id AND ir.import_date BETWEEN '$f_date' AND '$t_date' AND ir.status = 'completed') as period_import,
            -- Xuất trong kỳ
            (SELECT IFNULL(SUM(quantity),0) FROM order_details od JOIN orders o ON od.order_id = o.id WHERE od.product_id = p.id AND DATE(o.order_date) BETWEEN '$f_date' AND '$t_date' AND o.status = 'delivered') as period_export
            FROM products p 
            JOIN categories c ON p.category_id = c.id
            WHERE p.id = '$p_id'";

    $res = $conn->query($sql);
    if ($res) $movement_result = $res->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="../assets/images/logo-1.png" />
    <title>ChickenJoy Admin | Tra Cứu N-X-T</title>
    <link rel="stylesheet" href="../assets/css/admin.css" />
</head>

<body class="admin-body">
    <?php include 'layout/sidebar.php'; ?>

    <main class="main-content">
        <header class="main-header">
            <h1>Quản Lý Nhập - Xuất - Tồn Kho</h1>
        </header>
        <a href="inventory.php" class="back-btn"><img src="../assets/images/icons/arrow-small-left.png" alt="quay lại">
            Quay lại</a>

        <div class="inventory-container">
            <div class="search-section">
                <h2>Tra cứu nhập - xuất - tồn</h2>
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="product-name">Tên sản phẩm:</label>
                        <select id="product-name" name="product_id" required>
                            <option value="">-- Chọn sản phẩm --</option>
                            <?php while($p = $all_products->fetch_assoc()): ?>
                            <option value="<?php echo $p['id']; ?>"
                                <?php if(isset($_POST['product_id']) && $_POST['product_id'] == $p['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($p['product_name']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="from-date">Từ ngày:</label>
                        <input type="date" id="from-date" name="from_date" required value="<?php echo htmlspecialchars($f_date); ?>" />
                    </div>
                    <div class="form-group">
                        <label for="to-date">Đến ngày:</label>
                        <input type="date" id="to-date" name="to_date" required value="<?php echo htmlspecialchars($t_date); ?>" />
                    </div>
                    <button type="submit" name="btn_movement" class="btn-primary">Tra cứu N-X-T</button>
                </form>
            </div>

            <div class="result-section">
                <h2>Kết quả tra cứu</h2>
                <div class="result-box">
                    <?php if ($movement_result): 
                        $closing_stock = $movement_result['opening_stock'] + $movement_result['period_import'] - $movement_result['period_export'];
                    ?>
                    <div class="result-row"><span class="label">Mã SP:</span> <span
                            class="value"><?php echo htmlspecialchars($movement_result['product_code']); ?></span></div>
                    <div class="result-row"><span class="label">Tên sản phẩm:</span> <span
                            class="value"><?php echo htmlspecialchars($movement_result['product_name']); ?></span></div>
                    <div class="result-row"><span class="label">Tồn đầu kỳ:</span> <span
                            class="value"><?php echo number_format($movement_result['opening_stock']); ?>
                            <?php echo htmlspecialchars($movement_result['unit']); ?></span></div>
                    <div class="result-row"><span class="label">Nhập:</span> <span
                            class="value"><?php echo number_format($movement_result['period_import']); ?></span></div>
                    <div class="result-row"><span class="label">Xuất:</span> <span
                            class="value"><?php echo number_format($movement_result['period_export']); ?></span></div>
                    <div class="result-row"><span class="label">Tồn cuối kỳ:</span> <span
                            class="value"><?php echo number_format($closing_stock); ?></span></div>
                    <div class="result-row">
                        <span class="label">Trạng thái:</span>
                        <span class="value">
                            <?php 
            if ($closing_stock <= 0) {
                echo '<span class="status cancelled">Hết hàng</span>';
            } elseif ($closing_stock < 10) {
                echo '<span class="status cancelled">Sắp hết hàng</span>';
            } else {
                echo '<span class="status active">Còn hàng</span>';
            }
        ?>
                        </span>
                    </div>
                    <?php else: ?>
                    <p style="padding: 20px; text-align: center; color: #666;">Vui lòng chọn sản phẩm và khoảng thời
                        gian.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</body>

</html>