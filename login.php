<?php
session_start();

$host = 'localhost';
$user = 'root';
$password = '';
$db_name = 'social_net_db';
$link = mysqli_connect($host, $user, $password, $db_name);
if (!$link) die('Ошибка подключения к базе данных');

function verifyPassword($password, $hashedPassword) {
    return password_verify($password, $hashedPassword);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $login = $_POST['login'];
    $password = $_POST['password'];

    $query = "SELECT * FROM user WHERE login='$login'";
    $result = mysqli_query($link, $query);
    $row = mysqli_fetch_assoc($result);

    if ($row && verifyPassword($password, $row['password'])) {
        $_SESSION['login'] = $login; // Записываем логин в сессию
        header("Location: main.php");
        exit();
    } else {
        echo "Ошибка авторизации. Неправильный логин или пароль.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация</title>
</head>
<body>
<h2>Авторизация</h2>
<form method="POST" action="">
    <input type="text" name="login" placeholder="Логин" required><br>
    <input type="password" name="password" placeholder="Пароль" required><br>
    <button type="submit">Войти</button>
</form>
<p>Еще не зарегистрированы? <a href="register.php">Зарегистрируйтесь</a></p>
</body>
</html>