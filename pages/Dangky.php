<?php
include '../config/database.php'; 

// Khởi tạo biến để tránh lỗi undefined
$ho = $ten = $email = $phone = $city = $district = $address_detail = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ho = trim($_POST['ho']);
    $ten = trim($_POST['ten']);
    $fullname = trim($ho . " " . $ten);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $city = trim($_POST['city']);
    $district = trim($_POST['district']);
    $address_detail = trim($_POST['address_detail']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_address = trim($address_detail . ", " . $district . ", " . $city);
    if (strlen($password) < 5 || strlen($password) > 10) {
        $errors['password'] = "Mật khẩu phải từ 5-10 ký tự!";
    }
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = "Mật khẩu xác nhận không khớp!";
    }
    if (!preg_match('/^0[0-9]{9}$/', $phone)) {
        $errors['phone'] = "Số điện thoại không hợp lệ!";
    }

    if (empty($errors)) {
        $stmt_check = mysqli_prepare($conn, "SELECT email, phone FROM users WHERE email = ? OR phone = ?");
        mysqli_stmt_bind_param($stmt_check, "ss", $email, $phone);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        
        while ($row = mysqli_fetch_assoc($result_check)) {
            if ($row['email'] === $email) {
                $errors['email'] = "Email này đã được sử dụng!";
            }
            if ($row['phone'] === $phone) {
                $errors['phone'] = "Số điện thoại này đã được đăng ký!";
            }
        }
        mysqli_stmt_close($stmt_check);
    }

    if (empty($errors)) {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        $role = 'user';
        
        $stmt_insert = mysqli_prepare($conn, "INSERT INTO users (fullname, email, password, phone, address, role) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt_insert, "ssssss", $fullname, $email, $password_hashed, $phone, $full_address, $role);
        
        if (mysqli_stmt_execute($stmt_insert)) {
            echo "<script>alert('Đăng ký thành công!'); window.location.href='Dangnhap.php';</script>";
            exit();
        } else {
            $errors['database'] = "Lỗi hệ thống, vui lòng thử lại sau!";
        }
        mysqli_stmt_close($stmt_insert);
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chicken Joy - Đăng ký</title>
    <link rel="stylesheet" href="../css/Dangky.css" />
    <style>
        /* Hiệu ứng báo lỗi */
        .input-error { border: 2px solid #ff4d4d !important; background-color: #fff5f5 !important; }
        .error-text { color: #ff4d4d; font-size: 11px; font-weight: bold; margin-top: 3px; display: block; min-height: 15px; }
        .required { color: red; }

        /* Tối ưu Select Box */
        select {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
            font-size: 14px;
            color: #333;
            outline: none;
            transition: all 0.3s ease;
            appearance: none; 
            background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23666%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px top 50%;
            background-size: 12px auto;
        }
        select:focus {
            border-color: #ffcc00;
            background-color: #fff;
            box-shadow: 0 0 5px rgba(255, 204, 0, 0.3);
        }

        .addr-grid { display: flex; gap: 15px; margin-bottom: 5px; }
        .addr-grid div { flex: 1; }
        
        textarea { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; resize: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-box">
            <div class="form-content">
                
                <div style="text-align: left; margin-bottom: 10px;">
                    <a href="javascript:window.history.back();" style="color: #f97407; text-decoration: none; font-size: 14px; font-weight: bold; transition: 0.3s;" onmouseover="this.style.color='#e66a06'" onmouseout="this.style.color='#f97407'">&larr; Quay lại</a>
                </div>

                <div class="logo">
                    <img src="../images/logo-1.png" alt="Logo" width="50">
                    <h2>Chicken Joy</h2>
                </div>

                <form action="Dangky.php" method="POST" id="registerForm" onsubmit="return validateOnSubmit()">
                    <div class="row" style="display:flex; gap:10px;">
                        <div style="flex:1">
                            <label>Họ<span class="required">*</span></label>
                            <input type="text" name="ho" id="ho" placeholder="Họ" value="<?= htmlspecialchars($_POST['ho'] ?? '') ?>">
                            <span class="error-text" id="error-ho"></span>
                        </div>
                        <div style="flex:1">
                            <label>Tên<span class="required">*</span></label>
                            <input type="text" name="ten" id="ten" placeholder="Tên" value="<?= htmlspecialchars($_POST['ten'] ?? '') ?>">
                            <span class="error-text" id="error-ten"></span>
                        </div>
                    </div>
                    
                    <label>Email<span class="required">*</span></label>
                    <input type="email" name="email" id="email" placeholder="email@gmail.com" value="<?= htmlspecialchars($email) ?>">
                    <span class="error-text" id="error-email"><?= $errors['email'] ?? '' ?></span>

                    <label>Số điện thoại<span class="required">*</span></label>
                    <input type="tel" name="phone" id="phone" placeholder="0xxxxxxxxx" value="<?= htmlspecialchars($phone) ?>">
                    <span class="error-text" id="error-phone"></span>

                    <div class="addr-grid">
                        <div>
                            <label>Thành phố<span class="required">*</span></label>
                            <select name="city" id="city">
                                <option value="">Chọn TP</option>
                                <option value="Hồ Chí Minh">Hồ Chí Minh</option>
                                <option value="Hà Nội">Hà Nội</option>
                                <option value="Đà Nẵng">Đà Nẵng</option>
                                <option value="Hải Phòng">Hải Phòng</option>
                                <option value="Cần Thơ">Cần Thơ</option>
                            </select>
                            <span class="error-text" id="error-city"></span>
                        </div>
                        <div>
                            <label>Quận/Huyện<span class="required">*</span></label>
                            <select name="district" id="district">
                                <option value="">Chọn Quận</option>
                            </select>
                            <span class="error-text" id="error-district"></span>
                        </div>
                    </div>

                    <label>Địa chỉ chi tiết<span class="required">*</span></label>
                    <textarea name="address_detail" id="address_detail" placeholder="Số nhà, tên đường..." rows="1"><?= htmlspecialchars($address_detail) ?></textarea>
                    <span class="error-text" id="error-address_detail"></span>

                    <label>Mật khẩu <span class="required">*</span></label>
                    <input type="password" name="password" id="password" placeholder="Mật khẩu">
                    <span class="error-text" id="error-password"></span>

                    <label>Xác nhận mật khẩu<span class="required">*</span></label>
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Nhập lại mật khẩu">
                    <span class="error-text" id="error-confirm_password"></span>

                    <button type="submit" class="btn">Đăng Ký</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // DATA QUẬN HUYỆN MỞ RỘNG
        const districtsData = {
            "Hồ Chí Minh": ["Quận 1", "Quận 3", "Quận 10", "Bình Thạnh", "Tân Bình", "TP. Thủ Đức", "Huyện Hóc Môn"],
            "Hà Nội": ["Quận Ba Đình", "Quận Cầu Giấy", "Quận Đống Đa", "Quận Hai Bà Trưng", "Huyện Đông Anh"],
            "Đà Nẵng": ["Quận Hải Châu", "Quận Thanh Khê", "Quận Liên Chiểu", "Quận Ngũ Hành Sơn"],
            "Hải Phòng": ["Quận Hồng Bàng", "Quận Lê Chân", "Quận Ngô Quyền"],
            "Cần Thơ": ["Quận Ninh Kiều", "Quận Bình Thủy", "Quận Cái Răng"]
        };

        const citySelect = document.getElementById('city');
        const districtSelect = document.getElementById('district');

        // Logic thay đổi Quận theo Thành phố
        citySelect.addEventListener('change', function() {
            const city = this.value;
            districtSelect.innerHTML = '<option value="">Chọn Quận</option>';
            if (districtsData[city]) {
                districtsData[city].forEach(d => {
                    districtSelect.add(new Option(d, d));
                });
            }
            document.getElementById('error-city').innerText = "";
        });

        districtSelect.addEventListener('change', () => {
            document.getElementById('error-district').innerText = "";
        });

        // REGEX & VALIDATE RULES
        const nameRegex = /^[a-zA-ZÀ-ỹ\s]+$/;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const phoneRegex = /^0[0-9]{9}$/; // Bắt đầu bằng 0 và đủ 10 số

        function checkField(id, condition, message) {
            const input = document.getElementById(id);
            const error = document.getElementById('error-' + id);
            input.addEventListener('blur', () => {
                if (!condition(input.value)) {
                    input.classList.add('input-error');
                    error.innerText = message;
                } else {
                    input.classList.remove('input-error');
                    error.innerText = '';
                }
            });
        }

        checkField('ho', val => nameRegex.test(val), "Họ chỉ được chứa chữ");
        checkField('ten', val => nameRegex.test(val), "Tên chỉ được chứa chữ");
        checkField('email', val => emailRegex.test(val), "Email không hợp lệ");
        checkField('phone', val => phoneRegex.test(val), "SĐT phải bắt đầu bằng 0 (đủ 10 số)");
        checkField('address_detail', val => val.trim().length >= 5, "Địa chỉ tối thiểu 5 ký tự");
        checkField('password', val => val.length >= 5 && val.length <= 10, "Mật khẩu từ 5-10 ký tự");

        // CHẶN SUBMIT NẾU CÓ LỖI
        function validateOnSubmit() {
            let isValid = true;

            const fields = [
                { id: 'ho', cond: val => nameRegex.test(val), msg: "Họ chỉ chứa chữ" },
                { id: 'ten', cond: val => nameRegex.test(val), msg: "Tên chỉ chứa chữ" },
                { id: 'email', cond: val => emailRegex.test(val), msg: "Email không hợp lệ" },
                { id: 'phone', cond: val => phoneRegex.test(val), msg: "SĐT bắt đầu bằng 0 (10 số)" },
                { id: 'address_detail', cond: val => val.trim().length >= 5, msg: "Địa chỉ quá ngắn" },
                { id: 'password', cond: val => val.length >= 5 && val.length <= 10, msg: "Mật khẩu 5-10 ký tự" }
            ];

            fields.forEach(f => {
                const input = document.getElementById(f.id);
                const error = document.getElementById('error-' + f.id);
                if (!f.cond(input.value)) {
                    input.classList.add('input-error');
                    error.innerText = f.msg;
                    isValid = false;
                }
            });

            // Kiểm tra khớp mật khẩu
            const p = document.getElementById('password').value;
            const cp = document.getElementById('confirm_password');
            if (cp.value !== p || cp.value === "") {
                cp.classList.add('input-error');
                document.getElementById('error-confirm_password').innerText = "Mật khẩu không khớp";
                isValid = false;
            }

            // Kiểm tra chọn TP/Quận
            if (citySelect.value === "") {
                document.getElementById('error-city').innerText = "Vui lòng chọn TP";
                isValid = false;
            }
            if (districtSelect.value === "") {
                document.getElementById('error-district').innerText = "Vui lòng chọn Quận";
                isValid = false;
            }

            if (!isValid) {
                alert("Vui lòng sửa các vùng báo đỏ trước khi gửi!");
                return false;
            }
            return true;
        }
    </script>
</body>
</html>