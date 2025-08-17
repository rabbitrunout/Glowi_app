<?php
require 'db.php';

header('Content-Type: application/json');

$stmt = $pdo->query("SELECT requestID, requestDate, status, message 
                     FROM private_lesson_requests");
$data = [];

while ($row = $stmt->fetch()) {
    // Цвет в зависимости от статуса
    $color = '#999999'; // серый по умолчанию
    switch (strtolower($row['status'])) {
        case 'approved':
        case 'одобрено':
            $color = '#28a745'; // зелёный
            break;
        case 'declined':
        case 'отклонено':
            $color = '#dc3545'; // красный
            break;
        case 'pending':
        case 'в ожидании':
            $color = '#ffc107'; // жёлтый
            break;
    }

    $data[] = [
        'id' => $row['requestID'],
        'title' => $row['status'] . ' - ' . $row['message'],
        'start' => $row['requestDate'],
        'color' => $color
    ];
}

echo json_encode($data);
?>