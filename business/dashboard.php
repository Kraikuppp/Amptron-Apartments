<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

// ตรวจสอบว่าเป็น Business User
if (!isLoggedIn() || !isBusiness()) {
    redirect("../login.php");
}

$userId = $_SESSION["user_id"];

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
$hasDB = isDBConnected();
$pdo = $hasDB ? getDB() : null;

// ข้อมูลตัวอย่างสำหรับโหมดทดสอบ (ไม่มีฐานข้อมูล)
if (!$hasDB || !$pdo) {
    // Business Profile
    $businessProfile = [
        "id" => 1,
        "user_id" => $userId,
        "business_name" => $_SESSION["business_name"] ?? "ธุรกิจตัวอย่าง",
        "business_type" => $_SESSION["business_type"] ?? "both",
        "phone" => "02-123-4567",
        "email" => $_SESSION["email"] ?? "business@example.com",
        "address" => "123 ถนนสุขุมวิท แขวงคลองเตย เขตคลองเตย กรุงเทพฯ 10110",
        "description" => "ธุรกิจด้านการให้เช่าห้องพักและจำหน่ายอุปกรณ์ไฟฟ้า",
        "verified" => 1,
        "verification_status" => "verified",
        "rating" => 4.5,
        "total_reviews" => 128,
    ];

    // สถิติรวม
    $stats = [
        "total_properties" => 25,
        "available_properties" => 18,
        "rented_properties" => 7,
        "total_views" => 3547,
        "total_favorites" => 234,
        "total_contacts" => 89,
        "total_revenue" => 158500,
        "expiring_contracts" => 3,
        "total_electricity_cost" => 45280,
    ];

    // ห้องล่าสุด
    $recentProperties = [
        [
            "id" => 1,
            "title" => "คอนโดหรูใกล้ BTS อโศก",
            "price" => 15000,
            "status" => "available",
            "property_type" => "condo",
            "bedroom" => 1,
            "bathroom" => 1,
            "area" => 35,
            "views" => 456,
            "favorites" => 23,
            "contacts" => 12,
            "main_image" => "https://via.placeholder.com/300x200?text=Condo+1",
            "district" => "คลองเตย",
            "created_at" => date("Y-m-d H:i:s", strtotime("-2 days")),
        ],
        [
            "id" => 2,
            "title" => "อพาร์ทเมนท์สไตล์มินิมอล",
            "price" => 8500,
            "status" => "available",
            "property_type" => "apartment",
            "bedroom" => 1,
            "bathroom" => 1,
            "area" => 28,
            "views" => 342,
            "favorites" => 18,
            "contacts" => 8,
            "main_image" =>
                "https://via.placeholder.com/300x200?text=Apartment",
            "district" => "วัฒนา",
            "created_at" => date("Y-m-d H:i:s", strtotime("-5 days")),
        ],
        [
            "id" => 3,
            "title" => "ห้องสตูดิโอใกล้มหาวิทยาลัย",
            "price" => 5500,
            "status" => "rented",
            "property_type" => "studio",
            "bedroom" => 0,
            "bathroom" => 1,
            "area" => 22,
            "views" => 289,
            "favorites" => 15,
            "contacts" => 6,
            "main_image" => "https://via.placeholder.com/300x200?text=Studio",
            "district" => "ปทุมวัน",
            "created_at" => date("Y-m-d H:i:s", strtotime("-1 week")),
        ],
    ];

    // สถิติ 7 วันล่าสุด
    $weeklyStats = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date("Y-m-d", strtotime("-$i days"));
        $weeklyStats[] = [
            "date" => $date,
            "daily_views" => rand(30, 80),
            "daily_detail_views" => rand(20, 50),
            "daily_favorites" => rand(2, 10),
            "daily_contacts" => rand(1, 8),
        ];
    }

    // ข้อความที่ยังไม่อ่าน
    $unreadMessages = 5;

    // โปรโมชันที่ active
    $activePromotions = [
        [
            "id" => 1,
            "property_id" => 1,
            "property_title" => "คอนโดหรูใกล้ BTS อโศก",
            "promotion_title" => "ลดพิเศษต้อนรับเปิดเทอม",
            "promotion_type" => "discount",
            "discount_percent" => 10,
            "start_date" => date("Y-m-d"),
            "end_date" => date("Y-m-d", strtotime("+30 days")),
            "is_active" => 1,
        ],
        [
            "id" => 2,
            "property_id" => 2,
            "property_title" => "อพาร์ทเมนท์สไตล์มินิมอล",
            "promotion_title" => "โปรโมชันย้ายเข้าด่วน",
            "promotion_type" => "discount",
            "discount_percent" => 15,
            "start_date" => date("Y-m-d", strtotime("-5 days")),
            "end_date" => date("Y-m-d", strtotime("+10 days")),
            "is_active" => 1,
        ],
    ];

    // สัญญาที่กำลังจะหมด
    $expiringContractsList = [
        [
            "id" => 1,
            "room_number" => "A204",
            "tenant_name" => "คุณสมชาย ใจดี",
            "end_date" => date("Y-m-d", strtotime("+5 days")),
            "days_remaining" => 5,
            "status" => "expiring_soon"
        ],
        [
            "id" => 2,
            "room_number" => "B105",
            "tenant_name" => "คุณวิภา สุขใจ",
            "end_date" => date("Y-m-d", strtotime("+12 days")),
            "days_remaining" => 12,
            "status" => "expiring_soon"
        ],
        [
            "id" => 3,
            "room_number" => "C301",
            "tenant_name" => "คุณนภา รักเรียน",
            "end_date" => date("Y-m-d", strtotime("+25 days")),
            "days_remaining" => 25,
            "status" => "warning"
        ],
        [
            "id" => 4,
            "room_number" => "D402",
            "tenant_name" => "คุณสมหญิง จริงใจ",
            "end_date" => date("Y-m-d", strtotime("+45 days")),
            "days_remaining" => 45,
            "status" => "warning"
        ],
        [
            "id" => 5,
            "room_number" => "E505",
            "tenant_name" => "คุณมานะ อดทน",
            "end_date" => date("Y-m-d", strtotime("+75 days")),
            "days_remaining" => 75,
            "status" => "normal"
        ]
    ];

    // คำนวณสถิติสัญญาใกล้หมดอายุ
    $expiringStats = [
        "30_days" => 0,
        "60_days" => 0,
        "90_days" => 0
    ];

    foreach ($expiringContractsList as $contract) {
        if ($contract['days_remaining'] <= 30) {
            $expiringStats['30_days']++;
        } elseif ($contract['days_remaining'] <= 60) {
            $expiringStats['60_days']++;
        } elseif ($contract['days_remaining'] <= 90) {
            $expiringStats['90_days']++;
        }
    }

    // คำขอรับบริการ
    $serviceRequests = [
        [
            "id" => 1,
            "room_number" => "A101",
            "service_type" => "แจ้งซ่อม",
            "description" => "แอร์ไม่เย็น",
            "status" => "pending",
            "created_at" => date("Y-m-d H:i", strtotime("-2 hours"))
        ],
        [
            "id" => 2,
            "room_number" => "B202",
            "service_type" => "ทำความสะอาด",
            "description" => "ทำความสะอาดใหญ่",
            "status" => "in_progress",
            "created_at" => date("Y-m-d H:i", strtotime("-1 day"))
        ],
        [
            "id" => 3,
            "room_number" => "C103",
            "service_type" => "ขออุปกรณ์เพิ่ม",
            "description" => "ขอไมโครเวฟ",
            "status" => "pending",
            "created_at" => date("Y-m-d H:i", strtotime("-3 hours"))
        ]
    ];
} else {
    // ดึงข้อมูลจากฐานข้อมูล
    try {
        // ดึงข้อมูล Business Profile
        $stmt = $pdo->prepare(
            "SELECT * FROM business_profiles WHERE user_id = ? LIMIT 1",
        );
        $stmt->execute([$userId]);
        $businessProfile = $stmt->fetch();

        // ดึงสถิติรวม
        $stmt = $pdo->prepare("
            SELECT
                COUNT(*) as total_properties,
                SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available_properties,
                SUM(CASE WHEN status = 'rented' THEN 1 ELSE 0 END) as rented_properties,
                SUM(views) as total_views,
                SUM(favorites) as total_favorites,
                SUM(contacts) as total_contacts
            FROM business_properties
            WHERE business_id = ?
        ");
        $stmt->execute([$userId]);
        $stats = $stmt->fetch();

        // ดึงห้องล่าสุด
        $stmt = $pdo->prepare("
            SELECT bp.*,
                   (SELECT image_path FROM property_images WHERE property_id = bp.id AND image_type = 'main' LIMIT 1) as main_image
            FROM business_properties bp
            WHERE bp.business_id = ?
            ORDER BY bp.created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$userId]);
        $recentProperties = $stmt->fetchAll();

        // ดึงสถิติ 7 วันล่าสุด
        $stmt = $pdo->prepare("
            SELECT
                pa.date,
                SUM(pa.views) as daily_views,
                SUM(pa.detail_views) as daily_detail_views,
                SUM(pa.favorites) as daily_favorites,
                SUM(pa.contacts) as daily_contacts
            FROM property_analytics pa
            INNER JOIN business_properties bp ON pa.property_id = bp.id
            WHERE bp.business_id = ?
            AND pa.date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY pa.date
            ORDER BY pa.date ASC
        ");
        $stmt->execute([$userId]);
        $weeklyStats = $stmt->fetchAll();

        // ดึงข้อความที่ยังไม่อ่าน
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as unread_messages
            FROM business_messages
            WHERE receiver_id = ? AND is_read = 0
        ");
        $stmt->execute([$userId]);
        $unreadMessages = $stmt->fetch()["unread_messages"];

        // ดึงโปรโมชันที่ active
        $stmt = $pdo->prepare("
            SELECT pp.*, bp.title as property_title
            FROM property_promotions pp
            INNER JOIN business_properties bp ON pp.property_id = bp.id
            WHERE bp.business_id = ? AND pp.is_active = 1 AND pp.end_date >= CURDATE()
            ORDER BY pp.end_date ASC
            LIMIT 5
        ");
        $stmt->execute([$userId]);
        $activePromotions = $stmt->fetchAll();
    } catch (PDOException $e) {
        // กรณี error ให้ใช้ข้อมูลตัวอย่าง
        $businessProfile = [
            "id" => 1,
            "business_name" => $_SESSION["business_name"] ?? "ธุรกิจตัวอย่าง",
            "business_type" => $_SESSION["business_type"] ?? "both",
            "verified" => 1,
            "verification_status" => "verified",
            "phone" => "02-123-4567",
            "email" => $_SESSION["email"] ?? "business@example.com",
        ];
        $stats = [
            "total_properties" => 0,
            "available_properties" => 0,
            "rented_properties" => 0,
            "total_views" => 0,
            "total_favorites" => 0,
            "total_contacts" => 0,
            "total_electricity_cost" => 0,
        ];
        $recentProperties = [];
        $weeklyStats = [];
        $unreadMessages = 0;
        $activePromotions = [];
        $unreadMessages = 0;
        $activePromotions = [];
        $expiringContractsList = [];
        $serviceRequests = [];
        $stats["total_revenue"] = 0;
        $stats["expiring_contracts"] = 0;
        $stats["electricity_income"] = 0;
        $stats["electricity_expense"] = 0;
        $expiringStats = [
            "30_days" => 0,
            "60_days" => 0,
            "90_days" => 0
        ];
    }
}

// ถ้าไม่มี Database ให้ใช้ Mock Data คำนวณ (ทำไปแล้วข้างบน)
// แต่ถ้ามี Database ต้องคำนวณ expiringStats จากข้อมูลจริง (ถ้ายังไม่ได้ทำ)
if ($hasDB && $pdo) {
    // ดึงข้อมูลสัญญาที่กำลังจะหมดอายุภายใน 90 วัน
    try {
        $stmt = $pdo->prepare("
            SELECT 
                r.room_number,
                t.first_name, t.last_name,
                c.end_date,
                DATEDIFF(c.end_date, CURDATE()) as days_remaining
            FROM contracts c
            JOIN rooms r ON c.room_id = r.id
            JOIN tenants t ON c.tenant_id = t.id
            JOIN business_properties bp ON r.property_id = bp.id
            WHERE bp.business_id = ? 
            AND c.status = 'active'
            AND c.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)
            ORDER BY c.end_date ASC
        ");
        $stmt->execute([$userId]);
        $expiringContractsList = $stmt->fetchAll();

        $expiringStats = [
            "30_days" => 0,
            "60_days" => 0,
            "90_days" => 0
        ];

        foreach ($expiringContractsList as $contract) {
            if ($contract['days_remaining'] <= 30) {
                $expiringStats['30_days']++;
            } elseif ($contract['days_remaining'] <= 60) {
                $expiringStats['60_days']++;
            } elseif ($contract['days_remaining'] <= 90) {
                $expiringStats['90_days']++;
            }
        }
        
        // Update total expiring contracts in stats
        $stats['expiring_contracts'] = count($expiringContractsList);

    } catch (PDOException $e) {
        $expiringStats = ["30_days" => 0, "60_days" => 0, "90_days" => 0];
        $expiringContractsList = [];
    }

    // คำนวณรายรับ-รายจ่ายค่าไฟ (สมมติว่ามีการบันทึกมิเตอร์)
    // รายรับ = หน่วยที่ใช้ * ราคาต่อหน่วยที่เก็บผู้เช่า (เช่น 7 บาท)
    // รายจ่าย = หน่วยที่ใช้ * ราคาต้นทุนการไฟฟ้า (เช่น 4.5 บาท)
    try {
        $stmt = $pdo->prepare("
            SELECT SUM(units_used) as total_units
            FROM meter_readings mr
            JOIN business_properties bp ON mr.property_id = bp.id
            WHERE bp.business_id = ? 
            AND MONTH(reading_date) = MONTH(CURRENT_DATE())
            AND YEAR(reading_date) = YEAR(CURRENT_DATE())
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        $totalUnits = $result['total_units'] ?? 0;

        $stats['electricity_income'] = $totalUnits * 7; // สมมติเก็บ 7 บาท/หน่วย
        $stats['electricity_expense'] = $totalUnits * 4.5; // สมมติต้นทุน 4.5 บาท/หน่วย
        
    } catch (PDOException $e) {
        $stats['electricity_income'] = 0;
        $stats['electricity_expense'] = 0;
    }
} else {
    // Mock Data for Electricity
    $stats['electricity_income'] = 45280; // รายรับจากผู้เช่า
    $stats['electricity_expense'] = 29150; // รายจ่ายให้การไฟฟ้า (สมมติ)
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
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        /* Page Specific Styles matching my-room.php */
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
            height: 100%;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 60px rgba(37, 99, 235, 0.15);
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

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f5f9;
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.05) 100%);
            border-radius: 0 0 0 100%;
            transition: all 0.3s ease;
        }

        .stat-card:hover::before {
            width: 120px;
            height: 120px;
        }

        .stat-icon-wrapper {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 1.8rem;
            transition: all 0.3s ease;
        }

        .stat-card:hover .stat-icon-wrapper {
            transform: scale(1.1) rotate(5deg);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark-gray);
            line-height: 1;
            margin-bottom: 5px;
            font-family: 'League Spartan', sans-serif;
        }

        .stat-label {
            color: var(--medium-gray);
            font-weight: 500;
            font-size: 0.95rem;
        }

        .property-mini-card {
            background: white;
            border: 1px solid #f1f5f9;
            border-radius: 16px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .property-mini-card:hover {
            border-color: var(--primary-blue);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            transform: translateX(5px);
        }

        .property-mini-card img {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 12px;
        }

        .status-badge {
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-available {
            background: #ecfdf5;
            color: #059669;
        }

        .status-rented {
            background: #fef2f2;
            color: #dc2626;
        }

        .status-maintenance {
            background: #fffbeb;
            color: #d97706;
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

        .btn-action {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.4);
            color: white;
        }

        .chart-container {
            position: relative;
            height: 320px;
        }
    </style>
</head>
<body>
    <?php include "../includes/header.php"; ?>

    <!-- Page Header -->
    <div class="page-header-section">
        <div class="page-header-pattern"></div>
        <div class="container position-relative z-2 text-center">
            <h1 class="display-4 fw-bold mb-3">แดชบอร์ดธุรกิจ</h1>
            <p class="lead opacity-90 mb-0">ภาพรวมและสถิติธุรกิจของคุณ</p>
        </div>
    </div>

    <div class="container pb-5" style="margin-top: -20px;">
        <div class="row g-4">
            <!-- Sidebar -->
            <div class="col-lg-3">
                <div class="dashboard-sidebar">
                    <div class="text-center mb-4">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px; box-shadow: 0 10px 20px rgba(0,0,0,0.05);">
                            <i class="bi bi-building fs-1 text-primary"></i>
                        </div>
                        <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($businessProfile["business_name"] ?? "ธุรกิจของคุณ"); ?></h5>
                        <div class="mt-2">
                            <span class="verification-badge <?php echo isset($businessProfile["verification_status"]) && $businessProfile["verification_status"] === "verified" ? "verified" : "pending"; ?>">
                                <i class="bi bi-<?php echo isset($businessProfile["verification_status"]) && $businessProfile["verification_status"] === "verified" ? "patch-check-fill" : "clock"; ?>"></i>
                                <?php echo isset($businessProfile["verification_status"]) && $businessProfile["verification_status"] === "verified" ? "ยืนยันแล้ว" : "รอการยืนยัน"; ?>
                            </span>
                        </div>
                    </div>

                    <hr class="opacity-10 my-4">

                    <nav class="nav flex-column gap-2">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="bi bi-speedometer2 me-3 fs-5"></i> Dashboard
                        </a>
                        <a class="nav-link" href="properties.php">
                            <i class="bi bi-building me-3 fs-5"></i> จัดการห้องพัก
                        </a>
                        <a class="nav-link" href="energy.php">
                            <i class="bi bi-lightning-charge me-3 fs-5"></i> จัดการไฟฟ้า
                        </a>
                        <a class="nav-link" href="service-requests.php">
                            <i class="bi bi-tools me-3 fs-5"></i> คำขอรับบริการ
                            <?php if (count($serviceRequests) > 0): ?>
                            <span class="badge bg-danger rounded-pill ms-auto"><?php echo count($serviceRequests); ?></span>
                            <?php endif; ?>
                        </a>
                        <a class="nav-link" href="promotions.php">
                            <i class="bi bi-tag me-3 fs-5"></i> จัดการโปรโมชัน
                        </a>
                        <a class="nav-link" href="news.php">
                            <i class="bi bi-newspaper me-3 fs-5"></i> ข่าวสาร/ประกาศ
                        </a>
                        <a class="nav-link" href="analytics.php">
                            <i class="bi bi-graph-up me-3 fs-5"></i> รายงานสรุป
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <!-- Header Actions -->
                <div class="d-flex justify-content-between align-items-center mb-4 px-2">
                    <h4 class="fw-bold mb-0 text-dark">ภาพรวมวันนี้</h4>
                 
                </div>

                <!-- Stats Row -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon-wrapper bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-building"></i>
                            </div>
                            <div class="stat-number"><?php echo $stats["total_properties"] ?? 0; ?></div>
                            <div class="stat-label">ห้องทั้งหมด</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon-wrapper bg-success bg-opacity-10 text-success">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div class="stat-number"><?php echo $stats["available_properties"] ?? 0; ?></div>
                            <div class="stat-label">ห้องว่าง</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon-wrapper bg-info bg-opacity-10 text-info">
                                <i class="bi bi-eye"></i>
                            </div>
                            <div class="stat-number"><?php echo number_format($stats["total_views"] ?? 0); ?></div>
                            <div class="stat-label">ยอดเข้าชม</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon-wrapper bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-heart"></i>
                            </div>
                            <div class="stat-number"><?php echo $stats["total_favorites"] ?? 0; ?></div>
                            <div class="stat-label">ถูกใจ</div>
                        </div>
                    </div>
                </div>

                <!-- Revenue & Alerts Row -->
                <div class="row g-4 mb-4">
                    <div class="col-md-8">
                        <div class="glass-card p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle p-3 bg-primary bg-opacity-10">
                                        <i class="bi bi-graph-up text-primary fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0 fw-bold">สถิติ 7 วันล่าสุด</h5>
                                        <small class="text-muted">ยอดเข้าชมและผู้ติดต่อ</small>
                                    </div>
                                </div>
                            </div>
                            <div class="chart-container">
                                <canvas id="weeklyChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="row g-4">
                            <div class="col-12">
                                <div class="glass-card p-4" style="background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%); color: white; border: none;">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <p class="mb-1 opacity-75">รายได้รวมเดือนนี้</p>
                                            <h2 class="mb-0 fw-bold">฿<?php echo number_format($stats["total_revenue"] ?? 0); ?></h2>
                                        </div>
                                    
                                    </div>
                                    <div class="d-flex align-items-center gap-2 opacity-90">
                                        <i class="bi bi-arrow-up-circle-fill"></i>
                                        <small>+12% จากเดือนที่แล้ว</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="glass-card p-4" style="background: linear-gradient(135deg, #8B5CF6 0%, #6366F1 100%); color: white; border: none;">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <p class="mb-1 opacity-75">สัญญาใกล้หมดอายุ</p>
                                            <h2 class="mb-0 fw-bold"><?php echo $stats["expiring_contracts"] ?? 0; ?> ห้อง</h2>
                                        </div>
                                        <div class="rounded-circle bg-white bg-opacity-25 p-2">
                                            <i class="bi bi-clock-history fs-4"></i>
                                        </div>
                                    </div>
                                    
                                    <div class="row g-2 mt-3">
                                        <div class="col-4">
                                            <div class="bg-white bg-opacity-10 rounded p-2 text-center">
                                                <small class="d-block opacity-75" style="font-size: 0.7rem;">30 วัน</small>
                                                <span class="fw-bold"><?php echo $expiringStats['30_days']; ?></span>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="bg-white bg-opacity-10 rounded p-2 text-center">
                                                <small class="d-block opacity-75" style="font-size: 0.7rem;">60 วัน</small>
                                                <span class="fw-bold"><?php echo $expiringStats['60_days']; ?></span>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="bg-white bg-opacity-10 rounded p-2 text-center">
                                                <small class="d-block opacity-75" style="font-size: 0.7rem;">90 วัน</small>
                                                <span class="fw-bold"><?php echo $expiringStats['90_days']; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="glass-card p-4" style="background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%); color: white; border: none;">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <p class="mb-1 opacity-75">ค่าไฟเดือนนี้</p>
                                            <h2 class="mb-0 fw-bold">฿<?php echo number_format($stats["electricity_income"] ?? 0); ?></h2>
                                            <small class="opacity-75">รายรับจากห้องพัก</small>
                                        </div>
                                        <div class="rounded-circle bg-white bg-opacity-25 p-2">
                                            <i class="bi bi-lightning-charge-fill fs-4"></i>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3 pt-3 border-top border-white border-opacity-25">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="opacity-90 small">รายจ่าย (การไฟฟ้า)</span>
                                            <span class="fw-bold">฿<?php echo number_format($stats["electricity_expense"] ?? 0); ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-1">
                                            <span class="opacity-90 small">กำไรส่วนต่าง</span>
                                            <span class="fw-bold text-white">+฿<?php echo number_format(($stats["electricity_income"] ?? 0) - ($stats["electricity_expense"] ?? 0)); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Recent Properties -->
                    <div class="col-lg-7">
                        <div class="glass-card p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle p-3 bg-info bg-opacity-10">
                                        <i class="bi bi-clock-history text-info fs-4"></i>
                                    </div>
                                    <h5 class="mb-0 fw-bold">ห้องล่าสุด</h5>
                                </div>
                                <a href="properties.php" class="text-decoration-none text-primary fw-bold small">ดูทั้งหมด <i class="bi bi-arrow-right"></i></a>
                            </div>

                            <?php if (empty($recentProperties)): ?>
                                <div class="text-center py-5">
                                    <div class="mb-3 text-muted opacity-50">
                                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                    </div>
                                    <p class="text-muted">ยังไม่มีห้องเช่า</p>
                                    <a href="add-property.php" class="btn btn-sm btn-outline-primary rounded-pill px-4">เพิ่มห้องแรกของคุณ</a>
                                </div>
                            <?php else: ?>
                                <?php foreach ($recentProperties as $prop): ?>
                                <div class="property-mini-card">
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo htmlspecialchars($prop["main_image"] ?? "https://via.placeholder.com/80"); ?>" alt="<?php echo htmlspecialchars($prop["title"]); ?>">
                                        <div class="ms-3 flex-grow-1">
                                            <h6 class="mb-1 fw-bold text-dark"><?php echo htmlspecialchars($prop["title"]); ?></h6>
                                            <p class="text-muted small mb-1">
                                                <i class="bi bi-geo-alt me-1"></i> <?php echo htmlspecialchars($prop["district"] ?? "ไม่ระบุ"); ?>
                                            </p>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fw-bold text-primary">฿<?php echo number_format($prop["price"]); ?>/เดือน</span>
                                            </div>
                                        </div>
                                        <div class="text-end ms-3">
                                            <span class="status-badge status-<?php echo $prop["status"]; ?> mb-2 d-inline-block">
                                                <?php
                                                $statusText = [
                                                    "available" => "ว่าง",
                                                    "rented" => "เช่าแล้ว",
                                                    "maintenance" => "ปรับปรุง",
                                                    "hidden" => "ซ่อน",
                                                ];
                                                $status = $prop["status"] ?? "available";
                                                echo $statusText[$status] ?? "ไม่ระบุ";
                                                ?>
                                            </span>
                                            <div>
                                                <a href="edit-property.php?id=<?php echo $prop["id"]; ?>" class="btn btn-sm btn-light rounded-circle text-muted">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Active Promotions -->
                    <div class="col-lg-5">
                        <div class="glass-card p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle p-3 bg-warning bg-opacity-10">
                                        <i class="bi bi-tag text-warning fs-4"></i>
                                    </div>
                                    <h5 class="mb-0 fw-bold">โปรโมชัน</h5>
                                </div>
                                <a href="promotions.php" class="text-decoration-none text-primary fw-bold small">จัดการ <i class="bi bi-arrow-right"></i></a>
                            </div>

                            <?php if (empty($activePromotions)): ?>
                                <div class="text-center py-5">
                                    <div class="mb-3 text-muted opacity-50">
                                        <i class="bi bi-tags" style="font-size: 3rem;"></i>
                                    </div>
                                    <p class="text-muted">ยังไม่มีโปรโมชัน</p>
                                    <a href="promotions.php" class="btn btn-sm btn-outline-primary rounded-pill px-4">สร้างโปรโมชัน</a>
                                </div>
                            <?php else: ?>
                                <?php foreach ($activePromotions as $promo): ?>
                                <div class="p-3 mb-3 rounded-4 border border-warning border-opacity-25 bg-warning bg-opacity-10">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="badge bg-warning text-dark rounded-pill px-3">
                                            <?php if ($promo["promotion_type"] == "discount" && isset($promo["discount_percent"])): ?>
                                                ลด <?php echo $promo["discount_percent"]; ?>%
                                            <?php elseif ($promo["promotion_type"] == "discount" && isset($promo["discount_value"])): ?>
                                                ลด ฿<?php echo number_format($promo["discount_value"]); ?>
                                            <?php endif; ?>
                                        </span>
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i>
                                            เหลือ <?php echo ceil((strtotime($promo['end_date']) - time()) / (60 * 60 * 24)); ?> วัน
                                        </small>
                                    </div>
                                    <h6 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($promo["promotion_title"] ?? "โปรโมชัน"); ?></h6>
                                    <p class="text-muted small mb-0 text-truncate"><?php echo htmlspecialchars($promo["property_title"]); ?></p>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Service Requests -->
                    <div class="col-lg-7">
                        <div class="glass-card p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle p-3 bg-success bg-opacity-10">
                                        <i class="bi bi-tools text-success fs-4"></i>
                                    </div>
                                    <h5 class="mb-0 fw-bold">คำขอรับบริการล่าสุด</h5>
                                </div>
                                <a href="service-requests.php" class="text-decoration-none text-primary fw-bold small">ดูทั้งหมด <i class="bi bi-arrow-right"></i></a>
                            </div>

                            <?php if (empty($serviceRequests)): ?>
                                <div class="text-center py-5">
                                    <div class="mb-3 text-muted opacity-50">
                                        <i class="bi bi-bell" style="font-size: 3rem;"></i>
                                    </div>
                                    <p class="text-muted">ไม่มีคำขอรับบริการใหม่</p>
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                <?php foreach ($serviceRequests as $req): ?>
                                    <div class="list-group-item px-0 py-3 border-0 border-bottom">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1 fw-bold">
                                                    <span class="badge bg-primary bg-opacity-10 text-primary me-2"><?php echo $req['room_number']; ?></span>
                                                    <?php echo $req['service_type']; ?>
                                                </h6>
                                                <p class="text-muted small mb-0"><?php echo $req['description']; ?></p>
                                                <small class="text-muted opacity-75"><i class="bi bi-clock me-1"></i> <?php echo $req['created_at']; ?></small>
                                            </div>
                                            <span class="badge bg-warning text-dark rounded-pill"><?php echo $req['status']; ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Expiring Contracts -->
                    <div class="col-lg-5">
                        <div class="glass-card p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle p-3 bg-danger bg-opacity-10">
                                        <i class="bi bi-file-earmark-text text-danger fs-4"></i>
                                    </div>
                                    <h5 class="mb-0 fw-bold">สัญญาใกล้หมดอายุ</h5>
                                </div>
                                <a href="properties.php?filter=expiring" class="text-decoration-none text-primary fw-bold small">ดูทั้งหมด <i class="bi bi-arrow-right"></i></a>
                            </div>

                            <?php if (empty($expiringContractsList)): ?>
                                <div class="text-center py-5">
                                    <div class="mb-3 text-muted opacity-50">
                                        <i class="bi bi-file-check" style="font-size: 3rem;"></i>
                                    </div>
                                    <p class="text-muted">ไม่มีสัญญาที่ใกล้หมดอายุ</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="text-muted small text-uppercase">
                                            <tr>
                                                <th class="ps-0">ห้อง</th>
                                                <th>ผู้เช่า</th>
                                                <th class="text-end pe-0">เหลือ (วัน)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($expiringContractsList as $contract): ?>
                                            <tr>
                                                <td class="ps-0"><span class="fw-bold text-dark"><?php echo $contract['room_number']; ?></span></td>
                                                <td><?php echo $contract['tenant_name']; ?></td>
                                                <td class="text-end pe-0"><span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3"><?php echo $contract['days_remaining']; ?> วัน</span></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

              

            </div>
        </div>
    </div>

    <?php include "../includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Weekly Stats Chart
        const ctx = document.getElementById('weeklyChart').getContext('2d');
        
        // Prepare data
        const labels = <?php echo json_encode(array_map(function($stat) {
            return date('d/m', strtotime($stat['date']));
        }, $weeklyStats)); ?>;
        
        const viewsData = <?php echo json_encode(array_column($weeklyStats, 'daily_views')); ?>;
        const contactsData = <?php echo json_encode(array_column($weeklyStats, 'daily_contacts')); ?>;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'ยอดเข้าชม',
                        data: viewsData,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#3b82f6',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'ผู้ติดต่อ',
                        data: contactsData,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#10b981',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            font: {
                                family: "'IBM Plex Sans Thai', sans-serif",
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(30, 41, 59, 0.9)',
                        padding: 12,
                        titleFont: {
                            family: "'IBM Plex Sans Thai', sans-serif",
                            size: 14
                        },
                        bodyFont: {
                            family: "'IBM Plex Sans Thai', sans-serif",
                            size: 13
                        },
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f1f5f9',
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                family: "'League Spartan', sans-serif",
                                size: 11
                            },
                            color: '#94a3b8'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                family: "'League Spartan', sans-serif",
                                size: 11
                            },
                            color: '#94a3b8'
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    </script>
</body>
</html>
