<?php
require_once '../config/config.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$businessId = $_POST['business_id'] ?? 0;
$type = $_POST['type'] ?? 'general';
$itemId = $_POST['item_id'] ?? null;
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$message = $_POST['message'] ?? '';
$subject = $_POST['subject'] ?? '';

if ($businessId && $name && $email && $message) {
    $db = getDB();
    try {
        $stmt = $db->prepare("INSERT INTO contacts (user_id, business_id, name, email, phone, message, subject, type, item_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'new')");
        $stmt->execute([$_SESSION['user_id'], $businessId, $name, $email, $phone, $message, $subject, $type, $itemId]);
        logActivity($_SESSION['user_id'], 'create', 'contacts', $db->lastInsertId(), 'ส่งข้อความติดต่อ');
        $_SESSION['success'] = 'ส่งข้อความสำเร็จ!';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    }
}

$referer = $_SERVER['HTTP_REFERER'] ?? '../index.php';
redirect($referer);
?>

