<?php
require_once 'check_admin.php';
require_once '../config/database.php';

$current_admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 1; 
$error_msg = "";
$success_msg = "";
$show_modal = false;

if (isset($_SESSION['success_msg'])) {
    $success_msg = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']); 
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
    $name = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $raw_password = $_POST['password'];
    $role = isset($_POST['role']) ? $_POST['role'] : ''; 
    $address = "Chưa cập nhật";
    if (!preg_match('/^0[0-9]{9}$/', $phone)) {
        $error_msg = "Lỗi: Số điện thoại không hợp lệ (phải bắt đầu bằng số 0 và đủ 10 số)!";
        $show_modal = true;
    } elseif (strlen($raw_password) < 5 || strlen($raw_password) > 10) {
        $error_msg = "Lỗi: Mật khẩu bắt buộc phải từ 5 - 10 ký tự!";
        $show_modal = true;
    } else {
        $stmt_check = $conn->prepare("SELECT email, phone FROM users WHERE email = ? OR phone = ? LIMIT 1");
        $stmt_check->bind_param("ss", $email, $phone);
        $stmt_check->execute();
        $stmt_check->store_result();
        
        if ($stmt_check->num_rows > 0) {
            $stmt_check->bind_result($existing_email, $existing_phone);
            $stmt_check->fetch();
            
            if ($existing_email === $email) {
                $error_msg = "Lỗi: Email '$email' đã tồn tại trong hệ thống!";
            } else {
                $error_msg = "Lỗi: Số điện thoại '$phone' đã được sử dụng cho tài khoản khác!";
            }
            $show_modal = true; 
        } else {
            $hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users(fullname, email, phone, password, address, role) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $name, $email, $phone, $hashed_password, $address, $role);
            
            if($stmt->execute()){
                $_SESSION['success_msg'] = "Thêm người dùng mới thành công!";
                header("Location: user-management.php");
                exit();
            } else {
                $error_msg = "Lỗi CSDL: Không thể thêm người dùng!";
                $show_modal = true;
            }
            $stmt->close();
        }
        $stmt_check->close();
    }
}

$raw_search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search = $raw_search;

