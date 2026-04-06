<?php
require_once '../config/database.php';

$search = "";
if (isset($_GET['search']) && $_GET['search'] != '') {
    $search = $conn->real_escape_string($_GET['search']);
    $sql = "SELECT id, product_code, product_name, import_price, profit_rate, price FROM products WHERE product_code LIKE '%$search%' OR product_name LIKE '%$search%' ORDER BY id ASC";
} else {
    $sql = "SELECT id, product_code, product_name, import_price, profit_rate, price FROM products ORDER BY id ASC";
}
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/png" href="../assets/images/logo-1.png" />
  <title>ChickenJoy Admin | Giá bán</title>
  <link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body class="admin-body">
   <?php include 'layout/sidebar.php'; ?>

  <main class="main-content">
    <header class="main-header">
      <h1>Quản lý giá bán</h1>
    </header>
    
    <div class="table-toolbar">
      <form action="price.php" method="GET" style="display: flex; gap: 10px; width: 100%;">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Tìm theo mã hoặc tên sản phẩm..." style="padding: 8px; flex: 1; border: 1px solid #ccc; border-radius: 4px; outline: none; font-family: inherit;">
        <button type="submit" class="btn-primary" style="padding: 8px 15px;">Tìm kiếm</button>
        <?php if($search != '') { ?>
          <a href="price.php" class="btn-gray" style="padding: 8px 15px;">Hủy lọc</a>
        <?php } ?>
      </form>
    </div>

    <section class="table-section">
      <table class="data-table">
        <thead>
          <tr>
            <th>Mã SP</th>
            <th>Tên sản phẩm</th>
            <th style="text-align: center;">Giá nhập (Vốn)</th>
            <th style="text-align: center;">% Lợi nhuận</th> 
            <th style="text-align: center;">Giá bán (VAT)</th>
            <th style="text-align: center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($result->num_rows > 0) {
              while($row = $result->fetch_assoc()) {
                  echo "<tr>";
                    echo "<td>" . $row['product_code'] . "</td>";
                    echo "<td><strong>" . htmlspecialchars($row['product_name']) . "</strong></td>";
                    
                    echo "<td style='text-align: center;'>" . number_format($row['import_price'], 0, ',', '.') . "đ</td>";
                    $loi_nhuan_hien_thi = $row['profit_rate'] * 100;
                    echo "<td style='text-align: center;'><span class='status active' style='background-color: #e8f5e9; color: #28a745;'>" . $loi_nhuan_hien_thi . "%</span></td>";
                    
                    echo "<td style='text-align: center; font-weight: bold; color: #e74c3c;'>" . number_format($row['price'], 0, ',', '.') . "đ</td>";
                    
                    echo "<td style='text-align: center;'>
                            <div class='actions' style='justify-content: center;'>
                              <a href='price-edit.php?id=" . $row['id'] . "' class='btn-edit'>Sửa giá</a>
                            </div>
                          </td>";
                  echo "</tr>";
              }
          } else {
              echo "<tr><td colspan='6' style='text-align: center; padding: 20px;'>Không tìm thấy sản phẩm!</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </section>
  </main>
</body>
</html>