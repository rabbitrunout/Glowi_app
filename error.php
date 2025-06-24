
<?php
session_start();
$error = $_SESSION["add_error"] ?? "Неизвестная ошибка.";
unset($_SESSION["add_error"]);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Ошибка – Glowi</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
<?php include("header.php"); ?>
<main>
    <h2>Произошла ошибка</h2>
    <p style="color:red; font-weight: bold;"><?= htmlspecialchars($error) ?></p>
    <p><a href="index.php">Вернуться на главную</a></p>
</main>
<?php include("footer.php"); ?>
</body>
</html>
