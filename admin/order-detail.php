<?php
require_once 'check_admin.php';
require_once '../config/database.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$sql = "SELECT o.*, u.fullname 
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE o.id = $id";
$order = mysqli_fetch_assoc(mysqli_query($conn, $sql));
if (!$order) {
    echo "<h2>Không tìm thấy đơn hàng</h2>";
    exit;
}
$sqlDetail = "SELECT od.*, p.product_name, p.image
              FROM order_details od
              JOIN products p ON od.product_id = p.id
              WHERE od.order_id = $id";
$details = mysqli_query($conn, $sqlDetail) or die(mysqli_error($conn));
$paymentMethod = "Không xác định";
if ($order['payment_method'] == 'cod') $paymentMethod = "Thanh toán khi nhận hàng (COD)";
if ($order['payment_method'] == 'banking') $paymentMethod = "Chuyển khoản ngân hàng";
if ($order['payment_method'] == 'online') $paymentMethod = "Thanh toán Online";
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>ChickenJoy Admin | Chi Tiết Đơn Hàng</title>

    <link rel="icon" type="image/png" href="../assets/images/logo-1.png" />
    <link rel="stylesheet" href="../assets/css/admin.css" />
    
    <style>
        .order-header-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #ffe3d6;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .order-header-info h2 { margin-bottom: 5px; color: #333; }
        .order-grid {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
        }
        .info-box {
            flex: 1;
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #ffe3d6;
        }
        .info-box h3 {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
            font-size: 16px;
        }
        .info-box p { margin-bottom: 8px; font-size: 14px; }
        .order-summary {
            text-align: right;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 2px dashed #ffe3d6;
            font-size: 15px;
        }
        .order-summary p { margin-bottom: 8px; color: #555; }
        .order-summary .total-price {
            font-size: 22px;
            color: var(--main-color);
            margin-top: 10px;
        }
        .product-img-mini {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
            vertical-align: middle;
            margin-right: 10px;
        }
    </style>
</head>

<body class="admin-body">

    <?php include 'layout/sidebar.php'; ?>

    <main class="main-content">

        <header class="main-header">
            <h1>Chi Tiết Đơn Hàng</h1>
        </header>

        <a href="order-management.php" class="back-btn">
            &larr; Quay lại
        </a>
        
        <div class="detail-card">
            
            <div class="order-header-info">
                <div>
                    <h2>Mã đơn: DH<?= str_pad($order['id'], 3, '0', STR_PAD_LEFT) ?></h2>
                    <p style="color: #666;">Ngày đặt: <?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></p>
                </div>
                <div>
                    <?php
                    $class = "";
                    switch ($order['status']) {
                        case 'pending': $statusText = "Chưa xử lý"; $class = "pending"; break;
                        case 'confirmed': $statusText = "Đã xác nhận"; $class = "confirmed"; break;
                        case 'delivered': $statusText = "Đã giao hàng"; $class = "delivered"; break;
                        case 'cancelled': $statusText = "Đã huỷ"; $class = "cancelled"; break;
                    }
                    ?>
                    <span class="status <?= $class ?>" style="font-size: 14px; padding: 6px 15px;">
                        <?= $statusText ?>
                    </span>
                </div>
            </div>

            <div class="order-grid">
                <div class="info-box">
                    <h3> Thông tin giao hàng</h3>
                    <p><strong>Người nhận:</strong> <?= htmlspecialchars($order['receiver_name']) ?></p>
                    <p><strong>Số điện thoại:</strong> <?= htmlspecialchars($order['phone']) ?></p>
                    <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($order['address']) ?>, <?= htmlspecialchars($order['ward']) ?></p>
                    <p><strong>Ghi chú:</strong> <span style="color: #d35400; font-style: italic;"><?= !empty($order['order_note']) ? htmlspecialchars($order['order_note']) : 'Không có' ?></span></p>
                </div>
                
                <div class="info-box">
                    <h3>Thông tin thanh toán</h3>
                    <p><strong>Khách hàng đặt:</strong> <?= htmlspecialchars($order['fullname']) ?></p>
                    <p><strong>Phương thức:</strong> <?= $paymentMethod ?></p>
                    <p><strong>Mã giảm giá áp dụng:</strong> 
                        <?php if(!empty($order['promo_code'])): ?>
                            <span style="background: #27ae60; color: #fff; padding: 2px 8px; border-radius: 4px; font-weight: bold; font-size: 12px;">
                                <?= htmlspecialchars($order['promo_code']) ?>
                            </span>
                        <?php else: ?>
                            Không áp dụng
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <h3> Chi tiết món ăn</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50%;">Sản phẩm</th>
                        <th style="text-align: center;">Đơn giá</th>
                        <th style="text-align: center;">Số lượng</th>
                        <th style="text-align: right;">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $subTotal = 0;
                    while ($item = mysqli_fetch_assoc($details)) { 
                        $itemTotal = $item['quantity'] * $item['price_at_purchase'];
                        $subTotal += $itemTotal;
                    ?>
                        <tr>
                            <td>
                                <img src="../assets/images/products/<?= htmlspecialchars($item['image']) ?>" class="product-img-mini" onerror="this.src='../assets/images/default.jpg'">
                                <strong><?= htmlspecialchars($item['product_name']) ?></strong>
                            </td>
                            <td style="text-align: center;"><?= number_format($item['price_at_purchase']) ?>đ</td>
                            <td style="text-align: center;">x <?= $item['quantity'] ?></td>
                            <td style="text-align: right; font-weight: 500;"><?= number_format($itemTotal) ?>đ</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <div class="order-summary">
                <p><strong>Tạm tính:</strong> <?= number_format($subTotal) ?>đ</p>
                <p><strong>Giảm giá:</strong> <span style="color: #e74c3c;">- <?= number_format($order['discount_amount']) ?>đ</span></p>
                <h3 class="total-price"><strong>Tổng thanh toán:</strong> <?= number_format($order['total_price']) ?>đ</h3>
            </div>

        </div>
    </main>
</body>

</html>