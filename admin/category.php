<?php
require_once '../config/database.php';

$search = "";
if (isset($_GET['search']) && $_GET['search'] != '') {
    $search = $conn->real_escape_string($_GET['search']);
    $sql = "SELECT * FROM categories WHERE category_code LIKE '%$search%' OR category_name LIKE '%$search%' ORDER BY id ASC";
} else {
    $sql = "SELECT * FROM categories ORDER BY id ASC";
}
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
  <main class="main-content">
    <header class="main-header">
      <h1>Quản lý loại sản phẩm</h1>
      <a href="category-add.php" class="btn-primary">+ Thêm loại</a>
    </header>

    <div class="table-toolbar">
      <form action="category.php" method="GET" style="display: flex; gap: 10px; width: 100%;">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Tìm theo Tên hoặc Mã loại..." style="padding: 8px; flex: 1; border: 1px solid #ccc; border-radius: 4px; outline: none; font-family: inherit;">
        <button type="submit" class="btn-primary" style="padding: 8px 15px;">Tìm kiếm</button>
        <?php if($search != '') { ?>
          <a href="category.php" class="btn-gray" style="padding: 8px 15px;">Hủy lọc</a>
        <?php } ?>
      </form>
    </div>

    <section class="table-section">
      <table class="data-table">
        <thead>
          <tr>
            <th>Mã loại</th>
            <th>Tên loại</th>
            <th style="text-align: center;">Trạng thái</th>
            <th style="text-align: center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($result && $result->num_rows > 0) {
              while($row = $result->fetch_assoc()) {
                  $status_class = ($row['status'] == 'active') ? 'active' : 'hidden';
                  $status_text = ($row['status'] == 'active') ? 'Đang hiển thị' : 'Đang ẩn';

                  echo "<tr>";
                    echo "<td>" . $row['category_code'] . "</td>";
                    echo "<td><strong>" . htmlspecialchars($row['category_name']) . "</strong></td>";
                    echo "<td style='text-align: center;'><span class='status " . $status_class . "'>" . $status_text . "</span></td>";
                    
                    echo "<td>
                            <div class='actions' style='justify-content: center;'>
                              <a href='category-edit.php?id=" . $row['id'] . "' class='btn-edit'>Sửa</a>";
                              
                    if ($row['status'] == 'active') {
                        echo "<a href='category-delete.php?id=" . $row['id'] . "' class='btn-gray' onclick=\"return confirm('Bạn có chắc chắn muốn ẩn loại sản phẩm này không?');\">Ẩn</a>";
                    } else {
                        echo "<a href='category-restore.php?id=" . $row['id'] . "' class='btn-primary' style='background-color: #28a745;' onclick=\"return confirm('Khôi phục loại sản phẩm này?');\">Bật</a>";
                    }
                    
                    echo "    </div>
                          </td>";
                  echo "</tr>";
              }
          } else {
              echo "<tr><td colspan='4' style='text-align: center; padding: 20px;'>Không tìm thấy loại sản phẩm nào!</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </section>
  </main>
</body>
</html>