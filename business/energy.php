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

// ข้อมูลตัวอย่างสำหรับโหมดทดสอบ
if (!$hasDB || !$pdo) {
    // ข้อมูลห้องพร้อมค่าไฟ
    $rooms = [
        [
            "id" => 1,
            "room_number" => "A101",
            "room_name" => "คอนโดหรูใกล้ BTS อโศก",
            "tenant_name" => "คุณสมชาย ใจดี",
            "meter_number" => "MTR-001",
            "current_reading" => 2458.5,
            "previous_reading" => 2398.2,
            "units_used" => 60.3,
            "rate_per_unit" => 4.5,
            "total_cost" => 271.35,
            "last_reading_date" => date("Y-m-d", strtotime("-2 days")),
            "status" => "normal",
            "avg_daily_usage" => 2.01,
        ],
        [
            "id" => 2,
            "room_number" => "A102",
            "room_name" => "อพาร์ทเมนท์สไตล์มินิมอล",
            "tenant_name" => "คุณวิภา สุขใจ",
            "meter_number" => "MTR-002",
            "current_reading" => 1856.75,
            "previous_reading" => 1811.5,
            "units_used" => 45.25,
            "rate_per_unit" => 4.5,
            "total_cost" => 203.63,
            "last_reading_date" => date("Y-m-d", strtotime("-1 day")),
            "status" => "normal",
            "avg_daily_usage" => 1.51,
        ],
        [
            "id" => 3,
            "room_number" => "A103",
            "room_name" => "ห้องสตูดิโอใกล้มหาวิทยาลัย",
            "tenant_name" => "คุณนภา รักเรียน",
            "meter_number" => "MTR-003",
            "current_reading" => 3245.9,
            "previous_reading" => 3156.4,
            "units_used" => 89.5,
            "rate_per_unit" => 4.5,
            "total_cost" => 402.75,
            "last_reading_date" => date("Y-m-d", strtotime("-3 days")),
            "status" => "high",
            "avg_daily_usage" => 2.98,
        ],
        [
            "id" => 4,
            "room_number" => "B201",
            "room_name" => "ห้องแอร์ใกล้ BTS",
            "tenant_name" => "คุณประเสริฐ มั่งคั่ง",
            "meter_number" => "MTR-004",
            "current_reading" => 1678.25,
            "previous_reading" => 1643.8,
            "units_used" => 34.45,
            "rate_per_unit" => 4.5,
            "total_cost" => 155.03,
            "last_reading_date" => date("Y-m-d"),
            "status" => "normal",
            "avg_daily_usage" => 1.15,
        ],
        [
            "id" => 5,
            "room_number" => "B202",
            "room_name" => "ห้องเดี่ยวพร้อมครัว",
            "tenant_name" => "ว่าง",
            "meter_number" => "MTR-005",
            "current_reading" => 945.1,
            "previous_reading" => 942.3,
            "units_used" => 2.8,
            "rate_per_unit" => 4.5,
            "total_cost" => 12.6,
            "last_reading_date" => date("Y-m-d", strtotime("-1 day")),
            "status" => "vacant",
            "avg_daily_usage" => 0.09,
        ],
    ];

    // สถิติรวม
    $totalUnitsUsed = array_sum(array_column($rooms, "units_used"));
    $totalCost = array_sum(array_column($rooms, "total_cost"));
    $avgUsagePerRoom = $totalUnitsUsed / count($rooms);
    $totalRooms = count($rooms);
    $occupiedRooms = count(
        array_filter($rooms, function ($r) {
            return $r["status"] !== "vacant";
        }),
    );

    // ข้อมูลกราฟ 7 วันล่าสุด
    $dailyUsage = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date("Y-m-d", strtotime("-$i days"));
        $dailyUsage[] = [
            "date" => $date,
            "total_units" => rand(150, 250),
            "total_cost" => rand(675, 1125),
            "peak_hour_usage" => rand(20, 40),
        ];
    }

    // ข้อมูลการใช้ไฟตามช่วงเวลา (24 ชั่วโมง)
    $hourlyUsage = [];
    for ($h = 0; $h < 24; $h++) {
        $hour = str_pad($h, 2, "0", STR_PAD_LEFT) . ":00";
        $usage = 0;
        // จำลองการใช้ไฟสูงช่วง 18:00-23:00
        if ($h >= 18 && $h <= 23) {
            $usage = rand(15, 25);
        } elseif ($h >= 6 && $h <= 9) {
            $usage = rand(10, 18);
        } elseif ($h >= 12 && $h <= 14) {
            $usage = rand(8, 15);
        } else {
            $usage = rand(3, 8);
        }
        $hourlyUsage[] = [
            "hour" => $hour,
            "usage" => $usage,
        ];
    }

    // เปรียบเทียบเดือนนี้กับเดือนที่แล้ว
    $currentMonthUsage = 1845;
    $lastMonthUsage = 1672;
    $usageChange =
        (($currentMonthUsage - $lastMonthUsage) / $lastMonthUsage) * 100;
} else {
    // ดึงข้อมูลจากฐานข้อมูล
    try {
        // Query ข้อมูลห้องและการใช้ไฟ
        // (Keep existing empty arrays for now as per original file if DB logic wasn't fully implemented there, 
        // or implement it if needed. Original file had empty arrays in catch block and empty arrays in try block? 
        // No, original file had empty arrays in try block too? Let's check.
        // Original file lines 148-160 just initialized variables to 0/empty. It seems DB logic wasn't implemented in the original file either.)
        $rooms = [];
        $totalUnitsUsed = 0;
        $totalCost = 0;
        $avgUsagePerRoom = 0;
        $totalRooms = 0;
        $occupiedRooms = 0;
        $dailyUsage = [];
        $hourlyUsage = [];
        $currentMonthUsage = 0;
        $lastMonthUsage = 0;
        $usageChange = 0;
        
        // Fetch Business Profile for Sidebar
        $stmt = $pdo->prepare("SELECT * FROM business_profiles WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $businessProfile = $stmt->fetch();

    } catch (PDOException $e) {
        // กรณี error ใช้ mock data
        $rooms = [];
        // ...
    }
}

if (!isset($businessProfile)) {
    $businessProfile = [
        "id" => 1,
        "business_name" => $_SESSION["business_name"] ?? "ธุรกิจตัวอย่าง",
        "business_type" => $_SESSION["business_type"] ?? "both",
        "verification_status" => "pending"
    ];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการไฟฟ้า - <?php echo SITE_NAME; ?></title>
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
        
        /* Energy Specific Styles */
        .stat-card-gradient {
            border-radius: 20px;
            padding: 25px;
            color: white;
            height: 100%;
            position: relative;
            overflow: hidden;
            border: none;
            transition: transform 0.3s ease;
        }
        
        .stat-card-gradient:hover {
            transform: translateY(-5px);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            font-family: 'League Spartan', sans-serif;
            margin: 10px 0;
        }
        
        .room-card {
            background: white;
            border: 1px solid #f1f5f9;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 16px;
            transition: all 0.3s ease;
        }

        .room-card:hover {
            border-color: var(--primary-blue);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }
        
        .meter-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px;
            margin-top: 16px;
        }

        .meter-item {
            text-align: center;
            padding: 12px;
            background: #f8fafc;
            border-radius: 12px;
        }
        
        .meter-value {
            font-weight: 700;
            font-family: 'League Spartan', sans-serif;
            font-size: 1.2rem;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
        }
    </style>
</head>
<body>
    <?php include "../includes/header.php"; ?>

    <!-- Page Header -->
    <div class="page-header-section">
        <div class="page-header-pattern"></div>
        <div class="container position-relative z-2 text-center">
            <h1 class="display-4 fw-bold mb-3">จัดการไฟฟ้า</h1>
            <p class="lead opacity-90 mb-0">ระบบติดตามและจัดการการใช้ไฟฟ้า</p>
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
                
                <!-- Alert Banner -->
                <?php if ($usageChange > 10): ?>
                <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center gap-3 p-3">
                    <div class="bg-warning bg-opacity-25 text-warning rounded-circle p-2">
                        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">แจ้งเตือนการใช้ไฟสูงผิดปกติ</h6>
                        <p class="mb-0 text-muted small">การใช้ไฟฟ้าเดือนนี้เพิ่มขึ้น <?php echo number_format(abs($usageChange), 1); ?>% จากเดือนที่แล้ว</p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Summary Stats -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="stat-card-gradient" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                            <i class="bi bi-lightning-charge-fill fs-3 opacity-50"></i>
                            <div class="stat-number"><?php echo number_format($totalUnitsUsed, 1); ?></div>
                            <div class="small opacity-75">หน่วย (kWh)</div>
                            <div class="small opacity-50 mt-2">รวม 7 วันล่าสุด</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card-gradient" style="background: linear-gradient(135deg, #10B981 0%, #059669 100%);">
                            <i class="bi bi-cash-stack fs-3 opacity-50"></i>
                            <div class="stat-number">฿<?php echo number_format($totalCost, 2); ?></div>
                            <div class="small opacity-75">ค่าไฟรวม (บาท)</div>
                            <div class="small opacity-50 mt-2">ประมาณการ 7 วัน</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card-gradient" style="background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);">
                            <i class="bi bi-speedometer2 fs-3 opacity-50"></i>
                            <div class="stat-number"><?php echo number_format($avgUsagePerRoom, 1); ?></div>
                            <div class="small opacity-75">เฉลี่ย/ห้อง (kWh)</div>
                            <div class="small opacity-50 mt-2">จาก <?php echo $occupiedRooms; ?> ห้องที่มีผู้เช่า</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card-gradient" style="background: linear-gradient(135deg, #06B6D4 0%, #0891B2 100%);">
                            <i class="bi bi-calendar-month fs-3 opacity-50"></i>
                            <div class="stat-number"><?php echo number_format($currentMonthUsage); ?></div>
                            <div class="small opacity-75">หน่วย (เดือนนี้)</div>
                            <div class="d-flex align-items-center gap-1 mt-2 small opacity-90">
                                <i class="bi bi-arrow-<?php echo $usageChange > 0 ? "up" : "down"; ?>"></i>
                                <span><?php echo number_format(abs($usageChange), 1); ?>%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row g-4 mb-4">
                    <div class="col-md-8">
                        <div class="glass-card p-4 h-100">
                            <h5 class="fw-bold mb-4"><i class="bi bi-graph-up text-primary me-2"></i>การใช้ไฟฟ้า 7 วันล่าสุด</h5>
                            <div class="chart-container">
                                <canvas id="dailyUsageChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="glass-card p-4 h-100">
                            <h5 class="fw-bold mb-4"><i class="bi bi-clock text-primary me-2"></i>ช่วงเวลาใช้ไฟ</h5>
                            <div class="chart-container">
                                <canvas id="hourlyUsageChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Room Energy Details -->
                <div class="glass-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0"><i class="bi bi-building text-primary me-2"></i>รายละเอียดรายห้อง</h5>
                        <select class="form-select form-select-sm w-auto rounded-pill">
                            <option>ทุกห้อง</option>
                            <option>ห้องที่มีผู้เช่า</option>
                            <option>ห้องว่าง</option>
                            <option>การใช้ไฟสูง</option>
                        </select>
                    </div>

                    <?php foreach ($rooms as $room): ?>
                    <div class="room-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="fw-bold mb-1 text-dark">
                                    <i class="bi bi-door-closed me-2 text-muted"></i><?php echo $room["room_number"]; ?>
                                </h5>
                                <p class="text-muted small mb-0"><?php echo $room["room_name"]; ?></p>
                            </div>
                            <span class="badge rounded-pill px-3 py-2 <?php 
                                echo $room["status"] === "normal" ? "bg-success bg-opacity-10 text-success" : 
                                    ($room["status"] === "high" ? "bg-danger bg-opacity-10 text-danger" : "bg-secondary bg-opacity-10 text-secondary"); 
                            ?>">
                                <?php 
                                    if ($room["status"] === "normal") echo '<i class="bi bi-check-circle me-1"></i>ปกติ';
                                    elseif ($room["status"] === "high") echo '<i class="bi bi-exclamation-triangle me-1"></i>ใช้ไฟสูง';
                                    else echo '<i class="bi bi-dash-circle me-1"></i>ว่าง';
                                ?>
                            </span>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center gap-2 text-muted small">
                                    <i class="bi bi-person"></i>
                                    <span>ผู้เช่า: <strong class="text-dark"><?php echo $room["tenant_name"]; ?></strong></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center gap-2 text-muted small">
                                    <i class="bi bi-upc-scan"></i>
                                    <span>มิเตอร์: <strong class="text-dark"><?php echo $room["meter_number"]; ?></strong></span>
                                </div>
                            </div>
                        </div>

                        <div class="meter-info">
                            <div class="meter-item">
                                <div class="meter-value text-primary"><?php echo number_format($room["current_reading"], 2); ?></div>
                                <div class="small text-muted">เลขปัจจุบัน</div>
                            </div>
                            <div class="meter-item">
                                <div class="meter-value text-dark"><?php echo number_format($room["previous_reading"], 2); ?></div>
                                <div class="small text-muted">เลขครั้งก่อน</div>
                            </div>
                            <div class="meter-item">
                                <div class="meter-value text-warning"><?php echo number_format($room["units_used"], 2); ?></div>
                                <div class="small text-muted">หน่วยที่ใช้</div>
                            </div>
                            <div class="meter-item">
                                <div class="meter-value text-success">฿<?php echo number_format($room["total_cost"], 2); ?></div>
                                <div class="small text-muted">ค่าไฟรวม</div>
                            </div>
                        </div>

                        <div class="mt-3 pt-3 border-top d-flex gap-2">
                            <button class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                <i class="bi bi-graph-up me-1"></i>กราฟ
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include "../includes/footer.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Daily Usage Chart
        const dailyCtx = document.getElementById('dailyUsageChart').getContext('2d');
        const dailyData = <?php echo json_encode($dailyUsage); ?>;

        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: dailyData.map(d => {
                    const date = new Date(d.date);
                    return date.toLocaleDateString('th-TH', { day: '2-digit', month: 'short' });
                }),
                datasets: [{
                    label: 'หน่วยไฟที่ใช้ (kWh)',
                    data: dailyData.map(d => d.total_units),
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(30, 41, 59, 0.9)',
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y.toFixed(2) + ' kWh';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            font: { family: "'League Spartan', sans-serif" }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { family: "'IBM Plex Sans Thai', sans-serif" }
                        }
                    }
                }
            }
        });

        // Hourly Usage Chart
        const hourlyCtx = document.getElementById('hourlyUsageChart').getContext('2d');
        const hourlyData = <?php echo json_encode($hourlyUsage); ?>;

        new Chart(hourlyCtx, {
            type: 'bar',
            data: {
                labels: hourlyData.map(d => d.hour),
                datasets: [{
                    label: 'การใช้ไฟ (kWh)',
                    data: hourlyData.map(d => d.usage),
                    backgroundColor: function(context) {
                        const value = context.parsed.y;
                        if (value > 20) return 'rgba(239, 68, 68, 0.8)';
                        if (value > 15) return 'rgba(245, 158, 11, 0.8)';
                        return 'rgba(16, 185, 129, 0.8)';
                    },
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(30, 41, 59, 0.9)',
                        padding: 12,
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            font: { family: "'League Spartan', sans-serif" }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            maxRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: 6,
                            font: { family: "'League Spartan', sans-serif" }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
