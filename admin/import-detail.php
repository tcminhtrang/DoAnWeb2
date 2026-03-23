<?php
require_once '../config/connect.php';

// 1. CHỤP ID TỪ THANH ĐỊA CHỈ
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Lấy thông tin CHUNG của phiếu nhập
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
      <div class="main-header">
        <h2>Chi tiết phiếu nhập: <?php echo $receipt['receipt_code']; ?></h2>
        <a href="import.php" class="btn-primary">Quay lại danh sách</a>
      </div>

      <div class="receipt-info" style="background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <p><strong>Ngày nhập:</strong> <?php echo date('d/m/Y', strtotime($receipt['import_date'])); ?></p>
        <p><strong>Trạng thái:</strong> 
          <?php 
            if($receipt['status'] == 'completed') echo '<span class="status active">Hoàn thành</span>';
            else echo '<span class="status hidden">Đang xử lý</span>';
          ?>
        </p>
        <p><strong>Tổng tiền phiếu nhập:</strong> <span style="color: #e74c3c; font-size: 1.2em; font-weight: bold;"><?php echo number_format($receipt['total_amount'], 0, ',', '.'); ?> VNĐ</span></p>
      </div>

      <div class="table-section">
        <table class="data-table">
          <thead>
            <tr>
              <th>Mã SP</th>
              <th>Tên sản phẩm</th>
              <th>Số lượng nhập</th>
              <th>Đơn giá nhập</th>
              <th>Thành tiền</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if ($result_details->num_rows > 0) {
                while($row = $result_details->fetch_assoc()) {
                    echo "<tr>";
                      echo "<td>" . $row['product_code'] . "</td>";
                      echo "<td>" . $row['product_name'] . "</td>";
                      echo "<td>" . $row['quantity'] . "</td>";
                      echo "<td>" . number_format($row['import_price'], 0, ',', '.') . " VNĐ</td>";
                      
                      // Cột Thành tiền = Số lượng * Đơn giá nhập
                      $subtotal = $row['quantity'] * $row['import_price'];
                      echo "<td><strong>" . number_format($subtotal, 0, ',', '.') . " VNĐ</strong></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5' style='text-align:center;'>Không có chi tiết mặt hàng nào!</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </main>
</body>
</html>