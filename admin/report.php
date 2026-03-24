<?php
require_once '../config/database.php';

// 1. Xử lý bộ lọc thời gian (Mặc định là từ đầu tháng đến hiện tại)
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// 2. Truy vấn Tổng quan (Tổng doanh thu & Tổng đơn hoàn tất)
// Đã sửa lại thành total_price, order_date và status = 'delivered' theo đúng file SQL
$sql_summary = "SELECT COUNT(id) as total_orders, SUM(total_price) as total_revenue 
                FROM orders 
                WHERE status = 'delivered' 
                AND DATE(order_date) BETWEEN '$start_date' AND '$end_date'";
$res_summary = $conn->query($sql_summary);
$summary = $res_summary->fetch_assoc();

$total_orders = $summary['total_orders'] ?? 0;
$total_revenue = $summary['total_revenue'] ?? 0;
$sql_top_products = "SELECT p.product_name, SUM(od.quantity) as total_sold, SUM(od.price_at_purchase * od.quantity) as product_revenue 
                     FROM order_details od 
                     JOIN orders o ON od.order_id = o.id 
                     JOIN products p ON od.product_id = p.id 
                     WHERE o.status = 'delivered' 
                     AND DATE(o.order_date) BETWEEN '$start_date' AND '$end_date' 
                     GROUP BY p.id 
                     ORDER BY total_sold DESC 
                     LIMIT 5";
$res_top_products = $conn->query($sql_top_products);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/png" href="../assets/images/logo-1.png" />
  <title>ChickenJoy Admin | Báo cáo Doanh thu</title>
  <link rel="stylesheet" href="../assets/css/admin.css" />
  <style>
    .stat-cards { display: flex; gap: 20px; margin-bottom: 25px; }
    .stat-card { flex: 1; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-left: 5px solid #ff6b35; }
    .stat-card h3 { margin: 0 0 10px 0; color: #666; font-size: 16px; }
    .stat-card .value { font-size: 28px; font-weight: bold; color: #333; }
    .stat-card .revenue { color: #e74c3c; }
  </style>
</head>
<body class="admin-body">
  <?php include 'layout/sidebar.php'; ?>
  <main class="main-content">
    <header class="main-header">
      <h1>Báo cáo & Thống kê</h1>
    </header>

    <div class="table-toolbar" style="background: #fffaf5; padding: 15px; border: 1px dashed #ff6b35; border-radius: 8px;">
      <form action="report.php" method="GET" style="display: flex; align-items: center; gap: 15px;">
        <label style="font-weight: bold;">Từ ngày:</label>
        <input type="date" name="start_date" value="<?php echo $start_date; ?>" required style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
        
        <label style="font-weight: bold;">Đến ngày:</label>
        <input type="date" name="end_date" value="<?php echo $end_date; ?>" required style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
        
        <button type="submit" class="btn-primary" style="padding: 8px 20px;">Lọc Dữ Liệu</button>
      </form>
    </div>

    <div class="stat-cards">
        <div class="stat-card">
            <h3>Tổng số đơn hoàn tất</h3>
            <div class="value"><?php echo number_format($total_orders, 0, ',', '.'); ?> <span style="font-size: 16px; color: #888;">đơn</span></div>
        </div>
        <div class="stat-card">
            <h3>Tổng Doanh Thu</h3>
            <div class="value revenue"><?php echo number_format($total_revenue, 0, ',', '.'); ?>đ</div>
        </div>
    </div>

    
      <h2 style="margin-bottom: 15px; font-size: 18px; color: #333;"> Top 5 Sản Phẩm Bán Chạy Nhất</h2>
      <section class="table-section">
      <table class="data-table">
        
      <thead>
          <tr>
            <th style="width: 50px; text-align: center;">Top</th>
            <th>Tên sản phẩm</th>
            <th class="text-center">Số lượng đã bán</th>
            <th class="text-center">Doanh thu mang lại</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($res_top_products && $res_top_products->num_rows > 0) {
              $rank = 1;
              while($row = $res_top_products->fetch_assoc()) {
                  echo "<tr>";
                    echo "<td style='text-align: center; font-weight: bold; color: #ff6b35;'>#" . $rank++ . "</td>";
                    echo "<td><strong>" . $row['product_name'] . "</strong></td>";
                    echo "<td class='text-center'>" . number_format($row['total_sold'], 0, ',', '.') . "</td>";
                    echo "<td class='text-center' style='color: #28a745; font-weight: bold;'>" . number_format($row['product_revenue'], 0, ',', '.') . "đ</td>";
                  echo "</tr>";
              }
          } else {
              echo "<tr><td colspan='4' class='text-center'>Chưa có dữ liệu bán hàng trong khoảng thời gian này!</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </section>
  </main>
</body>
</html>