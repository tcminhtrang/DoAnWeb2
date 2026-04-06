<?php
session_start();
include '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { 
    echo json_encode(['status' => 'error', 'message' => 'Bạn chưa đăng nhập!']); 
    exit(); 
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

if ($product_id <= 0 && $action !== 'clear') {
    echo json_encode(['status' => 'error', 'message' => 'ID sản phẩm không hợp lệ!']);
    exit();
}

switch ($action) {
    case 'add':
        $qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        $check = mysqli_query($conn, "SELECT id, quantity FROM cart WHERE user_id = $user_id AND product_id = $product_id");
        
        if (mysqli_num_rows($check) > 0) {
            $sql = "UPDATE cart SET quantity = quantity + $qty WHERE user_id = $user_id AND product_id = $product_id";
        } else {
            $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, $qty)";
        }

        if (mysqli_query($conn, $sql)) {
            echo json_encode(['status' => 'success', 'message' => 'Đã thêm vào giỏ hàng!']);
        } else {
            // Nếu lỗi, nó sẽ báo chi tiết lỗi SQL ở đây
            echo json_encode(['status' => 'error', 'message' => 'Lỗi Database: ' . mysqli_error($conn)]);
        }
        break;

    case 'plus':
        $res = mysqli_query($conn, "UPDATE cart SET quantity = quantity + 1 WHERE user_id = $user_id AND product_id = $product_id");
        echo json_encode(['status' => $res ? 'success' : 'error']);
        break;

    case 'minus':
        $res = mysqli_query($conn, "UPDATE cart SET quantity = GREATEST(1, quantity - 1) WHERE user_id = $user_id AND product_id = $product_id");
        echo json_encode(['status' => $res ? 'success' : 'error']);
        break;

    case 'delete':
        $res = mysqli_query($conn, "DELETE FROM cart WHERE user_id = $user_id AND product_id = $product_id");
        echo json_encode(['status' => $res ? 'success' : 'error']);
        break;

    case 'clear':
        $res = mysqli_query($conn, "DELETE FROM cart WHERE user_id = $user_id");
        echo json_encode(['status' => $res ? 'success' : 'error']);
        break;
    
    default:
        echo json_encode(['status' => 'error', 'message' => 'Hành động không hợp lệ!']);
        break;
}