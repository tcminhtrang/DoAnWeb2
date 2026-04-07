<?php
require_once '../config/database.php';

$success_msg = "";
$error_msg = "";
$current_rate = 10000;

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
  <title>ChickenJoy Admin | Quản lý Khuyến mãi</title>
  <link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body class="admin-body">
  <?php include 'layout/sidebar.php'; ?>
  <main class="main-content">
    <header class="main-header">
      <h1>Quản lý Khuyến mãi & Tích điểm</h1>
      <a href="promotion-add.php" class="btn-primary">+ Thêm khuyến mãi</a>
    </header>

    <?php if($success_msg != ""): ?>
        <p style="color: #28a745; font-weight: bold; margin-bottom: 15px;">✓ <?= $success_msg ?></p>
    <?php endif; ?>
    <?php if($error_msg != ""): ?>
        <p style="color: #e74c3c; font-weight: bold; margin-bottom: 15px;">⚠ <?= $error_msg ?></p>
    <?php endif; ?>

    <section class="form-section" style="margin-bottom: 25px; padding: 20px; background: #fffaf5; border: 1px dashed #ff6b35; border-radius: 8px;">
        <h3 style="margin-bottom: 10px; color: #ff6b35;">Quy tắc Tích điểm hiện tại</h3>
        <p style="font-size: 14px; color: #333; margin-bottom: 5px;">
            Hệ thống đang áp dụng tỷ lệ quy đổi: <strong>10.000đ = 1 điểm thưởng</strong>.
        </p>
        <p style="font-size: 13px; color: #666; margin-top: 10px; margin-bottom: 0;">
            * Ví dụ: Hóa đơn thanh toán <strong>150.000đ</strong> của khách sẽ tự động hệ thống tích cho <strong style="color: #e74c3c; font-size: 15px;">15</strong> điểm.
        </p>
    </section>

    <div class="table-toolbar">
      <form action="promotion.php" method="GET" style="display: flex; gap: 10px; width: 100%;">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Tìm theo Mã hoặc Tên chương trình..." style="padding: 8px; flex: 1; border: 1px solid #ccc; border-radius: 4px; outline: none; font-family: inherit;">
        <button type="submit" class="btn-primary" style="padding: 8px 15px;">Tìm kiếm</button>
        <?php if($search != '') { ?>
          <a href="promotion.php" class="btn-gray" style="padding: 8px 15px;">Hủy lọc</a>
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
            <th class="text-center">Thao tác</th>
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
                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                    echo "<td class='text-center' style='color: #e74c3c; font-weight: bold;'>" . $row['discount_percent'] . "%</td>";
                    echo "<td class='text-center'>" . $date_range . "</td>";
                    echo "<td class='text-center'><span class='status " . $status_class . "'>" . $status_text . "</span></td>";
                    echo "<td>
                            <div class='actions' style='justify-content: center;'>
                                <a href='promotion-edit.php?id=" . $row['id'] . "' class='btn-edit'>Sửa</a>";
                    if ($row['status'] == 'active') {
                        echo "<a href='promotion.php?action=lock&id=" . $row['id'] . "' class='btn-gray' onclick=\"return confirm('Bạn có chắc chắn muốn khóa mã này?');\">Khóa</a>";
                    } else {
                        echo "<a href='promotion.php?action=unlock&id=" . $row['id'] . "' class='btn-primary' style='background-color: #28a745;' onclick=\"return confirm('Khôi phục mã này?');\">Mở khóa</a>";
                    }
                    echo "    </div>
                          </td>";
                  echo "</tr>";
              }
          } else {
              echo "<tr><td colspan='6' class='text-center' style='padding: 20px;'>Không tìm thấy mã khuyến mãi nào!</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </section>
  </main>

  <script>
      const rateInput = document.getElementById('rate-input');
      const liveRate = document.getElementById('live-rate');
      const livePoints = document.getElementById('live-points');

      rateInput.addEventListener('input', function() {
          let rate = parseInt(this.value);
          if (rate > 0) {
              liveRate.innerText = rate.toLocaleString('vi-VN');
              livePoints.innerText = Math.floor(150000 / rate);
          } else {
              liveRate.innerText = '0';
              livePoints.innerText = '0';
          }
      });
  </script>
</body>
</html>