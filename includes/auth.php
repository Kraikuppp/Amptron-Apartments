<?php
require_once __DIR__ . "/../config/config.php";

// ฟังก์ชันเข้ารหัสรหัสผ่าน
function hashPassword($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

// ฟังก์ชันตรวจสอบรหัสผ่าน
function verifyPassword($password, $hash)
{
    return password_verify($password, $hash);
}

// ฟังก์ชันลงทะเบียนผู้ใช้
function registerUser(
    $username,
    $email,
    $password,
    $full_name,
    $phone = "",
    $role = "user",
) {
    if (!isDBConnected()) {
        return [
            "success" => false,
            "message" =>
                "ไม่สามารถเชื่อมต่อฐานข้อมูลได้ กรุณาตรวจสอบการตั้งค่า",
        ];
    }

    $db = getDB();
    if (!$db) {
        return [
            "success" => false,
            "message" =>
                "ไม่สามารถเชื่อมต่อฐานข้อมูลได้ กรุณาตรวจสอบการตั้งค่า",
        ];
    }

    try {
        // ตรวจสอบว่ามี username หรือ email อยู่แล้วหรือไม่
        $stmt = $db->prepare(
            "SELECT id FROM users WHERE username = ? OR email = ?",
        );
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            return [
                "success" => false,
                "message" => "Username หรือ Email นี้มีอยู่แล้ว",
            ];
        }

        // บันทึกผู้ใช้ใหม่
        $hashedPassword = hashPassword($password);
        $stmt = $db->prepare(
            "INSERT INTO users (username, email, password, full_name, phone, role) VALUES (?, ?, ?, ?, ?, ?)",
        );
        $stmt->execute([
            $username,
            $email,
            $hashedPassword,
            $full_name,
            $phone,
            $role,
        ]);

        $userId = $db->lastInsertId();

        // ถ้าเป็น business ให้สร้าง business profile
        if ($role === "business") {
            $stmt = $db->prepare(
                "INSERT INTO business_profiles (user_id, business_name, business_type) VALUES (?, ?, 'both')",
            );
            $stmt->execute([$userId, $full_name]);
        }

        return [
            "success" => true,
            "message" => "ลงทะเบียนสำเร็จ",
            "user_id" => $userId,
        ];
    } catch (PDOException $e) {
        return [
            "success" => false,
            "message" => "เกิดข้อผิดพลาด: " . $e->getMessage(),
        ];
    }
}

// ฟังก์ชันดึงข้อมูล Test Users
function getTestUsers() {
    return [
        "admin" => [
            "id" => 1,
            "username" => "admin",
            "password" => "admin",
            "email" => "admin@example.com",
            "full_name" => "Administrator",
            "role" => "admin",
            "status" => "active",
            "business_id" => null,
        ],
        "business" => [
            "id" => 2,
            "username" => "business",
            "password" => "business",
            "email" => "business@example.com",
            "full_name" => "ธุรกิจตัวอย่าง",
            "role" => "business",
            "status" => "active",
            "business_id" => 1,
            "business_name" => "ธุรกิจตัวอย่าง",
            "business_type" => "both",
        ],
        "user" => [
            "id" => 3,
            "username" => "user",
            "password" => "user",
            "email" => "user@example.com",
            "full_name" => "ผู้ใช้ทั่วไป",
            "role" => "user",
            "status" => "active",
            "business_id" => null,
        ],
    ];
}

