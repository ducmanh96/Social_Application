<?php
// comment.php

// データベースに接続
require_once('db_connection.php');
// セッションの初期化
session_start();

// ログインの確認
if (!isset($_SESSION['username'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['message' => 'コメントするにはログインする必要があります。']);
    exit();
}

// Ajaxリクエストを処理してコメントを追加する
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $postID = $_POST['post_id'];
    $userID = $_SESSION['user_id'];
    $content = $_POST['content'];

    // コメントをデータベースに追加
    $sql = "INSERT INTO Comments (PostID, UserID, Content) VALUES ('$postID', '$userID', '$content')";
    
    if ($conn->query($sql) === TRUE) {
        // クエリの実行が成功した場合
        $commentID = $conn->insert_id; // 追加された新しいコメントのIDを取得
        echo json_encode(['message' => 'コメントが正常に追加されました。', 'comment_id' => $commentID]);
    } else {
        // クエリ実行時のエラーを処理
        http_response_code(500); // Internal Server Error
        echo json_encode(['message' => 'コメントの追加中にエラーが発生しました: ' . $conn->error]);
    }

    // データベース接続を閉じる
    $conn->close();
}
?>
