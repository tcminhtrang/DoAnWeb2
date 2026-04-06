<?php
session_start();
require_once '../config/database.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: Dangnhap.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- XỬ LÝ YÊU CẦU HỦY ĐƠN HÀNG TỪ NGƯỜI DÙNG ---
if (isset($_GET['action']) && $_GET['action'] == 'cancel' && isset($_GET['order_id'])) {
    $cancel_id = (int)$_GET['order_id'];
    
    // Kiểm tra xem đơn hàng có thuộc về user này và đang ở trạng thái pending (chưa được cửa hàng xử lý) không
    $check_sql = "SELECT id FROM orders WHERE id = $cancel_id AND user_id = $user_id AND status = 'pending'";
    $check_res = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_res) > 0) {
        // Thực hiện hủy đơn và ghi chú lại
        $cancel_sql = "UPDATE orders SET status = 'cancelled', order_note = 'Khách hàng tự hủy đơn.' WHERE id = $cancel_id";
        if(mysqli_query($conn, $cancel_sql)) {
            echo "<script>alert('Hủy đơn hàng thành công!'); window.location.href='Donhang.php';</script>";
            exit();
        } else {
            echo "<script>alert('Lỗi: Không thể hủy đơn hàng ngay lúc này!');</script>";
        }
    } else {
        echo "<script>alert('Không thể hủy! Đơn hàng này đã được cửa hàng xử lý hoặc không tồn tại.'); window.location.href='Donhang.php';</script>";
        exit();
    }
}

// --- 1. LẤY THAM SỐ TÌM KIẾM & LỌC ---
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$filter = isset($_GET['filter']) ? mysqli_real_escape_string($conn, $_GET['filter']) : 'all';

// Tạo điều kiện truy vấn mặc định
$where_clause = "WHERE user_id = $user_id";

// Xử lý tìm kiếm theo mã đơn hàng (Ví dụ: Nhập DH000001 hoặc chỉ số 1 đều ra)
if (!empty($search)) {
    $clean_id = preg_replace('/[^0-9]/', '', $search); // Chỉ giữ lại số
    if (!empty($clean_id)) {
        $where_clause .= " AND id = $clean_id";
    }
}

// Xử lý lọc theo trạng thái đơn hàng
if ($filter !== 'all') {
    if ($filter === 'recent') {
        $where_clause .= " AND order_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    } else {
        $where_clause .= " AND status = '$filter'";
    }
}

// --- 2. THUẬT TOÁN PHÂN TRANG ---
$limit = 5; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$start = ($page - 1) * $limit;

// Tính tổng số đơn dựa trên bộ lọc hiện tại
$sql_total = "SELECT count(id) AS total FROM orders $where_clause";
$res_total = mysqli_query($conn, $sql_total);
$row_total = mysqli_fetch_assoc($res_total);
$total_orders = $row_total['total'] ?? 0;
$total_pages = ceil($total_orders / $limit);

// Lấy dữ liệu đơn hàng theo bộ lọc và phân trang
$sql_orders = "SELECT * FROM orders 
               $where_clause 
               ORDER BY order_date DESC 
               LIMIT $start, $limit";
$res_orders = mysqli_query($conn, $sql_orders);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử đơn hàng - Chicken Joy</title>
    <link rel="stylesheet" href="../css/Donhang.css">
    <style>
        /* Thêm style nhỏ cho nút Hủy đơn */
        .btn-cancel-order {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
            transition: 0.3s ease;
        }
        .btn-cancel-order:hover {
            background: #c82333;
        }
    </style>
