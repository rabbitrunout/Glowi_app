
<?php
session_start();    
?>
<!DOCTYPE html>
<html>
<head>
    <title>Glowi – Подтверждение регистрации</title>
    <link rel="stylesheet" type="text/css" href="css/main.css" />
</head>
<body>
<?php include("header.php"); ?>

<main>
    <h2>Регистрация успешно завершена</h2>
    <p>Спасибо, <?= htmlspecialchars($_SESSION["userName"] ?? 'гость') ?>, вы успешно зарегистрированы.</p>
    <p>Теперь вы можете перейти в <a href="dashboard.php">личный кабинет</a>.</p>
</main>

<?php include("footer.php"); ?>
</body>
</html>
