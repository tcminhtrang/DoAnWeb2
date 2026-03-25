<?php
session_start();
require_once '../config/database.php'; 

if (!isset($_SESSION['user_id'])) { header("Location: Dangnhap.php"); exit(); }
$user_id = $_SESSION['user_id'];

// 1. Lấy thông tin User và Giỏ hàng
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id"));
$total_money = 0;
$cart_items = [];
$res = mysqli_query($conn, "SELECT c.*, p.product_name, p.price, p.image FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = $user_id");
while($row = mysqli_fetch_assoc($res)) {
    $total_money += $row['price'] * $row['quantity'];
    $cart_items[] = $row;
}

// 2. Cấu hình điểm
$point_to_money = mysqli_fetch_assoc(mysqli_query($conn, "SELECT config_value FROM points WHERE config_key = 'point_to_money'"))['config_value'] ?? 1000;
$user_points = $user['points'] ?? 0;
$max_points_discount = $user_points * $point_to_money;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thanh Toán - Chicken Joy</title>
    <link rel="stylesheet" href="../css/Thanhtoan.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <form action="process_checkout.php" method="POST">
        <div class="checkout-content">
            <div class="left-column">

                <br><br>

                <div class="section address-section">

                    <h3 style="margin-bottom: 20px;"><i class="fas fa-map-marker-alt"></i> Địa chỉ nhận hàng</h3>

                    

                    <div class="address-card-wrapper" style="position: relative; margin-bottom: 15px;">

                        <input type="radio" id="addr-default" name="address_type" value="default" checked 

                            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; z-index: 10; cursor: pointer; margin: 0;">

                        

                        <label for="addr-default" class="address-card-label" 

                            style="display: block; padding: 15px 20px; border: 2px solid #ddd; border-radius: 10px; cursor: pointer; transition: all 0.3s; background: #fff;">

                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">

                                <div>

                                    <strong style="font-size: 1.1em; color: #333;"><?php echo $user['fullname']; ?></strong>

                                    <span style="background: #ff6347; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px; margin-left: 10px;">Mặc định</span>

                                    <div style="margin-top: 8px; color: #666;">

                                        <p style="margin: 3px 0;"><i class="fas fa-phone-alt" style="width: 20px; color: #ff6347;"></i> 0<?php echo $user['phone']; ?></p>

                                        <p style="margin: 3px 0;"><i class="fas fa-home" style="width: 20px; color: #ff6347;"></i> <?php echo $user['address']; ?></p>

                                    </div>

                                </div>

                                <div class="check-icon" style="color: #ff6347; display: none;"><i class="fas fa-check-circle fa-lg"></i></div>

                            </div>

                        </label>

                    </div>



                    <div class="address-card-wrapper" style="position: relative;">

                        <input type="radio" id="addr-new" name="address_type" value="new"

                            style="position: absolute; top: 0; left: 0; width: 100%; height: 60px; opacity: 0; z-index: 10; cursor: pointer; margin: 0;">

                        

                        <label for="addr-new" class="address-card-label" 

                            style="display: block; padding: 15px 20px; border: 2px solid #ddd; border-radius: 10px; cursor: pointer; transition: all 0.3s; background: #fff;">

                            <div style="display: flex; justify-content: space-between; align-items: center;">

                                <strong style="color: #333;"><i class="fas fa-plus-circle" style="color: #ff6347; margin-right: 10px;"></i> Giao đến địa chỉ mới</strong>

                                <div class="check-icon" style="color: #ff6347; display: none;"><i class="fas fa-check-circle fa-lg"></i></div>

                            </div>

                        </label>

                        

                        <div id="new-address-fields" style="display: none; margin-top: 15px; padding: 10px; border-top: 1px dashed #ddd;">

                            <div style="display: flex; gap: 10px; margin-bottom: 10px;">

                                <input type="text" name="new_name" placeholder="Tên người nhận" style="flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">

                                <input type="text" name="new_phone" placeholder="Số điện thoại" style="flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">

                            </div>

                            <div style="display: flex; gap: 10px; margin-bottom: 10px;">

                                <select name="city" style="flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">

                                    <option value="">Chọn Tỉnh/Thành phố</option>

                                    <option value="Hồ Chí Minh">Hồ Chí Minh</option>

                                    <option value="Hà Nội">Hà Nội</option>

                                </select>

                                <select name="district" style="flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">

                                    <option value="">Chọn Quận/Huyện</option>

                                </select>

                            </div>

                            <textarea name="specific_address" placeholder="Số nhà, tên đường..." style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; min-height: 70px;"></textarea>

                        </div>

                    </div>

                </div>



                <style>

                    /* Khi radio được chọn, đổi màu viền label ngay sau nó */

                    input[name="address_type"]:checked + .address-card-label {

                        border-color: #ff6347 !important;

                        background-color: #fff9f8 !important;

                        box-shadow: 0 4px 12px rgba(255, 99, 71, 0.1);

                    }

                    /* Hiện icon check khi được chọn */

                    input[name="address_type"]:checked + .address-card-label .check-icon {

                        display: block !important;

                    }

                </style>



                <script>

                document.querySelectorAll('input[name="address_type"]').forEach(radio => {

                    radio.addEventListener('change', function() {

                        const newFields = document.getElementById('new-address-fields');

                        newFields.style.display = (this.value === 'new') ? 'block' : 'none';

                    });

                });

                </script>



                <div class="section payment-section">

                    <h3>Phương thức thanh toán</h3>

                    <div class="payment-method">

                        <input type="radio" id="cod" name="payment_method" value="cod" checked>

                        <label for="cod">💰 Tiền mặt khi nhận hàng (COD)</label>

                    </div>

                    <div class="payment-method">

                        <input type="radio" id="banking" name="payment_method" value="banking">

                        <label for="banking">💳 Chuyển khoản ngân hàng</label>

                        <div id="bank-info" style="display: none; padding: 10px; border: 1px dashed #ca2510; margin-top: 10px;">

                            <p>Ngân hàng: <strong>MB Bank</strong></p>

                            <p>Số TK: <strong>123456789</strong></p>

                            <p>Chủ TK: <strong>CHICKEN JOY</strong></p>

                        </div>

                    </div>

                    <div class="payment-method">

                        <input type="radio" id="online" name="payment_method" value="online">

                        <label for="online">🌐 Thanh toán trực tuyến </label>

                        

                        <div id="online-notice" style="display: none; padding: 10px; margin-top: 10px; color: #0e7a3b; background: #e8f5e9; border-radius: 5px; font-size: 0.9em;">

                            <i class="fas fa-info-circle"></i> Hệ thống đang liên kết với các cổng thanh toán (VNPay, MoMo). 

                            <br>Tạm thời bạn có thể đặt hàng, nhân viên sẽ xác nhận sau!

                        </div>

                    </div>

                </div>



                <div class="section note-section">

                    <h3>Ghi chú đơn hàng</h3>

                    <textarea name="order_note" placeholder="Ví dụ: Giao giờ hành chính..."></textarea>

                </div>

            </div>

            <div class="right-column">
                <div class="order-summary-box">
                    <h3>Đơn hàng của bạn</h3>
                    <div class="order-items">
                        <?php foreach($cart_items as $item): ?>
                        <div class="order-item" style="display:flex; justify-content:space-between; margin-bottom:10px;">
                            <span><?php echo $item['product_name']; ?> (x<?php echo $item['quantity']; ?>)</span>
                            <span><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>đ</span>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="order-totals" style="background:#f9f9f9; padding:15px; border-radius:8px;">
                        <p>Tạm tính: <span style="float:right;"><?php echo number_format($total_money, 0, ',', '.'); ?>đ</span></p>
                        
                        <p id="promo-row" style="display:none; color: #28a745;">
                            Giảm giá mã (<span id="promo-percent-label">0</span>%): 
                            <span style="float:right;">-<span id="promo-discount-val">0</span>đ</span>
                        </p>

                        <p id="points-row" style="display:none; color: #28a745;">
                            Dùng điểm tích lũy: 
                            <span style="float:right;">-<span id="points-discount-val">0</span>đ</span>
                        </p>

                        <p>Phí giao hàng: <span style="float:right; color:green">Miễn phí</span></p>
                        <hr>
                        <p class="total-row" style="font-size:1.2em; font-weight:bold; color:#ca2510;">
                            Tổng cộng: <span id="final-total" style="float:right;"><?php echo number_format($total_money, 0, ',', '.'); ?>đ</span>
                        </p>
                    </div>

                    <div style="margin-top:20px;">
                        <label>Mã khuyến mãi:</label>
                        <div style="display:flex; gap:10px; margin-bottom:15px;">
                            <input type="text" name="promo_code" id="promo-input" placeholder="Nhập mã..." style="flex:1; padding:8px; border:1px solid #ccc; border-radius:5px;">
                            <button type="button" onclick="applyPromo()" style="background:#333; color:#fff; border:none; padding:0 15px; border-radius:5px; cursor:pointer;">Áp dụng</button>
                        </div>
                        <p id="promo-msg" style="font-size:0.85em; margin-top:-10px; margin-bottom:10px;"></p>

                        <?php if ($user_points > 0): ?>
                        <label style="display:flex; align-items:center; gap:10px; background:#fff9f8; padding:10px; border:1px dashed #ff6347; border-radius:5px; cursor:pointer;">
                            <input type="checkbox" name="use_points" id="use-points-checkbox" value="1" onchange="calculateAll()">
                            <span>Dùng <strong><?php echo $user_points; ?></strong> điểm (-<?php echo number_format($max_points_discount, 0, ',', '.'); ?>đ)</span>
                        </label>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="place-order-btn" style="width:100%; padding:15px; background:#ca2510; color:#fff; border:none; border-radius:8px; font-weight:bold; margin-top:20px; cursor:pointer;">ĐẶT HÀNG NGAY</button>
                </div>
            </div>
        </div>
    </form>

    <script>
    let promoPercent = 0;
    const subtotal = <?php echo $total_money; ?>;
    const pointsValue = <?php echo $max_points_discount; ?>;

    function applyPromo() {
        const code = document.getElementById('promo-input').value;
        const msg = document.getElementById('promo-msg');
        
        if(!code) return;

        fetch('check_promo.php?code=' + code)
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    promoPercent = data.percent;
                    msg.style.color = 'green';
                    msg.innerText = "Áp dụng thành công!";
                    calculateAll();
                } else {
                    promoPercent = 0;
                    msg.style.color = 'red';
                    msg.innerText = "Mã sai hoặc hết hạn!";
                    calculateAll();
                }
            });
    }

    function calculateAll() {
        // 1. Tính giảm giá từ mã
        const promoDiscount = subtotal * (promoPercent / 100);
        const promoRow = document.getElementById('promo-row');
        if(promoPercent > 0) {
            promoRow.style.display = 'block';
            document.getElementById('promo-percent-label').innerText = promoPercent;
            document.getElementById('promo-discount-val').innerText = promoDiscount.toLocaleString('vi-VN');
        } else {
            promoRow.style.display = 'none';
        }

        // 2. Tính giảm giá từ điểm
        const pointsCheckbox = document.getElementById('use-points-checkbox');
        const pointsRow = document.getElementById('points-row');
        let currentPointsDiscount = 0;

        if(pointsCheckbox && pointsCheckbox.checked) {
            currentPointsDiscount = pointsValue;
            pointsRow.style.display = 'block';
            document.getElementById('points-discount-val').innerText = currentPointsDiscount.toLocaleString('vi-VN');
        } else {
            pointsRow.style.display = 'none';
        }

        // 3. Tổng cộng cuối cùng
        const finalTotal = subtotal - promoDiscount - currentPointsDiscount;
        document.getElementById('final-total').innerText = (finalTotal > 0 ? finalTotal : 0).toLocaleString('vi-VN') + 'đ';
    }
    </script>
</body>
</html>