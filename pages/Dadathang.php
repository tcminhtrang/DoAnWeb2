<?php
session_start();
require_once '../config/database.php';

// Kiểm tra nếu không có order_id trên URL thì quay về thực đơn
if (!isset($_GET['order_id'])) {
    header("Location: Thucdon.php");
    exit();
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['user_id'];

// 1. Lấy thông tin chung của đơn hàng
$order_query = mysqli_query($conn, "SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id");
$order = mysqli_fetch_assoc($order_query);

if (!$order) {
    echo "Không tìm thấy thông tin đơn hàng!";
    exit();
}

// 2. Lấy danh sách sản phẩm
$details_query = mysqli_query($conn, "SELECT od.*, p.product_name, p.image 
                                     FROM order_details od 
                                     JOIN products p ON od.product_id = p.id 
                                     WHERE od.order_id = $order_id");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Hàng Thành Công - Chicken Joy</title>
    <link rel="stylesheet" href="../css/Dadathang.css"> 
</head>
<body>
    
   <?php include '../includes/header.php'; ?>
        
    <div class="container">
        <div class="success-container">
            <img src="../images/accept.png" alt="Thành công" class="success-icon">
            <div class="success-message">
                <h2>ĐẶT HÀNG THÀNH CÔNG!</h2>
                <p>Cảm ơn bạn đã đặt hàng. Đơn hàng của bạn đang được chế biến và sẽ sớm được giao.</p>
            </div>
    
            <div class="order-card status-<?php echo $order['status']; ?>">
                <div class="order-header">
                    <span class="order-id">#DH<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></span>
                    
                    <span class="status-tag <?php echo $order['status']; ?>">
                        <?php 
                            $status_map = [
                                'pending' => 'Chờ duyệt',
                                'processing' => 'Đang xử lý',
                                'shipped' => 'Đang giao',
                                'delivered' => 'Đã giao',
                                'cancelled' => 'Đã hủy'
                            ];
                            echo $status_map[$order['status']] ?? 'Đang xử lý';
                        ?>
                    </span>
                    
                    <span class="order-date">Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></span>
                </div>
                
                <div class="order-body">
                    <div class="products-info">
                        <h3>Sản phẩm đã đặt:</h3>
                        
                        <?php while($item = mysqli_fetch_assoc($details_query)): ?>
                        <div class="product-item">
                            <img src="../images/<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                            <div class="product-details">
                                <p><?php echo htmlspecialchars($item['product_name']); ?></p>
                                <p class="quantity">Số lượng: <?php echo $item['quantity']; ?></p>
                            </div>
                            <span class="product-price"><?php echo number_format($item['price_at_purchase'] * $item['quantity'], 0, ',', '.'); ?>đ</span>
                        </div>
                        <?php endwhile; ?>
                        
                    </div>

                    <div class="shipping-info">
                        <h3>Thông tin giao hàng:</h3>
                        <p><strong>Người nhận:</strong> <?php echo htmlspecialchars($order['receiver_name']); ?></p>
                        <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
                        <p><strong>SĐT:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                        <p><strong>PTTT:</strong> <?php echo strtoupper($order['payment_method']); ?></p>
                        
                        <?php if(!empty($order['order_note'])): ?>
                            <p><strong>Ghi chú:</strong> <em><?php echo htmlspecialchars($order['order_note']); ?></em></p>
                        <?php endif; ?>

                        <p class="total-highlight"><strong>TỔNG TIỀN: <span><?php echo number_format($order['total_price'], 0, ',', '.'); ?>đ</span></strong></p>
                    </div>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="../pages/Thucdon.php" class="back-home-link">Tiếp tục mua hàng</a>
                </div>
        </div>
    </div>
</body>
</html>