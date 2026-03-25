<?php
include '../config/database.php'; 

// Khởi tạo biến để giữ lại giá trị cũ
$ho = $ten = $email = $phone = $city = $district = $address_detail = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ho = mysqli_real_escape_string($conn, $_POST['ho']);
    $ten = mysqli_real_escape_string($conn, $_POST['ten']);
    $fullname = $ho . " " . $ten; 
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $district = mysqli_real_escape_string($conn, $_POST['district']);
    $address_detail = mysqli_real_escape_string($conn, $_POST['address_detail']);
    
    $full_address = $address_detail . ", " . $district . ", " . $city;
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Kiểm tra trùng email
    $check_email = "SELECT id FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $check_email);

    if (mysqli_num_rows($result) > 0) {
        $email_error = "Email này đã được sử dụng!";
    } else {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (fullname, email, password, phone, address) 
                VALUES ('$fullname', '$email', '$password_hashed', '$phone', '$full_address')";

        if (mysqli_query($conn, $sql)) {
            echo "<script>window.location.href='Dangnhap.php?success=1';</script>";
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <title>Chicken Joy - Đăng ký</title>
    <link rel="stylesheet" href="../css/Dangky.css" />
    <style>
        /* Hiệu ứng báo lỗi đỏ toàn ô */
        .input-error { 
            border: 2px solid #ff4d4d !important; 
            background-color: #fff5f5 !important; 
        }
        .error-text { 
            color: #ff4d4d; 
            font-size: 11px; 
            font-weight: bold;
            margin-top: 3px;
            display: block;
            min-height: 15px;
        }
        .required { color: red; }
        .addr-grid { display: flex; gap: 10px; margin-bottom: 5px; }
        .addr-grid div { flex: 1; }
        select { width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-box">
            <div class="form-content">
                <div class="logo">
                    <img src="../images/logo-1.png" alt="Logo" width="50">
                    <h2>Chicken Joy</h2>
                </div>

                <form action="Dangky.php" method="POST" onsubmit="return validateForm()">
                    <div class="row" style="display:flex; gap:10px;">
                        <div style="flex:1">
                            <label>Họ<span class="required">*</span></label>
                            <input type="text" name="ho" id="ho" placeholder="Nhập họ" value="<?php echo htmlspecialchars($ho); ?>">
                            <span class="error-text" id="error-ho"></span>
                        </div>
                        <div style="flex:1">
                            <label>Tên<span class="required">*</span></label>
                            <input type="text" name="ten" id="ten" placeholder="Nhập tên" value="<?php echo htmlspecialchars($ten); ?>">
                            <span class="error-text" id="error-ten"></span>
                        </div>
                    </div>
                    
                    <label>Email<span class="required">*</span></label>
                    <input type="email" name="email" id="email" placeholder="email@gmail.com" value="<?php echo htmlspecialchars($email); ?>">
                    <span class="error-text" id="error-email"><?php echo isset($email_error) ? $email_error : ''; ?></span>

                    <label>Số điện thoại<span class="required">*</span></label>
                    <input type="tel" name="phone" id="phone" placeholder="090xxxxxxx" value="<?php echo htmlspecialchars($phone); ?>">
                    <span class="error-text" id="error-phone"></span>

                    <div class="addr-grid">
                        <div>
                            <label>Thành phố<span class="required">*</span></label>
                            <select name="city" id="city" onchange="updateDistricts()">
                                <option value="">Chọn TP</option>
                                <option value="Hồ Chí Minh" <?php if($city=="Hồ Chí Minh") echo "selected"; ?>>Hồ Chí Minh</option>
                                <option value="Hà Nội" <?php if($city=="Hà Nội") echo "selected"; ?>>Hà Nội</option>
                                <option value="Đà Nẵng" <?php if($city=="Đà Nẵng") echo "selected"; ?>>Đà Nẵng</option>
                            </select>
                            <span class="error-text" id="error-city"></span>
                        </div>
                        <div>
                            <label>Quận/Huyện<span class="required">*</span></label>
                            <select name="district" id="district">
                                <option value="">Chọn Quận</option>
                                <?php if($district): ?>
                                    <option value="<?php echo $district; ?>" selected><?php echo $district; ?></option>
                                <?php endif; ?>
                            </select>
                            <span class="error-text" id="error-district"></span>
                        </div>
                    </div>

                    <label>Số nhà, Tên đường<span class="required">*</span></label>
                    <textarea name="address_detail" id="address_detail" placeholder="Ví dụ: 123/45_Nguyễn Huệ" rows="1"><?php echo htmlspecialchars($address_detail); ?></textarea>
                    <span class="error-text" id="error-address_detail"></span>

                    <label>Mật khẩu<span class="required">*</span></label>
                    <input type="password" name="password" id="password" placeholder="Mật khẩu">
                    <span class="error-text" id="error-password"></span>

                    <label>Xác nhận lại<span class="required">*</span></label>
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Nhập lại mật khẩu">
                    <span class="error-text" id="error-confirm_password"></span>

                    <button type="submit" class="btn">Đăng Ký</button>
                </form>
            </div>
        </div>
    </div>
    <script src="../js/main.js"></script>
</body>
</html>