<?php
require_once '../config/connect.php';

// Lấy danh sách sản phẩm cùng với giá vốn và giá bán
$sql = "SELECT id, product_code, product_name, import_price, profit_rate, price FROM products ORDER BY id ASC";
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
      <input type="text" placeholder="🔍 Tìm theo mã sản phẩm...">
    </div>
    <section class="table-section">
      <table class="data-table">
        <thead>
          <tr>
            <th>Mã SP</th>
            <th>Tên sản phẩm</th>
            <th class="text-center">Giá nhập (Vốn)</th>
            <th class="text-center">% Lợi nhuận</th> 
            <th class="text-center">Giá bán (VAT)</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($result->num_rows > 0) {
              while($row = $result->fetch_assoc()) {
                  echo "<tr>";
                    echo "<td>" . $row['product_code'] . "</td>";
                    echo "<td>" . $row['product_name'] . "</td>";
                    
                    // Hiển thị Giá nhập
                    echo "<td class='text-center'>" . number_format($row['import_price'], 0, ',', '.') . "đ</td>";
                    
                    // Hiển thị % Lợi nhuận
                    echo "<td class='text-center'><span style='color: #008000; font-weight: 500;'>" . $row['profit_rate'] . "%</span></td>";
                    
                    // Hiển thị Giá bán
                    echo "<td class='text-center' style='font-weight: bold;'>" . number_format($row['price'], 0, ',', '.') . "đ</td>";
                    
                    // Nút Sửa giá (truyền ID)
                    echo "<td>
                            <div class='actions'>
                              <a href='price-edit.php?id=" . $row['id'] . "' class='btn-edit'>Sửa giá</a>
                            </div>
                          </td>";
                  echo "</tr>";
              }
          } else {
              echo "<tr><td colspan='6' class='text-center'>Chưa có sản phẩm nào!</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </section>
  </main>
</body>
</html>