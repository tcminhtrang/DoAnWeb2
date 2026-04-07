<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include '../config/database.php'; 

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$raw_input = $search; 

$search_name = $search;
$category = $_GET['category'] ?? '';

$min_price = (isset($_GET['min_price']) && $_GET['min_price'] !== '') ? (float)$_GET['min_price'] : 0;
$max_price = (isset($_GET['max_price']) && $_GET['max_price'] !== '') ? (float)$_GET['max_price'] : 999999999;

if ($min_price > $max_price && $max_price != 999999999) {
    $temp = $min_price;
    $min_price = $max_price;
    $max_price = $temp;
}

if (preg_match('/#(\w+)/u', $raw_input, $matches)) {
    $category = $matches[1];
    $search_name = str_replace($matches[0], '', $search_name);
}

if (preg_match('/>(\d+)[kK]?/u', $raw_input, $matches)) {
    $min_price = (float)$matches[1];
    if ($min_price < 1000) $min_price *= 1000;
    $search_name = str_replace($matches[0], '', $search_name);
} 

if (preg_match('/<(\d+)[kK]?/u', $raw_input, $matches)) {
    $max_price = (float)$matches[1];
    if ($max_price < 1000) $max_price *= 1000;
    $search_name = str_replace($matches[0], '', $search_name);
}
$search_name = trim($search_name);

$conditions = ["p.status = 'active'"];
$params = [];
$types = "";

if ($search_name !== '') {
    $keywords = explode(' ', $search_name);
    $sub_conditions = [];
    
    foreach ($keywords as $word) {
        $word = trim($word);
        if ($word !== '') {
            $sub_conditions[] = "p.product_name LIKE ?";
            $params[] = "%" . $word . "%"; 
            $types .= "s";
        }
    }
    
    if (!empty($sub_conditions)) {
        $conditions[] = "(" . implode(" AND ", $sub_conditions) . ")";
    }
}

if ($category !== '') {
    if (is_numeric($category)) {
        $conditions[] = "p.category_id = ?";
        $params[] = (int)$category;
        $types .= "i";
    } else {
        $conditions[] = "REPLACE(c.category_name, ' ', '') LIKE ?";
        $params[] = "%" . $category . "%";
        $types .= "s";
    }
}
// Xử lý giá
$conditions[] = "p.price >= ? AND p.price <= ?";
$params[] = (float)$min_price;
$params[] = (float)$max_price;
$types .= "dd"; // d đại diện cho kiểu double/float

$where_clause = " WHERE " . implode(" AND ", $conditions);

// 4. PHÂN TRANG VÀ THỰC THI TRUY VẤN CHÍNH
$limit = 8; 
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

$sql = "SELECT p.* FROM products p 
        JOIN categories c ON p.category_id = c.id 
        $where_clause 
        LIMIT ? OFFSET ?";

// Thêm limit và offset vào mảng tham số
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

// Chuẩn bị câu lệnh (Prepare)
$stmt = mysqli_prepare($conn, $sql);

// Gắn tham số linh hoạt bằng Splat Operator (...)
mysqli_stmt_bind_param($stmt, $types, ...$params);

// Thực thi
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// 5. CẬP NHẬT CÂU ĐẾM TỔNG (TOTAL ROWS) CHO PHÂN TRANG
$total_sql = "SELECT COUNT(p.id) FROM products p 
              JOIN categories c ON p.category_id = c.id 
              $where_clause";

$total_stmt = mysqli_prepare($conn, $total_sql);

// Gắn tham số cho câu đếm tổng (bỏ 2 tham số LIMIT và OFFSET cuối cùng)
$total_types = substr($types, 0, -2);
$total_params = array_slice($params, 0, -2);

if (!empty($total_params)) {
    mysqli_stmt_bind_param($total_stmt, $total_types, ...$total_params);
}

mysqli_stmt_execute($total_stmt);
$total_result = mysqli_stmt_get_result($total_stmt);
$total_rows = mysqli_fetch_array($total_result)[0];
$total_pages = ceil($total_rows / $limit);

$query_string = "search=" . urlencode($raw_input) . "&category=" . urlencode($category) . "&min_price=" . $min_price . "&max_price=" . ($max_price == 999999999 ? '' : $max_price);
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
                            <?php
                            $cat_query = mysqli_query($conn, "SELECT * FROM categories WHERE status = 'active' ORDER BY category_name ASC");
                            if (mysqli_num_rows($cat_query) > 0) {
                                while($cat = mysqli_fetch_assoc($cat_query)) {
                                    // Kiểm tra xem ID danh mục này có đang được chọn không
                                    $selected = ($category == $cat['id']) ? 'selected' : '';
                                    // In ra thẻ option
                                    echo "<option value='{$cat['id']}' $selected>" . htmlspecialchars($cat['category_name']) . "</option>";
                                }
                            }
                            ?>
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
                            <img src="../images/<?php echo $row['image']; ?>" onerror="this.src='../images/default.jpg'" alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                        </a>
                        <div class="card-content">
                            <h3><?php echo htmlspecialchars($row['product_name']); ?></h3>
                            <p class="description"><?php echo htmlspecialchars($row['description']); ?></p>
                            <div class="details">
                                <span class="price"><?php echo number_format($row['price'], 0, ',', '.'); ?>đ</span>
                                <div class="rating"><i class="fas fa-star" style="color: #ffc107;"></i> 4.8</div>
                            </div>
                            <div class="actions">
                                <?php if($row['stock'] > 0): ?>
                                    <button class="btn-add-to-cart" data-id="<?php echo $row['id']; ?>">Thêm vào giỏ</button>
                                <?php else: ?>
                                    <button class="btn-add-to-cart" disabled style="background-color: #ccc; color: #666; cursor: not-allowed; border: none;">Đã hết hàng</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 50px;">
                    <p style="color: #666; font-size: 16px;">Không tìm thấy món ăn nào phù hợp với bộ lọc của bạn.</p>
                </div>
            <?php endif; ?> </section>

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