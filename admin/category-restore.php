<?php
require_once '../config/database.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $conn->query("UPDATE categories SET status = 'active' WHERE id = $id");
}
header("Location: category.php");
exit();
?>