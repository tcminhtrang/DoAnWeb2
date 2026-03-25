<?php
session_start();
require_once '../config/database.php';

if (isset($_POST['add_user'])) {
    $name = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $raw_password = $_POST['password'];
    $role = $_POST['role']; 
    $address = "Chưa cập nhật";
    $hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users(fullname, email, phone, password, address, role) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $email, $phone, $hashed_password, $address, $role);
    $stmt->execute();
    $stmt->close();

    header("Location: user-management.php");
    exit();
}

$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}

if (!empty($search)) {
    $sql = "SELECT o.*, u.fullname 
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE $where
        ORDER BY o.id DESC";
} else {
    $sql = "SELECT * FROM users ORDER BY id DESC";
}
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="../assets/images/logo-1.png" />
    <title> ChickenJoy Admin | Quản Lý Người Dùng</title>
    <link rel="stylesheet" href="../assets/css/admin.css" />
    <style>
        .modal-content select {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-family: inherit;
        }
    </style>
</head>

<body class="admin-body">

    <?php include 'layout/sidebar.php'; ?>
    
    <main class="main-content">
        <header class="main-header">
            <h1>Quản Lý Người Dùng</h1>
            <div class="header-actions">
                <a href="#" class="btn-primary" onclick="openModal()">+ Thêm người dùng</a>
            </div>
        </header>

        <div class="table-toolbar">
            <form method="GET" action="user-management.php" style="width: 100%;">
                <input type="text" name="search" placeholder="🔍 Tìm kiếm tên, email hoặc SĐT..."
                    value="<?= htmlspecialchars($search) ?>" />
                <button type="submit" style="display: none;">Tìm kiếm</button>
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
                    <th>Quyền</th> <th>Trạng thái</th>
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
                    <td>
                        <?php if ($row['role'] == 'admin') { ?>
                            <span style="color: #ff6b35; font-weight: bold;">Quản trị viên</span>
                        <?php } else { ?>
                            <span>Khách hàng</span>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if ($row['status'] == 'active') { ?>
                            <span class="status active">Hoạt động</span>
                        <?php } else { ?>
                            <span class="status hidden">Bị khóa</span>
                        <?php } ?>
                    </td>
                    <td>
                        <div class="actions">
                            <?php if ($row['status'] == 'active') { ?>
                                <a href="lock-user.php?id=<?= $row['id'] ?>" class="btn-hide">Khóa</a>
                            <?php } else { ?>
                                <a href="unlock-user.php?id=<?= $row['id'] ?>" class="btn-show">Mở khóa</a>
                            <?php } ?>
                            <a href="reset-password.php?id=<?= $row['id'] ?>" class="btn-edit">Reset</a>
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
            <h2>Thêm người dùng mới</h2>
            <form method="POST">
                <input type="text" name="fullname" placeholder="Họ tên" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="phone" placeholder="Số điện thoại" required>
                
                <select name="role" required>
                    <option value="" disabled selected>-- Chọn quyền hạn --</option>
                    <option value="user">Khách hàng</option>
                    <option value="admin">Quản trị viên</option>
                </select>

                <input type="password" name="password" placeholder="Mật khẩu" required>
                
                <div style="display: flex; gap: 10px; margin-top: 15px;">
                    <button type="submit" name="add_user" style="flex: 1;">Thêm mới</button>
                    <button type="button" onclick="closeModal()" style="flex: 1; background: #6c757d;">Hủy</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openModal() {
        document.getElementById("userModal").style.display = "block";
    }

    function closeModal() {
        document.getElementById("userModal").style.display = "none";
    }
    
    window.onclick = function(event) {
        var modal = document.getElementById("userModal");
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
    </script>
</body>

</html>