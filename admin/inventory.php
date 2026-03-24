<?php
require_once '../config/database.php';

// 1. Lấy từ khóa tìm kiếm và ngưỡng cảnh báo từ GET
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$low_stock_threshold = isset($_GET['threshold']) ? intval($_GET['threshold']) : 10;

// 2. Lấy danh sách sản phẩm sắp hết hàng (vẫn giữ nguyên lọc theo threshold)
$sql_low_stock = "SELECT product_name, stock, unit FROM products WHERE stock < $low_stock_threshold AND status = 'active'";
$low_stock_result = $conn->query($sql_low_stock);

// 3. Lấy danh sách hiển thị bảng (Có thêm điều kiện WHERE để tìm kiếm)
$sql_main = "SELECT p.product_code, p.product_name, c.category_name, p.stock, p.unit,
            (SELECT SUM(quantity) FROM import_receipt_details id JOIN import_receipts ir ON id.receipt_id = ir.id WHERE id.product_id = p.id AND ir.status = 'completed') as total_import,
            (SELECT SUM(quantity) FROM order_details od JOIN orders o ON od.order_id = o.id WHERE od.product_id = p.id AND o.status = 'delivered') as total_export
            FROM products p
            JOIN categories c ON p.category_id = c.id";

// Nếu có từ khóa tìm kiếm, thêm điều kiện lọc vào SQL
if (!empty($search)) {
    $sql_main .= " WHERE p.product_name LIKE '%$search%' OR p.product_code LIKE '%$search%'";
}

$main_result = $conn->query($sql_main);
?>

<!doctype html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="../assets/images/logo-1.png" />
    <title>ChickenJoy Admin | Quản Lý Tồn Kho</title>
    <link rel="stylesheet" href="../assets/css/admin.css" />
</head>

<body class="admin-body">
    <?php include 'layout/sidebar.php'; ?>
    <main class="main-content">
        <header class="main-header">
            <h1>Quản Lý Tồn Kho</h1>
        </header>

        <div class="low-stock-alert">
            <h3><img src="../assets/images/icons/triangle-warning.png" class="alert-icon" />
                Cảnh báo: Sản phẩm sắp hết hàng (tồn < <?php echo $low_stock_threshold; ?>) </h3>
                    <form class="threshold-form" method="GET">
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        Chỉ định số lượng được gọi là sắp hết:
                        <input type="number" name="threshold" value="<?php echo $low_stock_threshold; ?>">
                        <button type="submit" class="btn-update">Cập nhật dữ liệu</button>
                    </form>
                    <ul>
                        <?php while($row = $low_stock_result->fetch_assoc()): ?>
                        <li><?php echo $row['product_name']; ?>: còn <?php echo $row['stock'] . " " . $row['unit']; ?>
                        </li>
                        <?php endwhile; ?>
                    </ul>
        </div>

        <div class="table-toolbar">
            <form method="GET" style="display: flex; gap: 12px; width: 100%; align-items: center;">
                <input type="hidden" name="threshold" value="<?php echo $low_stock_threshold; ?>">

                <input type="text" name="search" placeholder="🔍 Tìm tên hoặc mã sản phẩm..."
                    value="<?php echo htmlspecialchars($search); ?>" style="flex: 1; max-width: 400px;">

                <button type="submit" class="btn-primary" style="background: #28a745;">Tìm kiếm</button>

                <a href="inventory-stock.php" class="btn-primary">Tra cứu tồn kho</a>
                <a href="inventory-movement.php" class="btn-primary">Tra cứu N-X-T</a>
            </form>
        </div>

        <section class="table-section">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã SP</th>
                        <th>Tên sản phẩm</th>
                        <th>Loại</th>
                        <th>Tổng Nhập</th>
                        <th>Tổng Xuất</th>
                        <th>Tồn hiện tại</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($main_result->num_rows > 0): ?>
                    <?php while($row = $main_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['product_code']; ?></td>
                        <td><?php echo $row['product_name']; ?></td>
                        <td><?php echo $row['category_name']; ?></td>
                        <td><?php echo $row['total_import'] ?? 0; ?></td>
                        <td><?php echo $row['total_export'] ?? 0; ?></td>
                        <td><?php echo $row['stock']; ?></td>
                        <td>
                            <?php if($row['stock'] <= 0): ?>
                            <span class="status cancelled">Hết hàng</span>
                            <?php elseif($row['stock'] < $low_stock_threshold): ?>
                            <span class="status cancelled">Sắp hết</span>
                            <?php else: ?>
                            <span class="status active">Còn hàng</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px;">Không tìm thấy sản phẩm nào khớp với
                            "<?php echo htmlspecialchars($search); ?>"</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>

</html>