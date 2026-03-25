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
$message = "";

// 2. Lấy dữ liệu user hiện tại
$sql = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);
$member_year = date("Y", strtotime($user['created_at']));

// 3. Xử lý khi nhấn nút "Lưu thay đổi"
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender'] ?? 'khac');
    
    $current_pass_input = $_POST['current-password'] ?? '';
    $new_pass = $_POST['new-password'] ?? '';
    $confirm_pass = $_POST['confirm-password'] ?? '';

    $error = false;
    $update_fields = "fullname='$fullname', email='$email', phone='$phone', address='$address', gender='$gender'";

    // Logic đổi mật khẩu
    if (!empty($new_pass)) {
        if (!password_verify($current_pass_input, $user['password'])) {
            $message = "Mật khẩu hiện tại không chính xác!";
            $error = true;
        } else if ($new_pass !== $confirm_pass) {
            $message = "Xác nhận mật khẩu mới không khớp!";
            $error = true;
        } else if (password_verify($new_pass, $user['password'])) {
            $message = "Mật khẩu mới không được trùng mật khẩu cũ!";
            $error = true;
        } else {
            $password_hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $update_fields .= ", password='$password_hashed'";
        }
    }

    if (!$error) {
        if (mysqli_query($conn, "UPDATE users SET $update_fields WHERE id='$user_id'")) {
            $message = "Cập nhật thông tin thành công!";
            $_SESSION['user_fullname'] = $fullname; // Cập nhật tên trên Header
            
            // Tải lại dữ liệu mới
            $result = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
            $user = mysqli_fetch_assoc($result);
        } else {
            $message = "Lỗi kết nối cơ sở dữ liệu.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chỉnh sửa thông tin - Chicken Joy</title>
  <link rel="stylesheet" href="../css/Thongtincanhan.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="account-container">
        <?php if($message != ""): ?>
            <script>alert("<?php echo $message; ?>");</script>
        <?php endif; ?>

        <div class="welcome-banner">
            <div class="profile-summary">
                <img src="../images/Taikhoan.png" alt="Ảnh đại diện"> 
                <div class="text-content">
                    <h1>Chỉnh sửa hồ sơ</h1>
                    <h2><?php echo htmlspecialchars($user['fullname']); ?></h2>
                    <p class="status">
                        <span class="vip"><i class="fa-solid fa-star"></i> Khách hàng</span>
                        <span class="member-date">Thành viên từ <?php echo $member_year; ?></span>
                    </p>
                </div>
            </div>
        </div>
        
        <section class="account-content">
            <form class="info-section edit-mode" method="POST" action="Chinhsuathongtincanhan.php">
                <div class="info-header">
                    <div class="title">
                        <i class="fa-solid fa-user-pen"></i>
                        <h3>Thông tin cá nhân</h3>
                    </div>
                    <div class="info-actions">
                        <a href="Thongtincanhan.php" class="btn-cancel" style="text-decoration:none; color:#666; margin-right:15px;">Hủy</a>
                        <button type="submit" class="btn-save">Lưu thay đổi</button>
                    </div>
                </div>

                <div class="info-grid">
                    <div class="col-basic">
                        <p class="section-title"><i class="fa-solid fa-circle-info"></i> Thông tin cơ bản</p>
                        
                        <div class="field-group">
                            <label>HỌ VÀ TÊN</label>
                            <div class="input-display">
                                <i class="fa-solid fa-user"></i>
                                <input type="text" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="field-group">
                            <label>EMAIL</label>
                            <div class="input-display">
                                <i class="fa-solid fa-envelope"></i>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                        </div>

                        <div class="field-group">
                            <label>GIỚI TÍNH</label>
                            <div class="input-display">
                                <i class="fa-solid fa-venus-mars"></i>
                                <select name="gender" style="border:none; background:transparent; width:100%; outline:none;">
                                    <option value="khac" <?php if($user['gender']=='khac') echo 'selected'; ?>>Khác</option>
                                    <option value="nam" <?php if($user['gender']=='nam') echo 'selected'; ?>>Nam</option>
                                    <option value="nu" <?php if($user['gender']=='nu') echo 'selected'; ?>>Nữ</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-contact">
                        <p class="section-title"><i class="fa-solid fa-location-dot"></i> Liên hệ</p>
                        
                        <div class="field-group">
                            <label>SỐ ĐIỆN THOẠI</label>
                            <div class="input-display">
                                <i class="fa-solid fa-phone"></i>
                                <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                            </div>
                        </div>

                        <div class="field-group">
                            <label>ĐỊA CHỈ</label>
                            <div class="input-display">
                                <i class="fa-solid fa-map-location-dot"></i>
                                <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="info-section-separator" style="margin-top: 30px; border-top: 1px dashed #ccc; padding-top: 20px;">
                    <p class="section-title"><i class="fa-solid fa-shield-halved"></i> Bảo mật & Mật khẩu</p>
                    <div class="info-grid">
                        <div class="col-basic">
                            <div class="field-group">
                                <label>MẬT KHẨU HIỆN TẠI</label>
                                <div class="input-display">
                                    <i class="fa-solid fa-lock"></i>
                                    <input type="password" name="current-password" placeholder="Nhập mật khẩu cũ">
                                </div>
                            </div>
                        </div>
                        <div class="col-contact">
                            <div class="field-group">
                                <label>MẬT KHẨU MỚI</label>
                                <div class="input-display">
                                    <i class="fa-solid fa-key"></i>
                                    <input type="password" name="new-password" placeholder="Tối thiểu 6 ký tự">
                                </div>
                            </div>
                            <div class="field-group" style="margin-top: 15px;">
                                <label>XÁC NHẬN MẬT KHẨU</label>
                                <div class="input-display">
                                    <i class="fa-solid fa-check-double"></i>
                                    <input type="password" name="confirm-password" placeholder="Nhập lại mật khẩu mới">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </section>
    </main>

    <script src="../js/main.js"></script>
</body>
</html>