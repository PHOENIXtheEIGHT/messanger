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

function hashPassword($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hashedPassword)
{
    return password_verify($password, $hashedPassword);
}

function validatePassword($password)
{
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{4,30}$/', $password);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $oldPassword = $_POST['old_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if (verifyPassword($oldPassword, $row['password'])) {
        if ($newPassword === $confirmPassword) {
            if (validatePassword($newPassword)) {
                $newPasswordHash = hashPassword($newPassword);
                $updateQuery = "UPDATE user SET password='$newPasswordHash' WHERE login='$login'";
                if (mysqli_query($link, $updateQuery)) {
                    echo "Пароль успешно изменен!";
                } else {
                    echo "Ошибка при изменении пароля: " . mysqli_error($link);
                }
            } else {
                echo "Новый пароль не соответствует требованиям безопасности!";
            }
        } else {
            echo "Новые пароли не совпадают!";
        }
    } else {
        echo "Неправильный старый пароль!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль</title>
</head>
<body>
<div class="profile_items">
    <h2>Профиль</h2>
    <p>Фамилия: <?php echo $row['second_name']; ?></p>
    <p>Имя: <?php echo $row['first_name']; ?></p>
    <p>Отчество: <?php echo $row['third_name']; ?></p>
    <p>Логин: <?php echo $row['login'] ?></p>
    <form method="POST" action="">
        <label>Старый пароль:</label><br>
        <input type="password" name="old_password" required><br>
        <label>Новый пароль:</label><br>
        <input type="password" name="new_password" pattern="[a-zA-Z0-9]{5,20}" autocomplete="off" required><br>
        <label>Повторите новый пароль:</label><br>
        <input type="password" name="confirm_password" pattern="[a-zA-Z0-9]{5,20}" autocomplete="off" required><br>
        <button type="submit" name="change_password">Сменить пароль</button>
    </form>
    <br>
    <a href="main.php">Вернуться на главную страницу</a><br><br>
    <a href="logout_script.php">Выйти из аккаунта</a>
</div>
</body>
</html>