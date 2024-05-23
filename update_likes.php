<?php
// update_likes.php

// データベースへの接続
require_once('db_connection.php');

// セッションの開始
session_start();

// news_feed.php から post_id が送信されたかどうかを確認
if (isset($_POST['post_id'])) {
    $postID = $_POST['post_id'];

    // データベースから投稿の情報を取得
    $sqlPost = "SELECT * FROM Posts WHERE PostID = $postID";
    $resultPost = $conn->query($sqlPost);

    if ($resultPost && $resultPost->num_rows > 0) {
        $row = $resultPost->fetch_assoc();

        // ユーザーが既にいいねしているかどうかを確認
        $likedBy = $_SESSION["username"];
        $likedArray = json_decode($row['LikedBy'], true);

        if ($likedArray && in_array($likedBy, $likedArray)) {
            // ユーザーが既にいいねしている場合、いいねを解除
            $likedArray = array_diff($likedArray, array($likedBy));
            $newLikesCount = $row['LikesCount'] - 1;
            $isLiked = false;
        } else {
            // ユーザーがまだいいねしていない場合、いいね
            $likedArray[] = $likedBy;
            $newLikesCount = $row['LikesCount'] + 1;
            $isLiked = true;
        }

        // データベースでいいねの状態を更新
        $updatedLikedBy = json_encode($likedArray);
        $sqlUpdateLikes = "UPDATE Posts SET LikesCount = $newLikesCount, LikedBy = '$updatedLikedBy' WHERE PostID = $postID";

        if ($conn->query($sqlUpdateLikes) === TRUE) {
            // 新しいいいね数、いいねの状態、および post_id を含む JSON を返す
            echo json_encode(array('likesCount' => $newLikesCount, 'isLiked' => $isLiked, 'postID' => $postID));
        } else {
            echo "error";
        }
    } else {
        echo "error";
    }

    // データベースの接続を閉じる
    $conn->close();
} else {
    echo "error";
}
?>
