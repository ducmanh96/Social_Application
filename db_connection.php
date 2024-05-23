<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "SNSApp";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("接続に失敗しました： " . $conn->connect_error);
}
?>
