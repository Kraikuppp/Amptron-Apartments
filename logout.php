<?php
// เริ่ม session ก่อนเพื่อใช้ตรวจสอบ login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "config/config.php";
require_once "includes/auth.php";

// ออกจากระบบ
if (isLoggedIn()) {
    // บันทึก activity log (ถ้ามีฐานข้อมูล)
    if (isDBConnected()) {
        try {
            logActivity(
                $_SESSION["user_id"] ?? 0,
                "logout",
                "users",
                $_SESSION["user_id"] ?? 0,
                "ผู้ใช้ออกจากระบบ",
            );
        } catch (Exception $e) {
            // Ignore errors
        }
    }
}

// ลบ session ทั้งหมด
$_SESSION = [];

// ถ้ามีการใช้ cookie session ให้ลบ cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        "",
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"],
    );
}

// ทำลาย session
session_unset();
session_destroy();

// Redirect ไปหน้า home ด้วย relative path
header("Location: index.php");
exit();
