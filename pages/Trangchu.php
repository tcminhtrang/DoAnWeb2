<?php 
// 1. Khởi tạo session để kiểm tra đăng nhập
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chicken Joy - Món ăn nhanh ngon nhất</title>
    <link rel="stylesheet" href="../css/Trangchu.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <section class="hero-section">
        <div class="container hero-content">
            <div class="hero-text">
                <h1>Thức ăn nhanh ngon nhất thành phố</h1>
                <p>Giao hàng nhanh chóng, chất lượng tuyệt vời, giá cả hợp lý. Đặt ngay để thưởng thức!</p>
                <div class="hero-buttons">
                    <button onclick="window.location.href='Thucdon.php'" class="btn btn-primary">Đặt hàng ngay</button>
                </div>
            </div>
            <div class="hero-image">
                <img src="../images/ga-ran-1.jpg" alt="Đĩa gà rán giòn ngon" class="main-chicken-img">
            </div>
        </div>
    </section>

    <section class="section product-categories-section">
        <div class="container">
            <h2>Danh mục sản phẩm</h2>
            <p>Khám phá các món ăn tuyệt vời của chúng tôi</p>
            <div class="categories-grid">
                <div class="category-card">
                    <span class="category-icon">🍗</span>
                    <h3>Gà rán</h3>
                    <p>Gà rán giòn tan, thơm ngon</p>
                </div>
                <div class="category-card">
                    <span class="category-icon">🍔</span>
                    <h3>Hamburger</h3>
                    <p>Burger thịt bò tươi ngon</p>
                </div>
                <div class="category-card">
                    <span class="category-icon">🍝</span>
                    <h3>Mì Ý</h3>
                    <p>Mì Ý xốt đậm đà hương vị</p>
                </div>
                <div class="category-card">
                    <span class="category-icon">🥤</span>
                    <h3>Nước uống</h3>
                    <p>Đồ uống tươi mát, đa dạng</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section featured-products-section">
        <div class="container">
            <h2>Sản phẩm nổi bật</h2>
            <p>Những món ăn được yêu thích nhất</p>
            <div class="products-grid">
                
                <div class="product-card">
                    <a href="../pages/Chitietmonan.php">
                        <img src="../images/ga-ran-1.jpg" alt="Gà rán giòn tan">
                        <h3>Gà rán giòn tan</h3>
                        <p>Gà tươi tẩm gia vị đặc biệt</p>
                    </a>
                    <div class="product-info">
                        <span class="price">89.000đ</span>
                        <button class="add-to-cart-btn btn-buy-now">+</button>
                    </div>
                </div>

                <div class="product-card">
                    <a href="../pages/Chitietmonan.php">
                        <img src="../images/hamburger-1.jpg" alt="Burger bò phô mai">
                        <h3>Burger bò phô mai</h3>
                        <p>Thịt bò 100% kèm phô mai</p>
                    </a>
                    <div class="product-info">
                        <span class="price">129.000đ</span>
                        <button class="add-to-cart-btn btn-buy-now">+</button>
                    </div>
                </div>

                <div class="product-card">
                    <a href="../pages/Chitietmonan.php">
                        <img src="../images/mi-y-1.jpg" alt="Mì Ý xốt cà chua">
                        <h3>Mì Ý xốt cà chua</h3>
                        <p>Mì Ý truyền thống Ý</p>
                    </a>
                    <div class="product-info">
                        <span class="price">99.000đ</span>
                        <button class="add-to-cart-btn btn-buy-now">+</button>
                    </div>
                </div>
                
                <div class="product-card">
                    <a href="../pages/Chitietmonan.php">
                        <img src="../images/khoai-tay-1.jpg" alt="Khoai tây chiên">
                        <h3>Khoai tây chiên</h3>
                        <p>Khoai tây vàng giòn ngon</p>
                    </a>
                    <div class="product-info">
                        <span class="price">39.000đ</span>
                        <button class="add-to-cart-btn btn-buy-now">+</button>
                    </div>
                </div>

            </div>
            <div class="center-button">
                 <button onclick="window.location.href='Thucdon.php'" class="btn btn-secondary view-all-btn">Xem tất cả sản phẩm</button>
            </div>
        </div>
    </section>

    <section class="promotion-banner">
        <div class="container promotion-content">
            <h2>Khuyến mãi đặc biệt</h2>
            <p>Giảm 30% cho đơn hàng từ 200.000đ</p>
            <button class="btn btn-white btn-buy-now">Đặt hàng ngay</button>
        </div>
    </section>

    <footer class="footer">
        <div class="container footer-content">
            <div class="footer-block brand-info">
                <div class="logo footer-logo">
                    <img src="../images/logo-1.png" alt="Chicken Joy Logo" class="footer-logo-img">
                    ChickenJoy
                </div>
                <p>Thực đơn phong phú, giao hàng nhanh, chỉ trong 30 phút.</p>
            </div>

            <div class="footer-block">
                <h3>Menu</h3>
                <ul>
                    <li><a href="#">Gà rán</a></li>
                    <li><a href="#">Hamburger</a></li>
                    <li><a href="#">Mì Ý</a></li>
                    <li><a href="#">Nước uống</a></li>
                </ul>
            </div>

            <div class="footer-block">
                <h3>Hỗ trợ</h3>
                <ul>
                    <li><a href="#">Liên hệ</a></li>
                    <li><a href="#">Chính sách giao hàng</a></li>
                    <li><a href="#">Cách thức đặt hàng</a></li>
                    <li><a href="#">FAQ</a></li>
                </ul>
            </div>

            <div class="footer-block contact-info">
                <h3>Liên hệ</h3>
                <p>☎️ 0987 654 321</p>
                <p>✉️ info@chickenjoy.com</p>
                <p>📍 123 Đường ABC, Quận 1, TP.HCM</p>
            </div>
        </div>
    </footer>

    <script>
        // Lấy biến đăng nhập từ PHP
        const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

        // Lắng nghe sự kiện click trên tất cả các nút có class .btn-buy-now hoặc .add-to-cart-btn
        document.querySelectorAll('.btn-buy-now, .add-to-cart-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                if (!isLoggedIn) {
                    e.preventDefault();
                    alert("Bạn cần Đăng nhập để thực hiện mua hàng!");
                    window.location.href = "Dangnhap.php";
                } else {
                    // Nếu là nút + (add-to-cart-btn) thì thông báo thêm giỏ hàng
                    if(this.classList.contains('add-to-cart-btn')) {
                        alert("Đã thêm món ăn vào giỏ hàng!");
                    }
                }
            });
        });
    </script>
</body>
</html>