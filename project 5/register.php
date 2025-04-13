<?php
require_once('db.php');

$login = $_POST['login'];
$pass = $_POST['pass'];
$repeatpass = $_POST['repeatpass'];
$agreement = isset($_POST['agreement']) ? 1 : 0;

if (empty($login) || empty($pass) || empty($repeatpass)) {
    echo "Заполните все поля";
} elseif ($pass !== $repeatpass) {
    echo "Пароли не совпадают";
} elseif (!preg_match('/^[A-Za-z0-9]{2,32}$/', $login)) {
    echo "Имя пользователя должно содержать только латинские буквы и цифры (2-32 символа)";
} elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{4,}$/', $pass)) {
    echo "Пароль должен содержать минимум одну заглавную букву, одну цифру и один спецсимвол (минимум 4 символа)";
} elseif ($agreement != 1) {
    echo "Вы должны принять Пользовательское соглашение для регистрации.";
} else {

    $stmt_check = $conn->prepare("SELECT id FROM users WHERE login = ?");
    $stmt_check->bind_param("s", $login);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        echo "Имя пользователя уже занято. Выберите другое.";
    } else {

        $hashed_password = password_hash($pass, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("INSERT INTO users (login, pass, agreement_accepted) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $login, $hashed_password, $agreement);

        if ($stmt->execute()) {
            echo "Успешная регистрация!";
        } else {
            echo "Ошибка: " . $conn->error;
        }

        $stmt->close();
    }

    $stmt_check->close();
    $conn->close();
}
