<?php
require_once '../config/connect.php';
  $sql = "SELECT p.*, c.category_name 
          FROM products p 
          JOIN categories c ON p.category_id = c.id 
          ORDER BY p.id ASC";
  $result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/png" href="../assets/images/logo-1.png" />
  <title>ChickenJoy Admin | Danh mục sản phẩm</title>
  <link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body class="admin-body">
  <?php include 'layout/sidebar.php'; ?>
  <main class="main-content">
    <header class="main-header">
      <h1>Quản lý danh mục sản phẩm</h1>
      <a href="../admin/list-add.php" class="btn-primary">+ Thêm sản phẩm</a>
    </header>

    <section class="table-section">
      <table class="data-table">
        <thead>
          <tr>
            <th>Mã SP</th>
            <th>Hình ảnh</th>
            <th>Tên sản phẩm</th>
            <th>Loại sản phẩm</th>
            <th>Mô tả</th>
            <th>Trạng thái</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tbody>
  <?php
  if ($result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {
          
          $status_class = ($row['status'] == 'active') ? 'active' : 'hidden';
          $status_text = ($row['status'] == 'active') ? 'Đang hiển thị' : 'Đang ẩn';

          echo "<tr>";
            echo "<td>" . $row['product_code'] . "</td>";
            
            // Cột hình ảnh: Lấy tên file ảnh từ CSDL ghép vào đường dẫn
            echo "<td><img src='../assets/images/products/" . $row['image'] . "' alt='Thumb' class='thumb' style='width: 50px; height: 50px; object-fit: cover;'></td>";
            
            echo "<td>" . $row['product_name'] . "</td>";
            
            // Cột Tên Loại: Cột này lấy được là nhờ lệnh JOIN ở Bước 1
            echo "<td>" . $row['category_name'] . "</td>"; 
            
            echo "<td>" . $row['description'] . "</td>";
            echo "<td><span class='status " . $status_class . "'>" . $status_text . "</span></td>";
            
            // Các nút thao tác
            echo "<td>
                    <div class='actions'>
                      <a href='list-edit.php?id=" . $row['id'] . "' class='btn-edit'>Sửa</a>
                      <a href='list-delete.php?id=" . $row['id'] . "' class='btn-delete' onclick='return confirm(\"Cảnh báo: Bạn có chắc chắn muốn ẩn sản phẩm này không?\");'>Ẩn</a>
                    </div>
                  </td>";
          echo "</tr>";
      }
  } else {
      echo "<tr><td colspan='7' style='text-align:center;'>Chưa có sản phẩm nào!</td></tr>";
  }
  ?>
</tbody>
        </tbody>
      </table>
    </section>
  </main>
</body>
</html>

