<?php
// データベースに接続
require_once('db_connection.php');

// セッションの初期化
session_start();

// ユーザーがログインしているかどうかを確認し、news_feed.phpページにリダイレクトする
if (isset($_SESSION['username'])) {
    header("Location: news_feed.php");
    exit();
}

// フォームが送信されたときの登録処理
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // フォームからデータを取得
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // パスワードのハッシュ化
    // パスワードの検証条件を確認
    if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $_SESSION['error'] = "パスワードは8文字以上で、大文字と数字を含む必要があります。";
    } else {
        // ユーザー名の重複を確認
        $checkUsernameQuery = "SELECT * FROM Users WHERE Username = ?";
        $stmtUsername = $conn->prepare($checkUsernameQuery);
        $stmtUsername->bind_param("s", $username);
        $stmtUsername->execute();
        $resultUsername = $stmtUsername->get_result();

        // メールアドレスの重複を確認
        $checkEmailQuery = "SELECT * FROM Users WHERE Email = ?";
        $stmtEmail = $conn->prepare($checkEmailQuery);
        $stmtEmail->bind_param("s", $email);
        $stmtEmail->execute();
        $resultEmail = $stmtEmail->get_result();

        if ($resultUsername->num_rows > 0) {
            $_SESSION['error'] = "ユーザー名は既に存在します。別のユーザー名を選択してください。";
        } elseif ($resultEmail->num_rows > 0) {
            $_SESSION['error'] = "メールアドレスは既に登録されています。別のメールアドレスを使用してください。";
        } else {
            // サーバーにアバターをアップロードする処理
            $avatarPath = 'uploads/default_avatar.jpg'; // デフォルトのパス

            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
                $avatarDir = 'uploads/';
                $avatarName = uniqid() . '_' . $_FILES['avatar']['name'];
                $avatarPath = $avatarDir . $avatarName;
                move_uploaded_file($_FILES['avatar']['tmp_name'], $avatarPath);
            }

            // プリペアドステートメントを使用してSQLクエリを準備
            $registerQuery = "INSERT INTO Users (Username, Password, Email, Avatar) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($registerQuery);
            $stmt->bind_param("ssss", $username, $hashedPassword, $email, $avatarPath);

            // クエリを実行
            if ($stmt->execute()) {
                // 登録成功時、ログインページにリダイレクト
                header("Location: login.php");
                exit();
            } else {
                // 登録失敗
                $_SESSION['error'] = "登録に失敗しました。もう一度お試しください。";
            }

            // プリペアドステートメントを閉じる
            $stmt->close();
        }

        // データベースの接続を閉じる
        $conn->close();
    }    
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規登録</title>
    
</head>
    <?php
    // エラーメッセージの表示
    $error_message = isset($_SESSION['error']) ? $_SESSION['error'] : '';
    unset($_SESSION['error']); // 使用後にエラーをリセット

    if ($error_message) {
        echo '<p style="color: red;">' . $error_message . '</p>';
    }
    ?>
    <title>新規登録</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #FAEBD7;
        }

        .register-container {
            margin-top: 50px;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .card-body {
            padding: 40px;
        }

        label {
            font-weight: bold;
        }

        input[type="text"],
        input[type="password"],
        input[type="email"],
        input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }

        input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        p {
            margin-top: 20px;
            text-align: center;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container register-container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <!-- 登録フォーム -->
                    <form action="register.php" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="username">ユーザー名：</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="password">パスワード：</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <!-- メールアドレスの項目を追加 -->
                        <div class="form-group">
                            <label for="email">メールアドレス：</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <!-- アバターの項目を追加 -->
                        <div class="form-group">
                            <label for="avatar">アバター：</label>
                            <input type="file" name="avatar" class="form-control-file" accept="image/*">
                        </div>

                        <div class="form-group">
                            <input type="submit" value="新規登録" class="btn btn-primary">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- アカウントが既にある場合はログインページに移動するリンク -->
<p class="text-center">アカウントをお持ちですか？ <a href="login.php">こちらからログイン</a></p>

</body>
</html>
