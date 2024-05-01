<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit();
}

$host = 'localhost';
$user = 'root';
$password = '';
$db_name = 'social_net_db';
$link = mysqli_connect($host, $user, $password, $db_name);
if (!$link) die('Ошибка подключения к базе данных');

$currentLogin = $_SESSION['login'];
$idQuery = "SELECT id FROM user WHERE login='$currentLogin'";
$idResult = mysqli_query($link, $idQuery);
$idRow = mysqli_fetch_assoc($idResult);
$currentUser = $idRow['id'];

$friendQuery = "SELECT * FROM user WHERE id IN (SELECT friend2_id FROM friendship WHERE friend1_id='$currentUser') OR id IN (SELECT friend1_id FROM friendship WHERE friend2_id='$currentUser')";
$friendResult = mysqli_query($link, $friendQuery);

$friendIds = [];
while ($row = mysqli_fetch_assoc($friendResult)) {
    $friendIds[] = $row['id'];
}

if (!empty($friendIds)) {
    $conversationQuery = "SELECT * FROM user WHERE id IN (" . implode(',', $friendIds) . ")";
    $conversationResult = mysqli_query($link, $conversationQuery);
} else {
    $noFriendsMessage = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <style>
        #main_page_link {
            display: inline-block;
            margin-bottom: 20px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
<h2>Сообщения</h2>
<?php if (isset($noFriendsMessage)) { ?>
    <p>У вас ещё нет друзей для переписки. <a href="friends.php">Добавить друзей</a></p>
<?php } else { ?>
    <table>
        <thead>
        <tr>
            <th>Фамилия</th>
            <th>Имя</th>
            <th>Отчество</th>
            <th>Переписка</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($conversationResult)) { ?>
            <tr>
                <td><?php echo $row['second_name']; ?></td>
                <td><?php echo $row['first_name']; ?></td>
                <td><?php echo $row['third_name']; ?></td>
                <td><a href="conversation.php?user=<?php echo $row['login']; ?>">Перейти к переписке с <?php echo $row['login']; ?></a></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
<?php } ?>
<br>
<a href="main.php" id="main_page_link">Вернуться на главную страницу</a>
</body>
</html>