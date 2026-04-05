<?php
session_start();
require_once '../config/database.php';
$success_msg = "";
if (isset($_SESSION['success_msg'])) {
    $success_msg = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}
$where = "1=1";
if (!empty($_GET['fromDate'])) {
    $from = $_GET['fromDate'];
    $where .= " AND o.order_date >= '$from 00:00:00'";
}

if (!empty($_GET['toDate'])) {
    $to = $_GET['toDate'];
    $where .= " AND o.order_date <= '$to 23:59:59'";
}
if (!empty($_GET['status']) && $_GET['status'] != 'all') {
    $status = $_GET['status'];
    $where .= " AND o.status = '$status'";
}
$sql = "SELECT o.*, u.fullname 
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE $where
        ORDER BY o.id DESC";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="../assets/images/logo-1.png" />
    <title> ChickenJoy Admin | Quản Lý Đơn Đặt Hàng</title>

    <link rel="icon" type="image/png" href="../assets/images/logo-1.png" />
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

        <div class="filters">
            <div class="filter-group">
                <div class="filter">
                    <label>Từ ngày:</label>
                    <input type="date" id="fromDate" value="<?= isset($_GET['fromDate']) ? htmlspecialchars($_GET['fromDate']) : '' ?>" />
                </div>
                <div class="filter">
                    <label>Đến ngày:</label>
                    <input type="date" id="toDate" value="<?= isset($_GET['toDate']) ? htmlspecialchars($_GET['toDate']) : '' ?>" />
                </div>
                <div class="filter">
                    <label>Tình trạng:</label>
                    <select id="statusFilter">
                        <?php
                        $currentStatus = isset($_GET['status']) ? $_GET['status'] : 'all';
                        ?>
                        <option value="all" <?= $currentStatus == 'all' ? 'selected' : '' ?>>Tất cả</option>
                        <option value="pending" <?= $currentStatus == 'pending' ? 'selected' : '' ?>>Chưa xử lý</option>
                        <option value="confirmed" <?= $currentStatus == 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                        <option value="delivered" <?= $currentStatus == 'delivered' ? 'selected' : '' ?>>Đã giao</option>
                        <option value="cancelled" <?= $currentStatus == 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                    </select>
                </div>
            </div>
            <div class="filter">
                <button id="filterBtn" class="btn-primary">Lọc</button>
            </div>
        </div>

        <section class="table-section">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Ngày đặt</th>
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
                            <td><?= date('Y-m-d H:i', strtotime($row['order_date'])) ?></td>
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
                                    <img src="../assets/images/icons/<?= $icon ?>" class="icon">
                                    <?= $statusText ?>
                                </span>
                            </td>

                            <td>
                                <div class="actions">
                                    <a href="order-detail.php?id=<?= $row['id'] ?>" class="btn-view">
                                        <img src="../assets/images/icons/eye.png" alt="Xem chi tiết" class="icon-eye" />
                                        <span>Xem chi tiết</span>
                                    </a>

                                    <div class="quick-actions">
                                        <?php if ($row['status'] == 'pending'): ?>
                                            <a href="update-order-status.php?id=<?= $row['id'] ?>&status=confirmed"
                                                class="status-link btn-confirm"
                                                onclick="return confirm('Xác nhận đơn hàng này?')">Xác nhận</a>
                                            <a href="update-order-status.php?id=<?= $row['id'] ?>&status=delivered"
                                                class="status-link btn-delivery"
                                                onclick="return confirm('Giao hàng ngay?')">Giao</a>
                                            <a href="update-order-status.php?id=<?= $row['id'] ?>&status=cancelled"
                                                class="status-link btn-cancel-order"
                                                onclick="return confirm('Hủy đơn hàng này?')">Huỷ</a>

                                        <?php elseif ($row['status'] == 'confirmed'): ?>
                                            <a href="update-order-status.php?id=<?= $row['id'] ?>&status=pending"
                                                class="status-link btn-undo"
                                                onclick="return confirm('Đưa đơn hàng này quay lại trạng thái Chưa xử lý?')">Hủy
                                                xác nhận</a>

                                            <a href="update-order-status.php?id=<?= $row['id'] ?>&status=delivered"
                                                class="status-link btn-delivery"
                                                onclick="return confirm('Bắt đầu giao hàng?')">Giao</a>
                                            <a href="update-order-status.php?id=<?= $row['id'] ?>&status=cancelled"
                                                class="status-link btn-cancel-order"
                                                onclick="return confirm('Vẫn muốn hủy đơn này?')">Huỷ</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>

                        </tr>
                    <?php 
                        } 
                    } else {
                        echo "<tr><td colspan='6' style='text-align: center; padding: 20px;'>Không có đơn hàng nào!</td></tr>";
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

            window.location.href =
                `order-management.php?fromDate=${from}&toDate=${to}&status=${status}`;
        });
    </script>

</body>

</html>