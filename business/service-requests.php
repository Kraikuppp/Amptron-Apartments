<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

if (!isLoggedIn() || !isBusiness()) {
    redirect("../login.php");
}

$userId = $_SESSION["user_id"];

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
$hasDB = isDBConnected();
$pdo = $hasDB ? getDB() : null;

if ($hasDB && $pdo) {
    // ดึงข้อมูล Business Profile
    try {
        $stmt = $pdo->prepare("SELECT * FROM business_profiles WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $businessProfile = $stmt->fetch();
    } catch (PDOException $e) {
        // Error handling
    }
}

// Mock Data
if (!isset($requests)) {
    $requests = [
        [
            "id" => 1,
            "room" => "A101",
            "type" => "แจ้งซ่อม",
            "detail" => "แอร์ไม่เย็น มีน้ำหยด",
            "status" => "pending",
            "date" => "2023-10-26 10:30",
            "tenant" => "สมชาย ใจดี"
        ],
        [
            "id" => 2,
            "room" => "B202",
            "type" => "ทำความสะอาด",
            "detail" => "ขอจ้างแม่บ้านทำความสะอาดใหญ่",
            "status" => "in_progress",
            "date" => "2023-10-25 14:20",
            "tenant" => "วิภา สุขใจ"
        ],
        [
            "id" => 3,
            "room" => "C305",
            "type" => "ขออุปกรณ์",
            "detail" => "ขอเพิ่มราวตากผ้าที่ระเบียง",
            "status" => "completed",
            "date" => "2023-10-24 09:15",
            "tenant" => "สมศักดิ์ มั่งมี"
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คำขอรับบริการ - <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/img/amptron-apartments.png?v=2">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@100;200;300;400;500;600;700&family=League+Spartan:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        :root {
            --primary-blue: #3b82f6;
            --secondary-blue: #2563eb;
            --medium-gray: #64748b;
            --dark-gray: #1e293b;
            --font-thai: 'IBM Plex Sans Thai', sans-serif;
        }

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
            transition: all 0.3s ease;
        }

        .dashboard-sidebar {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
            padding: 25px;
            position: sticky;
            top: 100px;
            border: 1px solid #f1f5f9;
        }

        .dashboard-sidebar .nav-link {
            color: var(--medium-gray);
            padding: 12px 15px;
            border-radius: 12px;
            margin-bottom: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
            display: flex;
            align-items: center;
        }

        .dashboard-sidebar .nav-link:hover {
            background: #eff6ff;
            color: var(--primary-blue);
        }

        .dashboard-sidebar .nav-link.active {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            color: white !important;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .verification-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .verified {
            background: #ecfdf5;
            color: #059669;
        }

        .pending {
            background: #fffbeb;
            color: #d97706;
        }
        
        .request-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid #f1f5f9;
            transition: all 0.3s ease;
        }
        
        .request-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border-color: var(--primary-blue);
        }
        
        .status-badge {
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-pending { background: #fffbeb; color: #d97706; }
        .status-in_progress { background: #eff6ff; color: #2563eb; }
        .status-completed { background: #ecfdf5; color: #059669; }
    </style>
</head>
<body>
    <?php include "../includes/header.php"; ?>

    <!-- Page Header -->
    <div class="page-header-section">
        <div class="page-header-pattern"></div>
        <div class="container position-relative z-2 text-center">
            <h1 class="display-4 fw-bold mb-3">คำขอรับบริการ</h1>
            <p class="lead opacity-90 mb-0">จัดการคำขอแจ้งซ่อมและบริการต่างๆ จากผู้เช่า</p>
        </div>
    </div>

    <div class="container pb-5" style="margin-top: -20px;">
        <div class="row g-4">
            <!-- Sidebar -->
            <div class="col-lg-3">
                <?php include "sidebar.php"; ?>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="glass-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-tools me-2"></i> รายการคำขอ</h4>
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="statusfilter" id="statusall" autocomplete="off" checked>
                            <label class="btn btn-outline-primary btn-sm" for="statusall">ทั้งหมด</label>

                            <input type="radio" class="btn-check" name="statusfilter" id="statuspending" autocomplete="off">
                            <label class="btn btn-outline-primary btn-sm" for="statuspending">รอดำเนินการ</label>

                            <input type="radio" class="btn-check" name="statusfilter" id="statusprogress" autocomplete="off">
                            <label class="btn btn-outline-primary btn-sm" for="statusprogress">กำลังดำเนินการ</label>
                        </div>
                    </div>

                    <?php foreach ($requests as $req): ?>
                    <div class="request-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="d-flex gap-3">
                                <div class="rounded-circle p-3 bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="bi bi-<?php echo $req['type'] == 'แจ้งซ่อม' ? 'wrench' : ($req['type'] == 'ทำความสะอาด' ? 'stars' : 'box-seam'); ?> text-primary"></i>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2">ห้อง <?php echo $req['room']; ?></span>
                                        <h6 class="fw-bold mb-0 text-dark"><?php echo $req['type']; ?></h6>
                                    </div>
                                    <p class="text-muted mb-2"><?php echo $req['detail']; ?></p>
                                    <div class="d-flex align-items-center gap-3 text-muted small opacity-75">
                                        <span><i class="bi bi-person me-1"></i> <?php echo $req['tenant']; ?></span>
                                        <span><i class="bi bi-clock me-1"></i> <?php echo $req['date']; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="mb-2">
                                    <span class="status-badge status-<?php echo $req['status']; ?>">
                                        <?php 
                                            $statusMap = [
                                                'pending' => 'รอดำเนินการ',
                                                'in_progress' => 'กำลังดำเนินการ',
                                                'completed' => 'เสร็จสิ้น'
                                            ];
                                            echo $statusMap[$req['status']];
                                        ?>
                                    </span>
                                </div>
                                <div>
                                    <?php if ($req['status'] == 'pending'): ?>
                                        <button class="btn btn-sm btn-primary rounded-pill px-3">รับเรื่อง</button>
                                    <?php elseif ($req['status'] == 'in_progress'): ?>
                                        <button class="btn btn-sm btn-success rounded-pill px-3">ปิดงาน</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($requests)): ?>
                    <div class="text-center py-5">
                        <div class="mb-3 text-muted opacity-50">
                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                        </div>
                        <p class="text-muted">ไม่มีคำขอรับบริการ</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include "../includes/footer.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
