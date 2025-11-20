<?php
require_once '../config/config.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$roomId = $_POST['room_id'] ?? null;
$productId = $_POST['product_id'] ?? null;
$rating = $_POST['rating'] ?? 0;
$comment = $_POST['comment'] ?? '';

if (($roomId || $productId) && $rating && $comment) {
    $db = getDB();
    try {
        $stmt = $db->prepare("INSERT INTO reviews (user_id, room_id, product_id, rating, comment, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$_SESSION['user_id'], $roomId, $productId, $rating, $comment]);
        logActivity($_SESSION['user_id'], 'create', 'reviews', $db->lastInsertId(), 'เพิ่มรีวิว');
        $_SESSION['success'] = 'ส่งรีวิวสำเร็จ! รอการอนุมัติ';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    }
}

$referer = $_SERVER['HTTP_REFERER'] ?? '../index.php';
redirect($referer);
?>

