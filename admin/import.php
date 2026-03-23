<?php
  require_once '../config/connect.php';
  $search = "";
  if (isset($_GET['search']) && $_GET['search'] != '') {
      $search = $_GET['search'];
      $search_escaped = $conn->real_escape_string($search);
      $sql = "SELECT * FROM import_receipts WHERE receipt_code LIKE '%$search_escaped%' ORDER BY id ASC";
  } else {
      $sql = "SELECT * FROM import_receipts ORDER BY id ASC";
  }
  
  $result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/png" href="../assets/images/logo-1.png" />
  <title>ChickenJoy Admin | Nhập hàng</title>
  <link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body class="admin-body">
  <?php include 'layout/sidebar.php'; ?>
  <main class="main-content">
    <header class="main-header">
      <h1>Quản lý nhập hàng</h1>
      <a href="../admin/import-add.php" class="btn-primary">+ Tạo phiếu nhập</a>
    </header>

    <div class="table-toolbar">
      <form action="import.php" method="GET" style="display: flex; gap: 10px;">
        <input type="text" name="search" value="<?php echo $search; ?>" placeholder="🔍 Tìm theo Mã Phiếu..." style="padding: 8px; width: 1100px; border: 1px solid #ccc; border-radius: 4px;">
        <button type="submit" class="btn-primary" style="padding: 8px 15px;">Tìm kiếm</button>
        
        <?php if($search != '') { ?>
          <a href="import.php" class="btn-cancel" style="padding: 8px 15px; text-decoration: none; background: #6c757d; color: white; border-radius: 4px;">Hủy lọc</a>
        <?php } ?>
      </form>
    </div>

    <section class="table-section">
      <table class="data-table">
        <thead>
          <tr>
            <th>Mã Phiếu</th>
            <th>Ngày Nhập</th>
            <th>Tổng Tiền</th>
            <th>Trạng thái</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody>
  <?php
  if ($result && $result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {
          
          $status_class = ($row['status'] == 'completed') ? 'active' : 'hidden';
          $status_text = ($row['status'] == 'completed') ? 'Hoàn thành' : 'Đang xử lý';

          echo "<tr>";
            echo "<td>" . $row['receipt_code'] . "</td>";
            
            $formatted_date = date('d/m/Y', strtotime($row['import_date']));
            echo "<td>" . $formatted_date . "</td>";
            
            $formatted_money = number_format($row['total_amount'], 0, ',', '.');
            echo "<td>" . $formatted_money . " VNĐ</td>";
            
            echo "<td><span class='status " . $status_class . "'>" . $status_text . "</span></td>";
            
            echo "<td>
                    <div class='actions'>
                      <a href='import-detail.php?id=" . $row['id'] . "' class='btn-edit'>Chi tiết</a>
                      <a href='import-delete.php?id=" . $row['id'] . "' class='btn-delete' onclick='return confirm(\"Cảnh báo: Bạn có chắc chắn muốn xóa dữ liệu này không?\");'>Xóa</a>
                    </div>
                  </td>";
          echo "</tr>";
      }
  } else {
      echo "<tr><td colspan='5' style='text-align:center;'>Không tìm thấy phiếu nhập nào phù hợp!</td></tr>";
  }
  ?>
        </tbody>
      </table>
    </section>
  </main>
</body>
</html>