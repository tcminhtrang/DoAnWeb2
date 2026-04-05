<?php
require_once '../config/database.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql_check = "SELECT COUNT(*) as total FROM products WHERE category_id = $id";
    $result_check = $conn->query($sql_check);
    $row = $result_check->fetch_assoc();

    if ($row['total'] > 0) {
        $conn->query("UPDATE categories SET status = 'hidden' WHERE id = $id");
    } else {
        $conn->query("DELETE FROM categories WHERE id = $id");
    }
}
header("Location: category.php");
exit();
?>