<?php
require_once('db.php');

$login = $_POST['login'];
$pass = $_POST['pass'];
$remember = isset($_POST['remember']);

if (empty($login) || empty($pass)) {
    echo "Заполните все поля";
} else {
    $stmt = $conn->prepare("SELECT login, pass FROM users WHERE login = ?");
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if (password_verify($pass, $row['pass'])) {
            echo "Добро пожаловать, " . $row['login'] . "!";

            if ($remember) {
                echo "<script>localStorage.setItem('remembered_login', '" . $login . "');</script>";
            } else {
                echo "<script>localStorage.removeItem('remembered_login');</script>";
            }

        } else {
            echo "Неверный пароль!";
        }
    } else {
        echo "Нет такого пользователя.";
    }

    $stmt->close();
    $conn->close();
}
