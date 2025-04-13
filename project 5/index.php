<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Форма входа и регистрации</title>
    <script>
        window.onload = function() {
            document.getElementById('login').value = localStorage.getItem('remembered_login') || '';
        };
    </script>
</head>
<body>

    <h2>Регистрация</h2>
    <form action="register.php" method="post">
        <input type="text" placeholder="Имя пользователя" name="login" required>
        <input type="password" placeholder="Пароль" name="pass" required>
        <input type="password" placeholder="Повторите пароль" name="repeatpass" required>
        <input type="email" placeholder="Email" name="email" required>
        
        <label>
            <input type="checkbox" name="agreement" required>
            Я принимаю <a href="terms.php" target="_blank">Пользовательское соглашение</a>
        </label>

        <button type="submit">Зарегистрироваться</button>
    </form>

    <h2>Вход</h2>
    <form action="login.php" method="post">
        <input type="text" placeholder="Имя пользователя" name="login" id="login" required>
        <input type="password" placeholder="Пароль" name="pass" required>

        <label>
            <input type="checkbox" name="remember">
            Запомнить меня
        </label>

        <button type="submit">Войти</button>
    </form>

</body>
</html>
