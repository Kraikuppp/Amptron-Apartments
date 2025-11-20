<?php
require_once 'config/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$db = getDB();
$user = getUserById($_SESSION['user_id']);

if (!$user) {
    // User not found in DB, force logout
    redirect('logout.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$db) {
        $error = 'ไม่สามารถบันทึกข้อมูลได้ในโหมดทดสอบ (ไม่มีการเชื่อมต่อฐานข้อมูล)';
    } else {
        $full_name = $_POST['full_name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        try {
            $updateFields = [];
            $params = [];
            
            if (!empty($full_name)) {
                $updateFields[] = "full_name = ?";
                $params[] = $full_name;
            }
            
            if (!empty($phone)) {
                $updateFields[] = "phone = ?";
                $params[] = $phone;
            }
            
            if (!empty($current_password) && !empty($new_password)) {
                if (verifyPassword($current_password, $user['password'])) {
                    if ($new_password === $confirm_password) {
                        $updateFields[] = "password = ?";
                        $params[] = hashPassword($new_password);
                    } else {
                        $error = 'รหัสผ่านใหม่ไม่ตรงกัน';
                    }
                } else {
                    $error = 'รหัสผ่านปัจจุบันไม่ถูกต้อง';
                }
            }
            
            if (empty($error) && !empty($updateFields)) {
                $params[] = $_SESSION['user_id'];
                $query = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute($params);
                
                // Update session
                if (!empty($full_name)) {
                    $_SESSION['full_name'] = $full_name;
                }
                
                // Refresh user data
                $user = getUserById($_SESSION['user_id']);
                
                logActivity($_SESSION['user_id'], 'update', 'users', $_SESSION['user_id'], 'อัปเดตโปรไฟล์');
                $success = 'อัปเดตโปรไฟล์สำเร็จ';
            }
        } catch (PDOException $e) {
            $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรไฟล์ของฉัน - <?php echo SITE_NAME; ?></title>
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
        /* Page Specific Styles */
        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-thai) !important;
        }

        .page-header-section {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            padding: 120px 0 80px;
            color: white;
            position: relative;
            overflow: hidden;
            margin-bottom: -60px;
            border-radius: 0 0 50px 50px;
        }
        
        .page-header-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: radial-gradient(rgba(255, 255, 255, 0.1) 1px, transparent 1px);
            background-size: 30px 30px;
            opacity: 0.5;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            padding: 40px;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #e0e7ff 0%, #dbeafe 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--primary-blue);
            margin: 0 auto 20px;
            border: 4px solid white;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.15);
        }

        .form-label {
            font-weight: 600;
            color: var(--dark-gray);
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .form-control:disabled {
            background-color: #f8fafc;
            border-color: #f1f5f9;
            color: #64748b;
        }

        .section-divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #e2e8f0, transparent);
            margin: 40px 0;
        }

        .btn-save {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2);
            color: white;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Page Header -->
    <div class="page-header-section">
        <div class="page-header-pattern"></div>
        <div class="container position-relative z-2 text-center">
            <h1 class="display-4 fw-bold mb-3">โปรไฟล์ของฉัน</h1>
            <p class="lead opacity-90 mb-0">จัดการข้อมูลส่วนตัวและรหัสผ่านของคุณ</p>
        </div>
    </div>

    <div class="container pb-5" style="margin-top: -20px;">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="glass-card">
                    
                    <div class="text-center mb-4">
                        <div class="profile-avatar">
                            <i class="bi bi-person-circle"></i>
                        </div>
                        <h3 class="fw-bold mb-1"><?php echo htmlspecialchars($user['full_name'] ?? ''); ?></h3>
                        <p class="text-muted"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                    </div>

                    <?php if ($error): ?>
                    <div class="alert alert-danger border-0 bg-danger-subtle text-danger rounded-4 mb-4">
                        <i class="bi bi-exclamation-circle me-2"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                    <div class="alert alert-success border-0 bg-success-subtle text-success rounded-4 mb-4">
                        <i class="bi bi-check-circle me-2"></i> <?php echo htmlspecialchars($success); ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 rounded-start-4 text-muted"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control border-start-0 rounded-end-4 ps-0" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" disabled>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 rounded-start-4 text-muted"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control border-start-0 rounded-end-4 ps-0" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" disabled>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">ชื่อ-นามสกุล</label>
                                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">เบอร์โทรศัพท์</label>
                                <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="section-divider"></div>
                        
                        <h5 class="fw-bold mb-4"><i class="bi bi-shield-lock me-2 text-primary"></i>เปลี่ยนรหัสผ่าน</h5>
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">รหัสผ่านปัจจุบัน</label>
                                <input type="password" name="current_password" class="form-control" placeholder="กรอกรหัสผ่านปัจจุบันเพื่อยืนยัน">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">รหัสผ่านใหม่</label>
                                <input type="password" name="new_password" class="form-control" placeholder="กำหนดรหัสผ่านใหม่">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="ยืนยันรหัสผ่านใหม่อีกครั้ง">
                            </div>
                        </div>
                        
                        <div class="mt-5">
                            <button type="submit" class="btn-save">
                                <i class="bi bi-save me-2"></i> บันทึกการเปลี่ยนแปลง
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

