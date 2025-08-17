<?php
session_start();
require 'database.php';

if (!isset($_SESSION['parentID'])) header("Location: login_form.php");

$parentID = $_SESSION['parentID'];
$scheduleID = $_GET['scheduleID'] ?? die("No schedule ID");

$stmt = $pdo->prepare("
    SELECT s.*, c.parentID 
    FROM schedule s 
    JOIN children c ON c.childID = s.childID 
    WHERE s.scheduleID = ?
");
$stmt->execute([$scheduleID]);
$sched = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sched || $sched['parentID'] != $parentID) die("Access denied.");

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dayOfWeek = $_POST['dayOfWeek'];
    $startTime = $_POST['startTime'];
    $endTime = $_POST['endTime'];
    $activity = trim($_POST['activity']);

    $allowedDays = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
    if ($dayOfWeek && in_array($dayOfWeek,$allowedDays) && $startTime && $endTime && $activity) {
        $stmt = $pdo->prepare("UPDATE schedule SET dayOfWeek=?, startTime=?, endTime=?, activity=? WHERE scheduleID=?");
        $stmt->execute([$dayOfWeek,$startTime,$endTime,$activity,$scheduleID]);
        $success = "Schedule updated successfully!";
    } else {
        $error = "Please fill all fields correctly.";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Edit Schedule</title>
<link rel="stylesheet" href="css/main.css">
<script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
<?php include 'header.php'; ?>

<main class="container glowi-card">
  <h2><i data-lucide="edit-3"></i> Edit schedule</h2>

  <?php if ($error) echo "<div class='glowi-message error'><i data-lucide='alert-triangle'></i> $error</div>"; ?>
  <?php if ($success) echo "<div class='glowi-message success'><i data-lucide='check-circle'></i> $success</div>"; ?>

  <form method="POST" class="styled-form">
    <label>Day of the week:</label>
    <select name="dayOfWeek" required>
      <?php foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $d): ?>
        <option value="<?= $d ?>" <?= $sched['dayOfWeek']==$d?'selected':'' ?>><?= $d ?></option>
      <?php endforeach; ?>
    </select>

    <label>Start Time:</label>
    <input type="time" name="startTime" value="<?= $sched['startTime'] ?>" required>

    <label>End Time:</label>
    <input type="time" name="endTime" value="<?= $sched['endTime'] ?>" required>

    <label>Activity:</label>
    <input type="text" name="activity" value="<?= htmlspecialchars($sched['activity']) ?>" required>

    <button type="submit" class="btn-save"><i data-lucide="save"></i> Save</button>
  </form>

  <p><a href="child_profile.php?childID=<?= $sched['childID'] ?>" class="btn-secondary">← Back to profile</a></p>
</main>

<?php include 'footer.php'; ?>
<script> lucide.createIcons(); </script>
</body>
</html>
