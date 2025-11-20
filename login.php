<?php
require_once "config/config.php";
require_once "includes/auth.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";
    $isAjax = isset($_POST['ajax_login']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

    if (empty($username) || empty($password)) {
        $error = "กรุณากรอก Username และ Password";
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $error]);
            exit;
        }
    } else {
        // ระบบทดสอบ: ตรวจสอบ admin/admin ก่อน (ไม่ต้องเชื่อมต่อฐานข้อมูล)
        // ระบบปกติ: ตรวจสอบผ่านฐานข้อมูล

        $result = loginUser($username, $password);
        if ($result["success"]) {
            // Determine redirect URL
            $redirectUrl = "my-room.php";
            if ($result["user"]["role"] === "admin") {
                $redirectUrl = "admin/index.php";
            } elseif ($result["user"]["role"] === "business") {
                $redirectUrl = "business/dashboard.php";
            }

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'redirect_url' => $redirectUrl]);
                exit;
            }

            // Standard Redirect
            redirect($redirectUrl);
        } else {
            $error = $result["message"];
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $error]);
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/img/amptron-apartments.png?v=2">
    <link rel="apple-touch-icon" href="<?php echo SITE_URL; ?>/img/amptron-apartments.png?v=2">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@100;200;300;400;500;600;700&family=League+Spartan:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        :root {
            --primary-color: #3b82f6;
            --secondary-color: #8b5cf6;
            --dark-gray: #1e293b;
            --medium-gray: #64748b;
            --light-gray: #f1f5f9;
            --sky-blue: #0ea5e9;
            
            /* Fonts */
            --font-english: 'League Spartan', sans-serif;
            --font-thai: 'IBM Plex Sans Thai', sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-thai), var(--font-english);
            background: linear-gradient(135deg, #0ea5e9 0%, #3b82f6 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated background elements */
        body::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.3) 0%, transparent 70%);
            border-radius: 50%;
            top: -250px;
            left: -250px;
            animation: float 20s ease-in-out infinite;
        }

        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.3) 0%, transparent 70%);
            border-radius: 50%;
            bottom: -200px;
            right: -200px;
            animation: float 15s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% {
                transform: translate(0, 0) scale(1);
            }
            50% {
                transform: translate(50px, 50px) scale(1.1);
            }
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-english);
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .login-container {
            width: 100%;
            max-width: 480px;
            padding: 20px;
            position: relative;
            z-index: 10;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.5);
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            background: linear-gradient(135deg, #0ea5e9 0%, #3b82f6 100%);
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        .login-header h2 {
            color: white;
            font-size: 2rem;
            margin: 0;
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .login-header .subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
            margin-top: 8px;
            position: relative;
            z-index: 1;
        }

        .login-body {
            padding: 40px 30px;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark-gray);
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            font-family: var(--font-thai), var(--font-english);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .input-group-custom {
            position: relative;
        }

        .input-group-custom i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--medium-gray);
            font-size: 1.1rem;
            z-index: 5;
        }

        .input-group-custom .form-control {
            padding-left: 45px;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #0ea5e9 0%, #3b82f6 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(14, 165, 233, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .form-check-input {
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .form-check-label {
            color: var(--medium-gray);
            cursor: pointer;
            user-select: none;
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 24px 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e2e8f0;
        }

        .divider span {
            padding: 0 16px;
            color: var(--medium-gray);
            font-size: 0.85rem;
            font-weight: 500;
        }

        .alert-custom {
            border-radius: 12px;
            border: none;
            padding: 14px 18px;
            margin-bottom: 20px;
            animation: slideDown 0.4s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
        }

        .alert-info {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
            border-left: 4px solid var(--primary-color);
        }

        .alert-info .alert-heading {
            font-size: 0.9rem;
            font-weight: 600;
            margin: 0;
        }

        .alert-info small {
            font-size: 0.85rem;
            line-height: 1.6;
        }

        .alert-info code {
            background: rgba(59, 130, 246, 0.1);
            padding: 2px 8px;
            border-radius: 6px;
            color: var(--primary-color);
            font-weight: 600;
        }

        .quick-login-section {
            margin-top: 20px;
        }

        .quick-login-title {
            text-align: center;
            color: var(--medium-gray);
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 12px;
        }

        .btn-quick-login {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            background: white;
            color: var(--dark-gray);
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-quick-login:hover {
            border-color: var(--primary-color);
            background: rgba(59, 130, 246, 0.05);
            color: var(--primary-color);
            transform: translateY(-2px);
        }

        .btn-quick-login.admin:hover {
            border-color: var(--primary-color);
            background: rgba(59, 130, 246, 0.05);
        }

        .btn-quick-login.business:hover {
            border-color: #10b981;
            background: rgba(16, 185, 129, 0.05);
            color: #10b981;
        }

        .btn-quick-login.user:hover {
            border-color: var(--medium-gray);
            background: rgba(100, 116, 139, 0.05);
            color: var(--medium-gray);
        }

        .text-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .text-link:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        .back-to-home {
            position: absolute;
            top: 30px;
            left: 30px;
            z-index: 100;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateX(-5px);
        }

        @media (max-width: 576px) {
            .login-container {
                padding: 15px;
            }

            .login-header {
                padding: 30px 20px;
            }

            .login-header h2 {
                font-size: 1.6rem;
            }

            .login-body {
                padding: 30px 20px;
            }

            .back-to-home {
                top: 15px;
                left: 15px;
            }

            .btn-back {
                padding: 8px 16px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <!-- Back to Home Button -->
    <div class="back-to-home">
        <a href="index.php" class="btn-back">
            <i class="bi bi-arrow-left"></i>
            <span>กลับหน้าหลัก</span>
        </a>
    </div>

    <div class="login-container">
        <div class="login-card">
            <!-- Body -->
            <div class="login-body">
                <?php if ($error): ?>
                <div class="alert alert-danger alert-custom">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="alert alert-success alert-custom">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
                <?php endif; ?>
                <!-- Login Form -->
                <form method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username หรือ Email</label>
                        <div class="input-group-custom">
                            <i class="bi bi-person-fill"></i>
                            <input type="text" class="form-control" id="username" name="username" placeholder="กรอก username หรือ email" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group-custom">
                            <i class="bi bi-lock-fill"></i>
                            <input type="password" class="form-control" id="password" name="password" placeholder="กรอกรหัสผ่าน" required>
                        </div>
                    </div>

                    <div class="mb-4 d-flex justify-content-between align-items-center">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remember">
                            <label class="form-check-label" for="remember">จดจำฉันไว้</label>
                        </div>
                        <a href="#" class="text-link" style="font-size: 0.9rem;">ลืมรหัสผ่าน?</a>
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        เข้าสู่ระบบ
                    </button>
                </form>

                <div class="text-center mt-4">
                    <span style="color: var(--medium-gray);">ยังไม่มีบัญชี? </span>
                    <a href="register.php" class="text-link">สมัครสมาชิก</a>
                </div>

                <div class="divider">
                    <span>หรือเข้าสู่ระบบด่วน</span>
                </div>

                <!-- Quick Login Buttons -->
                <div class="quick-login-section">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn-quick-login admin" onclick="quickLogin('admin', 'admin')">
                            <i class="bi bi-shield-check"></i>
                            <span>Login as Admin</span>
                        </button>
                        <button type="button" class="btn-quick-login business" onclick="quickLogin('business', 'business')">
                            <i class="bi bi-shop"></i>
                            <span>Login as Business</span>
                        </button>
                        <button type="button" class="btn-quick-login user" onclick="quickLogin('user', 'user')">
                            <i class="bi bi-person"></i>
                            <span>Login as User</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function quickLogin(username, password) {
            document.getElementById('username').value = username;
            document.getElementById('password').value = password;
            document.querySelector('form').submit();
        }
    </script>
</body>
</html>
