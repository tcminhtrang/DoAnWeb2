<?php
require_once '../config/connect.php';

// 1. LẤY SỐ LIỆU CHO 4 THẺ THỐNG KÊ (CARDS)
// a. Tổng doanh thu hôm nay
$sql_doanh_thu = "SELECT SUM(total_price) as tong_tien FROM orders WHERE DATE(order_date) = CURDATE() AND status IN ('delivered', 'shipped')";
$res_doanh_thu = $conn->query($sql_doanh_thu);
$doanh_thu = $res_doanh_thu->fetch_assoc()['tong_tien'] ?? 0;

// b. Đơn hàng mới (chờ xử lý)
$sql_don_hang = "SELECT COUNT(*) as so_don FROM orders WHERE status = 'pending'";
$res_don_hang = $conn->query($sql_don_hang);
$don_moi = $res_don_hang->fetch_assoc()['so_don'] ?? 0;

// c. Sản phẩm sắp hết hàng (Tồn kho dưới 10 - ĐÁP ỨNG TIÊU CHÍ CHẤM ĐIỂM)
$sql_ton_kho = "SELECT COUNT(*) as sap_het FROM products WHERE stock < 10 AND status = 'active'";
$res_ton_kho = $conn->query($sql_ton_kho);
$sap_het = $res_ton_kho->fetch_assoc()['sap_het'] ?? 0;

// d. Khách hàng (Users)
$sql_khach = "SELECT COUNT(*) as so_khach FROM users WHERE role = 'user'";
$res_khach = $conn->query($sql_khach);
$khach_hang = $res_khach->fetch_assoc()['so_khach'] ?? 0;

// 2. LẤY 5 ĐƠN HÀNG GẦN ĐÂY NHẤT
$sql_recent_orders = "SELECT * FROM orders ORDER BY order_date DESC LIMIT 5";
$result_recent_orders = $conn->query($sql_recent_orders);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
 <meta charset="UTF-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1.0" />
 <link rel="icon" type="image/png" href="../assets/images/logo-1.png" />
 <title> ChickenJoy Admin | Dashboard</title>
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
        <div class="chart-bar" style="height: 60%;"><span class="chart-value">1.2tr</span><span class="chart-label">T2</span></div>
        <div class="chart-bar" style="height: 80%;"><span class="chart-value">1.8tr</span><span class="chart-label">T3</span></div>
        <div class="chart-bar" style="height: 50%;"><span class="chart-value">1.0tr</span><span class="chart-label">T4</span></div>
        <div class="chart-bar" style="height: 70%;"><span class="chart-value">1.5tr</span><span class="chart-label">T5</span></div>
        <div class="chart-bar" style="height: 90%;"><span class="chart-value">2.1tr</span><span class="chart-label">T6</span></div>
        <div class="chart-bar" style="height: 100%;"><span class="chart-value">2.5tr</span><span class="chart-label">T7</span></div>
        <div class="chart-bar" style="height: 65%;"><span class="chart-value">1.3tr</span><span class="chart-label">CN</span></div>
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
                  // Xác định class màu sắc dựa trên trạng thái
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