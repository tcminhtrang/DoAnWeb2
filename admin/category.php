<?php
require_once '../config/connect.php';
  $sql = "SELECT * FROM categories ORDER BY id DESC";
  $result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/png" href="../assets/images/logo-1.png" />
  <title>ChickenJoy Admin | Loại sản phẩm</title>
  <link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body class="admin-body">
  <?php include 'layout/sidebar.php'; ?>
  <!-- ===== MAIN CONTENT ===== -->
  <main class="main-content">
    <header class="main-header">
      <h1>Quản lý loại sản phẩm</h1>
      <!-- 3. SỬA THÀNH THẺ <a> VÀ BỎ LINK -->
      <a href="../admin/category-add.php" class="btn-primary">+ Thêm loại</a>
      
    </header>

    <section class="table-section">
      <table class="data-table">
        <thead>
          <tr>
            <th>Mã loại</th>
            <th>Tên loại</th>
            <th>Trạng thái</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tbody>
  <?php
  // Kiểm tra xem trong kho có dữ liệu nào không (số dòng > 0)
  if ($result->num_rows > 0) {
      
      // Dùng vòng lặp while: Lôi từng dòng dữ liệu trong kho ra cho đến khi hết
      while($row = $result->fetch_assoc()) {
          
          // Xử lý nhỏ: Đổi chữ tiếng Anh trong CSDL thành tiếng Việt cho đẹp
          $status_class = ($row['status'] == 'active') ? 'active' : 'hidden';
          $status_text = ($row['status'] == 'active') ? 'Đang hiển thị' : 'Đang ẩn';

          // Bắt đầu in ra HTML
          echo "<tr>";
            echo "<td>" . $row['category_code'] . "</td>";     // Cột Mã loại
            echo "<td>" . $row['category_name'] . "</td>";     // Cột Tên loại
            echo "<td><span class='status " . $status_class . "'>" . $status_text . "</span></td>";
            
            // Cột Nút bấm Sửa/Xóa (Có nhúng sẵn ID để lát nữa biết bấm vào dòng nào)
            echo "<td>
                    <div class='actions'>
                      <a href='category-edit.php?id=" . $row['id'] . "' class='btn-edit'>Sửa</a>
                      <a href='category-delete.php?id=" . $row['id'] . "' class='btn-delete' onclick='return confirm(\"Cảnh báo: Bạn có chắc chắn muốn ẩn dữ liệu này không?\");'>Ẩn</a>
                    </div>
                  </td>";
          echo "</tr>";
      }
  } else {
      // Nếu kho trống thì in ra dòng này
      echo "<tr><td colspan='5' style='text-align:center;'>Chưa có loại sản phẩm nào!</td></tr>";
  }
  ?>
        </tbody>
      </table>
    </section>
  </main>
</body>
</html>

