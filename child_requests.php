<?php
session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) {
    die("Unauthorized");
}

$childID = (int)$_GET['childID'];

$stmt = $pdo->prepare("SELECT * FROM private_lesson_requests WHERE childID = ? ORDER BY requestDate DESC");
$stmt->execute([$childID]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Запросы на приватные занятия</title>
  <link rel="stylesheet" href="css/main.css">
</head>
<body>
  <h2>Заявки на приватные занятия</h2>

  <form action="add_request.php" method="POST">
    <input type="hidden" name="childID" value="<?= htmlspecialchars($childID) ?>">
    <textarea name="message" placeholder="Напишите запрос..." required></textarea>
    <button type="submit">Отправить заявку</button>
  </form>

  <ul>
    <?php foreach ($requests as $req): ?>
      <li>
        <strong>Дата:</strong> <?= htmlspecialchars($req['requestDate']) ?><br>
        <strong>Сообщение:</strong> <?= htmlspecialchars($req['message']) ?><br>
        <strong>Статус:</strong> <?= htmlspecialchars($req['status']) ?><br>
        <?php if ($req['response']): ?>
          <strong>Ответ:</strong> <?= htmlspecialchars($req['response']) ?><br>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>
</body>
</html>
