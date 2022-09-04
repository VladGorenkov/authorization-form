<?php
if (!isset($_SESSION)) {
    session_start();
}
if (isset($_SESSION['user'])) {
    header('Location: profile.php');
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <script src="assets/js/ajax.js"></script>
    <script src="assets/js/main.js"></script>
</head>

<body>

    <!-- Форма регистрации -->

    <form>
        <label id="login-label" class="label">Логин</label>
        <input id="login" type="text" placeholder="Введите свой логин">
        <label id="login-error" class="error"></label>

        <label id="password-label" class="label">Пароль</label>
        <input id="password" type="password" placeholder="Введите пароль">
        <label id="password-error" class="error"></label>

        <label id="confirm-password-label" class="label">Подтвердите пароль</label>
        <input id="confirm-password" type="password" placeholder="Введите пароль">
        <label id="confirm-password-error" class="error"></label>

        <label id="email-label" class="label">Почта</label>
        <input id="email" type="text" placeholder="Введите адрес своей почты">
        <label id="email-error" class="error"></label>

        <label id="name-label" class="label">Имя</label>
        <input id="name" type="text" placeholder="Введите имя">
        <label id="name-error" class="error"></label>

        <button type="button" class="register-btn" onclick="requestHandler.sendRequest()">Зарегистрироваться</button>
        <p>
            У вас уже есть аккаунт? - <a href="/">авторизируйтесь</a>!
        </p>
    </form>
    <script language="JavaScript">
        requestHandler = new registerRequestHandler
    </script>
</body>

</html>