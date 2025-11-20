<?php
// การตั้งค่าฐานข้อมูล
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'billing_rental_system');
define('DB_CHARSET', 'utf8mb4');

// ตัวแปรเก็บสถานะการเชื่อมต่อ
$GLOBALS['db_connected'] = false;
$GLOBALS['db_error'] = null;

// ฟังก์ชันเชื่อมต่อฐานข้อมูล
function getDB() {
    static $pdo = null;
    
    // ถ้าเชื่อมต่อแล้วให้ return เลย
    if ($pdo !== null && $GLOBALS['db_connected']) {
        return $pdo;
    }
    
    // ถ้ายังไม่ได้เชื่อมต่อ ให้ลองเชื่อมต่อ
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            $GLOBALS['db_connected'] = true;
            $GLOBALS['db_error'] = null;
        } catch (PDOException $e) {
            // ไม่ให้ error แต่เก็บ error message ไว้
            $GLOBALS['db_connected'] = false;
            $GLOBALS['db_error'] = $e->getMessage();
            $pdo = null;
            return null;
        }
    }
    
    return $pdo;
}

// ฟังก์ชันตรวจสอบว่าฐานข้อมูลพร้อมใช้งานหรือไม่
function isDBConnected() {
    $db = getDB();
    return $db !== null && $GLOBALS['db_connected'];
}

// ฟังก์ชันรับข้อความ error ของฐานข้อมูล
function getDBError() {
    return $GLOBALS['db_error'] ?? null;
}

// ฟังก์ชันปิดการเชื่อมต่อ
function closeDB() {
    // PDO connection is automatically closed when script ends
    $GLOBALS['db_connected'] = false;
}
?>

