<?php
// เริ่ม Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// การตั้งค่าทั่วไป
define('SITE_NAME', 'Amptron Apartment');
define('SITE_URL', 'http://localhost/billing');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB


define('GOOGLE_MAPS_API_KEY', 'YOUR_GOOGLE_MAPS_API_KEY');

// ตั้งค่า Timezone
date_default_timezone_set('Asia/Bangkok');

// ตั้งค่า Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// การตั้งค่าภาษา (i18n)
$supportedLocales = ['en', 'th'];
$defaultLocale = 'en';

$currentLocale = isset($_SESSION['lang']) ? $_SESSION['lang'] : $defaultLocale;
if (isset($_GET['lang']) && in_array($_GET['lang'], $supportedLocales, true)) {
    $currentLocale = $_SESSION['lang'] = $_GET['lang'];
}

// Include database
require_once __DIR__ . '/database.php';

// ตั้งค่า Translator ถ้ามีการติดตั้งไลบรารีผ่าน Composer แล้ว
$translator = null;
$vendorAutoload = __DIR__ . '/../vendor/autoload.php';

if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;

    $translator = new \Symfony\Component\Translation\Translator($currentLocale);
    $translator->addLoader('array', new \Symfony\Component\Translation\Loader\ArrayLoader());

    // English translations
    $translator->addResource('array', [
        'nav.login' => 'Login',
        'nav.signup' => 'Sign Up',
        'nav.wishlist' => 'Wishlist',
        'nav.energy' => 'Energy',
        'nav.profile' => 'Profile',
        'nav.dashboard' => 'Dashboard',
        'nav.logout' => 'Logout',
        'nav.nearby' => 'Near Me',
    ], 'en');

    // Thai translations
    $translator->addResource('array', [
        'nav.login' => 'เข้าสู่ระบบ',
        'nav.signup' => 'สมัครสมาชิก',
        'nav.wishlist' => 'รายการโปรด',
        'nav.energy' => 'พลังงาน',
        'nav.profile' => 'โปรไฟล์',
        'nav.dashboard' => 'แดชบอร์ด',
        'nav.logout' => 'ออกจากระบบ',
        'nav.nearby' => 'ใกล้ฉัน',
    ], 'th');
}

function t($key, $parameters = [], $default = '') {
    global $translator, $currentLocale;

    if ($translator instanceof \Symfony\Component\Translation\Translator) {
        return $translator->trans($key, $parameters, null, $currentLocale);
    }

    return $default !== '' ? $default : $key;
}

// ฟังก์ชันตรวจสอบการเข้าสู่ระบบ
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// ฟังก์ชันตรวจสอบบทบาทผู้ใช้
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isBusiness() {
    return isset($_SESSION['role']) && ($_SESSION['role'] === 'business' || $_SESSION['role'] === 'admin');
}

// ฟังก์ชัน Redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// ฟังก์ชันแยกชื่อไฟล์
function sanitizeFileName($filename) {
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    return $filename;
}

// ฟังก์ชันอัปโหลดไฟล์
function uploadFile($file, $directory = '') {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }
    
    $uploadPath = UPLOAD_DIR . $directory;
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0777, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . sanitizeFileName($file['name']);
    $targetPath = $uploadPath . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return 'uploads/' . $directory . '/' . $filename;
    }
    
    return false;
}

// ฟังก์ชันลบไฟล์
function deleteFile($filepath) {
    $fullPath = __DIR__ . '/../' . $filepath;
    if (file_exists($fullPath)) {
        unlink($fullPath);
        return true;
    }
    return false;
}
?>

