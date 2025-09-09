<?php
session_start();
require 'database.php';

// Включаем ошибки для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['parentID'])) {
    http_response_code(403);
    exit("Unauthorized");
}

$lessonID = $_GET['id'] ?? null;
if (!$lessonID) {
    http_response_code(400);
    exit("Missing lesson ID");
}

// Загружаем запрос и проверяем владельца
$stmt = $pdo->prepare("
    SELECT plr.*, c.name AS childName
    FROM private_lesson_requests plr
    JOIN children c ON plr.childID = c.childID
    WHERE plr.requestID = ? AND c.parentID = ?
");
$stmt->execute([$lessonID, $_SESSION['parentID']]);
$lesson = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lesson) {
    http_response_code(404);
    exit("Lesson not found or access denied.");
}

// Формируем даты
$start = date('Ymd\THis', strtotime($lesson['lessonDateTime']));
$end   = date('Ymd\THis', strtotime($lesson['lessonDateTime'] . ' +1 hour')); // длительность 1 час
$uid   = uniqid() . "@glowi.local";

// Очищаем текст для ICS
$summary = "Private lesson for " . $lesson['childName'];
$description = preg_replace('/[\r\n]+/', '\\n', trim($lesson['message'] ?? ''));

$ics = "BEGIN:VCALENDAR\r\n";
$ics .= "VERSION:2.0\r\n";
$ics .= "CALSCALE:GREGORIAN\r\n";
$ics .= "BEGIN:VEVENT\r\n";
$ics .= "UID:$uid\r\n";
$ics .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
$ics .= "DTSTART:$start\r\n";
$ics .= "DTEND:$end\r\n";
$ics .= "SUMMARY:" . $summary . "\r\n";
if (!empty($description)) {
    $ics .= "DESCRIPTION:" . $description . "\r\n";
}
$ics .= "STATUS:CONFIRMED\r\n";
$ics .= "END:VEVENT\r\n";
$ics .= "END:VCALENDAR\r\n";

// Отдаём как файл
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename=lesson_' . $lesson['requestID'] . '.ics');
echo $ics;
exit;

?>