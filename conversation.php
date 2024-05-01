<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit();
}

// Подключение к базе данных
$host = 'localhost';
$user = 'root';
$password = '';
$db_name = 'social_net_db';
$link = mysqli_connect($host, $user, $password, $db_name);
if (!$link) die('Ошибка подключения к базе данных');

$currentLogin = $_SESSION['login'];

if (isset($_GET['user'])) {
    $conversationUser = $_GET['user'];
} else {
    header("Location: messages.php");
    exit();
}

$userQuery = "SELECT * FROM user WHERE login='$conversationUser'";
$userResult = mysqli_query($link, $userQuery);
$conversationUserRow = mysqli_fetch_assoc($userResult);

$userQuery = "SELECT * FROM user WHERE login='$currentLogin'";
$userResult = mysqli_query($link, $userQuery);
$currentUserRow = mysqli_fetch_assoc($userResult);

function formatTime($time) {
    return date('d.m.Y H:i', strtotime($time));
}

$conversationUserId = $conversationUserRow['id'];
$currentUserId = $currentUserRow['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_message'])) {
    $content = $_POST['content'];
    $file = $_FILES['file'];

    if (!empty($content) || !empty($file['name'])) {
        if (!empty($file['name'])) {
            $fileName = $file['name'];
            $fileTmpName = $file['tmp_name'];
            $fileError = $file['error'];
            $uploadPath = 'uploads/' . $fileName;
            move_uploaded_file($fileTmpName, $uploadPath);
        } else {
            $fileName = '';
        }

        $sendMessageQuery = "INSERT INTO message (from_id, to_id, content, file, created_at) VALUES ('$currentUserId', '$conversationUserId', '$content', '$fileName', NOW())";
        if (mysqli_query($link, $sendMessageQuery)) {
            header("Location: conversation.php?user=$conversationUser");
            exit();
        } else {
            echo "Ошибка при отправке сообщения: " . mysqli_error($link);
        }
    } else {
        echo "Сообщение или файл не может быть пустым!";
    }
}

$conversationQuery = "SELECT * FROM message WHERE (from_id='$currentUserId' AND to_id='$conversationUserId') OR (from_id='$conversationUserId' AND to_id='$currentUserId') ORDER BY created_at ASC";
$conversationResult = mysqli_query($link, $conversationQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Переписка с <?php echo $conversationUser; ?></title>
    <style>
        .message {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .message p {
            margin: 0;
        }

        .message .file-link {
            display: block;
        }
    </style>
</head>
<body>
<a href="messages.php">Вернуться к перепискам</a>
<h2>Переписка с <?php echo $conversationUser; ?></h2>
<?php while ($messageRow = mysqli_fetch_assoc($conversationResult)) { ?>
    <div class="message">
        <p><strong><?php echo $messageRow['from_id'] === $currentUserId ? 'Вы' : $conversationUser; ?>:</strong></p>
        <?php if (!empty($messageRow['content'])) { ?>
            <p><?php echo $messageRow['content']; ?></p>
        <?php } ?>
        <?php if (!empty($messageRow['file'])) { ?>
            <a class="file-link" href="uploads/<?php echo $messageRow['file']; ?>" target="_blank">Файл: <?php echo $messageRow['file']; ?></a>
        <?php } ?>
        <p><small><?php echo formatTime($messageRow['created_at']); ?></small></p>
    </div>
<?php } ?>

<form method="POST" action="" enctype="multipart/form-data">
    <textarea name="content" placeholder="Введите сообщение" rows="4" cols="50"></textarea><br>
    <input type="file" name="file"><br>
    <button type="submit" name="send_message">Отправить</button>
</form>
</body>
</html>