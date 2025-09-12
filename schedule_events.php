<?php
session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) {
    http_response_code(403);
    exit('Access denied');
}

$parentID = $_SESSION['parentID'];
$childID = isset($_GET['childID']) && is_numeric($_GET['childID']) ? (int)$_GET['childID'] : die('Invalid child ID');

// Проверяем принадлежность ребенка родителю
$stmt = $pdo->prepare("SELECT groupLevel FROM children WHERE childID = ? AND parentID = ?");
$stmt->execute([$childID, $parentID]);
$child = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$child) {
    http_response_code(403);
    exit('Access denied');
}

$stmt = $pdo->prepare("SELECT weekday, time, activity, location FROM weekly_schedule WHERE groupLevel = ?");
$stmt->execute([$child['groupLevel']]);
$schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);

$daysMap = [
    'Sunday' => 0,
    'Monday' => 1,
    'Tuesday' => 2,
    'Wednesday' => 3,
    'Thursday' => 4,
    'Friday' => 5,
    'Saturday' => 6,
];

$events = [];

foreach ($schedule as $item) {
    if (!isset($daysMap[$item['weekday']])) continue;
    $dayOfWeek = $daysMap[$item['weekday']];
    // Предполагаем, что время в формате HH:MM:SS, FullCalendar ожидает startTime, endTime
    // Поскольку в таблице только одно время, добавим 1 час к endTime для примера
    $startTime = substr($item['time'], 0, 8);
    $h = (int)substr($startTime, 0, 2);
    $m = (int)substr($startTime, 3, 2);
    $s = (int)substr($startTime, 6, 2);
    $endH = str_pad($h + 1, 2, '0', STR_PAD_LEFT);
    $endTime = $endH . ':' . substr($startTime, 3, 5);

    $events[] = [
        'title' => $item['activity'] . ' (' . $item['location'] . ')',
        'daysOfWeek' => [$dayOfWeek],
        'startTime' => $startTime,
        'endTime' => $endTime,
        'allDay' => false,
        'color' => '#3788d8',
    ];
}

header('Content-Type: application/json');
echo json_encode($events);
exit;
?>