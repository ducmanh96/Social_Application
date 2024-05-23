<?php
// news_feed.php

// データベースへの接続
require_once('db_connection.php');
// セッションの開始
session_start();
// ユーザーがログインしていない場合、ログインページにリダイレクト
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
// フォームが送信されたときに画像、ビデオ、およびテキストを処理
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["file"])) {
    $userID = $_SESSION['user_id'];
    $caption = $_POST['caption'];
    $textContent = $_POST['text_content'];
    $mediaPaths = array(); // 画像とビデオのパスを格納する配列

    // 複数の画像またはビデオを処理
    foreach ($_FILES["file"]["name"] as $key => $value) {
        $mediaPath = "uploads/" . basename($_FILES["file"]["name"][$key]);
        move_uploaded_file($_FILES["file"]["tmp_name"][$key], $mediaPath);
        $mediaPaths[] = $mediaPath;
    }

    // 画像とビデオのパスを1つの投稿に保存
    $mediaPathsJson = json_encode($mediaPaths);

    $sql = "INSERT INTO Posts (UserID, Caption, TextContent, MediaPaths, LikesCount, LikedBy) 
            VALUES ('$userID', '$caption', '$textContent', '$mediaPathsJson', 0, '[]')";
    
    // クエリを実行
    if ($conn->query($sql) === TRUE) {
        // クエリが成功した場合、再度ページを読み込むことなくトップページにリダイレクト
        header("Location: news_feed.php");
        exit();
    } else {
        // クエリの実行中にエラーが発生した場合
        echo "投稿エラー: " . $conn->error;
    }
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

// データベースから投稿リストを取得
if (isset($_GET['search'])) {
    // 検索情報がある場合、ユーザー名でユーザー情報を取得
    $searchUsername = $_GET['search'];
    $sqlUser = "SELECT * FROM Users WHERE Username = '$searchUsername'";
    $resultUser = $conn->query($sqlUser);

    if ($resultUser && $resultUser->num_rows > 0) {
        $rowUser = $resultUser->fetch_assoc();
        $userID = $rowUser['UserID'];
    } else {
        echo "$searchUsername というユーザーが見つかりませんでした";
        exit();
    }
} else {
    // 検索情報がない場合、現在のユーザーIDを使用
    $userID = $_SESSION['user_id'];
}

// SQLクエリを実行してユーザーの投稿を取得
$sqlPosts = "SELECT * FROM Posts WHERE UserID = $userID";
$resultPosts = $conn->query($sqlPosts);

// データベースの接続を閉じる
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> ニュース</title>
    <script src="https://code.jquery.com/jquery-3.6.4.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #FAEBD7;
        }

        .navbar {
            background-color: #007bff;
        }

        .navbar-brand {
            color: #ffffff;
        }

        .navbar-light .navbar-toggler-icon {
            background-color: #ffffff;
        }

        .navbar-light .navbar-toggler {
            border-color: #ffffff;
        }

        .navbar-light .navbar-nav .nav-link {
            color: #007bff;
        }

        .navbar-light .navbar-nav .nav-link:hover {
            color: #0056b3;
        }

        .dropdown-item {
            color: #007bff;
        }

        .dropdown-item:hover {
            background-color: #007bff;
            color: #ffffff;
        }

        .container {
            margin-top: 20px;
        }

        .form-control {
            border-radius: 5px;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            border-radius: 5px;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .post-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            margin-top: 20px;
        }

        .post-item {
            margin: 20px;
            max-width: 400px;
            background-color: #ffffff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px 0px #000000;
        }

        .like-button {
            cursor: pointer;
        }

        .liked {
            color: red;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="news.php">
        <?php echo '<h2> こんにちは ' . $_SESSION['username'] . ' !            🏠         </h2>';?>
    </a>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img src="<?php echo $userAvatar; ?>" alt="Avatar" style="max-width: 30px; border-radius: 50%;">
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item" href="edit_profile.php">プロフィールを編集</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="logout.php">ログアウト</a>
                </div>
            </li>
        </ul>
    </div>
</nav>

<div class="container">
    <h2>新しい投稿</h2>
    <form action="news_feed.php" method="post" enctype="multipart/form-data" class="mx-auto" style="max-width: 500px;">
        <div class="mb-3">
            <label for="caption" class="form-label">キャプション:</label>
            <input type="text" class="form-control" name="caption" id="caption">
        </div>
        <div class="mb-3">
            <label for="text_content" class="form-label">コンテンツ:</label>
            <textarea class="form-control" name="text_content" id="text_content" rows="2"></textarea>
        </div>
        <div class="mb-3">
            <label for="file" class="form-label">画像またはビデオを選択:</label>
            <input type="file" class="form-control" name="file[]" id="file" accept="image/*,video/*" multiple>
        </div>
        <button type="submit" class="btn btn-primary">投稿する</button>
    </form>
</div>

<div class="post-container mx-auto">
 <?php
 // 投稿リストを表示
 while ($row = $resultPosts->fetch_assoc()) {
     echo '<div class="post-item">';
     echo '    <p>' . $row['Caption'] . '</p>';
     echo '    <p>' . $row['TextContent'] . '</p>';
 
     // ユーザーが現在のユーザーである場合、編集および削除を表示
     if ($row['UserID'] == $_SESSION['user_id']) {
         echo '    <a href="edit_post.php?post_id=' . $row['PostID'] . '">編集</a>';
         echo '    <button class="delete-button" data-post-id="' . $row['PostID'] . '">削除</button>';
     }
 
     // MediaPathsの配列から画像とビデオを表示
     $mediaPaths = json_decode($row['MediaPaths']);
     if (!empty($mediaPaths)) {
         echo '<div id="carousel-' . $row['PostID'] . '" class="carousel slide" data-ride="carousel">';
         echo '    <div class="carousel-inner">';
         foreach ($mediaPaths as $index => $mediaPath) {
             $activeClass = $index === 0 ? 'active' : '';
             // メディアの種類を確認して表示
             $mediaType = mime_content_type($mediaPath);
             if (strpos($mediaType, "image") !== false) {
                 echo '    <div class="carousel-item ' . $activeClass . '"><img src="' . $mediaPath . '" class="d-block w-100" alt="Image"></div>';
             } elseif (strpos($mediaType, "video") !== false) {
                 echo '    <div class="carousel-item ' . $activeClass . '">';
                 echo '        <video controls class="d-block w-100"><source src="' . $mediaPath . '" type="video/mp4"></video>';
                 echo '    </div>';
             }
         }
         echo '    </div>';
         echo '    <a class="carousel-control-prev" href="#carousel-' . $row['PostID'] . '" role="button" data-slide="prev">';
         echo '        <span class="carousel-control-prev-icon" aria-hidden="true"></span>';
         echo '        <span class="sr-only">Previous</span>';
         echo '    </a>';
         echo '    <a class="carousel-control-next" href="#carousel-' . $row['PostID'] . '" role="button" data-slide="next">';
         echo '        <span class="carousel-control-next-icon" aria-hidden="true"></span>';
         echo '        <span class="sr-only">Next</span>';
         echo '    </a>';
         echo '</div>';
     }
 
     // ユーザーが投稿をいいねしているかどうか、および以前の状態が保存されているかどうかを確認
     $likedBy = $_SESSION["username"];
     $likedArray = json_decode($row['LikedBy'], true);
 
     // ユーザーが投稿をいいねしており、以前の状態が保存されている場合
     if ($likedArray && in_array($likedBy, $likedArray) && isset($_SESSION['liked_posts'][$row['PostID']])) {
         $likedClass = 'liked';
     } else {
         $likedClass = '';
     }
     echo '    <button class="like-button ' . $likedClass . '" data-post-id="' . $row['PostID'] . '">' . ($likedClass ? '❤ ' : '🤍 ') . '</button>';
     echo '    <span class="likes-count">' . $row['LikesCount'] . '</span>';
     echo '</div>';
   }
 ?>
</div>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
    $(document).ready(function () {
        // クライアント側で保存されたいいねの状態データがあるかどうかを確認
        var likedPosts = JSON.parse(localStorage.getItem('likedPosts')) || [];
        // 保存されたいいねの状態に基づいてハートの色を設定
        likedPosts.forEach(function (postId) {
            $('.like-button[data-post-id="' + postId + '"]').addClass('liked');
        });
        // ユーザーが「いいね」ボタンをクリックしたときのイベント処理
        $('.like-button').on('click', function () {
            var postId = $(this).data('post-id');
            var likeButton = $(this);

            // いいねの数と状態を更新するためのAjaxリクエストを送信
            $.ajax({
                url: 'update_likes.php',
                type: 'POST',
                data: { post_id: postId },
                success: function (response) {
                    var data = JSON.parse(response);

                    // ユーザーインターフェース上でいいねの数を更新
                    var likesCount = parseInt(likeButton.siblings('.likes-count').text());

                    if (data.isLiked) {
                        likeButton.siblings('.likes-count').text((likesCount + 1) + ' Likes');
                        likeButton.addClass('liked');
                        // まだ存在しない場合、likedPostsにpost_idを追加
                        if (likedPosts.indexOf(data.postID) === -1) {
                            likedPosts.push(data.postID);
                        }
                    } else {
                        likeButton.siblings('.likes-count').text((likesCount - 1) + ' Likes');
                        likeButton.removeClass('liked');
                        // likedPostsからpost_idを削除
                        likedPosts = likedPosts.filter(function (id) {
                            return id !== data.postID;
                        });
                    }

                    // クライアント側でいいねの状態を保存
                    localStorage.setItem('likedPosts', JSON.stringify(likedPosts));

                    // いいねボタンのテキストを変更
                    likeButton.text(data.isLiked ? '❤ ' : '🤍 ');
                },
                error: function (error) {
                    console.log('Ajaxリクエストの実行中にエラーが発生しました: ' + error);
                }
            });
        });
    });
    $(document).ready(function() {
        $(".delete-button").on("click", function() {
            var postId = $(this).data("post-id");
            var confirmDelete = confirm("この投稿を削除してもよろしいですか？");
            if (confirmDelete) {
                // 投稿を削除するためのAjaxリクエストを送信
                $.ajax({
                    url: "delete_post.php",
                    type: "POST",
                    data: { post_id: postId },
                    success: function(response) {
                        // 削除が成功した場合、ユーザーインターフェースから投稿を削除
                        if (response === "success") {
                            $(this).closest(".post-item").remove();
                            // ページを自動的に再読み込み
                            location.reload();
                        } else {
                            alert("投稿の削除中にエラーが発生しました。");
                        }
                    },
                    error: function(error) {
                        console.log("Ajaxリクエストの実行中にエラーが発生しました: " + error);
                    }
                });
            }
        });
    });
</script>

</body>
</html>