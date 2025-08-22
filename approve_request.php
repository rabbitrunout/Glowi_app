<?php
require 'db.php';
$data = json_decode(file_get_contents("php://input"), true);
$requestID = $data['requestID'] ?? 0;

if (!$requestID) {
  echo json_encode(["success" => false, "error" => "Нет requestID"]);
  exit;
}

// Получаем заявку
$stmt = $pdo->prepare("SELECT * FROM lesson_requests WHERE id=?");
$stmt->execute([$requestID]);
$req = $stmt->fetch();

if (!$req) {
  echo json_encode(["success" => false, "error" => "Заявка не найдена"]);
  exit;
}

// Меняем статус
$pdo->prepare("UPDATE lesson_requests SET status='approved' WHERE id=?")->execute([$requestID]);

// Создаем событие в расписании
$start = $req['lessonDate']." ".$req['lessonTime'];
$stmt = $pdo->prepare("INSERT INTO events (childID, title, start, type, description) VALUES (?,?,?,?,?)");
$stmt->execute([$req['childID'], "Private Lesson", $start, "private", $req['message']]);
$eventID = $pdo->lastInsertId();

$event = [
  "id" => $eventID,
  "title" => "Private Lesson",
  "start" => $start,
  "eventType" => "private",
  "description" => $req['message']
];

echo json_encode(["success" => true, "event" => $event]);
?>