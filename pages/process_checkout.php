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
        $final_name = mysqli_real_escape_string($conn, $_POST['new_name'] ?? '');
        $final_phone = mysqli_real_escape_string($conn, $_POST['new_phone'] ?? '');
        
        $spec_addr = $_POST['specific_address'] ?? '';
        $dist = $_POST['district'] ?? '';
        $city = $_POST['city'] ?? '';
        
        $full_address = trim($spec_addr . ", " . $dist . ", " . $city, ", ");
        $final_addr = mysqli_real_escape_string($conn, $full_address);
        
        $u_res = mysqli_query($conn, "SELECT points FROM users WHERE id = $user_id");
        $current_points = mysqli_fetch_assoc($u_res)['points'] ?? 0;
    }

    if (empty($final_name) || empty($final_addr) || empty($final_phone)) {
        die("Vui lòng nhập đầy đủ thông tin giao hàng!");
    }

    // 2. LẤY GIỎ HÀNG (Lấy thêm tên sản phẩm để lỡ lỗi còn báo cho khách biết món nào)
    $cart_res = mysqli_query($conn, "SELECT c.quantity, p.price, p.id, p.product_name FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = $user_id");
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

    if (!empty($promo_code)) {
        $date_now = date('Y-m-d');
        $promo_sql = "SELECT discount_percent FROM promotions WHERE code = '$promo_code' AND status = 'active' AND start_date <= '$date_now' AND end_date >= '$date_now'";
        $promo_query = mysqli_query($conn, $promo_sql);
        
        if (mysqli_num_rows($promo_query) > 0) {
            $promo_data = mysqli_fetch_assoc($promo_query);
            $discount_amount += $total_price * ($promo_data['discount_percent'] / 100);
        } else {
            $promo_code = NULL; 
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
        // [CẬP NHẬT QUAN TRỌNG]: Kiểm tra tồn kho từng món trước khi tạo đơn
        foreach ($cart_items as $item) {
            $p_id = $item['id'];
            $qty_needed = $item['quantity'];
            $p_name = $item['product_name'];
            
            // Truy vấn lấy số lượng tồn kho hiện tại
            $stock_query = mysqli_query($conn, "SELECT stock FROM products WHERE id = $p_id");
            $stock_data = mysqli_fetch_assoc($stock_query);
            
            // Nếu khách mua nhiều hơn số lượng kho đang có -> Ném lỗi HỦY toàn bộ giao dịch
            if ($stock_data['stock'] < $qty_needed) {
                throw new Exception("Sản phẩm '$p_name' không đủ số lượng (Chỉ còn {$stock_data['stock']} phần). Vui lòng cập nhật lại giỏ hàng!");
            }
        }

        $promo_val = $promo_code ? "'$promo_code'" : "NULL";
        
        $sql_order = "INSERT INTO orders (user_id, receiver_name, total_price, discount_amount, promo_code, status, payment_method, address, phone, order_note) 
                      VALUES ($user_id, '$final_name', $final_price, $discount_amount, $promo_val, 'pending', '$payment_method', '$final_addr', '$final_phone', '$order_note')";
        
        if (!mysqli_query($conn, $sql_order)) throw new Exception(mysqli_error($conn));
        
        $order_id = mysqli_insert_id($conn);

        foreach ($cart_items as $item) {
            $p_id = $item['id']; 
            $qty = $item['quantity']; 
            $price = $item['price'];
            
            $sql_detail = "INSERT INTO order_details (order_id, product_id, quantity, price_at_purchase) 
                           VALUES ($order_id, $p_id, $qty, $price)";
            if (!mysqli_query($conn, $sql_detail)) throw new Exception(mysqli_error($conn));
        }

        $sql_update_points = "UPDATE users SET points = points - $points_used + $points_earned WHERE id = $user_id";
        if (!mysqli_query($conn, $sql_update_points)) throw new Exception(mysqli_error($conn));
        
        if ($points_used > 0) {
            $reason_use = "Dùng điểm thanh toán đơn hàng #" . $order_id;
            $sql_history_use = "INSERT INTO point (user_id, order_id, points_change, reason) 
                                VALUES ($user_id, $order_id, -$points_used, '$reason_use')";
            if (!mysqli_query($conn, $sql_history_use)) throw new Exception(mysqli_error($conn));
        }

        if ($points_earned > 0) {
            $reason_earn = "Tích điểm từ đơn hàng #" . $order_id;
            $sql_history_earn = "INSERT INTO point (user_id, order_id, points_change, reason) 
                                 VALUES ($user_id, $order_id, $points_earned, '$reason_earn')";
            if (!mysqli_query($conn, $sql_history_earn)) throw new Exception(mysqli_error($conn));
        }
        
        mysqli_query($conn, "DELETE FROM cart WHERE user_id = $user_id");

        mysqli_commit($conn);
        header("Location: Dadathang.php?order_id=$order_id");
        exit();

    } catch (Exception $e) {
        // [NÂNG CẤP]: Hiển thị popup báo lỗi rõ ràng và đưa về lại Giỏ Hàng
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