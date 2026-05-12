<?php
require_once 'includes/auth.php';
require_once 'db.php';
requireLogin();

$movieId = intval($_GET['id'] ?? 0);
$userId  = currentUserId();

if ($movieId > 0) {
    $conn = getConnection();
    $stmt = $conn->prepare("DELETE FROM movies WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $movieId, $userId);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

header('Location: dashboard.php?msg=deleted');
exit();
?>