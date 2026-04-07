<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // 1. FIX LỖI BIÊN BẢN: Đổi sang Prepared Statement an toàn tuyệt đối
    $stmt = mysqli_prepare($conn, "SELECT status FROM users WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $check_res = mysqli_stmt_get_result($stmt);
    
    if ($check_res && mysqli_num_rows($check_res) > 0) {
        $user_data = mysqli_fetch_assoc($check_res);
        if ($user_data['status'] === 'locked') {
            session_unset();
            session_destroy();
            echo "<script>
                alert('Tài khoản của bạn đã bị khóa bởi Quản trị viên. Vui lòng liên hệ CSKH!');
                window.location.href = '../pages/Dangnhap.php';
            </script>";
            exit();
        }
    }
    mysqli_stmt_close($stmt);
}
?>

<link rel="stylesheet" href="../css/header.css">

<header class="header">
    <div class="logo">
        <a href="../pages/Trangchu.php" style="text-decoration: none; display: flex; align-items: center; gap: 10px;">
            <img src="../images/logo-1.png" alt="Logo Chicken Joy"> 
            <span>Chicken Joy</span>
        </a>
    </div>
    
    <nav class="nav-icon-menu"> 
        <a href="../pages/Trangchu.php" class="nav-item">
            <img src="../images/Trangchu.png" alt="Trang chủ"> 
            <span class="nav-text">Trang chủ</span>
        </a>

        <a href="../pages/Thucdon.php" class="nav-item">
            <img src="../images/Thucdon.png" alt="Thực đơn"> 
            <span class="nav-text">Thực đơn</span>
        </a>

        <a href="<?php echo isset($_SESSION['user_id']) ? 'Donhang.php' : 'Dangnhap.php'; ?>" class="nav-item">
            <img src="../images/Donhang.png" alt="Đơn hàng"> 
            <span class="nav-text">Đơn hàng</span>
        </a>

        <a href="<?php echo isset($_SESSION['user_id']) ? 'Giohang.php' : 'Dangnhap.php'; ?>" class="nav-item">
            <img src="../images/Giohang.png" alt="Giỏ hàng">
            <span class="nav-text">Giỏ hàng</span>
        </a>

        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="../pages/Thongtincanhan.php" class="nav-item active">
                <img src="../images/Taikhoan.png" alt="Avatar"> 
                <span class="nav-text"><?php echo htmlspecialchars($_SESSION['user_fullname']); ?></span>
            </a>
            <a href="../pages/Logout.php" class="nav-item logout-btn">
                <img src="../images/logout.png" alt="logout">
                <span class="nav-text">Đăng xuất</span>
            </a>
        <?php else: ?>
            <a href="../pages/Dangnhap.php" class="nav-item">
                <img src="../images/dangnhap.png" alt="Login"> 
                <span class="nav-text">Đăng nhập</span>
            </a>
            <a href="../pages/Dangky.php" class="nav-item">
                <img src="../images/dangki.png" alt="Register" > 
                <span class="nav-text">Đăng ký</span>
            </a>
        <?php endif; ?>
    </nav>
</header>