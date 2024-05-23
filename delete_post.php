<?php
// delete_post.php

// データベースに接続
require_once('db_connection.php');

// セッションの初期化
session_start();

// ユーザーがログインしていない場合、ログインページにリダイレクトする
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// 削除する投稿情報が存在するかどうかを確認
if (!isset($_POST['post_id'])) {
    echo "削除する投稿情報がありません。";
    exit();
}

// Ajaxリクエストから情報を取得
$postID = $_POST['post_id'];
$userID = $_SESSION['user_id'];

// ユーザーが投稿を削除できるかどうかを確認
$sqlCheckOwnership = "SELECT * FROM Posts WHERE PostID = $postID AND UserID = $userID";
$resultCheckOwnership = $conn->query($sqlCheckOwnership);

if ($resultCheckOwnership && $resultCheckOwnership->num_rows > 0) {
    // ユーザーが投稿を削除できる場合、削除を実行
    $sqlDeletePost = "DELETE FROM Posts WHERE PostID = $postID";
    if ($conn->query($sqlDeletePost) === TRUE) {
        echo "success";
    } else {
        echo "error";
    }
} else {
    echo "この投稿を削除する権限がありません。";
}

// データベース接続を閉じる
$conn->close();
?>
