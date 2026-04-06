<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../config/database.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: Dangnhap.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Lấy dữ liệu người dùng từ databas
$sql = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

$order_count_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE user_id = '$user_id'");
$order_count = mysqli_fetch_assoc($order_count_res)['total'];
$gender_text = "Khác";
if ($user['gender'] == 'nam') $gender_text = "Nam";
if ($user['gender'] == 'nu') $gender_text = "Nữ";

// Lấy năm tham gia
$member_year = date("Y", strtotime($user['created_at']));
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thông tin cá nhân - Chicken Joy</title>
  <link rel="stylesheet" href="../css/Thongtincanhan.css">
</head>
<body>
  <?php include '../includes/header.php'; ?>

  <main class="account-container">
    <div class="welcome-banner">
      <div class="profile-summary">
        <img src="../images/Taikhoan.png" alt="Ảnh đại diện"> 
        <div class="text-content">
          <h1>Chào mừng trở lại!</h1>
          <h2><?php echo htmlspecialchars($user['fullname']); ?></h2>
          <p class="status">
            <span class="vip"><i class="fa-solid fa-star"></i> Khách hàng</span>
            <span class="member-date">Thành viên từ <?php echo $member_year; ?></span>
          </p>
        </div>
      </div>
    </div>
    
    <section class="account-content">
        <div class="info-section">
            <div class="info-header">
                <div class="title">
                    <i class="fa-solid fa-user-gear"></i>
                    <h3>Thông tin cá nhân</h3>
                    <p>Quản lý và cập nhật thông tin tài khoản của bạn</p>
                </div>
                <div class="info-actions">
                    <a href="../pages/Chinhsuathongtincanhan.php" class="btn-edit">
                        <i class="fa-solid fa-pen-to-square"></i> Chỉnh sửa thông tin
                    </a>
                </div>
            </div>

            <div class="info-grid">
                <div class="col-basic">
                    <p class="section-title"><i class="fa-solid fa-circle-info"></i> Thông tin cơ bản</p>
                    
                    <div class="field-group">
                        <label>HỌ VÀ TÊN</label>
                        <div class="input-display">
                            <i class="fa-solid fa-user"></i>
                            <input type="text" value="<?php echo htmlspecialchars($user['fullname']); ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="field-group">
                        <label>EMAIL</label>
                        <div class="input-display">
                            <i class="fa-solid fa-envelope"></i>
                            <input type="text" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                        </div>
                    </div>

                    <div class="field-group">
                        <label>SỐ ĐIỆN THOẠI</label>
                        <div class="input-display">
                            <i class="fa-solid fa-phone"></i>
                            <input type="text" value="<?php echo htmlspecialchars($user['phone'] ?? 'Chưa cập nhật'); ?>" readonly>
                        </div>
                    </div>

                    <div class="field-group">
                        <label>GIỚI TÍNH</label>
                        <div class="input-display">
                            <i class="fa-solid fa-venus-mars"></i>
                            <input type="text" value="<?php echo $gender_text; ?>" readonly>
                        </div>
                    </div>
                </div>

                <div class="col-contact">
                    <p class="section-title"><i class="fa-solid fa-location-pin"></i> Thông tin liên hệ</p>
                    
                    <div class="field-group">
                        <label>ĐỊA CHỈ</label>
                        <div class="input-display">
                            <i class="fa-solid fa-house"></i>
                            <input type="text" value="<?php echo htmlspecialchars($user['address'] ?? 'Chưa cập nhật'); ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="achievement-box">
    <p class="section-title achievement-title"><i class="fa-solid fa-trophy"></i> Thành tích</p>
    <div class="stats">
        <div class="stat-item">
            <strong><?php echo $order_count; ?></strong>
            <span>Đơn hàng</span>
        </div>
        <div class="stat-item">
            <strong><?php echo number_format($user['points'] ?? 0, 0, ',', '.'); ?></strong>
            <span>Điểm tích lũy</span>
        </div>
    </div>
</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
  </main>

  <script src="../js/main.js"></script>
</body>
</html>