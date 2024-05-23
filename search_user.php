<?php
// search_user.php

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["search"])) {
    $searchedUsername = $_GET["search"];

    // ユーザーが存在するかどうかを確認するためのSQLクエリの実行
    $sql = "SELECT UserID FROM Users WHERE Username = '$searchedUsername'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        // ユーザーが存在する場合、そのユーザーのニュースフィードページにリダイレクト
        $row = $result->fetch_assoc();
        $userID = $row["UserID"];
        header("Location: news_feed.php?user_id=$userID");
        exit();
    } else {
        // ユーザーが存在しない場合、メッセージを表示できます
        echo "ユーザーが見つかりませんでした。";
    }
}

// データベースの接続を閉じる
$conn->close();
?>
