<?php
require_once '../config/database.php';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_points'])) {
    $new_rate = (int)$_POST['money_per_point'];
    if ($new_rate > 0) {
        $conn->query("UPDATE points SET config_value = $new_rate WHERE config_key = 'money_per_point'");
        echo "<script>alert('Cập nhật quy tắc tích điểm thành công!'); window.location.href='promotion.php';</script>";
        exit();
    } else {
        echo "<script>alert('Lỗi: Số tiền quy đổi phải lớn hơn 0!');</script>";
    }
}

$sql_get_points = "SELECT config_value FROM points WHERE config_key = 'money_per_point'";
$res_points = $conn->query($sql_get_points);
$current_rate = ($res_points->num_rows > 0) ? $res_points->fetch_assoc()['config_value'] : 10000;
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    $new_status = ($action == 'lock') ? 'locked' : 'active';
    $conn->query("UPDATE promotions SET status = '$new_status' WHERE id = $id");
    header("Location: promotion.php");
    exit();
}

$search = "";
if (isset($_GET['search']) && $_GET['search'] != '') {
    $search = $conn->real_escape_string($_GET['search']);
    $sql_promo = "SELECT * FROM promotions WHERE code LIKE '%$search%' OR name LIKE '%$search%' ORDER BY id ASC";
} else {
    $sql_promo = "SELECT * FROM promotions ORDER BY id ASC";
}
$result_promo = $conn->query($sql_promo);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/png" href="../assets/images/logo-1.png" />
  <title>ChickenJoy Admin | Quản lý Khuyến mãi & Tích điểm</title>
  <link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body class="admin-body">
  <?php include 'layout/sidebar.php'; ?>
  <main class="main-content">
    <header class="main-header">
      <h1>Quản lý Khuyến mãi</h1>
      <a href="promotion-add.php" class="btn-primary">+ Thêm khuyến mãi</a>
    </header>

    <section class="form-section" style="margin-bottom: 25px; padding: 20px; background: #fffaf5; border: 1px dashed #ff6b35; border-radius: 8px;">
        <h3 style="margin-bottom: 15px; color: #ff6b35;">Thiết lập quy tắc Tích điểm</h3>
        <form action="promotion.php" method="POST" style="display: flex; align-items: center; gap: 15px;">
            <label style="font-weight: bold;">Số tiền hóa đơn để tích 1 điểm (VNĐ):</label>
            <input type="number" id="rate-input" name="money_per_point" value="<?php echo $current_rate; ?>" min="1000" step="1000" required style="padding: 8px; width: 150px; font-size: 16px; font-weight: bold; color: #e74c3c; text-align: right; border: 1px solid #ccc; border-radius: 4px;">
            <button type="submit" name="update_points" class="btn-primary" style="padding: 8px 15px;">Cập nhật</button>
        </form>
        <p style="font-size: 13px; color: #666; margin-top: 10px; margin-bottom: 0;">
            * Ví dụ: Với mức thiết lập <strong id="live-rate"><?php echo number_format($current_rate, 0, ',', '.'); ?></strong><strong>đ</strong>, hóa đơn <strong>150.000đ</strong> của khách sẽ tích được <strong id="live-points" style="color: #e74c3c; font-size: 15px;"><?php echo floor(150000 / $current_rate); ?></strong> điểm.
        </p>
    </section>

    <script>
      const rateInput = document.getElementById('rate-input');
      const liveRate = document.getElementById('live-rate');
      const livePoints = document.getElementById('live-points');

      // Lắng nghe sự kiện mỗi khi người dùng gõ hoặc thay đổi số
      rateInput.addEventListener('input', function() {
          let rate = parseInt(this.value);
          
          if (rate > 0) {
              // Cập nhật số tiền hiển thị (định dạng có dấu chấm, VD: 20.000)
              liveRate.innerText = rate.toLocaleString('vi-VN');
              
              // Tính lại số điểm cho đơn 150k và làm tròn xuống
              livePoints.innerText = Math.floor(150000 / rate);
          } else {
              liveRate.innerText = '0';
              livePoints.innerText = '0';
          }
      });
    </script>

    <div class="table-toolbar">
      <form action="promotion.php" method="GET" style="display: flex; gap: 10px;">
        <input type="text" name="search" value="<?php echo $search; ?>" placeholder="🔍 Tìm theo Mã hoặc Tên chương trình..." style="padding: 8px; width: 1100px; border: 1px solid #ccc; border-radius: 4px;">
        <button type="submit" class="btn-primary" style="padding: 8px 15px;">Tìm kiếm</button>
        <?php if($search != '') { ?>
          <a href="promotion.php" class="btn-cancel" style="padding: 8px 15px; text-decoration: none; background: #6c757d; color: white; border-radius: 4px;">Hủy lọc</a>
        <?php } ?>
      </form>
    </div>

    <section class="table-section">
      <table class="data-table">
        <thead>
          <tr>
            <th>Mã KM</th>
            <th>Tên chương trình</th>
            <th class="text-center">% Giảm</th>
            <th class="text-center">Thời gian áp dụng</th>
            <th class="text-center">Trạng thái</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($result_promo && $result_promo->num_rows > 0) {
              while($row = $result_promo->fetch_assoc()) {
                  $status_class = ($row['status'] == 'active') ? 'active' : 'hidden';
                  $status_text = ($row['status'] == 'active') ? 'Đang hoạt động' : 'Đã khóa';
                  $date_range = date('d/m/Y', strtotime($row['start_date'])) . ' - ' . date('d/m/Y', strtotime($row['end_date']));

                  echo "<tr>";
                    echo "<td><strong>" . $row['code'] . "</strong></td>";
                    echo "<td>" . $row['name'] . "</td>";
                    echo "<td class='text-center' style='color: #e74c3c; font-weight: bold;'>" . $row['discount_percent'] . "%</td>";
                    echo "<td class='text-center'>" . $date_range . "</td>";
                    echo "<td class='text-center'><span class='status " . $status_class . "'>" . $status_text . "</span></td>";
                    echo "<td>
                            <div class='actions'>
                                <a href='promotion-edit.php?id=" . $row['id'] . "' class='btn-edit'>Sửa</a>";
                    if ($row['status'] == 'active') {
                        echo "<a href='promotion.php?action=lock&id=" . $row['id'] . "' class='btn-delete' onclick=\"return confirm('Bạn có chắc chắn muốn khóa mã này?');\">Khóa</a>";
                    } else {
                        echo "<a href='promotion.php?action=unlock&id=" . $row['id'] . "' class='btn-edit' style='background-color: #28a745; color: white;' onclick=\"return confirm('Khôi phục mã này?');\">Mở khóa</a>";
                    }
                    echo "    </div>
                          </td>";
                  echo "</tr>";
              }
          } else {
              echo "<tr><td colspan='6' class='text-center'>Không tìm thấy mã khuyến mãi nào!</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </section>
  </main>
</body>
</html>