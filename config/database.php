<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "chickenjoy"; 

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8");

define('POINT_TO_MONEY', 1000);   
define('MONEY_PER_POINT', 10000);
?>