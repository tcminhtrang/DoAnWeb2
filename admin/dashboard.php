<?php
require_once '../config/database.php';

$sql_doanh_thu = "SELECT SUM(total_price) as tong_tien FROM orders WHERE DATE(order_date) = CURDATE() AND status IN ('delivered', 'shipped')";
$res_doanh_thu = $conn->query($sql_doanh_thu);
$doanh_thu = $res_doanh_thu->fetch_assoc()['tong_tien'] ?? 0;

$sql_don_hang = "SELECT COUNT(*) as so_don FROM orders WHERE status = 'pending'";
$res_don_hang = $conn->query($sql_don_hang);
$don_moi = $res_don_hang->fetch_assoc()['so_don'] ?? 0;

$sql_ton_kho = "SELECT COUNT(*) as sap_het FROM products WHERE stock < 10 AND status = 'active'";
$res_ton_kho = $conn->query($sql_ton_kho);
$sap_het = $res_ton_kho->fetch_assoc()['sap_het'] ?? 0;

$sql_khach = "SELECT COUNT(*) as so_khach FROM users WHERE role = 'user'";
$res_khach = $conn->query($sql_khach);
$khach_hang = $res_khach->fetch_assoc()['so_khach'] ?? 0;

$sql_recent_orders = "SELECT * FROM orders ORDER BY order_date DESC LIMIT 5";
$result_recent_orders = $conn->query($sql_recent_orders);
$revenue_7_days = [];
$max_revenue = 0;
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $day_of_week = date('N', strtotime("-$i days"));
    $labels = [1 => 'T2', 2 => 'T3', 3 => 'T4', 4 => 'T5', 5 => 'T6', 6 => 'T7', 7 => 'CN'];
    $label = $labels[$day_of_week];
    $revenue_7_days[$date] = [
        'label' => $label,
        'total' => 0
    ];
}

$start_date = date('Y-m-d', strtotime("-6 days"));
$end_date = date('Y-m-d');
$sql_chart = "SELECT DATE(order_date) as order_day, SUM(total_price) as daily_total 
              FROM orders 
              WHERE DATE(order_date) BETWEEN '$start_date' AND '$end_date' 
              AND status IN ('delivered', 'shipped')
              GROUP BY DATE(order_date)";
$res_chart = $conn->query($sql_chart);

if ($res_chart && $res_chart->num_rows > 0) {
    while($row = $res_chart->fetch_assoc()) {
        $day = $row['order_day'];
        if (isset($revenue_7_days[$day])) {
            $revenue_7_days[$day]['total'] = $row['daily_total'];
            if ($row['daily_total'] > $max_revenue) {
                $max_revenue = $row['daily_total'];
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
 <meta charset="UTF-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1.0" />
 <link rel="icon" type="image/png" href="../assets/images/logo-1.png" />
 <title>ChickenJoy Admin | Dashboard</title>
 <link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body class="admin-body">
  <?php include 'layout/sidebar.php'; ?>
  
  <main class="main-content">
  <header class="main-header">
   <h1>Trang quản trị</h1>
   <div class="user-section">
    <span>Xin chào, <strong>Admin</strong></span>
    <button class="user-btn">
     <img src="../assets/images/icons/profile.png" alt="User Icon">
    </button>
    <div class="user-dropdown">
     <a href="../index.html">
      <span>Đăng xuất</span>
     </a>
    </div>
   </div>
  </header>

  <section class="dashboard">
   <div class="card">
    <h3>Doanh thu hôm nay</h3>
    <p style="font-size: 20px;"><strong><?php echo number_format($doanh_thu, 0, ',', '.'); ?>đ</strong></p>
   </div>
   <div class="card">
    <h3>Đơn hàng mới</h3>
    <p style="font-size: 20px;"><strong><?php echo $don_moi; ?></strong> (chờ xử lý)</p>
   </div>
   <div class="card">
    <h3>Sắp hết hàng</h3>
    <p style="font-size: 20px; color: red;"><strong><?php echo $sap_het; ?></strong> (cần nhập)</p>
   </div>
   <div class="card">
    <h3>Tổng khách hàng</h3>
    <p style="font-size: 20px;"><strong><?php echo $khach_hang; ?></strong> (thành viên)</p>
   </div>
  </section>

  <h2 class="section-title">Doanh thu 7 ngày qua</h2>
  <section class="table-section" style="margin-top: 25px; margin-bottom: 15px;">
      <div class="chart-container">
        <?php foreach ($revenue_7_days as $date => $data): 
            $height_percent = ($max_revenue > 0) ? round(($data['total'] / $max_revenue) * 100) : 0;
            $display_height = $height_percent > 0 ? $height_percent : 2; 
            $val = $data['total'];
            if ($val >= 1000000) {
                $display_val = round($val / 1000000, 1) . 'tr';
            } elseif ($val >= 1000) {
                $display_val = round($val / 1000) . 'k';
            } else {
                $display_val = '0đ';
            }
        ?>
            <div class="chart-bar" style="height: <?php echo $display_height; ?>%;" title="<?php echo date('d/m/Y', strtotime($date)); ?>: <?php echo number_format($val, 0, ',', '.'); ?>đ">
                <span class="chart-value"><?php echo $display_val; ?></span>
                <span class="chart-label"><?php echo $data['label']; ?></span>
            </div>
        <?php endforeach; ?>
      </div>
  </section>

  <h2 class="section-title">Đơn hàng gần đây</h2>
  <section class="table-section"> 
      <table class="data-table">
        <thead>
          <tr>
            <th>Mã đơn</th>
            <th>Khách hàng</th>
            <th>Ngày đặt</th>
            <th>Tổng tiền</th>
            <th>Trạng thái</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($result_recent_orders && $result_recent_orders->num_rows > 0) {
              while($row = $result_recent_orders->fetch_assoc()) {
                  $status_class = '';
                  $status_text = '';
                  switch($row['status']) {
                      case 'pending': $status_class = 'new'; $status_text = 'Mới đặt'; break;
                      case 'processing': $status_class = 'processing'; $status_text = 'Đang xử lý'; break;
                      case 'shipped': $status_class = 'shipped'; $status_text = 'Đang giao'; break;
                      case 'delivered': $status_class = 'delivered'; $status_text = 'Đã giao'; break;
                      case 'cancelled': $status_class = 'cancelled'; $status_text = 'Đã hủy'; break;
                  }

                  echo "<tr>";
                    echo "<td>DH" . str_pad($row['id'], 3, '0', STR_PAD_LEFT) . "</td>";
                    echo "<td>" . $row['receiver_name'] . "</td>";
                    echo "<td>" . date('d/m/Y H:i', strtotime($row['order_date'])) . "</td>";
                    echo "<td>" . number_format($row['total_price'], 0, ',', '.') . "đ</td>";
                    echo "<td><span class='status " . $status_class . "'>" . $status_text . "</span></td>";
                  echo "</tr>";
              }
          } else {
              echo "<tr><td colspan='5' style='text-align: center;'>Chưa có đơn hàng nào!</td></tr>";
          }
          ?>
        </tbody>
      </table>
  </section>
 </main>
</body>
</html>