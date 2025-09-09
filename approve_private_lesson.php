// approve_private_lesson.php
<?session_start();
require 'database.php';

$eventID = $_POST['eventID'] ?? null;
if(!$eventID) exit('No ID');

$stmt = $pdo->prepare("UPDATE events SET status='approved' WHERE eventID=?");
$stmt->execute([$eventID]);

$stmt2 = $pdo->prepare("UPDATE private_lesson_requests SET status='approved' WHERE childID=(SELECT childID FROM child_event WHERE eventID=?) AND requestDate IS NOT NULL");
$stmt2->execute([$eventID]);

echo json_encode(['success'=>true]);
?>