if (!empty($raw_search)) {
    $search_term = "%" . $raw_search . "%";
    $sql = "SELECT * FROM users WHERE fullname LIKE ? OR email LIKE ? OR phone LIKE ? ORDER BY id DESC";
    $stmt_search = $conn->prepare($sql);
    $stmt_search->bind_param("sss", $search_term, $search_term, $search_term);
    $stmt_search->execute();
    $result = $stmt_search->get_result();
} else {
    $sql = "SELECT * FROM users ORDER BY id DESC";
    $result = mysqli_query($conn, $sql);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="../assets/images/logo-1.png" />
    <title>ChickenJoy Admin | Quản Lý Người Dùng</title>
    <link rel="stylesheet" href="../assets/css/admin.css" />
    <style>
        /* CSS làm đẹp cho Modal thêm User */
        .modal {
            display: <?= $show_modal ? 'flex' : 'none' ?>; 
            position: fixed; z-index: 1000; left: 0; top: 0;
            width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);
            align-items: center; justify-content: center;
        }
        .modal-content {
            background-color: #fff; padding: 25px; border-radius: 8px;
            width: 450px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .btn-disabled {
            color: #999; font-size: 13px; font-style: italic; background: #f1f1f1;
            padding: 6px 12px; border-radius: 4px; display: inline-block; border: 1px dashed #ccc;
        }
    </style>
</head>

<body class="admin-body">

    <?php include 'layout/sidebar.php'; ?>
    
    <main class="main-content">
        <header class="main-header">
            <h1>Quản Lý Người Dùng</h1>
            <div class="header-actions">
                <button type="button" class="btn-primary" onclick="openModal()">+ Thêm người dùng</button>
            </div>
        </header>

        <?php if($success_msg != ""): ?>
            <p style="color: #28a745; font-weight: bold; margin-bottom: 15px;">✓ <?= $success_msg ?></p>
        <?php endif; ?>

        <div class="table-toolbar">
            <form method="GET" action="user-management.php" style="display: flex; gap: 10px; width: 100%;">
                <input type="text" name="search" placeholder="Tìm kiếm tên, email hoặc SĐT..." value="<?= htmlspecialchars($search) ?>" style="padding: 8px; flex: 1; border: 1px solid #ccc; border-radius: 4px; outline: none; font-family: inherit;"/>
                <button type="submit" class="btn-primary" style="padding: 8px 15px;">Tìm kiếm</button>
                <?php if($search != '') { ?>
                  <a href="user-management.php" class="btn-gray" style="padding: 8px 15px;">Hủy lọc</a>
                <?php } ?>
            </form>
        </div>

        <section class="table-section">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th>SĐT</th>
                    <th class="text-center">Quyền</th> 
                    <th class="text-center">Trạng thái</th>
                    <th style="text-align: center;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) { 
                ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><strong><?= htmlspecialchars($row['fullname']) ?></strong></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td class="text-center">
                        <?php if ($row['role'] == 'admin') { ?>
                            <span style="color: #e74c3c; font-weight: bold; background: #ffe3d6; padding: 4px 8px; border-radius: 4px; font-size: 12px;">Quản trị viên</span>
                        <?php } else { ?>
                            <span style="color: #555; background: #eee; padding: 4px 8px; border-radius: 4px; font-size: 12px;">Khách hàng</span>
                        <?php } ?>
                    </td>
                    <td class="text-center">
                        <?php if ($row['status'] == 'active') { ?>
                            <span class="status active">Hoạt động</span>
                        <?php } else { ?>
                            <span class="status hidden">Bị khóa</span>
                        <?php } ?>
                    </td>
                    <td>
                        <div class="actions" style="justify-content: center;">
                            <?php 
                            $can_modify = false;
                            
                            if ($current_admin_id != $row['id']) { 
                                if ($row['role'] == 'user') {
                                    $can_modify = true; 
                                }
                            }

                            if ($can_modify) {
                                if ($row['status'] == 'active') { ?>
                                    <a href="lock-user.php?id=<?= $row['id'] ?>" class="btn-gray" onclick="return confirm('Bạn có chắc muốn khóa tài khoản này?')">Khóa</a>
                                <?php } else { ?>
                                    <a href="unlock-user.php?id=<?= $row['id'] ?>" class="btn-primary" style="background-color: #28a745;" onclick="return confirm('Mở khóa cho tài khoản này?')">Mở khóa</a>
                                <?php } ?>
                                <a href="reset-password.php?id=<?= $row['id'] ?>" class="btn-edit" onclick="return confirm('Reset mật khẩu tài khoản này về [123456]?')">Reset MK</a>
                            <?php } else { 
                                if ($current_admin_id == $row['id']) {
                                    echo "<span class='btn-disabled'>(Đang đăng nhập)</span>";
                                } else {
                                    echo "<span class='btn-disabled'>Không đủ quyền</span>";
                                }
                            } ?>
                        </div>
                    </td>
                </tr>
                <?php 
                    } 
                } else {
                    echo "<tr><td colspan='7' style='text-align:center; padding: 20px;'>Không tìm thấy người dùng nào.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        </section>
    </main>

    <div id="userModal" class="modal">
        <div class="modal-content">
            <h2 style="margin-top: 0; margin-bottom: 20px; font-size: 20px; border-bottom: 2px solid #ffe3d6; padding-bottom: 10px;">Thêm người dùng mới</h2>
            
            <?php if($error_msg != ""): ?>
                <p style="color: #e74c3c; font-size: 14px; font-weight: bold; margin-bottom: 15px;"><?= $error_msg ?></p>
            <?php endif; ?>

            <form method="POST" id="user-form" novalidate>
                <div class="form-group" style="margin-bottom: 12px;">
                    <label style="font-size: 13px; margin-bottom: 5px;">Họ và tên:</label>
                    <input type="text" name="fullname" class="form-input req-input" value="<?= isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : '' ?>" required>
                </div>
                
                <div class="form-group" style="margin-bottom: 12px;">
                    <label style="font-size: 13px; margin-bottom: 5px;">Email:</label>
                    <input type="email" name="email" class="form-input req-input" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                </div>
                
                <div class="form-group" style="margin-bottom: 12px;">
                    <label style="font-size: 13px; margin-bottom: 5px;">Số điện thoại:</label>
                    <input type="text" name="phone" class="form-input req-input" value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>" required>
                </div>
                
                <div style="display: flex; gap: 15px; margin-bottom: 12px;">
                    <div class="form-group" style="flex: 1; margin-bottom: 0;">
                        <label style="font-size: 13px; margin-bottom: 5px;">Quyền hạn:</label>
                        <select name="role" class="form-input req-input" required>
                            <option value="">-- Chọn quyền --</option>
                            <option value="user" <?= (isset($_POST['role']) && $_POST['role'] == 'user') ? 'selected' : '' ?>>Khách hàng</option>
                            <option value="admin" <?= (isset($_POST['role']) && $_POST['role'] == 'admin') ? 'selected' : '' ?>>Quản trị viên</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1; margin-bottom: 0;">
                        <label style="font-size: 13px; margin-bottom: 5px;">Mật khẩu:</label>
                        <input type="password" name="password" class="form-input req-input" required>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" name="add_user" class="btn-primary" style="flex: 1;">Xác nhận thêm</button>
                    <button type="button" onclick="closeModal()" class="btn-gray" style="flex: 1; text-align: center;">Hủy bỏ</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openModal() {
        document.getElementById("userModal").style.display = "flex";
    }

    function closeModal() {
        document.getElementById("userModal").style.display = "none";
        document.querySelectorAll('.req-input').forEach(el => el.style.borderColor = "#ccc");
    }
    
    window.onclick = function(event) {
        var modal = document.getElementById("userModal");
        if (event.target == modal) closeModal();
    }
    document.querySelectorAll('.req-input').forEach(input => {
        input.addEventListener('input', function() { this.style.borderColor = "#ccc"; });
        input.addEventListener('change', function() { this.style.borderColor = "#ccc"; });
    });

    document.getElementById('user-form').addEventListener('submit', function(e) {
        let hasError = false;
        
        document.querySelectorAll('.req-input').forEach(el => {
            el.style.borderColor = "#ccc";
            if (el.value.trim() === "") {
                hasError = true;
                el.style.borderColor = "red";
            }
        });

        if (hasError) e.preventDefault();
    });
    </script>
</body>
</html>