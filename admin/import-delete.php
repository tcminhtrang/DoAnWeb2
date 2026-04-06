<?php
require_once '../config/database.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $conn->query("DELETE FROM import_receipts WHERE id = $id");
}
header("Location: import.php");
exit();
?>