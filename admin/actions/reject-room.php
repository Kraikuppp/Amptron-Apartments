<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../../login.php');
}

$id = $_GET['id'] ?? 0;

if ($id) {
    $db = getDB();
    $stmt = $db->prepare("UPDATE rooms SET status = 'rejected' WHERE id = ?");
    $stmt->execute([$id]);
    logActivity($_SESSION['user_id'], 'update', 'rooms', $id, 'ปฏิเสธห้องเช่า');
    $_SESSION['success'] = 'ปฏิเสธห้องเช่าสำเร็จ';
}

redirect('../../admin/rooms.php');
?>

