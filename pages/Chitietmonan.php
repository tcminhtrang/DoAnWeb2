<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include '../config/database.php'; 

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$sql = "SELECT * FROM products WHERE id = $id";
$result = mysqli_query($conn, $sql);
$product = mysqli_fetch_assoc($result);

if (!$product) { header("Location: Thucdon.php"); exit(); }

$cat = $product['category'];
$sql_related = "SELECT * FROM products WHERE category = '$cat' AND id != $id LIMIT 4";
$result_related = mysqli_query($conn, $sql_related);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['product_name']; ?> | Chicken Joy</title>
    <link rel="stylesheet" href="../css/Chitietmonan.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="container">
        <section class="product-detail">
            <div class="product-images">
                <div class="main-image">
                    <img src="../images/<?php echo $product['image']; ?>" alt="<?php echo $product['product_name']; ?>">
                </div>
                <div class="thumbnail-images">
                    <img src="../images/<?php echo $product['image']; ?>" alt="Ảnh 1">
                    <img src="../images/ga-ran-a1.jpg" alt="Mẫu">
                    <img src="../images/ga-ran-a2.jpg" alt="Mẫu">
                    <img src="../images/ga-ran-a3.jpg" alt="Mẫu">
                </div>
            </div>

            <div class="product-info">
                <h1 class="product-title"><?php echo $product['product_name']; ?></h1>
                <div class="ratings">
                    <span class="star" style="color: #ffc107;">★★★★☆</span> (4.2) | 150 đánh giá | Còn hàng
                </div>
                
                <div class="price-section">
                    <span class="current-price"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</span>
                    <span class="old-price"><?php echo number_format($product['price'] * 1.2, 0, ',', '.'); ?>đ</span>
                    <span class="discount">-20%</span>
                </div>
                
                <div class="description-box">
                    <h2>Mô tả sản phẩm</h2>
                    <p><?php echo $product['description']; ?></p>
                </div>

                <div class="nutrition-info">
                    <h2 style="color: #e91e63; border-bottom: 2px solid #f8f8f8; padding-bottom: 10px;">
                        <i class="fas fa-leaf"></i> Thông tin dinh dưỡng
                    </h2>
                    <div class="nutri-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 15px;">
                        <div class="nutri-item"><strong>Năng lượng:</strong> <?php echo $product['calories']; ?> kcal</div>
                        <div class="nutri-item"><strong>Chất đạm:</strong> <?php echo $product['protein']; ?>g</div>
                        <div class="nutri-item"><strong>Tinh bột:</strong> <?php echo $product['carbs']; ?>g</div>
                        <div class="nutri-item"><strong>Chất béo:</strong> <?php echo $product['fat']; ?>g</div>
                    </div>
                </div>

                <div class="order-section">
                    <div class="quantity-control">
                        <label for="quantity">Số lượng</label>
                        <div class="quantity-input">
                            <button type="button" class="btn-qty" data-type="minus">-</button>
                            <input type="text" id="quantity" value="1" readonly>
                            <button type="button" class="btn-qty" data-type="plus">+</button>
                        </div>
                    </div>
                    <div class="action-buttons">
                        <button class="add-to-cart" id="addToCartBtn" data-id="<?php echo $id; ?>">Thêm vào giỏ hàng</button>
                    </div>
                </div>
                
                <div class="delivery-info">
                    <p><i class="fas fa-truck"></i> Miễn phí giao hàng cho đơn hàng trên 200.000đ</p>
                    <p><i class="fas fa-clock"></i> Giao hàng trong 30 phút</p>
                    <p><i class="fas fa-medal"></i> Đảm bảo chất lượng 100%</p>
                </div>
            </div>
        </section>

        <section class="related-products">
            <h2>Sản phẩm tương tự</h2>
            <div class="product-list">
                <?php while($item = mysqli_fetch_assoc($result_related)): ?>
                <a href="Chitietmonan.php?id=<?php echo $item['id']; ?>">
                    <div class="product-card">
                        <img src="../images/<?php echo $item['image']; ?>" alt="<?php echo $item['product_name']; ?>">
                        <h3><?php echo $item['product_name']; ?></h3>
                        <p><?php echo number_format($item['price'], 0, ',', '.'); ?>đ</p>
                        <button class="add-to-cart-sm">Xem chi tiết</button>
                    </div>
                </a>
                <?php endwhile; ?>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="footer-columns container">
            <div class="col footer-info">
                <h4>Chicken Joy</h4>
                <p>Thức ăn nhanh chất lượng cao, giao hàng tận nơi trong 30 phút.</p>
            </div>
            <div class="col"><h4>Menu</h4><ul><li>Gà rán</li><li>Hamburger</li><li>Nước uống</li></ul></div>
            <div class="col"><h4>Hỗ trợ</h4><ul><li>Chính sách giao hàng</li><li>Chính sách đổi trả</li><li>FAQ</li></ul></div>
            <div class="col"><h4>Liên hệ</h4><p>Điện thoại: 0123.456.789</p><p>Email: info@fastfoodhub.com</p></div>
        </div>
    </footer>

    <script>
        const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    </script>
    <script src="../js/main.js"></script>
</body>
</html>