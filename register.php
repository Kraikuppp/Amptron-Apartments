<?php
require_once 'config/config.php';
require_once 'includes/auth.php';

$error = '';
$success = '';

// ตรวจสอบ type ที่ต้องการ (user หรือ business)
$userType = $_GET['type'] ?? 'user';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isAjax = isset($_POST['ajax_register']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

    if (!isDBConnected()) {
        $error = 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้ กรุณาตรวจสอบการตั้งค่า';
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $error]);
            exit;
        }
    } else {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $full_name = $_POST['full_name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $role = $_POST['role'] ?? 'user';
        
        if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
            $error = 'กรุณากรอกข้อมูลให้ครบถ้วน';
        } elseif ($password !== $confirm_password) {
            $error = 'รหัสผ่านไม่ตรงกัน';
        } elseif (strlen($password) < 6) {
            $error = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
        } else {
            $result = registerUser($username, $email, $password, $full_name, $phone, $role);
            if ($result['success']) {
                $success = 'ลงทะเบียนสำเร็จ! กรุณาเข้าสู่ระบบ';
                // Auto login
                sleep(1);
                $loginResult = loginUser($username, $password);
                
                $redirectUrl = 'my-room.php';
                if ($loginResult['success']) {
                    if ($role === 'business') {
                        $redirectUrl = 'business/dashboard.php';
                    }
                }

                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'ลงทะเบียนสำเร็จ!', 'redirect_url' => $redirectUrl]);
                    exit;
                }

                redirect($redirectUrl);
            } else {
                $error = $result['message'];
            }
        }

        if ($error && $isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $error]);
            exit;
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4">สมัครสมาชิก</h2>
                        
                        <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">ประเภทผู้ใช้</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="role" id="role_user" value="user" <?php echo $userType === 'user' ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-primary" for="role_user">ผู้ใช้ทั่วไป</label>
                                    
                                    <input type="radio" class="btn-check" name="role" id="role_business" value="business" <?php echo $userType === 'business' ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-primary" for="role_business">ผู้ประกอบการ</label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="full_name" class="form-label">ชื่อ-นามสกุล</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">เบอร์โทรศัพท์</label>
                                <input type="tel" class="form-control" id="phone" name="phone">
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required minlength="6">
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3">สมัครสมาชิก</button>
                        </form>
                        
                        <div class="text-center">
                            <a href="login.php" class="text-decoration-none">มีบัญชีแล้ว? เข้าสู่ระบบ</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

