<?php
session_start();
require 'database.php';

header('Content-Type: application/json');
if (!isset($_SESSION['parentID'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$parentID = $_SESSION['parentID'];

$childID = $data['childID'] ?? null;
$dayOfWeekNum = $data['dayOfWeek'] ?? null;
$startTime = $data['startTime'] ?? null;
$endTime = $data['endTime'] ?? null;
$activity = trim($data['activity'] ?? '');

if (!is_numeric($childID) || $activity === '' || !preg_match('/^\d{2}:\d{2}:\d{2}$/', $startTime) || !preg_match('/^\d{2}:\d{2}:\d{2}$/', $endTime)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Некорректные данные']);
    exit;
}

// Проверка принадлежности ребенка родителю
$stmt = $pdo->prepare("SELECT 1 FROM children WHERE childID = ? AND parentID = ?");
$stmt->execute([$childID, $parentID]);
if (!$stmt->fetch()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен']);
    exit;
}

// Маппинг числа дня недели в название (FullCalendar: 0=Вс,1=Пн,...)
$daysMapRev = [
    0 => 'Воскресенье',
    1 => 'Понедельник',
    2 => 'Вторник',
    3 => 'Среда',
    4 => 'Четверг',
    5 => 'Пятница',
    6 => 'Суббота',
];

$dayOfWeek = $daysMapRev[$dayOfWeekNum] ?? null;
if (!$dayOfWeek) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Некорректный день недели']);
    exit;
}

// Добавляем в расписание
$stmt = $pdo->prepare("INSERT INTO schedule (childID, dayOfWeek, startTime, endTime, activity) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$childID, $dayOfWeek, $startTime, $endTime, $activity]);

echo json_encode(['success' => true]);
exit;
