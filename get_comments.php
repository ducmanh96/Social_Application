<?php
// get_comments.php
// データベースに接続
require_once('db_connection.php');

// POST_IDがリクエストから渡されたかどうかを確認する
if (isset($_GET['post_id'])) {
    $postId = $_GET['post_id'];

    // ポストのコメントリストを取得するためのSQLクエリ
    $sqlComments = "SELECT Comments.*, Users.Username FROM Comments JOIN Users ON Comments.UserID = Users.UserID WHERE Comments.PostID = $postId";
    $resultComments = $conn->query($sqlComments);

    // コメントが存在するか確認する
    if ($resultComments && $resultComments->num_rows > 0) {
        $commentsList = array();

        // 結果をループしてコメントリストに追加
        while ($rowComment = $resultComments->fetch_assoc()) {
            $comment = array(
                'username' => $rowComment['Username'],
                'content' => $rowComment['Content']
            );
            $commentsList[] = $comment;
        }
        // JSON形式でコメントリストを返す
        echo json_encode($commentsList);
    } else {
        // コメントが存在しない場合
        echo json_encode(array());
    }
} else {
    // POST_IDがない場合
    echo "リクエストにPOST_IDが渡されていません。";
}
?>
