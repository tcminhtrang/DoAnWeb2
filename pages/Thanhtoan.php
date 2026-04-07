<?php
session_start();
require_once '../config/database.php'; 
if(!defined('POINT_TO_MONEY')) define('POINT_TO_MONEY', 1000); 

if (!isset($_SESSION['user_id'])) { 
    header("Location: Dangnhap.php"); 
    exit(); 
}
$user_id = (int)$_SESSION['user_id'];
$stmt_user = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt_user, "i", $user_id);
mysqli_stmt_execute($stmt_user);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_user));

$total_money = 0;
$cart_items = [];

$stmt_cart = mysqli_prepare($conn, "SELECT c.*, p.product_name, p.price, p.image FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
mysqli_stmt_bind_param($stmt_cart, "i", $user_id);
mysqli_stmt_execute($stmt_cart);
$res = mysqli_stmt_get_result($stmt_cart);

while($row = mysqli_fetch_assoc($res)) {
    $total_money += $row['price'] * $row['quantity'];
    $cart_items[] = $row;
}

if (empty($cart_items)) {
    header("Location: Giohang.php");
    exit();
}

$user_points = $user['points'] ?? 0;
$max_points_discount = $user_points * POINT_TO_MONEY;
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
    <form action="process_checkout.php" method="POST" id="checkout-form">
        <div class="checkout-content">
            <div class="left-column">
                <br><br>
                <div class="section address-section">
                    <h3 style="margin-bottom: 20px;"><i class="fas fa-map-marker-alt"></i> Địa chỉ nhận hàng</h3>

                    <div class="address-card-wrapper" style="position: relative; margin-bottom: 15px;">
                        <input type="radio" id="addr-default" name="address_type" value="default" checked 
                            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; z-index: 10; cursor: pointer; margin: 0;">
                        <label for="addr-default" class="address-card-label" style="display: block; padding: 15px 20px; border: 2px solid #ddd; border-radius: 10px; cursor: pointer; transition: all 0.3s; background: #fff;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <div>
                                    <strong style="font-size: 1.1em; color: #333;"><?php echo $user['fullname']; ?></strong>
                                    <span style="background: #ff6347; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px; margin-left: 10px;">Mặc định</span>
                                    <div style="margin-top: 8px; color: #666;">
                                        <p style="margin: 3px 0;"><i class="fas fa-phone-alt" style="width: 20px; color: #ff6347;"></i> <?php echo htmlspecialchars($user['phone']); ?></p>
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
                        <label for="addr-new" class="address-card-label" style="display: block; padding: 15px 20px; border: 2px solid #ddd; border-radius: 10px; cursor: pointer; transition: all 0.3s; background: #fff;">
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
                                <select name="city" id="city-select" style="flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                                    <option value="">Chọn Tỉnh/Thành phố</option>
                                    <option value="Hồ Chí Minh">Hồ Chí Minh</option>
                                    <option value="Hà Nội">Hà Nội</option>
                                    <option value="Đà Nẵng">Đà Nẵng</option>
                                    <option value="Hải Phòng">Hải Phòng</option>
                                    <option value="Cần Thơ">Cần Thơ</option>
                                </select>
                                <select name="district" id="district-select" style="flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                                    <option value="">Chọn Quận/Huyện</option>
                                </select>
                            </div>
                            <textarea name="specific_address" placeholder="Số nhà, tên đường..." style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; min-height: 70px;"></textarea>
                        </div>
                    </div>
                </div>

                <style>
                    input[name="address_type"]:checked + .address-card-label { border-color: #ff6347 !important; background-color: #fff9f8 !important; box-shadow: 0 4px 12px rgba(255, 99, 71, 0.1); }
                    input[name="address_type"]:checked + .address-card-label .check-icon { display: block !important; }
                </style>

                <div class="section payment-section">
                    <h3>Phương thức thanh toán</h3>
                    <div class="payment-method">
                        <input type="radio" id="cod" name="payment_method" value="cod" checked>
                        <label for="cod">💰 Tiền mặt khi nhận hàng (COD)</label>
                    </div>
                    <div class="payment-method">
                        <input type="radio" id="banking" name="payment_method" value="banking">
                        <label for="banking">💳 Chuyển khoản ngân hàng</label>
                        
                        <div id="bank-info" style="display: none; padding: 15px; border: 1px dashed #ca2510; margin-top: 10px; background: #fff;">
                            <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                                <div style="flex: 1; min-width: 200px;">
                                    <p>Ngân hàng: <strong>MB Bank</strong></p>
                                    <p>Số TK: <strong>123456789</strong></p>
                                    <p>Chủ TK: <strong>CHICKEN JOY</strong></p>
                                    <p style="font-size: 13px; color: #666;">Nội dung:Chuyển khoản đơn hàng</p>
                                </div>
                                <div style="text-align: center;">
                                    <img src="../images/qr.png" alt="QR Code" style="width: 130px; border: 1px solid #ddd; padding: 3px;">
                                    <p style="font-size: 11px; color: #ca2510;">Quét để thanh toán</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="payment-method">
                        <input type="radio" id="online" name="payment_method" value="online">
                        <label for="online">🌐 Thanh toán trực tuyến </label>
                        <div id="online-notice" style="display: none; padding: 10px; margin-top: 10px; color: #0e7a3b; background: #e8f5e9; border-radius: 5px; font-size: 0.9em;">
                            <i class="fas fa-info-circle"></i> Hệ thống đang liên kết cổng VNPay/MoMo. Vui lòng đợi nhân viên xác nhận!
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
                        <p id="promo-row" style="display:none; color: #28a745;">Giảm giá mã (<span id="promo-percent-label">0</span>%): <span style="float:right;">-<span id="promo-discount-val">0</span>đ</span></p>
                        <p id="points-row" style="display:none; color: #28a745;">Dùng điểm: <span style="float:right;">-<span id="points-discount-val">0</span>đ</span></p>
                        <p>Phí giao hàng: <span style="float:right; color:green">Miễn phí</span></p>
                        <hr>
                        <p class="total-row" style="font-size:1.2em; font-weight:bold; color:#ca2510;">Tổng cộng: <span id="final-total" style="float:right;"><?php echo number_format($total_money, 0, ',', '.'); ?>đ</span></p>
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
    const districtData = {
        "Hồ Chí Minh": ["Quận 1", "Quận 3", "Quận 10", "Bình Thạnh", "Tân Bình", "TP. Thủ Đức", "Huyện Hóc Môn"],
        "Hà Nội": ["Quận Ba Đình", "Quận Cầu Giấy", "Quận Đống Đa", "Quận Hai Bà Trưng", "Huyện Đông Anh"],
        "Đà Nẵng": ["Quận Hải Châu", "Quận Thanh Khê", "Quận Liên Chiểu", "Quận Ngũ Hành Sơn"],
        "Hải Phòng": ["Quận Hồng Bàng", "Quận Lê Chân", "Quận Ngô Quyền"],
        "Cần Thơ": ["Quận Ninh Kiều", "Quận Bình Thủy", "Quận Cái Răng"]
    };

    const citySelect = document.getElementById('city-select');
    const districtSelect = document.getElementById('district-select');
    citySelect.addEventListener('change', function() {
        const city = this.value;
        districtSelect.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
        if(city && districtData[city]) {
            districtData[city].forEach(d => {
                const opt = document.createElement('option');
                opt.value = d; opt.textContent = d;
                districtSelect.appendChild(opt);
            });
        }
    });

    document.querySelectorAll('input[name="address_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('new-address-fields').style.display = (this.value === 'new') ? 'block' : 'none';
        });
    });

    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('bank-info').style.display = (this.value === 'banking') ? 'block' : 'none';
            document.getElementById('online-notice').style.display = (this.value === 'online') ? 'block' : 'none';
        });
    });

    let promoPercent = 0;
    const subtotal = <?php echo $total_money; ?>;
    const pointsValue = <?php echo $max_points_discount; ?>;

    function applyPromo() {
        const code = document.getElementById('promo-input').value;
        if(!code) return;
        fetch('check_promo.php?code=' + code).then(res => res.json()).then(data => {
            if(data.status === 'success') {
                promoPercent = data.percent;
                document.getElementById('promo-msg').style.color = 'green';
                document.getElementById('promo-msg').innerText = "Áp dụng thành công!";
            } else {
                promoPercent = 0;
                document.getElementById('promo-msg').style.color = 'red';
                document.getElementById('promo-msg').innerText = "Mã không hợp lệ!";
            }
            calculateAll();
        });
    }

    function calculateAll() {
        const promoDiscount = subtotal * (promoPercent / 100);
        document.getElementById('promo-row').style.display = (promoPercent > 0) ? 'block' : 'none';
        document.getElementById('promo-percent-label').innerText = promoPercent;
        document.getElementById('promo-discount-val').innerText = promoDiscount.toLocaleString('vi-VN');

        const pointsChecked = document.getElementById('use-points-checkbox')?.checked;
        const currentPointsDiscount = pointsChecked ? pointsValue : 0;
        if(document.getElementById('points-row')) document.getElementById('points-row').style.display = pointsChecked ? 'block' : 'none';
        if(document.getElementById('points-discount-val')) document.getElementById('points-discount-val').innerText = currentPointsDiscount.toLocaleString('vi-VN');

        const finalTotal = subtotal - promoDiscount - currentPointsDiscount;
        document.getElementById('final-total').innerText = (finalTotal > 0 ? finalTotal : 0).toLocaleString('vi-VN') + 'đ';
    }

    document.getElementById('checkout-form').addEventListener('submit', function(e) {
        if(document.querySelector('input[name="address_type"]:checked').value === 'new') {
            const name = document.querySelector('input[name="new_name"]').value.trim();
            const phone = document.querySelector('input[name="new_phone"]').value.trim();
            if(!name || !phone || !citySelect.value || !districtSelect.value) {
                e.preventDefault(); alert("Vui lòng điền đầy đủ thông tin địa chỉ mới!");
            }
        }
    });
    </script>
</body>
</html>