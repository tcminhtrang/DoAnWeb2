<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../config/database.php'; 

$error = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sql = "SELECT * FROM users WHERE email = '$email' AND role = 'user'";
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id']; 
            $_SESSION['user_fullname'] = $user['fullname']; 
            header("Location: Trangchu.php");
            exit();
        } else {
            $error = "Mật khẩu không chính xác!";
        }
    } else {
        $error = "Email này chưa được đăng ký!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Chicken Joy - Đăng nhập</title>
  <link rel="stylesheet" href="../css/Dangnhap.css" />
</head>
<body>
  
  <div class="container">
    
    <div class="login-box" style="position: relative;">
      
      <div style="position: absolute; top: 20px; left: 20px;">
          <a href="javascript:window.history.back();" style="color: #f97407; text-decoration: none; font-size: 14px; font-weight: bold; transition: 0.3s;" onmouseover="this.style.color='#e66a06'" onmouseout="this.style.color='#f97407'">&larr; Quay lại</a>
      </div>

      <div class="login-content">

        <div class="logo">
          <div class="logo-icon">
            <img src="../images/logo-1.png" alt="Logo Chicken Joy">
          </div>
          <h2>Chicken Joy</h2>
        </div>

        <?php if($error != ""): ?>
            <div class="error-message" style="color: #d9534f; background: #f2dede; padding: 12px; border: 1px solid #ebccd1; border-radius: 4px; text-align: center; margin-bottom: 20px; font-size: 14px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="Dangnhap.php" method="POST">
          <label for="email">Email</label>
          <div class="input-box">
            <input type="email" id="email" name="email" placeholder="Nhập email của bạn" required 
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
          </div>

          <label for="password">Mật khẩu</label>
          <div class="input-box">
            <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required />
          </div>
          <br><br>
          <button type="submit" class="btn">Đăng nhập</button>

          <p class="signup">Chưa có tài khoản? <a href="../pages/Dangky.php">Đăng ký ngay</a></p>
        </form>
      </div>
    </div>

    <div class="info-box">
      <div class="foods">
        <div class="food"><img src="../images/ga-ran-1.jpg" alt="Gà Rán Giòn"><p>Gà Rán Giòn</p></div>
        <div class="food"><img src="../images/hamburger-1.jpg" alt="Hamburger"><p>Hamburger</p></div>
        <div class="food"><img src="../images/mi-y-1.jpg" alt="Mì Ý"><p>Mì Ý</p></div>
        <div class="food"><img src="../images/khoai-tay-1.jpg" alt="Khoai Tây"><p>Khoai Tây</p></div>
      </div>
      <div class="welcome">
        <h1>Chào mừng đến với Chicken Joy!</h1>
        <p>Thưởng thức những món ăn ngon nhất từ gà rán giòn tan, hamburger thơm ngon đến mì Ý đậm đà hương vị.</p>
      </div>
    </div>
  </div>
</body>
</html>