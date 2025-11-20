<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$action = $_POST['action'] ?? '';
$roomId = $_POST['room_id'] ?? null;
$productId = $_POST['product_id'] ?? null;

if ($action === 'add') {
    $result = addToWishlist($_SESSION['user_id'], $roomId, $productId);
} elseif ($action === 'remove') {
    $result = removeFromWishlist($_SESSION['user_id'], $roomId, $productId);
}

$referer = $_SERVER['HTTP_REFERER'] ?? '../index.php';
redirect($referer);
?>

