<?php
require_once 'check_admin.php';
require_once '../config/database.php';
$success_msg = "";
if (isset($_SESSION['success_msg'])) {
    $success_msg = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}

// Lấy danh sách phường/xã cho dropdown (Không có tham số user nên truy vấn thường)
$wardSql = "SELECT DISTINCT ward FROM orders WHERE ward IS NOT NULL AND ward != '' ORDER BY ward ASC";
$wardResult = mysqli_query($conn, $wardSql);

// XÂY DỰNG SQL ĐỘNG BẰNG PREPARED STATEMENTS
$conditions = ["1=1"];
$params = [];
$types = "";

if (!empty($_GET['fromDate'])) {
    $conditions[] = "o.order_date >= ?";
    $params[] = $_GET['fromDate'] . " 00:00:00";
    $types .= "s";
}
if (!empty($_GET['toDate'])) {
    $conditions[] = "o.order_date <= ?";
    $params[] = $_GET['toDate'] . " 23:59:59";
    $types .= "s";
}
if (!empty($_GET['status']) && $_GET['status'] != 'all') {
    $conditions[] = "o.status = ?";
    $params[] = $_GET['status'];
    $types .= "s";
}
if (!empty($_GET['ward']) && $_GET['ward'] != 'all') {
    $conditions[] = "o.ward = ?";
    $params[] = trim($_GET['ward']);
    $types .= "s";
}

$where_clause = implode(" AND ", $conditions);

// Mặc định luôn ưu tiên đơn mới nhất (id DESC) để admin dễ làm việc
$order_by = "o.id DESC"; 

// Nếu người dùng chủ động chọn sắp xếp theo phường
if (isset($_GET['sort']) && $_GET['sort'] == 'ward') {
    $order_by = "o.ward ASC, o.id DESC";
}

$sql = "SELECT o.*, u.fullname 
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE $where_clause
        ORDER BY $order_by";

$stmt = mysqli_prepare($conn, $sql);

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

// Thực thi và lấy kết quả
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="../assets/images/logo-1.png" />
    <title> ChickenJoy Admin | Quản Lý Đơn Đặt Hàng</title>
    <link rel="stylesheet" href="../assets/css/admin.css" />
</head>

