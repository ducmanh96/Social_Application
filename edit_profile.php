<?php
// edit_profile.php

// データベースに接続
require_once('db_connection.php');

// セッションの初期化
session_start();

// ユーザーがログインしていない場合、ログインページにリダイレクトする
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// フォームが送信されたときのユーザー情報更新処理
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userID = $_SESSION['user_id'];

    // ユーザー名の更新処理
    $newUsername = $_POST['new_username'];
    $sqlUpdateUsername = "UPDATE Users SET Username = '$newUsername' WHERE UserID = $userID";
    $conn->query($sqlUpdateUsername);

    // アバターの更新処理 (存在する場合)
    if (!empty($_FILES["new_avatar"]["name"])) {
        $newAvatarPath = "uploads/" . basename($_FILES["new_avatar"]["name"]);
        move_uploaded_file($_FILES["new_avatar"]["tmp_name"], $newAvatarPath);

        $sqlUpdateAvatar = "UPDATE Users SET Avatar = '$newAvatarPath' WHERE UserID = $userID";
        $conn->query($sqlUpdateAvatar);
    }

    // パスワードの更新処理 (存在する場合)
    $newPassword = $_POST['new_password'];
    if (!empty($newPassword)) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $sqlUpdatePassword = "UPDATE Users SET Password = '$hashedPassword' WHERE UserID = $userID";
        $conn->query($sqlUpdatePassword);
    }

    // 情報更新後、news_feed.phpページにリダイレクト
    header("Location: news_feed.php");
    exit();
}

// ユーザー情報をデータベースから取得
$userID = $_SESSION['user_id'];
$sqlUser = "SELECT * FROM Users WHERE UserID = $userID";
$resultUser = $conn->query($sqlUser);

if ($resultUser && $resultUser->num_rows > 0) {
    $rowUser = $resultUser->fetch_assoc();
    $userAvatar = $rowUser['Avatar'];
} else {
    echo "ユーザー情報が見つかりませんでした。";
    exit();
}

// データベース接続を閉じる
$conn->close();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>プロフィール編集</title>
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
            max-width: 400px;
            margin: auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px 0px #000000;
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="password"],
        input[type="file"] {
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
    </style>
</head>
<body>

<div class="container mt-5">
    <h2>プロフィール編集</h2>
    <form action="edit_profile.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="new_username">新しいユーザー名：</label>
            <input type="text" class="form-control" name="new_username" required>
        </div>

        <div class="form-group">
            <label for="new_avatar">新しいアバター画像：</label>
            <input type="file" class="form-control-file" name="new_avatar" accept="image/*">
        </div>

        <div class="form-group">
            <label for="new_password">新しいパスワード：</label>
            <input type="password" class="form-control" name="new_password">
        </div>

        <button type="submit" class="btn btn-primary">変更を保存</button>
    </form>
</div>

</body>
</html>
