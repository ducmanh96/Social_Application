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

// フォームが送信されたときのログイン処理
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // フォームからデータを取得
    $username = $_POST['username'];
    $password = $_POST['password'];

    // プリペアドステートメントを使用してSQLクエリを準備
    $loginQuery = "SELECT * FROM Users WHERE Username = ?";
    $stmt = $conn->prepare($loginQuery);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // ユーザーが存在するか確認
    if ($result->num_rows == 1) {
        // クエリ結果からユーザー情報を取得
        $row = $result->fetch_assoc();
        $hashedPassword = $row['Password'];

        // パスワードの確認
        if (password_verify($password, $hashedPassword)) {
            // ログイン成功時、情報をセッションに保存
            $_SESSION['user_id'] = $row['UserID'];
            $_SESSION['username'] = $username;

            // news_feed.phpページにリダイレクト
            header("Location: news_feed.php");
            exit();
        } else {
            // パスワードが正しくない
            $login_error = "パスワードが正しくありません";
        }
    } else {
        // ユーザー名が存在しない
        $login_error = "ユーザー名が存在しません";
    }

    // プリペアドステートメントを閉じる
    $stmt->close();
}

// データベースの接続を閉じる
$conn->close();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン</title>
        <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="path/to/bootstrap.min.css">

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- Popper JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>

    <!-- Latest compiled JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</head>
<body>
<?php
    // エラーメッセージの表示
    if (isset($login_error)) {
        echo '<p style="color: red;">' . $login_error . '</p>';
    }
    ?>
    <style>
        body {
            background-color: #FAEBD7;
        }

        .login-container {
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
        input[type="password"] {
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

<div class="container login-container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <!-- ログインフォーム -->
                    <form action="login.php" method="post">
                        <div class="form-group">
                            <label for="username">ユーザー名：</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="password">パスワード：</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <input type="submit" value="ログイン" class="btn btn-primary">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- アカウントがまだない場合は新規登録ページへのリンク -->
<p class="text-center">アカウントがまだない場合は、<a href="register.php">新規登録</a>してください。</p>

</body>
</html>