<body class="admin-body">

    <?php include 'layout/sidebar.php'; ?>

    <main class="main-content">

        <header class="main-header">
            <h1>Quản Lý Đơn Đặt Hàng</h1>
        </header>

        <?php if($success_msg != ""): ?>
            <p style="color: #28a745; font-weight: bold; margin-bottom: 15px;">✓ <?= $success_msg ?></p>
        <?php endif; ?>

        <div class="filter-group" style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 30px;">  
            <div class="filter">
                <label style="font-size: 13px; font-weight: 600; color: #555; display: block; margin-bottom: 5px;">Khoảng thời gian:</label>
                <div style="display: flex; align-items: center; gap: 5px;">
                    <input type="date" id="fromDate" value="<?php echo $_GET['fromDate'] ?? ''; ?>" class="date-input" style="padding: 5px 8px; font-size: 13px; width: 125px; border: 1px solid #ccc; border-radius: 4px; outline: none; color: #333;">
                    <span style="font-size: 13px; color: #777;">đến</span>
                    <input type="date" id="toDate" value="<?php echo $_GET['toDate'] ?? ''; ?>" class="date-input" style="padding: 5px 8px; font-size: 13px; width: 125px; border: 1px solid #ccc; border-radius: 4px; outline: none; color: #333;">
                </div>
            </div>
            
            <div class="filter">
                <label style="font-size: 13px; font-weight: 600; color: #555; display: block; margin-bottom: 5px;">Tình trạng:</label>
                <select id="statusFilter" style="padding: 5px 8px; font-size: 13px; border: 1px solid #ccc; border-radius: 4px; outline: none; color: #333; cursor: pointer;">
                    <?php $currentStatus = isset($_GET['status']) ? $_GET['status'] : 'all'; ?>
                    <option value="all" <?= $currentStatus == 'all' ? 'selected' : '' ?>>Tất cả</option>
                    <option value="pending" <?= $currentStatus == 'pending' ? 'selected' : '' ?>>Chưa xử lý</option>
                    <option value="confirmed" <?= $currentStatus == 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                    <option value="delivered" <?= $currentStatus == 'delivered' ? 'selected' : '' ?>>Đã giao</option>
                    <option value="cancelled" <?= $currentStatus == 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                </select>
            </div>
            
            <div class="filter">
                <label style="font-size: 13px; font-weight: 600; color: #555; display: block; margin-bottom: 5px;">Khu vực:</label>
                <select id="wardFilter" style="padding: 5px 8px; font-size: 13px; max-width: 140px; border: 1px solid #ccc; border-radius: 4px; outline: none; color: #333; cursor: pointer;">
                    <?php $currentWard = isset($_GET['ward']) ? $_GET['ward'] : 'all'; ?>
                    <option value="all" <?= $currentWard == 'all' ? 'selected' : '' ?>>Tất cả phường</option>
                    <?php 
                    if(isset($wardResult) && mysqli_num_rows($wardResult) > 0) {
                        while ($w = mysqli_fetch_assoc($wardResult)) {
                            $selected = ($currentWard == $w['ward']) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($w['ward']) . "' $selected>" . htmlspecialchars($w['ward']) . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            
            <div class="filter">
                <label style="font-size: 13px; font-weight: 600; color: #555; display: block; margin-bottom: 5px;">Sắp xếp theo:</label>
                <select id="sortFilter" style="padding: 5px 8px; font-size: 13px; border: 1px solid #ccc; border-radius: 4px; outline: none; color: #333; cursor: pointer;">
                    <?php $currentSort = isset($_GET['sort']) ? $_GET['sort'] : 'newest'; ?>
                    <option value="newest" <?= $currentSort == 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                    <option value="ward" <?= $currentSort == 'ward' ? 'selected' : '' ?>>Phường </option>
                </select>
            </div>
            <button id="filterBtn" class="btn-primary" style="padding: 5px 20px; font-size: 13px; font-weight: bold; height: 30px; border-radius: 4px; border: none; cursor: pointer;  color: white; transition: 0.2s;">
                <i class="fas fa-filter" style="margin-right: 5px;"></i> Lọc
            </button>
        </div>
        
    

        <section class="table-section">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Khu vực</th> <th>Ngày đặt</th>
                        <th>Tổng tiền</th>
                        <th>Tình trạng</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) { 
                    ?>
                        <tr>
                            <td>DH<?= str_pad($row['id'], 3, '0', STR_PAD_LEFT) ?></td>
                            <td><?= htmlspecialchars($row['fullname']) ?></td>
                            
                            <td style="font-weight: 500; color: #0e7a3b;">
                                <?= !empty($row['ward']) ? htmlspecialchars($row['ward']) : 'Chưa cập nhật' ?>
                            </td>

                            <td><?= date('d/m/Y H:i', strtotime($row['order_date'])) ?></td>
                            <td style="font-weight: bold; color: #e74c3c;"><?= number_format($row['total_price']) ?>đ</td>

                            <td>
                                <?php
                                $statusText = "";
                                $class = "";
                                $icon = "";

                                switch ($row['status']) {
                                    case 'pending':
                                        $statusText = "Chưa xử lý";
                                        $class = "pending";
                                        $icon = "clock-three.png";
                                        break;
                                    case 'confirmed':
                                        $statusText = "Đã xác nhận";
                                        $class = "confirmed";
                                        $icon = "check.png";
                                        break;
                                    case 'delivered':
                                        $statusText = "Đã giao";
                                        $class = "delivered";
                                        $icon = "open-box-check.png";
                                        break;
                                    case 'cancelled':
                                        $statusText = "Đã huỷ";
                                        $class = "cancelled";
                                        $icon = "cross-small.png";
                                        break;
                                }
                                ?>
                                <span class="status <?= $class ?>">
                                    <img src="../assets/images/icons/<?= $icon ?>" class="icon" onerror="this.style.display='none'">
                                    <?= $statusText ?>
                                </span>
                            </td>

                            <td>
                                <div class="actions">
                                    <a href="order-detail.php?id=<?= $row['id'] ?>" class="btn-view">
                                        <img src="../assets/images/icons/eye.png" alt="Xem" class="icon-eye" onerror="this.style.display='none'" />
                                        <span>Xem chi tiết</span>
                                    </a>

                                    <div class="quick-actions">
                                        <?php if ($row['status'] == 'pending'): ?>
                                            <a href="update-order-status.php?id=<?= $row['id'] ?>&status=confirmed" class="status-link btn-confirm" onclick="return confirm('Xác nhận đơn hàng này?')">Xác nhận</a>
                                            <a href="update-order-status.php?id=<?= $row['id'] ?>&status=delivered" class="status-link btn-delivery" onclick="return confirm('Giao hàng ngay?')">Giao</a>
                                            <a href="update-order-status.php?id=<?= $row['id'] ?>&status=cancelled" class="status-link btn-cancel-order" onclick="return confirm('Hủy đơn hàng này?')">Huỷ</a>
                                        <?php elseif ($row['status'] == 'confirmed'): ?>
                                            <a href="update-order-status.php?id=<?= $row['id'] ?>&status=pending" class="status-link btn-undo" onclick="return confirm('Đưa đơn hàng này quay lại trạng thái Chưa xử lý?')">Hủy xác nhận</a>
                                            <a href="update-order-status.php?id=<?= $row['id'] ?>&status=delivered" class="status-link btn-delivery" onclick="return confirm('Bắt đầu giao hàng?')">Giao</a>
                                            <a href="update-order-status.php?id=<?= $row['id'] ?>&status=cancelled" class="status-link btn-cancel-order" onclick="return confirm('Vẫn muốn hủy đơn này?')">Huỷ</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php 
                        } 
                    } else {
                        echo "<tr><td colspan='7' style='text-align: center; padding: 20px;'>Không có đơn hàng nào!</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </main>

    <script>
        document.getElementById("filterBtn").addEventListener("click", () => {
            let from = document.getElementById("fromDate").value;
            let to = document.getElementById("toDate").value;
            let status = document.getElementById("statusFilter").value;
            let ward = document.getElementById("wardFilter").value; 
            let sort = document.getElementById("sortFilter").value;
            window.location.href = `order-management.php?fromDate=${from}&toDate=${to}&status=${status}&ward=${ward}&sort=${sort}`;
        });
        const fromInput = document.getElementById("fromDate");
        const toInput = document.getElementById("toDate");

        // 1. CHỐT CHẶN MỀM: Tự động giới hạn ngày chọn trên Lịch
        fromInput.addEventListener("change", function() {
            toInput.min = this.value; // "Đến ngày" không được nhỏ hơn "Từ ngày"
        });

        toInput.addEventListener("change", function() {
            fromInput.max = this.value; // "Từ ngày" không được lớn hơn "Đến ngày"
        });

        // 2. XỬ LÝ KHI BẤM NÚT LỌC
        document.getElementById("filterBtn").addEventListener("click", () => {
            let from = fromInput.value;
            let to = toInput.value;

            // CHỐT CHẶN CỨNG: Hiển thị cảnh báo nếu vẫn cố tình nhập ngược
            if (from !== "" && to !== "") {
                if (from > to) {
                    alert("CẢNH BÁO: 'Từ ngày' không thể lớn hơn 'Đến ngày'! Vui lòng chọn lại khoảng thời gian.");
                    return; // Lệnh return này sẽ ngắt luồng, chặn không cho reload trang
                }
            }

            let status = document.getElementById("statusFilter").value;
            let ward = document.getElementById("wardFilter").value; 
            let sort = document.getElementById("sortFilter").value;

            window.location.href = `order-management.php?fromDate=${from}&toDate=${to}&status=${status}&ward=${ward}&sort=${sort}`;
        });
    </script>

</body>
</html>