<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $address_type = $_POST['address_type'] ?? 'default';
    $payment_method = $_POST['payment_method'] ?? 'cod';
    $order_note = mysqli_real_escape_string($conn, $_POST['order_note'] ?? '');
    
    // Dữ liệu khuyến mãi & điểm
    $promo_code = mysqli_real_escape_string($conn, $_POST['promo_code'] ?? '');
    $use_points = isset($_POST['use_points']) ? 1 : 0;

    // 1. XÁC ĐỊNH ĐỊA CHỈ GIAO HÀNG
    if ($address_type === 'default') {
        $u_res = mysqli_query($conn, "SELECT fullname, phone, address, points FROM users WHERE id = $user_id");
        $u = mysqli_fetch_assoc($u_res);
        $final_name = $u['fullname']; 
        $final_phone = $u['phone']; 
        $final_addr = $u['address'];
        $current_points = $u['points'] ?? 0;
    } else {
        $final_name = mysqli_real_escape_string($conn, $_POST['new_name']);
        $final_phone = mysqli_real_escape_string($conn, $_POST['new_phone']);
        $full_address = $_POST['specific_address'] . ", " . $_POST['district'] . ", " . $_POST['city'];
        $final_addr = mysqli_real_escape_string($conn, $full_address);
        
        $u_res = mysqli_query($conn, "SELECT points FROM users WHERE id = $user_id");
        $current_points = mysqli_fetch_assoc($u_res)['points'] ?? 0;
    }

    if (empty($final_name) || empty($final_addr) || empty($final_phone)) {
        die("Vui lòng nhập đầy đủ thông tin giao hàng!");
    }

    // 2. LẤY GIỎ HÀNG VÀ TÍNH TỔNG TIỀN BAN ĐẦU
    $cart_res = mysqli_query($conn, "SELECT c.quantity, p.price, p.id FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = $user_id");
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

    // 3.1 Check Mã khuyến mãi
    if (!empty($promo_code)) {
        $date_now = date('Y-m-d');
        // Kiểm tra mã có tồn tại, active và còn hạn không
        $promo_sql = "SELECT discount_percent FROM promotions WHERE code = '$promo_code' AND status = 'active' AND start_date <= '$date_now' AND end_date >= '$date_now'";
        $promo_query = mysqli_query($conn, $promo_sql);
        
        if (mysqli_num_rows($promo_query) > 0) {
            $promo_data = mysqli_fetch_assoc($promo_query);
            $discount_amount += $total_price * ($promo_data['discount_percent'] / 100);
        } else {
            $promo_code = NULL; // Reset nếu mã sai hoặc hết hạn
        }
    }

    // 3.2 Check Điểm tích lũy (1 điểm = 1000đ)
    // 3.2 Check Điểm tích lũy
    if ($use_points && $current_points > 0) {
        // Query lấy giá trị quy đổi 1 điểm = ? VNĐ từ CSDL
        $config_spend_query = mysqli_query($conn, "SELECT config_value FROM points WHERE config_key = 'point_to_money'");
        $point_to_money = mysqli_fetch_assoc($config_spend_query)['config_value'] ?? 1000;

        $points_discount_value = $current_points * $point_to_money;
        
        // Đảm bảo không giảm quá số tiền còn lại của đơn hàng
        $price_after_promo = $total_price - $discount_amount;
        
        if ($points_discount_value > $price_after_promo) {
            $discount_amount += $price_after_promo;
            $points_used = ceil($price_after_promo / $point_to_money); // Chỉ trừ số điểm tương ứng
        } else {
            $discount_amount += $points_discount_value;
            $points_used = $current_points; // Dùng hết điểm
        }
    }

    // Giá cuối cùng khách phải trả
    $final_price = $total_price - $discount_amount;
    if ($final_price < 0) $final_price = 0;

    // 3.3 Tính điểm nhận được sau đơn này (Lấy từ bảng config: 10000đ = 1 điểm)
    $config_query = mysqli_query($conn, "SELECT config_value FROM points WHERE config_key = 'money_per_point'");
    $config_data = mysqli_fetch_assoc($config_query);
    $money_per_point = $config_data['config_value'] ?? 10000;
    
    $points_earned = floor($final_price / $money_per_point);

    // 4. LƯU VÀO DATABASE (Transaction)
    mysqli_begin_transaction($conn);

    try {
        $promo_val = $promo_code ? "'$promo_code'" : "NULL";
        $sql_order = "INSERT INTO orders (user_id, receiver_name, total_price, discount_amount, promo_code, status, payment_method, address, phone, order_note) 
                      VALUES ($user_id, '$final_name', $final_price, $discount_amount, $promo_val, 'pending', '$payment_method', '$final_addr', '$final_phone', '$order_note')";
        
        if (!mysqli_query($conn, $sql_order)) throw new Exception(mysqli_error($conn));
        
        $order_id = mysqli_insert_id($conn);

        foreach ($cart_items as $item) {
            $p_id = $item['id']; 
            $qty = $item['quantity']; 
            $price = $item['price'];
            
            // 1. Chỉ lưu vào bảng order_details 
            $sql_detail = "INSERT INTO order_details (order_id, product_id, quantity, price_at_purchase) 
                           VALUES ($order_id, $p_id, $qty, $price)";
            if (!mysqli_query($conn, $sql_detail)) throw new Exception(mysqli_error($conn));
        }

        // C. Cập nhật lại số điểm của User (Trừ điểm đã dùng + Cộng điểm vừa kiếm được)
        $sql_update_points = "UPDATE users SET points = points - $points_used + $points_earned WHERE id = $user_id";
        if (!mysqli_query($conn, $sql_update_points)) throw new Exception(mysqli_error($conn));
        // D1. Ghi nhận lịch sử TRỪ ĐIỂM (nếu khách có dùng điểm)
        if ($points_used > 0) {
            $reason_use = "Dùng điểm thanh toán đơn hàng #" . $order_id;
            // Điểm trừ thì lưu số âm (ví dụ: -50)
            $sql_history_use = "INSERT INTO point (user_id, order_id, points_change, reason) 
                                VALUES ($user_id, $order_id, -$points_used, '$reason_use')";
            if (!mysqli_query($conn, $sql_history_use)) throw new Exception(mysqli_error($conn));
        }

        // D2. Ghi nhận lịch sử CỘNG ĐIỂM (từ giá trị đơn hàng)
        if ($points_earned > 0) {
            $reason_earn = "Tích điểm từ đơn hàng #" . $order_id;
            // Điểm cộng thì lưu số dương
            $sql_history_earn = "INSERT INTO point (user_id, order_id, points_change, reason) 
                                 VALUES ($user_id, $order_id, $points_earned, '$reason_earn')";
            if (!mysqli_query($conn, $sql_history_earn)) throw new Exception(mysqli_error($conn));
        }
        // D. Xóa giỏ hàng
        mysqli_query($conn, "DELETE FROM cart WHERE user_id = $user_id");

        mysqli_commit($conn);
        header("Location: Dadathang.php?order_id=$order_id");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "Lỗi hệ thống: " . $e->getMessage();
    }

} else {
    header("Location: ../index.php");
    exit();
}
?>