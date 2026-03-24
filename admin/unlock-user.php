<?php
require_once '../config/database.php';

$id = $_GET['id'];

mysqli_query($conn, "UPDATE users SET status='active' WHERE id=$id");

header("Location: user-management.php");
?>