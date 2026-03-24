<?php
require_once '../config/database.php';

$id = $_GET['id'];

$newPass = "123456";

mysqli_query($conn, "UPDATE users SET password='$newPass' WHERE id=$id");

header("Location: user-management.php");
?>