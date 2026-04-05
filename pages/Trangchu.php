<?php 
// 1. Khởi tạo session và kết nối DB
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../config/database.php'; 


$sql_featured = "SELECT * FROM products WHERE category LIKE '%Gà%' OR product_name LIKE '%Gà%' ORDER BY id DESC LIMIT 4";
$result_featured = mysqli_query($conn, $sql_featured);

// 3. Xử lý thêm vào giỏ hàng (Giữ nguyên logic của bạn)
if (isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        echo "<script>alert('Bạn cần đăng nhập để mua hàng!'); window.location.href='Dangnhap.php';</script>";
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];
    
    $check_cart = "SELECT * FROM cart WHERE user_id = $user_id AND product_id = $product_id";
    $res_check = mysqli_query($conn, $check_cart);

    if (mysqli_num_rows($res_check) > 0) {
        $sql_action = "UPDATE cart SET quantity = quantity + 1 WHERE user_id = $user_id AND product_id = $product_id";
    } else {
        $sql_action = "INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, 1)";
    }
    
    if (mysqli_query($conn, $sql_action)) {
        echo "<script>alert('Đã thêm món gà vào giỏ hàng!'); window.location.href='Trangchu.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chicken Joy - Món ăn nhanh ngon nhất</title>
    <link rel="stylesheet" href="../css/Trangchu.css"> 
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
            <p>Các món gà được yêu thích nhất</p>
            <div class="products-grid">
                
                <?php if ($result_featured && mysqli_num_rows($result_featured) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result_featured)): ?>
                    <div class="product-card">
                        <a href="Chitietmonan.php?id=<?php echo $row['id']; ?>" style="text-decoration: none; color: inherit;">
                            <img src="../images/<?php echo $row['image']; ?>" alt="<?php echo $row['product_name']; ?>">
                            <h3><?php echo $row['product_name']; ?></h3>
                            <p><?php echo $row['description']; ?></p>
                        </a>
                        <div class="product-info">
                            <span class="price"><?php echo number_format($row['price'], 0, ',', '.'); ?>đ</span>
                            
                            <form method="POST" action="Trangchu.php" style="display: inline;">
                                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="add_to_cart" class="add-to-cart-btn btn-buy-now">+</button>
                            </form>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="grid-column: 1/-1; text-align: center;">Hiện chưa có món gà nào nổi bật.</p>
                <?php endif; ?>

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
            <button class="btn btn-white btn-buy-now" onclick="window.location.href='Thucdon.php'">Đặt hàng ngay</button>
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
            </div>
    </footer>

    <script>
        const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

        document.querySelectorAll('.btn-buy-now, .add-to-cart-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                if (!isLoggedIn) {
                    e.preventDefault(); 
                    alert("Bạn cần Đăng nhập để thực hiện mua hàng!");
                    window.location.href = "Dangnhap.php";
                }
            });
        });
    </script>
</body>
</html>