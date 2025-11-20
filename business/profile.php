<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

if (!isLoggedIn() || !isBusiness()) {
    redirect("../login.php");
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลธุรกิจ - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background: linear-gradient(135deg, #E0F2FE 0%, #F0F9FF 100%); padding-top: 80px; }
        .sidebar { background: white; border-radius: 15px; padding: 20px; position: sticky; top: 100px; }
        .nav-link { color: #64748B; padding: 12px 15px; margin-bottom: 5px; }
        .nav-link:hover, .nav-link.active { background: #E0F2FE; color: #0EA5E9; }
    </style>
</head>
<body>
    <?php include "../includes/header.php"; ?>
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-lg-3">
                <div class="sidebar">
                    <h5 class="text-center mb-4">ธุรกิจของคุณ</h5>
                    <nav class="nav flex-column">
                        <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                        <a class="nav-link" href="properties.php"><i class="bi bi-building me-2"></i> จัดการห้องพัก</a>
                        <a class="nav-link" href="energy.php"><i class="bi bi-lightning-charge me-2"></i> จัดการไฟฟ้า</a>
                        <a class="nav-link" href="service-requests.php"><i class="bi bi-tools me-2"></i> คำขอรับบริการ</a>
                        <a class="nav-link" href="promotions.php"><i class="bi bi-tag me-2"></i> จัดการโปรโมชัน</a>
                        <a class="nav-link" href="news.php"><i class="bi bi-newspaper me-2"></i> ข่าวสาร/ประกาศ</a>
                        <a class="nav-link" href="analytics.php"><i class="bi bi-graph-up me-2"></i> รายงานสรุป</a>
                        <a class="nav-link active" href="profile.php"><i class="bi bi-person-circle me-2"></i> ข้อมูลธุรกิจ</a>
                        <a class="nav-link text-danger" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i> ออกจากระบบ</a>
                    </nav>
                </div>
            </div>
            <div class="col-lg-9">
                <h2>ข้อมูลธุรกิจ</h2>
                <div class="alert alert-info">ส่วนนี้กำลังอยู่ระหว่างการพัฒนา</div>
            </div>
        </div>
    </div>
    <?php include "../includes/footer.php"; ?>
</body>
</html>
