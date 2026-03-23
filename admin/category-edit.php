<?php
require_once '../config/connect.php';

// 1. CHỤP ID TỪ THANH ĐỊA CHỈ & LẤY DỮ LIỆU CŨ HIỂN THỊ
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Tìm kiếm trong kho xem có mã ID này không
    $sql_get = "SELECT * FROM categories WHERE id = $id";
    $result = $conn->query($sql_get);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc(); // Biến $row giờ đang chứa thông tin cũ của loại SP này
    } else {
        echo "<script>alert('Không tìm thấy loại sản phẩm!'); window.location.href='category.php';</script>";
        exit();
    }
} else {
    // Nếu ai đó gõ thẳng link category-edit.php mà không có ID, đuổi về trang danh sách
    header("Location: category.php");
    exit();
}

// 2. XỬ LÝ KHI BẤM NÚT LƯU THAY ĐỔI (GỬI POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ten_loai = $_POST['category-name'];
    $mo_ta = $_POST['category-desc'];
    $trang_thai = $_POST['category-status'];
    // Mã loại (L01) thường là mã định danh không cho sửa, nên ta chỉ cập nhật tên, mô tả và trạng thái.

    // Câu lệnh UPDATE ghi đè dữ liệu mới
    $sql_update = "UPDATE categories 
                   SET category_name = '$ten_loai', description = '$mo_ta', status = '$trang_thai' 
                   WHERE id = $id";

    if ($conn->query($sql_update) === TRUE) {
        header("Location: category.php"); // Sửa xong thì quay về trang danh sách
        exit();
    } else {
        echo "<script>alert('Có lỗi xảy ra khi cập nhật!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
 <meta charset="UTF-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1.0" />
 <link rel="icon" type="image/png" href="../assets/images/logo-1.png" />
 <title>ChickenJoy Admin | Sửa Loại sản phẩm</title>
  <link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body class="admin-body">
  <?php include 'layout/sidebar.php'; ?>
  <main class="main-content">
  <header class="main-header">
   <h1>Sửa loại sản phẩm</h1>
  </header>

  <section class="form-section">
      <form action="" method="POST" class="data-form">
    
      <div class="form-group">
        <label for="category-id">Mã loại:</label>
        <input type="text" id="category-id" name="category-id" value="<?php echo $row['category_code']; ?>" readonly style="background-color: #eee;">
      </div>

      <div class="form-group">
        <label for="category-name">Tên loại:</label>
        <input type="text" id="category-name" name="category-name" value="<?php echo $row['category_name']; ?>" required>
      </div>

      <div class="form-group">
        <label for="category-desc">Mô tả:</label>
        <textarea id="category-desc" name="category-desc" rows="4"><?php echo $row['description']; ?></textarea>
      </div>

      <div class="form-group">
        <label for="category-status">Trạng thái:</label>
        <select id="category-status" name="category-status">
          <option value="active" <?php if($row['status'] == 'active') echo 'selected'; ?>>Đang hiển thị</option>
          <option value="hidden" <?php if($row['status'] == 'hidden') echo 'selected'; ?>>Ẩn</option>
        </select>
      </div>
      
      <div class="form-actions">
        <button type="submit" class="btn-primary">Lưu thay đổi</button>
      </div>
    </form>
  </section>
 </main>
</body>
</html>