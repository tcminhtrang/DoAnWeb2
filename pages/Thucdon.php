<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include '../config/database.php'; 

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$raw_input = $search; 

// Mặc định ban đầu
$search_name = $search;
$category = $_GET['category'] ?? '';
$min_price = (isset($_GET['min_price']) && $_GET['min_price'] !== '') ? (float)$_GET['min_price'] : 0;
$max_price = (isset($_GET['max_price']) && $_GET['max_price'] !== '') ? (float)$_GET['max_price'] : 999999999;

// --- BẮT ĐẦU LOGIC NÂNG CAO ---
if (!empty($search)) {
    // 1. Xử lý đơn vị 'k' hoặc 'K' trong chuỗi (ví dụ: 50k -> 50000)
    $temp_search = preg_replace_callback('/(\d+)[kK]\b/u', function($m) {
        return $m[1] * 1000;
    }, $search);

    // 2. Tìm tất cả các con số có trong chuỗi
    preg_match_all('/\d+/', $temp_search, $matches);
    $numbers = $matches[0];

    // Chỉ tự động tính toán giá từ search nếu người dùng không nhập tay vào ô min_price/max_price
    if (empty($_GET['min_price']) && empty($_GET['max_price'])) {
        if (count($numbers) >= 2) {
            // Có 2 số: hiểu là Khoảng giá (min - max)
            $n1 = (float)$numbers[0]; $n2 = (float)$numbers[1];
            $min_price = min($n1, $n2);
            $max_price = max($n1, $n2);
            if ($min_price < 1000) $min_price *= 1000;
            if ($max_price < 1000) $max_price *= 1000;
        } elseif (count($numbers) == 1) {
            // Có 1 số: hiểu là Giá tối đa (<= max)
            $n1 = (float)$numbers[0];
            if ($n1 < 1000) $n1 *= 1000;
            $max_price = $n1;
            $min_price = 0;
        }
    }

    // 3. Tách phần chữ ra khỏi phần số để tìm tên món chính xác
    // Xóa các số đã tìm thấy khỏi search_name để LIKE ko bị sai
    $search_name = $temp_search;
    foreach ($numbers as $num) {
        // Chỉ xóa số nếu nó đứng tách biệt (tránh xóa số trong tên món nếu có)
        $search_name = preg_replace('/\b' . $num . '\b/u', '', $search_name);
    }
    $search_name = trim(preg_replace('/\s+/', ' ', $search_name));
}
// --- KẾT THÚC LOGIC NÂNG CAO ---

// Giữ nguyên logic đảo giá của bạn
if ($min_price > $max_price && $max_price != 999999999) {
    $temp = $min_price;
    $min_price = $max_price;
    $max_price = $temp;
}

// Giữ nguyên logic Hashtag # của bạn
if (preg_match('/#(\w+)/u', $raw_input, $matches)) {
    $category = $matches[1];
}

$conditions = ["p.status = 'active'"];
$params = [];
$types = "";

// Logic tạo câu SQL LIKE (Giữ nguyên của bạn)
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

// Logic Category (Giữ nguyên của bạn)
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

// Xử lý giá (Giữ nguyên của bạn)
$conditions[] = "p.price >= ? AND p.price <= ?";
$params[] = (float)$min_price;
$params[] = (float)$max_price;
$types .= "dd";

$where_clause = " WHERE " . implode(" AND ", $conditions);

// ... (Phần code Phân trang và Query bên dưới giữ nguyên 100%) ...

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
                           placeholder="Tìm tên món" class="search-input">
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
                                    $selected = ($category == $cat['id']) ? 'selected' : '';
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
        const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    </script>
    <script src="../js/main.js"></script>
</body>
</html>