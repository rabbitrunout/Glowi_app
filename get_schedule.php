<?php
session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) {
    http_response_code(403);
    echo json_encode(["error" => "Not authorized"]);
    exit;
}

$parentID = $_SESSION['parentID'];
$childID = isset($_GET['childID']) && is_numeric($_GET['childID']) ? (int)$_GET['childID'] : 0;

// Проверяем доступ к ребёнку
$stmt = $pdo->prepare("SELECT * FROM children WHERE childID = ? AND parentID = ?");
$stmt->execute([$childID, $parentID]);
if (!$stmt->fetch()) {
    http_response_code(403);
    echo json_encode(["error" => "Forbidden"]);
    exit;
}

// Загружаем расписание
$stmt = $pdo->prepare("SELECT * FROM schedule WHERE childID = ?");
$stmt->execute([$childID]);
$schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Преобразуем дни недели в числа для FullCalendar
$dayMap = [
    'Sunday' => 0,
    'Monday' => 1,
    'Tuesday' => 2,
    'Wednesday' => 3,
    'Thursday' => 4,
    'Friday' => 5,
    'Saturday' => 6
];

// Определяем учебный год (с сентября по июнь)
$currentYear = (int)date('Y');
$startRecur = $currentYear . "-09-01";
$endRecur   = ($currentYear + 1) . "-06-30";

$events = [];
foreach ($schedule as $row) {
    $dayNum = $dayMap[$row['dayOfWeek']] ?? null;
    if ($dayNum === null) continue;

    $events[] = [
        'title' => $row['activity'],
        'daysOfWeek' => [$dayNum],
        'startTime' => $row['startTime'],
        'endTime'   => $row['endTime'],
        'startRecur'=> $startRecur,
        'endRecur'  => $endRecur,
        'color'     => '#1E90FF',
        'extendedProps' => ['eventType' => 'schedule']
    ];
}

header('Content-Type: application/json');
echo json_encode($events);

?>