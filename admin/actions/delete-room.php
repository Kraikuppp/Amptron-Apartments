<?php
session_start();
require_once '../../config/database.php';

// Set JSON header
header('Content-Type: application/json');

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'ไม่มีสิทธิ์เข้าถึง'
    ]);
    exit();
}

// ตรวจสอบ Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit();
}

try {
    // รับข้อมูล
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['room_id'])) {
        throw new Exception('ไม่พบ room_id');
    }

    $room_id = (int)$input['room_id'];

    // เริ่ม transaction
    $pdo->beginTransaction();

    // ดึงข้อมูลห้องก่อนลบ (เพื่อลบรูปภาพ)
    $stmt = $pdo->prepare("SELECT * FROM room_images WHERE room_id = ?");
    $stmt->execute([$room_id]);
    $images = $stmt->fetchAll();

    // ลบรูปภาพจากระบบไฟล์
    foreach ($images as $image) {
        $image_path = '../../uploads/' . $image['image_path'];
        if (file_exists($image_path)) {
            @unlink($image_path);
        }
    }

    // ลบรูปภาพจากฐานข้อมูล
    $stmt = $pdo->prepare("DELETE FROM room_images WHERE room_id = ?");
    $stmt->execute([$room_id]);

    // ลบรีวิว (ถ้ามี)
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE room_id = ?");
    $stmt->execute([$room_id]);

    // ลบห้องเช่า
    $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
    $stmt->execute([$room_id]);

    // Commit transaction
    $pdo->commit();

    // Log activity
    try {
        $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, created_at)
                                    VALUES (?, 'delete_room', ?, NOW())");
        $log_stmt->execute([
            $_SESSION['user_id'],
            "ลบห้องเช่า ID: {$room_id}"
        ]);
    } catch (Exception $e) {
        // Ignore log errors
    }

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'ลบห้องเช่าเรียบร้อยแล้ว',
        'room_id' => $room_id
    ]);

} catch (Exception $e) {
    // Rollback on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
