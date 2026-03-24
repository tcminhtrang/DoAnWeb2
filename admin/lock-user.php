<?php
require_once '../config/database.php';

$id = $_GET['id'];

mysqli_query($conn, "UPDATE users SET status='locked' WHERE id=$id");

header("Location: user-management.php");
?>