</head>
<body>

   <?php include '../includes/header.php'; ?>

    <div class="order-history-container">
        <h1>Lịch sử đơn hàng</h1>
        <p>Theo dõi tất cả đơn hàng của bạn</p>

        <div class="controls-bar">
            <select id="order-filter" onchange="window.location.href='Donhang.php?filter='+this.value">
                <option value="all" <?php echo ($filter == 'all') ? 'selected' : ''; ?>>Tất cả đơn hàng</option>
                <option value="recent" <?php echo ($filter == 'recent') ? 'selected' : ''; ?>>Mới đây (7 ngày)</option>
                <option value="pending" <?php echo ($filter == 'pending') ? 'selected' : ''; ?>>Đang xử lý</option>
                <option value="shipped" <?php echo ($filter == 'shipped') ? 'selected' : ''; ?>>Đang giao</option>
                <option value="delivered" <?php echo ($filter == 'delivered') ? 'selected' : ''; ?>>Đã giao</option>
                <option value="cancelled" <?php echo ($filter == 'cancelled') ? 'selected' : ''; ?>>Đã hủy</option>
            </select>
            <div class="search-box">
                <form action="Donhang.php" method="GET" style="display:flex; width:100%;">
                    <?php if($filter != 'all') echo "<input type='hidden' name='filter' value='$filter'>"; ?>
                    <input type="text" name="search" id="order-search" placeholder="Nhập mã đơn (VD: 000001)..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" id="btn-search">🔍</button>
                </form>
            </div>
        </div>

        <div class="order-list">
            <?php 
            if (mysqli_num_rows($res_orders) > 0):
                while ($order = mysqli_fetch_assoc($res_orders)): 
                    $order_id = $order['id'];
                    
                    // Xử lý trạng thái hiển thị
                    $status_text = "";
                    $status_class = "";
                    switch($order['status']) {
                        case 'pending': $status_text = "Đang xử lý"; $status_class = "processing"; break;
                        case 'processing': $status_text = "Đang chuẩn bị"; $status_class = "processing"; break;
                        case 'shipped': $status_text = "Đang giao"; $status_class = "shipping"; break;
                        case 'delivered': $status_text = "Đã giao"; $status_class = "delivered"; break;
                        case 'cancelled': $status_text = "Đã hủy"; $status_class = "cancelled"; break;
                    }
            ?>
                <div class="order-card status-<?php echo $status_class; ?>">
                    <div class="order-header">
                        <span class="order-id">#DH<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></span>
                        <span class="status-tag <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                        <span class="order-date">Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></span>
                        <span class="order-total"><?php echo number_format($order['total_price'], 0, ',', '.'); ?>đ</span>
                    </div>
                    
                    <div class="order-body">
                        <div class="products-info">
                            <h3>Sản phẩm đã đặt:</h3>
                            <?php
                            $sql_items = "SELECT od.*, p.product_name, p.image 
                                          FROM order_details od 
                                          JOIN products p ON od.product_id = p.id 
                                          WHERE od.order_id = $order_id";
                            $res_items = mysqli_query($conn, $sql_items);
                            while ($item = mysqli_fetch_assoc($res_items)):
                            ?>
                            <div class="product-item">
                                <img src="../images/<?php echo $item['image']; ?>" alt="<?php echo $item['product_name']; ?>">
                                <div class="product-details">
                                    <p><?php echo $item['product_name']; ?></p>
                                    <p class="quantity">Số lượng: <?php echo $item['quantity']; ?></p>
                                </div>
                                <span class="product-price"><?php echo number_format($item['price_at_purchase'], 0, ',', '.'); ?>đ</span>
                            </div>
                            <?php endwhile; ?>
                        </div>

                        <div class="<?php echo ($order['status'] == 'cancelled') ? 'cancellation-info' : 'shipping-info'; ?>">
                            <h3><?php echo ($order['status'] == 'cancelled') ? 'Lý do hủy:' : 'Thông tin giao hàng:'; ?></h3>
                            <?php if($order['status'] == 'cancelled'): ?>
                                <p><?php echo htmlspecialchars($order['order_note'] ?: "Hệ thống hoặc khách hàng đã hủy đơn."); ?></p>
                                <button class="btn-cancelled" style="background:#6c757d; color:white; border:none; padding:8px 15px; border-radius:4px; cursor:not-allowed;" disabled>Đã hủy</button>
                            <?php else: ?>
                                <p><strong>Người nhận:</strong> <?php echo htmlspecialchars($order['receiver_name']); ?></p>
                                <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
                                <p><strong>SĐT:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                                
                                <div style="display: flex; gap: 10px; margin-top: 15px;">
                                    <button class="btn-view" onclick="window.location.href='Dadathang.php?order_id=<?php echo $order_id; ?>'">Xem hóa đơn</button>
                                    
                                    <?php if($order['status'] == 'pending'): ?>
                                        <a href="Donhang.php?action=cancel&order_id=<?php echo $order_id; ?>" 
                                           class="btn-view"
                                           onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng #DH<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?> không? Hành động này không thể hoàn tác.');">
                                           Hủy đơn
                                        </a>
                                    <?php endif; ?>
                                </div>
                                
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php 
                endwhile; 
            else:
                echo "<div style='text-align:center; padding:50px;'>
                        <p>Không tìm thấy đơn hàng nào phù hợp.</p>
                        <a href='Thucdon.php' style='color:#ca2510; font-weight:bold;'>Quay lại đặt món ngay!</a>
                      </div>";
            endif;
            ?>
        </div>

        <div class="pagination">
            <?php 
            $query_string = "&filter=$filter&search=$search";
            if ($page > 1): ?>
                <a href="Donhang.php?page=<?php echo ($page - 1) . $query_string; ?>">&lt;</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="Donhang.php?page=<?php echo $i . $query_string; ?>" 
                class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="Donhang.php?page=<?php echo ($page + 1) . $query_string; ?>">&gt;</a>
            <?php endif; ?>
        </div>
    </div>

    <script src="../js/main.js"></script>
</body>
</html>