<?php
require 'database.php';

$stmt = $pdo->query("SELECT childID, name, photoImage FROM children ORDER BY childID");
$children = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Debug: Проверка фото</title>
    <style>
        body { font-family: sans-serif; }
        img { max-height: 100px; border-radius: 8px; }
        table { border-collapse: collapse; width: 100%; }
        td, th { padding: 10px; border: 1px solid #ccc; text-align: left; }
        .missing { color: red; font-weight: bold; }
    </style>
</head>
<body>
<h1>🔍 Отладка фото детей</h1>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>photoImage </th>
            <th>Превью</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($children as $child): ?>
            <tr>
                <td><?= $child['childID'] ?></td>
                <td><?= htmlspecialchars($child['name']) ?></td>
                <td><?= htmlspecialchars($child['photoImage']) ?></td>
                <td>
                    <?php 
                        $path = $child['photoImage'];
                        if (!empty($path) && file_exists($path)) {
                            echo "<img src='/$path' alt='Фото'>";
                        } else {
                            echo "<span class='missing'>❌ Не найден</span>";
                        }
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
