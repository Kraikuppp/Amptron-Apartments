<?php
require_once 'config/config.php';
require_once 'includes/auth.php';

// ตรวจสอบว่าผู้ใช้เป็น admin และ username เป็น "admin"
if (!isLoggedIn() || !isAdmin() || !isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    redirect('index.php');
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

    <div class="container my-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0">
                            <i class="bi bi-lightning-charge"></i> Energy Management
                        </h2>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h5><i class="bi bi-info-circle"></i> ยินดีต้อนรับสู่หน้า Energy</h5>
                            <p>หน้านี้แสดงให้เฉพาะ Admin ที่ login ด้วย username "admin" เท่านั้น</p>
                        </div>
                        
                        <div class="row g-4 mt-2">
                            <div class="col-md-4">
                                <div class="card text-center h-100">
                                    <div class="card-body">
                                        <i class="bi bi-lightning-charge-fill text-warning fs-1"></i>
                                        <h5 class="card-title mt-3">พลังงานไฟฟ้า</h5>
                                        <p class="card-text">จัดการข้อมูลพลังงานไฟฟ้า</p>
                                        <a href="#" class="btn btn-warning">ดูรายละเอียด</a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card text-center h-100">
                                    <div class="card-body">
                                        <i class="bi bi-speedometer2 text-success fs-1"></i>
                                        <h5 class="card-title mt-3">มิเตอร์ไฟฟ้า</h5>
                                        <p class="card-text">จัดการข้อมูลมิเตอร์ไฟฟ้า</p>
                                        <a href="#" class="btn btn-success">ดูรายละเอียด</a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card text-center h-100">
                                    <div class="card-body">
                                        <i class="bi bi-graph-up text-primary fs-1"></i>
                                        <h5 class="card-title mt-3">รายงาน</h5>
                                        <p class="card-text">รายงานการใช้พลังงาน</p>
                                        <a href="#" class="btn btn-primary">ดูรายละเอียด</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>ข้อมูลการใช้งาน</h5>
                                    </div>
                                    <div class="card-body">
                                        <p>ข้อมูลการใช้งาน Energy จะแสดงที่นี่</p>
                                        <p class="text-muted">
                                            <strong>ผู้ใช้ปัจจุบัน:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?> 
                                            (<?php echo htmlspecialchars($_SESSION['full_name']); ?>)
                                        </p>
                                        <p class="text-muted">
                                            <strong>บทบาท:</strong> <?php echo htmlspecialchars($_SESSION['role']); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
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

