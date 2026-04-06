<?php
session_start();
require_once '../config/database.php';

if (isset($_GET['id']) && isset($_GET['status'])) {
    $order_id = (int)$_GET['id']; 
    $new_status = mysqli_real_escape_string($conn, $_GET['status']);

    $allowed_statuses = ['pending', 'confirmed', 'delivered', 'cancelled'];
    
    if (in_array($new_status, $allowed_statuses)) {
        $sql_check = "SELECT status FROM orders WHERE id = $order_id";
        $res_check = mysqli_query($conn, $sql_check);
        
        if ($res_check && mysqli_num_rows($res_check) > 0) {
            $row = mysqli_fetch_assoc($res_check);
            $old_status = $row['status'];
            $sql_update = "UPDATE orders SET status = '$new_status' WHERE id = $order_id";
            if(mysqli_query($conn, $sql_update)) {
                $_SESSION['success_msg'] = "Cập nhật trạng thái đơn hàng DH" . str_pad($order_id, 3, '0', STR_PAD_LEFT) . " thành công!";
            }
            if ($old_status !== 'delivered' && $new_status === 'delivered') {
                $sql_details = "SELECT product_id, quantity FROM order_details WHERE order_id = $order_id";
                $res_details = mysqli_query($conn, $sql_details);
                while ($item = mysqli_fetch_assoc($res_details)) {
                    $p_id = $item['product_id'];
                    $qty = $item['quantity'];
                    mysqli_query($conn, "UPDATE products SET stock = stock - $qty WHERE id = $p_id");
                }
            }
            elseif ($old_status === 'delivered' && $new_status !== 'delivered') {
                $sql_details = "SELECT product_id, quantity FROM order_details WHERE order_id = $order_id";
                $res_details = mysqli_query($conn, $sql_details);
                while ($item = mysqli_fetch_assoc($res_details)) {
                    $p_id = $item['product_id'];
                    $qty = $item['quantity'];
                    mysqli_query($conn, "UPDATE products SET stock = stock + $qty WHERE id = $p_id");
                }
            }
        }
    }
}
header("Location: order-management.php");
exit();
?>