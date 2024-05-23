<?php
// add_comment.php

// データベースに接続
require_once('db_connection.php');
// セッションの初期化
session_start();

// post_idおよびcontentがリクエストされているかどうかを確認
if (isset($_POST['post_id']) && isset($_POST['content'])) {
    $postId = $_POST['post_id'];
    $content = $_POST['content'];
    $userId = $_SESSION['user_id'];

    // SQLインジェクションを防ぐためにプリペアドステートメントを使用
    $sqlAddComment = "INSERT INTO Comments (PostID, UserID, Content) VALUES (?, ?, ?)";
    $stmtAddComment = $conn->prepare($sqlAddComment);
    $stmtAddComment->bind_param("iis", $postId, $userId, $content);

    // データベースにコメントを追加するSQLクエリの実行
    if ($stmtAddComment->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    // プリペアドステートメントを閉じる
    $stmtAddComment->close();
} else {
    // post_idまたはcontentがない場合
    echo "missing_params";
}
?>
