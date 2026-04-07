<?php
session_start();
include '../config/database.php';
if (!isset($_SESSION['user_id'])) { header("Location: Dangnhap.php"); exit(); }

$user_id = $_SESSION['user_id'];
$sql = "SELECT p.*, c.quantity, c.product_id FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = $user_id";
$result = mysqli_query($conn, $sql);
$items_count = mysqli_num_rows($result);

// 1. CHUẨN BỊ DỮ LIỆU TỪ SỚM
$total_money = 0;
$cart_items = [];

if ($items_count > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $cart_items[] = $row; // Đẩy từng dòng dữ liệu vào mảng
        $total_money += $row['price'] * $row['quantity']; // Tính luôn tổng tiền
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng - Chicken Joy</title>
    <link rel="stylesheet" href="../css/Giohang.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="container">
        <div class="cart-column">
            <div class="cart-box">
                <h2>Giỏ hàng của bạn</h2>
                <p>Bạn có <?php echo $items_count; ?> sản phẩm trong giỏ hàng</p>
                
                <?php 
                // 2. DUYỆT MẢNG LẦN 1: IN DANH SÁCH GIỎ HÀNG
                if (!empty($cart_items)):
                    foreach($cart_items as $row): 
                        $subtotal = $row['price'] * $row['quantity'];
                        
                        // Kiểm tra tồn kho của món này
                        $out_of_stock = ($row['stock'] <= 0);
                        $exceed_stock = ($row['quantity'] > $row['stock'] && $row['stock'] > 0);
                ?>
                <div class="cart-item" <?php echo $out_of_stock ? 'style="opacity: 0.6;"' : ''; ?>>
                    <img src="../images/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>" class="item-image">
                    <div class="item-details">
                        <div class="item-name-desc">
                            <p class="item-name">
                                <?php echo htmlspecialchars($row['product_name']); ?>
                                <?php if($out_of_stock): ?>
                                    <span style="color: red; font-size: 12px; margin-left: 10px; font-weight: bold;">(Đã hết hàng)</span>
                                <?php endif; ?>
                            </p>
                            <p class="item-desc"><?php echo htmlspecialchars($row['description']); ?></p>
                            
                            <?php if($exceed_stock): ?>
                                <p style="color: #e74c3c; font-size: 12px; margin-top: 5px; font-weight: bold;">* Chỉ còn <?php echo $row['stock']; ?> phần trong kho</p>
                            <?php endif; ?>
                        </div>
                        <div class="item-quantity">
                            <?php if(!$out_of_stock): ?>
                                <button class="update-qty" data-id="<?php echo $row['product_id']; ?>" data-action="minus">-</button>
                                <span><?php echo $row['quantity']; ?></span>
                                <button class="update-qty" data-id="<?php echo $row['product_id']; ?>" data-action="plus">+</button>
                            <?php else: ?>
                                <span><?php echo $row['quantity']; ?></span>
                            <?php endif; ?>
                            <span class="delete-item delete-icon" data-id="<?php echo $row['product_id']; ?>" style="cursor:pointer; margin-left:15px;">🗑️</span>
                        </div>
                    </div>
                    <div class="item-price-info">
                        <p class="old-price"><?php echo number_format($row['price'] * 1.2, 0, ',', '.'); ?>đ</p>
                        <p class="current-price"><?php echo number_format($subtotal, 0, ',', '.'); ?>đ</p>
                    </div>
                </div>
                <hr>
                <?php 
                    endforeach; 
                endif;
                ?>

                <div class="cart-actions">
                    <a href="Thucdon.php" class="continue-shopping"> Tiếp tục mua sắm</a>
                    <a href="#" class="clear-cart">🗑️ Xóa tất cả</a>
                </div>
            </div>
        </div>
        
        <div class="summary-column">
            <div class="summary-box" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <h3 style="border-bottom: 2px solid #ca2510; padding-bottom: 10px; margin-bottom: 15px;">Tóm tắt đơn hàng</h3>
                
                <div class="summary-details" style="margin-bottom: 15px; max-height: 200px; overflow-y: auto;">
                    <?php 
                    // 3. DUYỆT MẢNG LẦN 2: IN TÓM TẮT ĐƠN HÀNG
                    if (!empty($cart_items)):
                        foreach($cart_items as $item): 
                    ?>
                        <div class="summary-item-row" style="display: flex; justify-content: space-between; font-size: 0.95em; margin-bottom: 8px; color: #333;">
                            <span style="flex: 1; padding-right: 10px;">
                                <strong><?php echo htmlspecialchars($item['product_name']); ?></strong> 
                                <small style="color: #666;">(x<?php echo $item['quantity']; ?>)</small>
                            </span>
                            <span style="font-weight: 500;">
                                <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>đ
                            </span>
                        </div>
                    <?php 
                        endforeach; 
                    endif;
                    ?>
                </div>

                <div class="summary-total" style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                    <span style="font-size: 1.1em; font-weight: bold;">Tổng thanh toán:</span>
                    <span class="total-value" style="color: #ca2510; font-size: 1.5em; font-weight: 800;">
                        <?php echo number_format($total_money, 0, ',', '.'); ?>đ
                    </span>
                </div>

                <p style="font-size: 0.85em; color: #28a745; margin-top: 5px; font-style: italic;">
                    * Miễn phí giao hàng cho mọi đơn hàng.
                </p>

                <a href="Thanhtoan.php" style="text-decoration: none;">
                    <button onclick="goToCheckout()" class="checkout-btn" style="width: 100%; padding: 12px; background: #ca2510; color: #fff; border: none; border-radius: 5px; font-size: 1.1em; font-weight: bold; cursor: pointer; margin-top: 15px;">
                        THANH TOÁN
                    </button>
                </a>
            </div>
        </div>
    </main>
    <script>const isLoggedIn = true;</script>
    <script src="../js/main.js"></script>
</body>
</html>