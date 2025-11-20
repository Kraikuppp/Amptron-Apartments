<?php
// การตั้งค่าฐานข้อมูล - รองรับ Environment Variables
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'billing_rental_system');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_CHARSET', 'utf8mb4');

// ตรวจสอบว่าใช้ PostgreSQL หรือ MySQL
define('DB_TYPE', getenv('DATABASE_URL') ? 'pgsql' : 'mysql');

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
            // ตรวจสอบว่ามี DATABASE_URL หรือไม่ (สำหรับ Render/Production)
            $database_url = getenv('DATABASE_URL');
            
            if ($database_url) {
                // ใช้ DATABASE_URL จาก Render (PostgreSQL)
                $pdo = new PDO($database_url);
            } else {
                // ใช้ค่าที่กำหนดเอง (MySQL สำหรับ local)
                if (DB_TYPE === 'pgsql') {
                    $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
                } else {
                    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                }
                $pdo = new PDO($dsn, DB_USER, DB_PASS);
            }
            
            // ตั้งค่า PDO options
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
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

