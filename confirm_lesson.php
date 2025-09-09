<?php
session_start();
require 'database.php';

// Только тренеры/админы могут подтверждать/отклонять
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['trainer','admin'])) {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestID = (int)($_POST['requestID'] ?? 0);
    $action = $_POST['action'] ?? '';

    // Достаём заявку
    $stmt = $pdo->prepare("SELECT * FROM private_lesson_requests WHERE requestID = ?");
    $stmt->execute([$requestID]);
    $req = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($req && $req['status'] === 'pending') {
        if ($action === 'confirm') {
            // Подтверждаем заявку
            $pdo->prepare("UPDATE private_lesson_requests SET status = 'confirmed' WHERE requestID=?")
                ->execute([$requestID]);

            // Создаём событие
            $stmt = $pdo->prepare("INSERT INTO events (title, date, time, description, eventType)
                                   VALUES (?, ?, ?, ?, 'private_lesson')");
            $stmt->execute([
                'Private lesson',
                $req['lessonDate'],
                $req['lessonTime'],
                $req['message']
            ]);
            $eventID = $pdo->lastInsertId();

            // Привязываем к ребёнку
            $stmt = $pdo->prepare("INSERT INTO child_event (childID, eventID, createdBy) VALUES (?, ?, 'trainer')");
            $stmt->execute([$req['childID'], $eventID]);

        } elseif ($action === 'reject') {
            // Просто меняем статус на rejected
            $pdo->prepare("UPDATE private_lesson_requests SET status = 'rejected' WHERE requestID=?")
                ->execute([$requestID]);
        }
    }

    header("Location: child_profile.php?childID=" . $req['childID']);
    exit;
}

?>