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

$login = $_SESSION['login'];
$query = "SELECT * FROM user WHERE login='$login'";
$result = mysqli_query($link, $query);
$row = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная</title>
</head>
<body>
<h1>Главная</h1>
<ul>
    <li><a href="profile.php">Профиль</a></li>
    <li><a href="friends.php">Друзья</a></li>
    <li><a href="messages.php">Сообщения</a></li>
    <li><a href="logout_script.php">Выйти из аккаунта</a></li>
</ul>
</body>
</html>