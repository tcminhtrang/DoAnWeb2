<?php
session_start();
require_once '../config/database.php';

// Đảm bảo hằng số tồn tại (đề phòng file database.php chưa có)
if(!defined('POINT_TO_MONEY')) define('POINT_TO_MONEY', 1000); 
if(!defined('MONEY_PER_POINT')) define('MONEY_PER_POINT', 10000);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $user_id = (int)$_SESSION['user_id'];
    $address_type = $_POST['address_type'] ?? 'default';
    $payment_method = $_POST['payment_method'] ?? 'cod';
    $order_note = trim($_POST['order_note'] ?? '');
    
    $promo_code = trim($_POST['promo_code'] ?? '');
    $use_points = isset($_POST['use_points']) ? 1 : 0;

    $final_ward = '';

    // 1. XÁC ĐỊNH ĐỊA CHỈ GIAO HÀNG VÀ TÁCH TÊN PHƯỜNG (FIX LỖI #14)
    if ($address_type === 'default') {
        $stmt_u = mysqli_prepare($conn, "SELECT fullname, phone, address, points FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt_u, "i", $user_id);
        mysqli_stmt_execute($stmt_u);
        $u = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_u));
        
        $final_name = $u['fullname']; 
        $final_phone = $u['phone']; 
        $final_addr = $u['address'];
        $current_points = $u['points'] ?? 0;

        // Bóc tách Phường/Quận từ chuỗi địa chỉ mặc định (VD: 123 ABC, Phường 5, TP HCM)
        $parts = array_map('trim', explode(',', $final_addr));
        if (count($parts) >= 2) {
            $final_ward = $parts[count($parts) - 2]; 
        }
    } else {
        $final_name = trim($_POST['new_name'] ?? '');
        $final_phone = trim($_POST['new_phone'] ?? '');
        
        $spec_addr = trim($_POST['specific_address'] ?? '');
        $final_ward = trim($_POST['district'] ?? ''); // Lấy giá trị Quận/Phường
        $city = trim($_POST['city'] ?? '');
        
        $final_addr = trim($spec_addr . ", " . $final_ward . ", " . $city, ", ");
        
        $stmt_pts = mysqli_prepare($conn, "SELECT points FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt_pts, "i", $user_id);
        mysqli_stmt_execute($stmt_pts);
        $current_points = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_pts))['points'] ?? 0;
    }

    if (empty($final_name) || empty($final_addr) || empty($final_phone)) {
        die("Vui lòng nhập đầy đủ thông tin giao hàng!");
    }

    // 2. LẤY GIỎ HÀNG BẰNG PREPARED STATEMENT
    $stmt_cart = mysqli_prepare($conn, "SELECT c.quantity, p.price, p.id, p.product_name FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
    mysqli_stmt_bind_param($stmt_cart, "i", $user_id);
    mysqli_stmt_execute($stmt_cart);
    $cart_res = mysqli_stmt_get_result($stmt_cart);
    
    $total_price = 0;
    $cart_items = [];
    
    while ($row = mysqli_fetch_assoc($cart_res)) {
        $total_price += $row['price'] * $row['quantity'];
        $cart_items[] = $row;
    }

    if (count($cart_items) == 0) {
        header("Location: Thucdon.php");
        exit();
    }

    // 3. TÍNH TOÁN KHUYẾN MÃI VÀ ĐIỂM
    $discount_amount = 0;
    $points_used = 0;
    $promo_val = NULL;

    if (!empty($promo_code)) {
        $date_now = date('Y-m-d');
        $stmt_promo = mysqli_prepare($conn, "SELECT discount_percent FROM promotions WHERE code = ? AND status = 'active' AND start_date <= ? AND end_date >= ?");
        mysqli_stmt_bind_param($stmt_promo, "sss", $promo_code, $date_now, $date_now);
        mysqli_stmt_execute($stmt_promo);
        $promo_query = mysqli_stmt_get_result($stmt_promo);
        
        if (mysqli_num_rows($promo_query) > 0) {
            $promo_data = mysqli_fetch_assoc($promo_query);
            $discount_amount += $total_price * ($promo_data['discount_percent'] / 100);
            $promo_val = $promo_code;
        }
    }

    if ($use_points && $current_points > 0) {
        $points_discount_value = $current_points * POINT_TO_MONEY;
        $price_after_promo = $total_price - $discount_amount;
        
        if ($points_discount_value >= $price_after_promo) {
            $discount_amount += $price_after_promo;
            $points_used = ceil($price_after_promo / POINT_TO_MONEY); 
        } else {
            $discount_amount += $points_discount_value;
            $points_used = $current_points; 
        }
    }

    $final_price = max(0, $total_price - $discount_amount);
    $points_earned = floor($final_price / MONEY_PER_POINT);

    // 4. LƯU VÀO DATABASE (Transaction)
    mysqli_begin_transaction($conn);

    try {
        // KIỂM TRA TỒN KHO VỚI LỆNH "FOR UPDATE" (FIX LỖI #6)
        // Lệnh này khóa dòng dữ liệu, tránh trường hợp 2 người cùng mua món cuối cùng 1 lúc
        foreach ($cart_items as $item) {
            $p_id = $item['id'];
            $qty_needed = $item['quantity'];
            $p_name = $item['product_name'];
            
            $stmt_stock = mysqli_prepare($conn, "SELECT stock FROM products WHERE id = ? FOR UPDATE");
            mysqli_stmt_bind_param($stmt_stock, "i", $p_id);
            mysqli_stmt_execute($stmt_stock);
            $stock_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_stock));
            
            if ($stock_data['stock'] < $qty_needed) {
                throw new Exception("Sản phẩm '$p_name' không đủ số lượng (Chỉ còn {$stock_data['stock']} phần). Vui lòng cập nhật lại giỏ hàng!");
            }
        }

        // THÊM ĐƠN HÀNG (ĐÃ BỔ SUNG CỘT WARD VÀO CÂU LỆNH INSERT)
        $stmt_order = mysqli_prepare($conn, "INSERT INTO orders (user_id, receiver_name, total_price, discount_amount, promo_code, status, payment_method, address, ward, phone, order_note) VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt_order, "isddssssss", $user_id, $final_name, $final_price, $discount_amount, $promo_val, $payment_method, $final_addr, $final_ward, $final_phone, $order_note);
        
        if (!mysqli_stmt_execute($stmt_order)) throw new Exception("Lỗi tạo đơn hàng!");
        $order_id = mysqli_insert_id($conn);

        // INSERT VÀO ORDER_DETAILS
        $stmt_detail = mysqli_prepare($conn, "INSERT INTO order_details (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
        foreach ($cart_items as $item) {
            $p_id = $item['id']; 
            $qty = $item['quantity']; 
            $price = $item['price'];
            mysqli_stmt_bind_param($stmt_detail, "iiid", $order_id, $p_id, $qty, $price);
            if (!mysqli_stmt_execute($stmt_detail)) throw new Exception("Lỗi lưu chi tiết món!");
        }

        // CẬP NHẬT ĐIỂM
        $stmt_pts = mysqli_prepare($conn, "UPDATE users SET points = points - ? + ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt_pts, "iii", $points_used, $points_earned, $user_id);
        if (!mysqli_stmt_execute($stmt_pts)) throw new Exception("Lỗi cập nhật điểm!");
        
        if ($points_used > 0) {
            $reason_use = "Dùng điểm thanh toán đơn hàng #" . $order_id;
            $stmt_h1 = mysqli_prepare($conn, "INSERT INTO point (user_id, order_id, points_change, reason) VALUES (?, ?, ?, ?)");
            $neg_pts = -$points_used;
            mysqli_stmt_bind_param($stmt_h1, "iiis", $user_id, $order_id, $neg_pts, $reason_use);
            mysqli_stmt_execute($stmt_h1);
        }

        if ($points_earned > 0) {
            $reason_earn = "Tích điểm từ đơn hàng #" . $order_id;
            $stmt_h2 = mysqli_prepare($conn, "INSERT INTO point (user_id, order_id, points_change, reason) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_h2, "iiis", $user_id, $order_id, $points_earned, $reason_earn);
            mysqli_stmt_execute($stmt_h2);
        }
        
        // XÓA GIỎ HÀNG
        $stmt_del_cart = mysqli_prepare($conn, "DELETE FROM cart WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt_del_cart, "i", $user_id);
        mysqli_stmt_execute($stmt_del_cart);

        mysqli_commit($conn);
        header("Location: Dadathang.php?order_id=$order_id");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error_message = addslashes($e->getMessage());
        echo "<script>
                alert('LỖI ĐẶT HÀNG: $error_message');
                window.location.href = 'Giohang.php';
              </script>";
        exit();
    }

} else {
    header("Location: ../index.php");
    exit();
}
?>