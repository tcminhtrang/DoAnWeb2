<?php
require_once '../config/database.php';

$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = strtoupper(trim($_POST['promo-code'])); 
    $name = trim($_POST['promo-name']);
    $discount = (int)$_POST['promo-discount'];
    $start_date = $_POST['promo-start'];
    $end_date = $_POST['promo-end'];
    $status = $_POST['promo-status'];

    $sql_check = "SELECT id FROM promotions WHERE code = '$code'";
    $result_check = $conn->query($sql_check);

    if ($result_check->num_rows > 0) {
        $error_msg = "Lỗi: Mã khuyến mãi '$code' đã tồn tại!";
    } elseif ($start_date > $end_date) {
        $error_msg = "Lỗi: Ngày bắt đầu không được lớn hơn ngày kết thúc!";
    } else {
        $sql_insert = "INSERT INTO promotions (code, name, discount_percent, start_date, end_date, status) 
                       VALUES ('$code', '$name', $discount, '$start_date', '$end_date', '$status')";
    
        if ($conn->query($sql_insert) === TRUE) {
            header("Location: promotion.php"); 
            exit();
        } else {
            $error_msg = "Lỗi CSDL: Không thể thêm khuyến mãi!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <title>ChickenJoy Admin | Thêm Khuyến Mãi</title>
  <link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body class="admin-body">
  <?php include 'layout/sidebar.php'; ?>
  <main class="main-content">
    <header class="main-header">
      <h1>Thêm mã giảm giá mới</h1>
      <a href="promotion.php" class="btn-gray">&larr; Quay lại</a>
    </header>

    <section class="form-section">
      <?php if($error_msg != ""): ?>
          <p style="color: #e74c3c; font-weight: bold; margin-bottom: 15px;"><img src="../assets/images/icons/triangle-warning.png" style="width:16px; vertical-align:text-bottom;"> <?= $error_msg ?></p>
      <?php endif; ?>

      <form action="" method="POST" id="promo-form" novalidate>
        <div style="display:flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
                <label>Mã giảm giá (Code):</label>
                <input type="text" name="promo-code" class="form-input req-input" placeholder="VD: TET2026" value="<?= isset($_POST['promo-code']) ? htmlspecialchars($_POST['promo-code']) : '' ?>" required style="text-transform: uppercase;" autofocus>
            </div>
            <div class="form-group" style="flex: 2;">
                <label>Tên chương trình:</label>
                <input type="text" name="promo-name" class="form-input req-input" placeholder="VD: Giảm giá Tết Nguyên Đán" value="<?= isset($_POST['promo-name']) ? htmlspecialchars($_POST['promo-name']) : '' ?>" required>
            </div>
        </div>

        <div style="display:flex; gap: 15px; background: #fffaf5; padding: 15px; border-radius: 8px; border: 1px dashed #ff6b35; margin-bottom: 15px;">
            <div class="form-group" style="flex: 1;">
                <label>Tỷ lệ giảm (%):</label>
                <input type="number" name="promo-discount" class="form-input req-number" min="1" max="100" value="<?= isset($_POST['promo-discount']) ? $_POST['promo-discount'] : '10' ?>" required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label>Ngày bắt đầu:</label>
                <input type="date" name="promo-start" class="form-input req-input date-check" value="<?= isset($_POST['promo-start']) ? $_POST['promo-start'] : '' ?>" required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label>Ngày kết thúc:</label>
                <input type="date" name="promo-end" class="form-input req-input date-check" value="<?= isset($_POST['promo-end']) ? $_POST['promo-end'] : '' ?>" required>
            </div>
        </div>

        <div class="form-group" style="width: 30%;">
            <label>Trạng thái:</label>
            <select name="promo-status" class="form-input">
                <option value="active" <?= (!isset($_POST['promo-status']) || $_POST['promo-status'] == 'active') ? 'selected' : '' ?>>Hoạt động</option>
                <option value="locked" <?= (isset($_POST['promo-status']) && $_POST['promo-status'] == 'locked') ? 'selected' : '' ?>>Khóa</option>
            </select>
        </div>

        <div class="form-actions"><button type="submit" class="btn-primary">Lưu khuyến mãi</button></div>
      </form>
    </section>
  </main>

  <script>
    document.querySelectorAll('.req-input, .req-number').forEach(input => {
        input.addEventListener('input', function() { this.style.borderColor = "#ccc"; });
        input.addEventListener('change', function() { this.style.borderColor = "#ccc"; });
    });

    document.getElementById('promo-form').addEventListener('submit', function(e) {
        let hasError = false;
        
        document.querySelectorAll('.req-input').forEach(el => {
            el.style.borderColor = "#ccc";
            if (el.value.trim() === "") {
                hasError = true;
                el.style.borderColor = "red";
            }
        });

        const discount = document.querySelector('input[name="promo-discount"]');
        discount.style.borderColor = "#ccc";
        if (discount.value === "" || parseFloat(discount.value) < 1 || parseFloat(discount.value) > 100) {
            hasError = true;
            discount.style.borderColor = "red";
        }

        const start = document.querySelector('input[name="promo-start"]');
        const end = document.querySelector('input[name="promo-end"]');
        if (start.value && end.value && start.value > end.value) {
            hasError = true;
            start.style.borderColor = "red";
            end.style.borderColor = "red";
        }

        if (hasError) e.preventDefault();
    });
  </script>
</body>
</html>