<?php
$servername = getenv('DB_SERVER');
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');
$dbname = getenv('DB_NAME');

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("接続に失敗しました： " . $conn->connect_error);
}
?>




<!DOCTYPE html>
<html>
<head>
    <title>Kết nối cơ sở dữ liệu MySQL</title>
</head>
<body>
    <h1>Kết quả kiểm tra kết nối MySQL</h1>
</body>
</html>