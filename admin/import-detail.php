<?php
require_once '../config/database.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql_receipt = "SELECT * FROM import_receipts WHERE id = $id";
    $result_receipt = $conn->query($sql_receipt);

    if ($result_receipt->num_rows > 0) {
        $receipt = $result_receipt->fetch_assoc();
    } else {
        echo "<script>alert('Không tìm thấy phiếu nhập!'); window.location.href='import.php';</script>";
        exit();
    }
    
    $sql_details = "SELECT d.*, p.product_code, p.product_name 
                    FROM import_receipt_details d
                    JOIN products p ON d.product_id = p.id
                    WHERE d.receipt_id = $id";
    $result_details = $conn->query($sql_details);

} else {
    header("Location: import.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/png" href="../assets/images/logo-1.png" />
  <title>ChickenJoy Admin | Chi tiết phiếu nhập</title>
  <link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body class="admin-body">
  
   <?php include 'layout/sidebar.php'; ?>
   
   <main class="main-content">
      <header class="main-header">
          <h1>Chi Tiết Phiếu Nhập Hàng</h1>
      </header>

      <a href="import.php" class="back-btn">
        &larr; Quay lại danh sách
      </a>

      <div class="detail-card">
          
          <div class="order-header-info">
              <div>
                  <h2>Mã phiếu: <?= htmlspecialchars($receipt['receipt_code']) ?></h2>
                  <p style="color: #666;">Ngày nhập: <?= date('d/m/Y', strtotime($receipt['import_date'])) ?></p>
              </div>
              <div>
                  <?php 
                    $status_class = ($receipt['status'] == 'completed') ? 'active' : 'pending';
                    $status_text = ($receipt['status'] == 'completed') ? 'Hoàn thành' : 'Đang xử lý';
                  ?>
                  <span class="status <?= $status_class ?>" style="font-size: 14px; padding: 6px 15px;">
                      <?= $status_text ?>
                  </span>
              </div>
          </div>

          <h3 style="margin-bottom: 15px; font-size: 16px;">Danh sách sản phẩm nhập</h3>
          <table class="data-table">
            <thead>
              <tr>
                <th>Mã SP</th>
                <th style="width: 40%;">Tên sản phẩm</th>
                <th style="text-align: center;">Số lượng nhập</th>
                <th style="text-align: center;">Đơn giá nhập</th>
                <th style="text-align: right;">Thành tiền</th>
              </tr>
            </thead>
            <tbody>
              <?php
              if ($result_details->num_rows > 0) {
                  while($row = $result_details->fetch_assoc()) {
                      $subtotal = $row['quantity'] * $row['import_price'];
                      echo "<tr>";
                        echo "<td>" . $row['product_code'] . "</td>";
                        echo "<td><strong>" . htmlspecialchars($row['product_name']) . "</strong></td>";
                        echo "<td style='text-align: center;'>" . $row['quantity'] . "</td>";
                        echo "<td style='text-align: center;'>" . number_format($row['import_price'], 0, ',', '.') . "đ</td>";
                        echo "<td style='text-align: right; font-weight: bold;'>" . number_format($subtotal, 0, ',', '.') . "đ</td>";
                      echo "</tr>";
                  }
              } else {
                  echo "<tr><td colspan='5' style='text-align:center;'>Không có chi tiết mặt hàng nào!</td></tr>";
              }
              ?>
            </tbody>
          </table>

          <div class="order-summary">
              <h3 class="total-price"><strong>Tổng tiền phiếu nhập:</strong> <?= number_format($receipt['total_amount'], 0, ',', '.') ?>đ</h3>
          </div>
      </div>
    </main>
</body>
</html>