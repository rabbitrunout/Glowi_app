<?php
session_start();
require 'database.php';

$id = $_GET['id'] ?? null;
if (!$id) exit("Нет ID");

$stmt = $pdo->prepare("SELECT * FROM private_lesson_requests WHERE requestID = ?");
$stmt->execute([$id]);
$request = $stmt->fetch();

if (!$request) exit("Заявка не найдена");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'];
    $response = trim($_POST['response']);

    $stmt = $pdo->prepare("UPDATE private_lesson_requests SET status = ?, response = ? WHERE requestID = ?");
    $stmt->execute([$status, $response, $id]);

    header("Location: lesson_requests.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Ответ на заявку</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <h2>Ответ на заявку от <?= htmlspecialchars($request['requestDate']) ?></h2>

    <form method="post">
        <label>Статус:</label>
        <select name="status">
            <option value="pending" <?= $request['status']=='pending'?'selected':'' ?>>Ожидает</option>
            <option value="approved" <?= $request['status']=='approved'?'selected':'' ?>>Одобрено</option>
            <option value="declined" <?= $request['status']=='declined'?'selected':'' ?>>Отклонено</option>
        </select>

        <label>Ответ:</label>
        <textarea name="response"><?= htmlspecialchars($request['response']) ?></textarea>

        <button type="submit">Сохранить</button>
    </form>
</body>
</html>
