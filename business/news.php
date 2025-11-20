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
if (!isset($newsList)) {
    $newsList = [
        [
            "id" => 1,
            "title" => "แจ้งปิดปรับปรุงระบบไฟฟ้า",
            "content" => "จะมีการปิดปรับปรุงระบบไฟฟ้าในวันที่ 1 พ.ย. เวลา 10:00 - 12:00 น. ขออภัยในความไม่สะดวก",
            "date" => "2023-10-25",
            "status" => "published",
            "views" => 45
        ],
        [
            "id" => 2,
            "title" => "โปรโมชั่นแนะนำเพื่อน",
            "content" => "แนะนำเพื่อนมาเช่าห้อง รับส่วนลดค่าเช่า 500 บาท ทันทีเมื่อเพื่อนทำสัญญาเช่าอย่างน้อย 6 เดือน",
            "date" => "2023-10-20",
            "status" => "published",
            "views" => 120
        ],
        [
            "id" => 3,
            "title" => "กฎระเบียบการใช้พื้นที่ส่วนกลาง (ฉบับปรับปรุง)",
            "content" => "ขอความร่วมมือผู้เช่าทุกท่านปฏิบัติตามกฎระเบียบใหม่ เพื่อความเป็นระเบียบเรียบร้อย",
            "date" => "2023-10-15",
            "status" => "draft",
            "views" => 0
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข่าวสาร/ประกาศ - <?php echo SITE_NAME; ?></title>
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
        
        .news-card {
            border: 1px solid #f1f5f9;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 16px;
            transition: all 0.3s ease;
            background: white;
        }
        
        .news-card:hover {
            border-color: var(--primary-blue);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <?php include "../includes/header.php"; ?>

    <!-- Page Header -->
    <div class="page-header-section">
        <div class="page-header-pattern"></div>
        <div class="container position-relative z-2 text-center">
            <h1 class="display-4 fw-bold mb-3">ข่าวสารและประกาศ</h1>
            <p class="lead opacity-90 mb-0">แจ้งข่าวสารและประกาศสำคัญให้ผู้เช่าทราบ</p>
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
                        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-newspaper me-2"></i> รายการประกาศ</h4>
                        <a href="#" class="btn btn-primary rounded-pill px-4">
                            <i class="bi bi-plus-lg me-2"></i> สร้างประกาศ
                        </a>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="newsfilter" id="newsall" autocomplete="off" checked>
                                <label class="btn btn-outline-light text-dark border-light-subtle" for="newsall">ทั้งหมด</label>

                                <input type="radio" class="btn-check" name="newsfilter" id="newspublished" autocomplete="off">
                                <label class="btn btn-outline-light text-dark border-light-subtle" for="newspublished">เผยแพร่แล้ว</label>

                                <input type="radio" class="btn-check" name="newsfilter" id="newsdraft" autocomplete="off">
                                <label class="btn btn-outline-light text-dark border-light-subtle" for="newsdraft">แบบร่าง</label>
                            </div>
                        </div>
                    </div>

                    <?php foreach ($newsList as $news): ?>
                    <div class="news-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="fw-bold text-dark mb-0"><?php echo $news['title']; ?></h5>
                            <div class="dropdown">
                                <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm rounded-3">
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-pencil me-2"></i>แก้ไข</a></li>
                                    <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-trash me-2"></i>ลบ</a></li>
                                </ul>
                            </div>
                        </div>
                        
                        <p class="text-muted mb-3"><?php echo $news['content']; ?></p>
                        
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top border-light">
                            <div class="d-flex align-items-center gap-3 text-muted small">
                                <span><i class="bi bi-calendar-event me-1"></i> <?php echo date('d/m/y', strtotime($news['date'])); ?></span>
                                <span><i class="bi bi-eye me-1"></i> <?php echo $news['views']; ?> ครั้ง</span>
                            </div>
                            <span class="badge rounded-pill px-3 py-2 <?php echo $news['status'] == 'published' ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary bg-opacity-10 text-secondary'; ?>">
                                <?php echo $news['status'] == 'published' ? '<i class="bi bi-check-circle me-1"></i>เผยแพร่แล้ว' : '<i class="bi bi-file-earmark me-1"></i>แบบร่าง'; ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($newsList)): ?>
                    <div class="text-center py-5">
                        <div class="mb-3 text-muted opacity-50">
                            <i class="bi bi-newspaper" style="font-size: 3rem;"></i>
                        </div>
                        <p class="text-muted">ยังไม่มีประกาศ</p>
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
