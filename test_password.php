<?php
/**
 * Script สำหรับสร้าง password hash และทดสอบการ login
 * เปิดไฟล์นี้ผ่านเบราว์เซอร์: http://localhost/billing/test_password.php
 */

// ปิดการแสดง error ใน production
error_reporting(E_ALL);
ini_set('display_errors', 1);

// สร้าง password hash สำหรับ "admin"
$password = 'admin';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "<h2>Password Hash Generator</h2>";
echo "<p><strong>Password:</strong> admin</p>";
echo "<p><strong>Hash:</strong> <code>" . htmlspecialchars($hash) . "</code></p>";

// ทดสอบการ verify password
echo "<hr>";
echo "<h3>ทดสอบการ Verify Password</h3>";
if (password_verify('admin', $hash)) {
    echo "<p style='color: green;'>✓ Password verification สำเร็จ!</p>";
} else {
    echo "<p style='color: red;'>✗ Password verification ล้มเหลว!</p>";
}

echo "<hr>";
echo "<h3>คำสั่ง SQL สำหรับอัปเดตรหัสผ่าน</h3>";
echo "<pre>UPDATE users SET password = '" . htmlspecialchars($hash) . "' WHERE username = 'admin';</pre>";

// ถ้ามีฐานข้อมูล ให้ลองอัปเดต
require_once 'config/config.php';

if (isDBConnected()) {
    echo "<hr>";
    echo "<h3>อัปเดตรหัสผ่านในฐานข้อมูล</h3>";
    
    try {
        $db = getDB();
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
        $stmt->execute([$hash]);
        
        echo "<p style='color: green;'>✓ อัปเดตรหัสผ่านสำเร็จ!</p>";
        echo "<p>ตอนนี้สามารถ login ด้วย:</p>";
        echo "<ul>";
        echo "<li><strong>Username:</strong> admin</li>";
        echo "<li><strong>Password:</strong> admin</li>";
        echo "</ul>";
        echo "<p><a href='login.php' class='btn btn-primary'>ไปหน้า Login</a></p>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>✗ เกิดข้อผิดพลาด: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠ ยังไม่ได้เชื่อมต่อฐานข้อมูล กรุณาตรวจสอบการตั้งค่า</p>";
    echo "<p>ข้อผิดพลาด: " . htmlspecialchars(getDBError() ?? 'ไม่ทราบสาเหตุ') . "</p>";
}

echo "<hr>";
echo "<h3>การทดสอบ Login</h3>";
echo "<p>หลังจากอัปเดตรหัสผ่านแล้ว:</p>";
echo "<ol>";
echo "<li>ไปที่หน้า <a href='login.php'>Login</a></li>";
echo "<li>กรอก Username: <strong>admin</strong></li>";
echo "<li>กรอก Password: <strong>admin</strong></li>";
echo "<li>กดปุ่ม 'เข้าสู่ระบบ'</li>";
echo "<li>เมื่อ login สำเร็จ จะเห็นเมนู <strong>Energy</strong> ในเมนูหลัก</li>";
echo "</ol>";

echo "<hr>";
echo "<p><small>หมายเหตุ: หลังจากใช้งานเสร็จแล้ว ควรลบไฟล์นี้เพื่อความปลอดภัย</small></p>";
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ทดสอบ Password - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
</body>
</html>

