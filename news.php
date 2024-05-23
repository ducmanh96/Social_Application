<?php
// news.php

// データベースに接続
require_once('db_connection.php');

// セッションの開始
session_start();
// ユーザーがログインしていない場合は、ログインページにリダイレクト
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
// ユーザーの投稿と投稿者情報、投稿日時を含むすべての投稿を取得するSQLクエリを実行
$sqlPosts = "SELECT Posts.*, Users.Username, Users.Avatar FROM Posts JOIN Users ON Posts.UserID = Users.UserID ORDER BY CreatedAt DESC";
$resultPosts = $conn->query($sqlPosts);

// データベースからユーザー情報を取得
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
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng tin</title>
    <script src="https://code.jquery.com/jquery-3.6.4.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #FAEBD7;
            padding: 20px;
        }

        .navbar {
            background-color: #007bff;
        }

        .navbar-brand {
            color: white;
        }

        .navbar-light .navbar-nav .nav-link {
            color: white;
        }

        .navbar-light .navbar-toggler-icon {
            background-color: white;
        }

        .post-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
        }

        .post-item {
            margin: 20px;
            max-width: 400px;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px 0px #000000;
        }

        .like-button, .post-comment-button {
            cursor: pointer;
        }

        .liked {
            color: red;
        }

        .comments-container {
            margin-top: 10px;
        }

        .comment-input {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            display: inline-block;
            border: 1px solid #ccc;
            box-sizing: border-box;
            border-radius: 5px;
        }

        .post-comment-button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        .post-comment-button:hover {
            background-color: #0056b3;
        }

        .likes-count {
            margin-left: 5px;
        }
        
        .comments-container {
            display: none;
        }

        .comments-container.active {
            display: block;
        }

        .show-comments-button {
            background-color: #007bff;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        .show-comments-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="news_feed.php">
        <?php echo '<h2> ' . $_SESSION['username'] . 'さん!</h2>';?>
    </a>
    <!-- 検索フォーム -->
    <div class="d-flex mx-auto"> <!-- 中央寄せのために mx-auto クラスを追加 -->
        <form class="d-flex" action="#" method="get">
            <input class="form-control me-2" type="search" name="search" id="search" placeholder="ユーザーを検索" aria-label="Search" required>
            <button class="btn btn-primary ml-2" type="submit">Search</button> <!-- 検索ボックスと検索ボタンの間にスペースを作るために ml-2 クラスを追加 -->
        </form>
    </div>
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

<!-- news.php画面内 -->
<div class="post-container">
    <?php
    // ユーザーと投稿情報を表示
    while ($row = $resultPosts->fetch_assoc()) {
        echo '<div class="post-item">';
        echo '    <p>投稿者: ' . $row['Username'] . '</p>';
        echo '    <p>投稿日時: ' . $row['CreatedAt'] . '</p>'; // 投稿日時を表示
        echo '    <p>' . $row['Caption'] . '</p>';
        echo '    <p>' . $row['TextContent'] . '</p>';

        // 画像と動画をMediaPaths配列から表示
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
        // コメント表示ボタンを追加
        echo '<button class="show-comments-button" data-post-id="' . $row['PostID'] . '">コメント表示</button>';

        // コメントとコメント入力欄を表示
        echo '<div class="comments-container" data-post-id="' . $row['PostID'] . '"></div>';
        echo '<textarea class="comment-input" placeholder="コメントを入力..."></textarea>';
        echo '<button class="post-comment-button">送信</button>';

        // ユーザーが投稿をいいねしているかどうかと、以前の状態が保存されているかどうかを確認して、いいねボタンの色を設定
        $likedBy = $_SESSION["username"];
        $likedArray = json_decode($row['LikedBy'], true);

        // ユーザーが投稿をいいねしているかどうか、および以前の状態が保存されているかどうかを確認
        if ($likedArray && in_array($likedBy, $likedArray) && isset($_SESSION['liked_posts'][$row['PostID']])) {
            $likedClass = 'liked';
        } else {
            $likedClass = '';
        }
        echo '    <button class="like-button ' . $likedClass . '" data-post-id="' . $row['PostID'] . '">' . ($likedClass ? '❤ ' : '🤍 ') . '</button>';
        echo '    <span class="likes-count">' . $row['LikesCount'] . ' いいね</span>';
        echo '</div>';
    }
    ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
    $(document).ready(function () {
        // クライアント側で保存されたいいねの状態があるかどうかを確認
        var likedPosts = JSON.parse(localStorage.getItem('likedPosts')) || [];

        // 保存されたいいねの状態に基づいてハートの色を設定
        likedPosts.forEach(function (postId) {
            $('.like-button[data-post-id="' + postId + '"]').addClass('liked');
        });

        // いいねボタンがクリックされたときのイベントを処理
        $('.like-button').on('click', function () {
            var postId = $(this).data('post-id');
            var likeButton = $(this);

            // いいねといいねの状態を更新するためのAjaxリクエストを送信
            $.ajax({
                url: 'update_likes.php',
                type: 'POST',
                data: { post_id: postId },
                success: function (response) {
                    var data = JSON.parse(response);

                    // ユーザーインターフェース上のいいね数を更新
                    var likesCount = parseInt(likeButton.siblings('.likes-count').text());

                    if (data.isLiked) {
                        likeButton.siblings('.likes-count').text((likesCount + 1) + ' いいね');
                        likeButton.addClass('liked');
                        // まだ存在しない場合、likedPosts配列にpost_idを追加
                        if (likedPosts.indexOf(data.postID) === -1) {
                            likedPosts.push(data.postID);
                        }
                    } else {
                        likeButton.siblings('.likes-count').text((likesCount - 1) + ' いいね');
                        likeButton.removeClass('liked');
                        // likedPosts配列からpost_idを削除
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
                    console.log('Ajaxリクエスト実行中にエラーが発生しました: ' + error);
                }
            });
        });

        // すべてのコメントリストを最初は非表示にする
        $('.comments-container').hide();

        // ユーザーが「コメント表示」ボタンを押したときのイベントを処理
        $('.show-comments-button').on('click', function () {
            var postId = $(this).data('post-id');
            var commentsContainer = $('.comments-container[data-post-id="' + postId + '"]');
            
            // コメントリストがまだない場合、ロードする
            if (!commentsContainer.hasClass('active')) {
                loadComments(postId);
            }
            
            // ユーザーがボタンを押したときにコメントリストを表示
            commentsContainer.slideToggle();
        });

        // ユーザーが「コメント送信」ボタンを押したときのイベントを処理
        $('.post-comment-button').on('click', function () {
            var postId = $(this).siblings('.comments-container').data('post-id');
            var commentInput = $(this).siblings('.comment-input');
            var commentContent = commentInput.val();
            // コメントの内容が空でないか確認
            if (commentContent.trim() === '') {
                // メッセージを表示したり、何もしなかったりできます
                return;
            }
            // コメントを追加するためのAjaxリクエストを送信
            $.ajax({
                url: 'add_comment.php',
                type: 'POST',
                data: { post_id: postId, content: commentContent },
                success: function (response) {
                    // コメントの追加が成功したら、コメントリストを更新
                    loadComments(postId);
                    // コメント入力欄をクリア
                    commentInput.val('');
                },
                error: function (error) {
                    console.log('Ajaxリクエスト実行中にエラーが発生しました: ' + error);
                }
            });
        });

        // コメントリストをロードするための関数
        function loadComments(postId) {
            var commentsContainer = $('.comments-container[data-post-id="' + postId + '"]');

            // コメントリストを取得するためのAjaxリクエストを送信
            $.ajax({
                url: 'get_comments.php',
                type: 'GET',
                data: { post_id: postId },
                success: function (response) {
                    // コメントリストを表示
                    displayComments(commentsContainer, JSON.parse(response));

                    // 表示するために "active" クラスを追加
                    commentsContainer.addClass('active');
                },
                error: function (error) {
                    console.log('Ajaxリクエスト実行中にエラーが発生しました: ' + error);
                }
            });
        }

        // コメントリストを表示するための関数
        function displayComments(container, commentsList) {
            container.empty(); // 古い内容をクリア

            if (commentsList.length > 0) {
                commentsList.forEach(function (comment) {
                    var commentHtml = '<p><strong>' + comment.username + ':</strong> ' + comment.content;
                    commentHtml += '</p>';
                    container.append(commentHtml);
                });
            } else {
                container.append('<p>コメントはまだありません。</p>');
            }
        }
        // ページが読み込まれたときにコメントリストをロード
        $('.comments-container').each(function () {
            var postId = $(this).data('post-id');
            loadComments(postId);
        });
    });
</script>
</body>
</html>