// ฟังก์ชันเข้าสู่ระบบ
function loginUser($username, $password)
{
    // ระบบทดสอบ: Login โดยไม่ต้องเชื่อมต่อฐานข้อมูล
    // Default Users:
    // 1. admin/admin - สำหรับผู้ดูแลระบบ
    // 2. business/business - สำหรับผู้ประกอบการ
    // 3. user/user - สำหรับผู้ใช้ทั่วไป

    $testUsers = getTestUsers();

    // ตรวจสอบ test users
    if (
        isset($testUsers[$username]) &&
        $testUsers[$username]["password"] === $password
    ) {
        $user = $testUsers[$username];

        $_SESSION["user_id"] = $user["id"];
        $_SESSION["username"] = $user["username"];
        $_SESSION["email"] = $user["email"];
        $_SESSION["full_name"] = $user["full_name"];
        $_SESSION["role"] = $user["role"];

        // สำหรับ business user เพิ่ม business info
        if ($user["role"] === "business") {
            $_SESSION["business_id"] = $user["business_id"];
            $_SESSION["business_name"] = $user["business_name"];
            $_SESSION["business_type"] = $user["business_type"];
        }

        // บันทึก activity log (ถ้ามีฐานข้อมูล)
        if (isDBConnected()) {
            logActivity(
                $user["id"],
                "login",
                "users",
                $user["id"],
                "ผู้ใช้เข้าสู่ระบบ (Test Mode)",
            );
        }

        return [
            "success" => true,
            "message" => "เข้าสู่ระบบสำเร็จ (โหมดทดสอบ)",
            "user" => $user,
        ];
    }

    // ระบบปกติ: Login ผ่านฐานข้อมูล
    if (!isDBConnected()) {
        return [
            "success" => false,
            "message" =>
                "ไม่สามารถเชื่อมต่อฐานข้อมูลได้ กรุณาตรวจสอบการตั้งค่า หรือใช้ username/password: admin/admin",
        ];
    }

    $db = getDB();
    if (!$db) {
        return [
            "success" => false,
            "message" =>
                "ไม่สามารถเชื่อมต่อฐานข้อมูลได้ กรุณาตรวจสอบการตั้งค่า",
        ];
    }

    try {
        $stmt = $db->prepare(
            "SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'active'",
        );
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user && verifyPassword($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["email"] = $user["email"];
            $_SESSION["full_name"] = $user["full_name"];
            $_SESSION["role"] = $user["role"];

            // บันทึก activity log
            logActivity(
                $user["id"],
                "login",
                "users",
                $user["id"],
                "ผู้ใช้เข้าสู่ระบบ",
            );

            return [
                "success" => true,
                "message" => "เข้าสู่ระบบสำเร็จ",
                "user" => $user,
            ];
        } else {
            return [
                "success" => false,
                "message" => "Username หรือ Password ไม่ถูกต้อง",
            ];
        }
    } catch (PDOException $e) {
        return [
            "success" => false,
            "message" => "เกิดข้อผิดพลาด: " . $e->getMessage(),
        ];
    }
}

// ฟังก์ชันออกจากระบบ
function logoutUser()
{
    // บันทึก activity log (ถ้ามีฐานข้อมูลและ login แล้ว)
    if (isLoggedIn() && isDBConnected()) {
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

    // Redirect ไปหน้า home
    // หา path กลับไป root directory
    $script_path = $_SERVER["SCRIPT_NAME"];
    $script_dir = dirname($script_path);

    $redirect_url = "index.php";

    // ถ้าเรียกจาก subdirectory ให้ใช้ relative path กลับไป root
    if (
        $script_dir !== "/" &&
        $script_dir !== "\\" &&
        !empty($script_dir) &&
        $script_dir !== "."
    ) {
        // นับจำนวน directory ที่ต้องกลับขึ้นไป
        $depth = substr_count(trim($script_dir, "/"), "/");
        if ($depth > 0) {
            $redirect_url = str_repeat("../", $depth) . "index.php";
        }
    }

    header("Location: " . $redirect_url);
    exit();
}

// ฟังก์ชันบันทึก Activity Log
function logActivity(
    $userId,
    $action,
    $tableName = null,
    $recordId = null,
    $description = "",
) {
    if (!isDBConnected()) {
        return;
    }

    $db = getDB();
    if (!$db) {
        return;
    }

    try {
        $ipAddress = $_SERVER["REMOTE_ADDR"] ?? "0.0.0.0";
        $stmt = $db->prepare(
            "INSERT INTO activity_logs (user_id, action, table_name, record_id, description, ip_address) VALUES (?, ?, ?, ?, ?, ?)",
        );
        $stmt->execute([
            $userId,
            $action,
            $tableName,
            $recordId,
            $description,
            $ipAddress,
        ]);
    } catch (PDOException $e) {
        // Ignore log errors
    }
}

// ฟังก์ชันดึงข้อมูลผู้ใช้
function getUserById($userId)
{
    $user = null;

    // 1. Try to get from DB if connected
    if (isDBConnected()) {
        $db = getDB();
        if ($db) {
            try {
                $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch();
            } catch (PDOException $e) {
                // Ignore error, try fallback
            }
        }
    }

    // 2. If not found in DB (or DB error), check Test Users
    if (!$user) {
        $testUsers = getTestUsers();
        foreach ($testUsers as $testUser) {
            if ($testUser['id'] == $userId) {
                $user = $testUser;
                break;
            }
        }
    }

    return $user;
}

// ฟังก์ชันดึงข้อมูล Business Profile
function getBusinessProfile($userId)
{
    if (!isDBConnected()) {
        return null;
    }

    $db = getDB();
    if (!$db) {
        return null;
    }

    try {
        $stmt = $db->prepare(
            "SELECT * FROM business_profiles WHERE user_id = ?",
        );
        $stmt->execute([$userId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}
?>
