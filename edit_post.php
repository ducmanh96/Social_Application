<?php
// edit_post.php

// データベースに接続
require_once('db_connection.php');

// セッションの初期化
session_start();

// ユーザーがログインしていない場合、ログインページにリダイレクトする
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// 編集対象の投稿情報が存在するかどうかを確認
if (!isset($_GET['post_id'])) {
    header("Location: news_feed.php");
    exit();
}

// 編集対象の投稿情報を取得
$postID = $_GET['post_id'];
$userID = $_SESSION['user_id'];

$sql = "SELECT * FROM Posts WHERE PostID = $postID AND UserID = $userID";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $post = $result->fetch_assoc();
} else {
    echo "投稿が見つかりませんでしたまたは編集する権限がありません。";
    exit();
}

// フォームが送信された場合の処理
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newCaption = $_POST['new_caption'];
    $newTextContent = $_POST['new_text_content'];

    // データベース内の投稿情報を更新
    $updateSql = "UPDATE Posts SET Caption = '$newCaption', TextContent = '$newTextContent' WHERE PostID = $postID";
    
    if ($conn->query($updateSql) === TRUE) {
        echo "投稿が正常に編集されました";
    } else {
        echo "投稿の編集中にエラーが発生しました：" . $conn->error;
    }
}

// データベース接続を閉じる
$conn->close();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>投稿の編集</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #FAEBD7;
            padding: 20px;
        }

        h2 {
            color: #007bff;
        }

        form {
            max-width: 600px;
            margin: auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px 0px #000000;
        }

        label {
            font-weight: bold;
            margin-bottom: 10px;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            display: inline-block;
            border: 1px solid #ccc;
            box-sizing: border-box;
            border-radius: 5px;
        }

        input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .back-link {
            display: block;
            margin-top: 10px;
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h2>投稿の編集</h2>
    <form action="edit_post.php?post_id=<?php echo $postID; ?>" method="post">
        <div class="form-group">
            <label for="new_caption">新しいキャプション：</label>
            <input type="text" class="form-control" name="new_caption" value="<?php echo $post['Caption']; ?>">
        </div>

        <div class="form-group">
            <label for="new_text_content">新しいテキストコンテンツ：</label>
            <textarea class="form-control" name="new_text_content"><?php echo $post['TextContent']; ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">編集内容を保存</button>
    </form>

    <a href="news_feed.php" class="back-link">戻る</a>
</div>

</body>
</html>
