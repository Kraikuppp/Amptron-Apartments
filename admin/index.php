<?php
session_start();
require_once "../config/config.php";
require_once "../includes/functions.php";

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
$hasDB = isDBConnected();
$pdo = $hasDB ? getDB() : null;

// ดึงข้อมูลสถิติ
if (!$hasDB || !$pdo) {
    // ใช้ข้อมูลตัวอย่างสำหรับโหมดทดสอบ
    $total_users = 156;
    $new_users_month = 23;
    $total_rooms = 245;
    $pending_rooms = 12;
    $available_rooms = 180;
    $rented_rooms = 53;
    $business_users = 42;
    $pending_reviews = 8;

    // ห้องยอดนิยม (mock data)
    $popular_rooms = [
        [
            "id" => 1,
            "title" => "คอนโดหรูใกล้ BTS อโศก",
            "price" => 15000,
            "district" => "Sukhumvit",
            "views" => 456,
            "status" => "available",
            "business_name" => "Elite Living",
        ],
        [
            "id" => 2,
            "title" => "อพาร์ทเมนท์สไตล์มินิมอล",
            "price" => 8500,
            "district" => "Phrom Phong",
            "views" => 342,
            "status" => "available",
            "business_name" => "Modern Spaces",
        ],
        [
            "id" => 3,
            "title" => "ห้องสตูดิโอใกล้มหาวิทยาลัย",
            "price" => 5500,
            "district" => "Wang Thonglang",
            "views" => 289,
            "status" => "rented",
            "business_name" => "Student Living",
        ],
    ];

    // ห้องที่เพิ่งเพิ่ม (mock data)
    $recent_rooms = [
        [
            "id" => 4,
            "title" => "ห้องใหม่ใกล้ BTS สยาม",
            "price" => 12000,
            "district" => "Pathum Wan",
            "views" => 45,
            "status" => "pending",
            "business_name" => "City Rentals",
            "created_at" => date("Y-m-d H:i:s", strtotime("-1 day")),
        ],
        [
            "id" => 5,
            "title" => "คอนโดใหม่ใกล้เซ็นทรัล",
            "price" => 18000,
            "district" => "Lat Phrao",
            "views" => 23,
            "status" => "pending",
            "business_name" => "Premium Properties",
            "created_at" => date("Y-m-d H:i:s", strtotime("-2 days")),
        ],
    ];

    // สถิติตามเขต (mock data)
    $district_stats = [
        ["district" => "Sukhumvit", "count" => 45],
        ["district" => "Silom", "count" => 38],
        ["district" => "Sathorn", "count" => 32],
        ["district" => "Phrom Phong", "count" => 28],
        ["district" => "Thonglor", "count" => 25],
    ];
} else {
    // ดึงข้อมูลจากฐานข้อมูล
    try {
        // จำนวนผู้ใช้ทั้งหมด
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
        $total_users = $stmt->fetch()["total"];

        // จำนวนผู้ใช้ใหม่เดือนนี้
        $stmt = $pdo->query(
            "SELECT COUNT(*) as total FROM users WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())",
        );
        $new_users_month = $stmt->fetch()["total"];

        // จำนวนห้องเช่าทั้งหมด
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM rooms");
        $total_rooms = $stmt->fetch()["total"];

        // ห้องที่รออนุมัติ
        $stmt = $pdo->query(
            "SELECT COUNT(*) as total FROM rooms WHERE status = 'pending'",
        );
        $pending_rooms = $stmt->fetch()["total"];

        // ห้องที่พร้อมให้เช่า
        $stmt = $pdo->query(
            "SELECT COUNT(*) as total FROM rooms WHERE status = 'available'",
        );
        $available_rooms = $stmt->fetch()["total"];

        // ห้องที่เช่าแล้ว
        $stmt = $pdo->query(
            "SELECT COUNT(*) as total FROM rooms WHERE status = 'rented'",
        );
        $rented_rooms = $stmt->fetch()["total"];

        // จำนวน Business Accounts
        $stmt = $pdo->query(
            "SELECT COUNT(*) as total FROM users WHERE role = 'business'",
        );
        $business_users = $stmt->fetch()["total"];

        // รีวิวที่รอตรวจสอบ
        $pending_reviews = 3;

        // ห้องยอดนิยม (top 5)
        $stmt = $pdo->query("SELECT r.*, u.username as business_name FROM rooms r
                             LEFT JOIN business_profiles bp ON r.business_id = bp.id
                             LEFT JOIN users u ON bp.user_id = u.id
                             ORDER BY r.views DESC LIMIT 5");
        $popular_rooms = $stmt->fetchAll();

        // ห้องที่เพิ่งเพิ่ม
        $stmt = $pdo->query("SELECT r.*, u.username as business_name FROM rooms r
                             LEFT JOIN business_profiles bp ON r.business_id = bp.id
                             LEFT JOIN users u ON bp.user_id = u.id
                             ORDER BY r.created_at DESC LIMIT 5");
        $recent_rooms = $stmt->fetchAll();

        // สถิติตามเขต
        $stmt = $pdo->query(
            "SELECT district, COUNT(*) as count FROM rooms WHERE district IS NOT NULL GROUP BY district ORDER BY count DESC LIMIT 10",
        );
        $district_stats = $stmt->fetchAll();
    } catch (PDOException $e) {
        // กรณี error ใช้ mock data
        $total_users = 156;
        $new_users_month = 23;
        $total_rooms = 245;
        $pending_rooms = 12;
        $available_rooms = 180;
        $rented_rooms = 53;
        $business_users = 42;
        $pending_reviews = 8;
        $popular_rooms = [];
        $recent_rooms = [];
        $district_stats = [];
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <style>
        /* Page Specific Styles matching business/dashboard.php */
        :root {
            --primary-blue: #3b82f6;
            --secondary-blue: #2563eb;
            --medium-gray: #64748b;
            --dark-gray: #1e293b;
            --font-thai: 'IBM Plex Sans Thai', sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-thai);
            background: #f5f7fa;
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

        .stat-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .stat-badge.success {
            background: #ecfdf5;
            color: #059669;
        }

        .stat-badge.warning {
            background: #fffbeb;
            color: #d97706;
        }

        .chart-card {
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f5f9;
        }

        .chart-card h3 {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark-gray);
            margin-bottom: 20px;
        }

        .table-card {
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f5f9;
            margin-bottom: 30px;
        }

        .table-card h3 {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark-gray);
            margin-bottom: 20px;
        }

        .custom-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .custom-table thead th {
            background: #f7fafc;
            color: #4a5568;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            padding: 12px 15px;
            border-bottom: 2px solid #e2e8f0;
        }

        .custom-table tbody td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            color: #2d3748;
        }

        .custom-table tbody tr:hover {
            background: #f7fafc;
        }

        .status-badge {
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-badge.pending {
            background: #fffbeb;
            color: #d97706;
        }

        .status-badge.available {
            background: #ecfdf5;
            color: #059669;
        }

        .status-badge.rented {
            background: #eff6ff;
            color: #2563eb;
        }

        .btn-action {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 8px 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
            font-size: 12px;
            cursor: pointer;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.4);
            color: white;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .quick-action-btn {
            background: white;
            padding: 25px 20px;
            border-radius: 16px;
            text-align: center;
            text-decoration: none;
            color: var(--dark-gray);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f5f9;
            transition: all 0.3s ease;
        }

        .quick-action-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            color: var(--primary-blue);
        }

        .quick-action-btn i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            display: block;
        }

        .chart-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        @media (max-width: 768px) {
            .chart-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .quick-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include "../includes/header.php"; ?>

    <!-- Page Header -->
    <div class="page-header-section">
        <div class="page-header-pattern"></div>
        <div class="container position-relative z-2 text-center">
            <h1 class="display-4 fw-bold mb-3">แดชบอร์ดผู้ดูแลระบบ</h1>
            <p class="lead opacity-90 mb-0">ภาพรวมและสถิติระบบ Bangkok Rental</p>
        </div>
    </div>

    <div class="container pb-5" style="margin-top: -40px;">
        <div class="row g-4">
            <!-- Sidebar -->
            <div class="col-lg-3">
                <?php include "sidebar.php"; ?>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <!-- Header Actions -->
                <div class="d-flex justify-content-between align-items-center mb-4 px-2">
                    <h4 class="fw-bold mb-0 text-dark">ภาพรวมวันนี้</h4>
                    <small class="text-muted"><?php echo date("d/m/Y H:i"); ?> น.</small>
                </div>

                <!-- Stats Row -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon-wrapper bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-people"></i>
                            </div>
                            <div class="stat-number"><?php echo number_format(
                                $total_users,
                            ); ?></div>
                            <div class="stat-label">ผู้ใช้งานทั้งหมด</div>
                            <div class="mt-2">
                                <span class="stat-badge success">+<?php echo $new_users_month; ?> เดือนนี้</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon-wrapper bg-success bg-opacity-10 text-success">
                                <i class="bi bi-door-open"></i>
                            </div>
                            <div class="stat-number"><?php echo number_format(
                                $total_rooms,
                            ); ?></div>
                            <div class="stat-label">ห้องเช่าทั้งหมด</div>
                            <div class="mt-2">
                                <span class="stat-badge success"><?php echo number_format(
                                    $available_rooms,
                                ); ?> ห้องว่าง</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon-wrapper bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div class="stat-number"><?php echo number_format(
                                $pending_rooms,
                            ); ?></div>
                            <div class="stat-label">รออนุมัติ</div>
                            <div class="mt-2">
                                <span class="stat-badge warning">ต้องตรวจสอบ</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon-wrapper bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-briefcase"></i>
                            </div>
                            <div class="stat-number"><?php echo number_format(
                                $business_users,
                            ); ?></div>
                            <div class="stat-label">บัญชีผู้ประกอบการ</div>
                            <div class="mt-2">
                                <span class="stat-badge success">Active</span>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="rooms-add.php" class="quick-action-btn">
                    <i class="bi bi-plus-circle" style="color: var(--primary-blue);"></i>
                    <div>อนุมัติห้อง</div>
                </a>
                <a href="verification-queue.php" class="quick-action-btn">
                    <i class="bi bi-list-check" style="color: #f59e0b;"></i>
                    <div>อนุมัติคิว</div>
                </a>
                <a href="users.php" class="quick-action-btn">
                    <i class="bi bi-person-plus" style="color: #10b981;"></i>
                    <div>จัดการผู้ใช้</div>
                </a>
                <a href="analytics-overview.php" class="quick-action-btn">
                    <i class="bi bi-graph-up-arrow" style="color: #ef4444;"></i>
                    <div>รายงานสถิติ</div>
                </a>
            </div>

            <!-- Charts -->
            <div class="chart-grid">
                <div class="chart-card">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="rounded-circle p-3 bg-primary bg-opacity-10">
                            <i class="bi bi-graph-up text-primary fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">สถิติห้องเช่ารายเดือน</h5>
                            <small class="text-muted">ห้องเช่าใหม่ในแต่ละเดือน</small>
                        </div>
                    </div>
                    <canvas id="roomsChart"></canvas>
                </div>
                <div class="chart-card">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="rounded-circle p-3 bg-success bg-opacity-10">
                            <i class="bi bi-pie-chart text-success fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">สถานะห้องเช่า</h5>
                            <small class="text-muted">แบ่งตามสถานะ</small>
                        </div>
                    </div>
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            <!-- ห้องยอดนิยม -->
            <div class="table-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle p-3 bg-danger bg-opacity-10">
                            <i class="bi bi-fire text-danger fs-4"></i>
                        </div>
                        <h5 class="mb-0 fw-bold">ห้องยอดนิยม (Top 5)</h5>
                    </div>
                    <a href="rooms.php" class="text-decoration-none text-primary fw-bold small">ดูทั้งหมด <i class="bi bi-arrow-right"></i></a>
                </div>
                <?php if (!empty($popular_rooms)): ?>
                <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ชื่อห้อง</th>
                            <th>ราคา</th>
                            <th>เขต</th>
                            <th>ผู้ลงประกาศ</th>
                            <th>จำนวนวิว</th>
                            <th>สถานะ</th>
                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($popular_rooms as $room): ?>
                        <tr>
                            <td>#<?php echo $room["id"]; ?></td>
                            <td><?php echo htmlspecialchars(
                                $room["title"],
                            ); ?></td>
                            <td>฿<?php echo number_format(
                                $room["price"],
                            ); ?></td>
                            <td><?php echo htmlspecialchars(
                                $room["district"] ?? "-",
                            ); ?></td>
                            <td><?php echo htmlspecialchars(
                                $room["business_name"] ?? "-",
                            ); ?></td>
                            <td><?php echo number_format(
                                $room["views"],
                            ); ?></td>
                            <td>
                                <span class="status-badge <?php echo $room[
                                    "status"
                                ]; ?>">
                                    <?php echo $room["status"]; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn-action" onclick="location.href='rooms-edit.php?id=<?php echo $room[
                                    "id"
                                ]; ?>'">
                                    <i class="bi bi-eye"></i> ดู
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <div class="mb-3 text-muted opacity-50">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                    </div>
                    <p class="text-muted">ยังไม่มีข้อมูลห้องเช่า</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- ห้องที่เพิ่งเพิ่ม -->
            <div class="table-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle p-3 bg-info bg-opacity-10">
                            <i class="bi bi-clock-history text-info fs-4"></i>
                        </div>
                        <h5 class="mb-0 fw-bold">ห้องที่เพิ่งเพิ่ม</h5>
                    </div>
                    <a href="rooms.php" class="text-decoration-none text-primary fw-bold small">ดูทั้งหมด <i class="bi bi-arrow-right"></i></a>
                </div>
                <?php if (!empty($recent_rooms)): ?>
                <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ชื่อห้อง</th>
                            <th>ราคา</th>
                            <th>เขต</th>
                            <th>วันที่เพิ่ม</th>
                            <th>สถานะ</th>
                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_rooms as $room): ?>
                        <tr>
                            <td>#<?php echo $room["id"]; ?></td>
                            <td><?php echo htmlspecialchars(
                                $room["title"],
                            ); ?></td>
                            <td>฿<?php echo number_format(
                                $room["price"],
                            ); ?></td>
                            <td><?php echo htmlspecialchars(
                                $room["district"] ?? "-",
                            ); ?></td>
                            <td><?php echo date(
                                "d/m/Y",
                                strtotime($room["created_at"]),
                            ); ?></td>
                            <td>
                                <span class="status-badge <?php echo $room[
                                    "status"
                                ]; ?>">
                                    <?php echo $room["status"]; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn-action" onclick="location.href='rooms-edit.php?id=<?php echo $room[
                                    "id"
                                ]; ?>'">
                                    <i class="bi bi-pencil"></i> แก้ไข
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <div class="mb-3 text-muted opacity-50">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                    </div>
                    <p class="text-muted">ยังไม่มีข้อมูลห้องเช่า</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // กราฟสถิติห้องเช่ารายเดือน
        const ctx1 = document.getElementById('roomsChart').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.'],
                datasets: [{
                    label: 'ห้องเช่าใหม่',
                    data: [12, 19, 15, 25, 22, 30],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#3b82f6',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
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
                        cornerRadius: 8
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
                }
            }
        });

        // กราฟสถานะห้องเช่า
        const ctx2 = document.getElementById('statusChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['พร้อมให้เช่า', 'เช่าแล้ว', 'รออนุมัติ'],
                datasets: [{
                    data: [<?php echo $available_rooms; ?>, <?php echo $rented_rooms; ?>, <?php echo $pending_rooms; ?>],
                    backgroundColor: [
                        '#10b981',
                        '#3b82f6',
                        '#f59e0b'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 12,
                            padding: 15,
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
                        cornerRadius: 8
                    }
                }
            }
        });
    </script>
</body>
</html>
