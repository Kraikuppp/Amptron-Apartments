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
if (!isset($promotions)) {
    $promotions = [
        [
            "id" => 1,
            "title" => "ลดพิเศษต้อนรับเปิดเทอม",
            "property" => "คอนโดหรูใกล้ BTS อโศก",
            "discount" => "10%",
            "start_date" => date("Y-m-d"),
            "end_date" => date("Y-m-d", strtotime("+30 days")),
            "status" => "active",
            "usage_count" => 12
        ],
        [
            "id" => 2,
            "title" => "โปรโมชันย้ายเข้าด่วน",
            "property" => "อพาร์ทเมนท์สไตล์มินิมอล",
            "discount" => "15%",
            "start_date" => date("Y-m-d", strtotime("-5 days")),
            "end_date" => date("Y-m-d", strtotime("+10 days")),
            "status" => "active",
            "usage_count" => 5
        ],
        [
            "id" => 3,
            "title" => "ส่วนลดค่าแรกเข้า",
            "property" => "ทุกห้องพัก",
            "discount" => "500 บาท",
            "start_date" => date("Y-m-d", strtotime("-60 days")),
            "end_date" => date("Y-m-d", strtotime("-1 days")),
            "status" => "expired",
            "usage_count" => 45
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการโปรโมชัน - <?php echo SITE_NAME; ?></title>
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
        
        .promotion-card {
            border: 1px solid #f1f5f9;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 16px;
            transition: all 0.3s ease;
            background: white;
        }
        
        .promotion-card:hover {
            border-color: var(--primary-blue);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }
        
        .status-badge {
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-active { background: #ecfdf5; color: #059669; }
        .status-expired { background: #f1f5f9; color: #64748b; }
        .status-scheduled { background: #fffbeb; color: #d97706; }
    </style>
</head>
<body>
    <?php include "../includes/header.php"; ?>

    <!-- Page Header -->
    <div class="page-header-section">
        <div class="page-header-pattern"></div>
        <div class="container position-relative z-2 text-center">
            <h1 class="display-4 fw-bold mb-3">จัดการโปรโมชัน</h1>
            <p class="lead opacity-90 mb-0">สร้างและจัดการโปรโมชันเพื่อดึงดูดลูกค้า</p>
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
                        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-tag me-2"></i> รายการโปรโมชัน</h4>
                        <a href="#" class="btn btn-primary rounded-pill px-4">
                            <i class="bi bi-plus-lg me-2"></i> สร้างโปรโมชัน
                        </a>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="promofilter" id="promoall" autocomplete="off" checked>
                                <label class="btn btn-outline-light text-dark border-light-subtle" for="promoall">ทั้งหมด</label>

                                <input type="radio" class="btn-check" name="promofilter" id="promoactive" autocomplete="off">
                                <label class="btn btn-outline-light text-dark border-light-subtle" for="promoactive">กำลังใช้งาน</label>

                                <input type="radio" class="btn-check" name="promofilter" id="promoexpired" autocomplete="off">
                                <label class="btn btn-outline-light text-dark border-light-subtle" for="promoexpired">หมดอายุ</label>
                            </div>
                        </div>
                    </div>

                    <?php foreach ($promotions as $promo): ?>
                    <div class="promotion-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="d-flex gap-3">
                                <div class="rounded-circle p-3 <?php echo $promo['status'] == 'active' ? 'bg-warning bg-opacity-10 text-warning' : 'bg-secondary bg-opacity-10 text-secondary'; ?> d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="bi bi-percent fs-3"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1 text-dark"><?php echo $promo['title']; ?></h5>
                                    <p class="text-muted mb-2 small"><i class="bi bi-building me-1"></i> <?php echo $promo['property']; ?></p>
                                    <div class="d-flex align-items-center gap-3 text-muted small">
                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2">ลด <?php echo $promo['discount']; ?></span>
                                        <span><i class="bi bi-calendar-event me-1"></i> <?php echo date('d/m/y', strtotime($promo['start_date'])); ?> - <?php echo date('d/m/y', strtotime($promo['end_date'])); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="mb-2">
                                    <span class="status-badge status-<?php echo $promo['status']; ?>">
                                        <?php 
                                            $statusMap = [
                                                'active' => 'กำลังใช้งาน',
                                                'expired' => 'หมดอายุ',
                                                'scheduled' => 'รอเริ่ม'
                                            ];
                                            echo $statusMap[$promo['status']];
                                        ?>
                                    </span>
                                </div>
                                <div class="small text-muted mb-2">
                                    ใช้ไปแล้ว <?php echo $promo['usage_count']; ?> ครั้ง
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-light rounded-circle text-primary me-1"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-sm btn-light rounded-circle text-danger"><i class="bi bi-trash"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($promotions)): ?>
                    <div class="text-center py-5">
                        <div class="mb-3 text-muted opacity-50">
                            <i class="bi bi-tags" style="font-size: 3rem;"></i>
                        </div>
                        <p class="text-muted">ยังไม่มีโปรโมชัน</p>
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
