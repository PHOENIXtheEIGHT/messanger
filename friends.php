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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search_friend'])) {
    $searchQuery = $_POST['search_query'];
    $searchResultQuery = "SELECT * FROM user WHERE id != '$currentUser' AND (login LIKE '%$searchQuery%' OR CONCAT_WS(' ', first_name, second_name, third_name) LIKE '%$searchQuery%')";
    $searchResult = mysqli_query($link, $searchResultQuery);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_friend'])) {
    $friendId = $_POST['friend_id'];
    $checkFriendshipQuery = "SELECT * FROM friendship WHERE (friend1_id='$currentUser' AND friend2_id='$friendId') OR (friend1_id='$friendId' AND friend2_id='$currentUser')";
    $checkFriendshipResult = mysqli_query($link, $checkFriendshipQuery);

    if (mysqli_num_rows($checkFriendshipResult) > 0) {
        echo "Этот пользователь уже у вас в друзьях!";
    } else {
        $addFriendQuery = "INSERT INTO friendship (friend1_id, friend2_id) VALUES ('$currentUser', '$friendId')";
        if (mysqli_query($link, $addFriendQuery)) {
            header("Location: friends.php");
            exit();
        } else {
            echo "Ошибка при добавлении в друзья: " . mysqli_error($link);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Друзья</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
<h2>Друзья</h2>
<?php if (mysqli_num_rows($friendResult) > 0) { ?>
    <h3>Ваши друзья:</h3>
    <table>
        <tr>
            <th>Фамилия</th>
            <th>Имя</th>
            <th>Отчество</th>
            <th>Логин</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($friendResult)) { ?>
            <tr>
                <td><?php echo $row['second_name']; ?></td>
                <td><?php echo $row['first_name']; ?></td>
                <td><?php echo $row['third_name']; ?></td>
                <td><?php echo $row['login']; ?></td>
            </tr>
        <?php } ?>
    </table>
<?php } else { ?>
    <p>У вас пока нет друзей.</p>
<?php } ?>

<form method="POST" action="">
    <br><label> Найти друга:<br>
        <input type="text" name="search_query" placeholder="Поиск по логину или ФИО" autocomplete="off" required>
    </label>
    <button type="submit" name="search_friend">Найти</button>
</form>

<?php if (isset($searchResult) && mysqli_num_rows($searchResult) > 0) { ?>
    <h3>Результаты поиска:</h3>
    <?php while ($row = mysqli_fetch_assoc($searchResult)) { ?>
        <div>
            <p><?php echo $row['first_name'] . ' ' . $row['second_name'] . ' ' . $row['third_name'] . ' (' . $row['login'] . ')'; ?></p>
            <form method="POST" action="">
                <input type="hidden" name="friend_id" value="<?php echo $row['id']; ?>">
                <button type="submit" name="add_friend">Добавить в друзья</button>
            </form>
        </div>
    <?php } ?>
<?php } elseif (isset($searchResult) && mysqli_num_rows($searchResult) === 0) { ?>
    <p>Ни одного пользователя не найдено.</p>
<?php } ?>

<br>
<a href="main.php">На главную страницу</a>
</body>
</html>