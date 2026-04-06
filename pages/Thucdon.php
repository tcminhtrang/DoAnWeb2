<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include '../config/database.php'; 

// 1. Lấy dữ liệu từ ô input (Dùng biến $search để đồng bộ với HTML của bạn)
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$raw_input = $search; // Lưu lại giá trị gốc để hiển thị lên thanh tìm kiếm

$search_name = $search;
$category = $_GET['category'] ?? '';
$min_price = 0;
$max_price = 999999999;

// 2. XỬ LÝ THÔNG MINH (Hashtag và Giá)
if (preg_match('/#(\w+)/u', $raw_input, $matches)) {
    $category = $matches[1];
    $search_name = str_replace($matches[0], '', $search_name);
}

if (preg_match('/>(\d+)/', $raw_input, $matches)) {
    $min_price = (float)$matches[1];
    if ($min_price < 1000) $min_price *= 1000;
    $search_name = str_replace($matches[0], '', $search_name);
} 

if (preg_match('/<(\d+)/', $raw_input, $matches)) {
    $max_price = (float)$matches[1];
    if ($max_price < 1000) $max_price *= 1000;
    $search_name = str_replace($matches[0], '', $search_name);
}

// Nếu không gõ cú pháp mà có số, lấy số đó làm giá tối đa
if ($max_price == 999999999 && preg_match('/\d+/', $search_name, $num_matches)) {
    $price_val = (float)$num_matches[0];
    if ($price_val < 1000) $price_val *= 1000;
    $max_price = $price_val;
    $search_name = str_replace($num_matches[0], '', $search_name);
}

$search_name = trim($search_name); // Đây chính là cụm từ như "gà cay"

// 3. XÂY DỰNG SQL (Tìm kiếm theo tên sản phẩm)
// 3. XÂY DỰNG SQL THÔNG MINH (Tách từ để tìm kiếm linh hoạt)
$conditions = ["1=1"];

// Lọc sản phẩm đang hiển thị (Không hiện sản phẩm đã ẩn)
$conditions[] = "status = 'active'"; 

if ($search_name !== '') {
    $safe_name = mysqli_real_escape_string($conn, $search_name);
    
    // Tách chuỗi tìm kiếm thành các từ riêng biệt (ví dụ: "gà cay" -> ["gà", "cay"])
    $keywords = explode(' ', $search_name);
    $sub_conditions = [];
    
    foreach ($keywords as $word) {
        $word = trim($word);
        if ($word !== '') {
            $safe_word = mysqli_real_escape_string($conn, $word);
            // Mỗi từ đều phải xuất hiện trong tên sản phẩm
            $sub_conditions[] = "product_name LIKE '%$safe_word%'";
        }
    }
    
    // Kết hợp bằng AND: Tên phải chứa chữ "gà" AND phải chứa chữ "cay"
    if (!empty($sub_conditions)) {
        $conditions[] = "(" . implode(" AND ", $sub_conditions) . ")";
    }
}

// Giữ nguyên các lọc Category và Price bên dưới
if ($category !== '') {
    $safe_cat = mysqli_real_escape_string($conn, $category);
    // Lưu ý: Nếu database của cậu lưu tên loại tiếng việt có dấu ở cột category, 
    // cần cẩn thận đối chiếu với value option trong HTML nhé.
    $conditions[] = "category = '$safe_cat'";
}
$conditions[] = "price >= $min_price AND price <= $max_price";

$where_clause = " WHERE " . implode(" AND ", $conditions);

// 4. PHÂN TRANG (Giữ nguyên cấu trúc của bạn)
$limit = 8; 
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM products $where_clause LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

$total_sql = "SELECT COUNT(*) FROM products $where_clause";
$total_rows = mysqli_fetch_array(mysqli_query($conn, $total_sql))[0];
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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