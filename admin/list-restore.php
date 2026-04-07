<?php
require_once 'check_admin.php';
require_once '../config/database.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $conn->query("UPDATE products SET status = 'active' WHERE id = $id");
}
header("Location: list.php");
exit();
?>