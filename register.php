<?php
session_start();

$host = 'localhost';
$user = 'root';
$password = '';
$db_name = 'social_net_db';
$link = mysqli_connect($host, $user, $password, $db_name);
if (!$link) die('Ошибка подключения к базе данных');

function hashPassword($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

function validatePassword($password)
{
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{4,30}$/', $password);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $login = $_POST['login'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $thirdName = $_POST['third_name'];

    if (!preg_match('/^[a-zA-Z0-9]{5,20}$/', $login)) {
        echo "Логин может содержать только строчные и заглавные английские буквы, а также цифры. Длина логина должна быть от 5 до 20 символов!";
    } elseif (!validatePassword($password)) {
        echo "Пароль должен содержать минимум 1 строчную английскую букву, минимум 1 заглавную английскую букву и минимум 1 цифру. Пароль должен быть длиной от 4 до 30 символов и содержать только строчные и заглавные английские буквы и цифры!";
    } elseif ($password !== $confirmPassword) {
        echo "Пароли не совпадают!";
    } else {
        $checkQuery = "SELECT * FROM user WHERE login='$login'";
        $checkResult = mysqli_query($link, $checkQuery);
        if (mysqli_num_rows($checkResult) > 0) {
            echo "Пользователь с таким логином уже зарегистрирован!";
        } else {
            $hashedPassword = hashPassword($password);
            $query = "INSERT INTO user (login, password, first_name, second_name, third_name) VALUES ('$login', '$hashedPassword', '$firstName', '$lastName', '$thirdName')";
            if (mysqli_query($link, $query)) {
                $_SESSION['login'] = $login;
                header("Location: main.php");
                exit();
            } else {
                echo "Ошибка при регистрации: " . mysqli_error($link);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
</head>
<body>
<h2>Регистрация</h2>
<form method="POST" action="">
    <input type="text" name="login" placeholder="Логин" pattern="[a-zA-Z0-9]{5,20}" autocomplete="off" required><br>
    <input type="password" name="password" placeholder="Пароль" pattern="[a-zA-Z0-9]{4,30}" required><br>
    <input type="password" name="confirm_password" placeholder="Повторите пароль" required><br>
    <input type="text" name="last_name" placeholder="Фамилия" autocomplete="off" required><br>
    <input type="text" name="first_name" placeholder="Имя" autocomplete="off" required><br>
    <input type="text" name="third_name" placeholder="Отчество" autocomplete="off" required><br>
    <button type="submit" name="register">Зарегистрироваться</button>
</form>
<p>Уже зарегистрированы? <a href="login.php">Войдите</a></p>
</body>
</html>