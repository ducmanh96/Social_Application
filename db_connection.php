<?php
$servername = "ducmanh96.mysql.database.azure.com";
$username = "socialapp";
$password = "Manh30091996";
$dbname = "SNSApp";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("接続に失敗しました： " . $conn->connect_error);
}
?>
