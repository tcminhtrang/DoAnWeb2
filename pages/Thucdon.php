<?php 
// 1. Khởi tạo session và kết nối DB
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../config/database.php'; 

// 2. Lấy tham số Tìm kiếm, Danh mục & Khoảng giá từ URL (GET)
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$min_price = (isset($_GET['min_price']) && $_GET['min_price'] !== '') ? (float)$_GET['min_price'] : 0;
$max_price = (isset($_GET['max_price']) && $_GET['max_price'] !== '') ? (float)$_GET['max_price'] : 999999999;

// 3. Xử lý phân trang
$limit = 8; // Hiển thị 8 món mỗi trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// 4. Xây dựng câu lệnh SQL lọc dữ liệu kết hợp nhiều tiêu chí
$where = " WHERE 1=1 "; 
if ($search != '') {
    $where .= " AND product_name LIKE '%$search%' ";
}
if ($category != '') {
    $where .= " AND category = '$category' ";
}
// Thêm điều kiện lọc theo khoảng giá
$where .= " AND price BETWEEN $min_price AND $max_price ";

// Lấy danh sách sản phẩm theo bộ lọc và phân trang
$sql = "SELECT * FROM products $where LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

// Tính tổng số trang (phải dựa trên điều kiện lọc $where)
$total_sql = "SELECT COUNT(*) FROM products $where";
$total_result = mysqli_query($conn, $total_sql);
$total_rows = mysqli_fetch_array($total_result)[0];
$total_pages = ceil($total_rows / $limit);

// Tạo query string để giữ các tham số lọc khi bấm chuyển trang
$query_string = "search=" . urlencode($search) . "&category=" . urlencode($category) . "&min_price=" . ($min_price > 0 ? $min_price : '') . "&max_price=" . ($max_price < 999999999 ? $max_price : '');
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thực Đơn - Chicken Joy</title>
    <link rel="stylesheet" href="../css/Thucdon.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <main class="main-content">
        <section class="search-bar-wrapper">
            <form action="Thucdon.php" method="GET" class="container">
                <div class="search-bar">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Tìm tên món, hoặc gõ #GaRan, >50k..." class="search-input">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                </div>

                <div class="filter-bar" style="margin-top: 20px; display: flex; flex-wrap: wrap; gap: 20px; align-items: center; background: #fff; padding: 15px; border-radius: 8px;">
                    
                    <div class="category-list">
                        <span class="label">Danh mục:</span>
                        <select name="category" style="padding: 5px 10px; border-radius: 4px; border: 1px solid #ddd;">
                            <option value="">Tất cả</option>
                            <option value="GaRan" <?php echo $category == 'GaRan' ? 'selected' : ''; ?>>Gà rán</option>
                            <option value="Hamburger" <?php echo $category == 'Hamburger' ? 'selected' : ''; ?>>Hamburger</option>
                            <option value="MiY" <?php echo $category == 'MiY' ? 'selected' : ''; ?>>Mì Ý</option>
                            <option value="KhoaiTay" <?php echo $category == 'KhoaiTay' ? 'selected' : ''; ?>>Khoai Tây</option>
                            <option value="NuocUong" <?php echo $category == 'NuocUong' ? 'selected' : ''; ?>>Nước uống</option>
                        </select>
                    </div>
                    
                    <div class="price-range-filter" style="display: flex; align-items: center; gap: 10px;">
                        <span class="label">Khoảng giá:</span>
                        <div class="price-inputs">
                            <input type="number" name="min_price" placeholder="Từ" value="<?php echo ($min_price > 0) ? $min_price : ''; ?>" style="width: 100px; padding: 5px; border: 1px solid #ddd;">
                            <span class="separator">-</span>
                            <input type="number" name="max_price" placeholder="Đến" value="<?php echo ($max_price < 999999999) ? $max_price : ''; ?>" style="width: 100px; padding: 5px; border: 1px solid #ddd;">
                        </div>
                        <button type="submit" class="apply-price-btn" style="background: #ca2510; color: white; border: none; padding: 6px 15px; border-radius: 4px; cursor: pointer;">Áp dụng</button>
                        
                        <button type="button" onclick="window.location.href='Thucdon.php'" style="background: #0e7a3b; color: white; border: none; padding: 6px 15px; border-radius: 4px; cursor: pointer; font-size: 13px;">
                            Xóa tất cả bộ lọc
                        </button>
                    </div>
                </div>
            </form>
        </section>

        <section class="container product-grid">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <div class="product-card">
                        <a href="../pages/Chitietmonan.php?id=<?php echo $row['id']; ?>">
                            <?php if(isset($row['is_new']) && $row['is_new'] == 1): ?>
                                <div class="badge-tag new-tag">New</div>
                            <?php endif; ?>
                            <img src="../images/<?php echo $row['image']; ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                        </a>
                        <div class="card-content">
                            <h3><?php echo htmlspecialchars($row['product_name']); ?></h3>
                            <p class="description"><?php echo htmlspecialchars($row['description']); ?></p>
                            <div class="details">
                                <span class="price"><?php echo number_format($row['price'], 0, ',', '.'); ?>đ</span>
                                <div class="rating"><i class="fas fa-star" style="color: #ffc107;"></i> 4.8</div>
                            </div>
                            <div class="actions">
                                <button class="btn-add-to-cart" data-id="<?php echo $row['id']; ?>">Thêm vào giỏ</button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 50px;">
                    <p style="color: #666;">Không tìm thấy món ăn nào phù hợp với bộ lọc của bạn.</p>
                </div>
            <?php endif; ?>
        </section>

        <div class="container pagination-info" style="margin-top: 30px; display: flex; justify-content: center;">
            <div class="pagination">
                <?php if($page > 1): ?>
                    <a href="Thucdon.php?page=<?php echo $page-1; ?>&<?php echo $query_string; ?>">&laquo;</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="Thucdon.php?page=<?php echo $i; ?>&<?php echo $query_string; ?>" 
                       class="<?php echo ($page == $i) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if($page < $total_pages): ?>
                    <a href="Thucdon.php?page=<?php echo $page+1; ?>&<?php echo $query_string; ?>">&raquo;</a>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Truyền trạng thái đăng nhập từ PHP sang JS
        const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    </script>
    <script src="../js/main.js"></script>
</body>
</html>