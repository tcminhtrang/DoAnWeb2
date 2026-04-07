<?php
require_once 'check_admin.php';
require_once '../config/database.php';

$search = "";
if (isset($_GET['search']) && $_GET['search'] != '') {
    $search = $conn->real_escape_string($_GET['search']);
    $sql = "SELECT p.*, c.category_name 
            FROM products p 
            JOIN categories c ON p.category_id = c.id 
            WHERE p.product_name LIKE '%$search%' OR p.product_code LIKE '%$search%'
            ORDER BY p.id ASC";
} else {
    $sql = "SELECT p.*, c.category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id ASC";
}
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
      <a href="list-add.php" class="btn-primary">+ Thêm sản phẩm</a>
    </header>

    <div class="table-toolbar">
      <form action="list.php" method="GET" style="display: flex; gap: 10px; width: 100%;">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Tìm theo Tên hoặc Mã SP..." style="padding: 8px; flex: 1; border: 1px solid #ccc; border-radius: 4px; outline: none; font-family: inherit;">
        <button type="submit" class="btn-primary" style="padding: 8px 15px;">Tìm kiếm</button>
        <?php if($search != '') { ?>
          <a href="list.php" class="btn-gray" style="padding: 8px 15px;">Hủy lọc</a>
        <?php } ?>
      </form>
    </div>

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
            <th style="text-align: center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($result && $result->num_rows > 0) {
              while($row = $result->fetch_assoc()) {
                  $status_class = ($row['status'] == 'active') ? 'active' : 'hidden';
                  $status_text = ($row['status'] == 'active') ? 'Đang bán' : 'Đang ẩn';

                  echo "<tr>";
                    echo "<td>" . $row['product_code'] . "</td>";
                    echo "<td><img src='../assets/images/products/" . $row['image'] . "' alt='Thumb' class='thumb' style='width: 50px; height: 50px; object-fit: cover; border-radius: 4px;'></td>";
                    echo "<td><strong>" . htmlspecialchars($row['product_name']) . "</strong></td>";
                    echo "<td>" . $row['category_name'] . "</td>"; 
                    echo "<td style='max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;'>" . htmlspecialchars($row['description']) . "</td>";
                    echo "<td><span class='status " . $status_class . "'>" . $status_text . "</span></td>";
                    
                    echo "<td>
                            <div class='actions' style='justify-content: center;'>
                              <a href='list-edit.php?id=" . $row['id'] . "' class='btn-edit'>Sửa</a>";
                    if ($row['status'] == 'active') {
                        echo "<a href='list-delete.php?id=" . $row['id'] . "' class='btn-gray' onclick=\"return confirm('Bạn có muốn ẩn sản phẩm này không?');\">Ẩn</a>";
                    } else {
                        echo "<a href='list-restore.php?id=" . $row['id'] . "' class='btn-primary' style='background-color: #28a745;' onclick=\"return confirm('Khôi phục sản phẩm này?');\">Bật</a>";
                    }
                    echo "    </div>
                          </td>";
                  echo "</tr>";
              }
          } else {
              echo "<tr><td colspan='7' style='text-align: center; padding: 20px;'>Không tìm thấy sản phẩm!</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </section>
  </main>
</body>
</html>