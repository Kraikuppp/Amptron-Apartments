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

// Mock Data for Charts
$monthlyRevenue = [120000, 125000, 118000, 130000, 135000, 140000];
$months = ['พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.'];
$occupancyRate = [85, 88, 90, 92, 95, 95];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานสรุป - <?php echo SITE_NAME; ?></title>
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
        
        .chart-container {
            position: relative;
            height: 350px;
        }
        
        .stat-card-mini {
            background: #f8fafc;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .stat-card-mini:hover {
            background: #eff6ff;
            transform: translateY(-5px);
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            font-family: 'League Spartan', sans-serif;
            color: var(--dark-gray);
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <?php include "../includes/header.php"; ?>

    <!-- Page Header -->
    <div class="page-header-section">
        <div class="page-header-pattern"></div>
        <div class="container position-relative z-2 text-center">
            <h1 class="display-4 fw-bold mb-3">รายงานสรุป</h1>
            <p class="lead opacity-90 mb-0">วิเคราะห์ประสิทธิภาพและรายได้ของธุรกิจ</p>
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
                
                <!-- Key Metrics -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="stat-card-mini">
                            <div class="text-muted small">รายได้รวม (เดือนนี้)</div>
                            <div class="stat-value text-primary">฿140,000</div>
                            <div class="text-success small"><i class="bi bi-arrow-up"></i> 3.5%</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card-mini">
                            <div class="text-muted small">อัตราการเข้าพัก</div>
                            <div class="stat-value text-success">95%</div>
                            <div class="text-success small"><i class="bi bi-arrow-up"></i> 2.0%</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card-mini">
                            <div class="text-muted small">ค่าใช้จ่ายรวม</div>
                            <div class="stat-value text-danger">฿25,000</div>
                            <div class="text-danger small"><i class="bi bi-arrow-up"></i> 1.2%</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card-mini">
                            <div class="text-muted small">กำไรสุทธิ</div>
                            <div class="stat-value text-info">฿115,000</div>
                            <div class="text-success small"><i class="bi bi-arrow-up"></i> 4.1%</div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-lg-8">
                        <div class="glass-card p-4 h-100">
                            <h5 class="fw-bold mb-4 text-dark"><i class="bi bi-graph-up text-primary me-2"></i>แนวโน้มรายได้ 6 เดือนย้อนหลัง</h5>
                            <div class="chart-container">
                                <canvas id="revenueChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="glass-card p-4 h-100">
                            <h5 class="fw-bold mb-4 text-dark"><i class="bi bi-pie-chart text-primary me-2"></i>สัดส่วนห้องพัก</h5>
                            <div class="chart-container" style="height: 300px;">
                                <canvas id="roomTypeChart"></canvas>
                            </div>
                            <div class="mt-4">
                                <div class="d-flex justify-content-between mb-2 small">
                                    <span><i class="bi bi-circle-fill text-primary me-2"></i>Standard</span>
                                    <span class="fw-bold">60%</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2 small">
                                    <span><i class="bi bi-circle-fill text-info me-2"></i>VIP</span>
                                    <span class="fw-bold">30%</span>
                                </div>
                                <div class="d-flex justify-content-between small">
                                    <span><i class="bi bi-circle-fill text-warning me-2"></i>Suite</span>
                                    <span class="fw-bold">10%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="glass-card p-4">
                    <h5 class="fw-bold mb-4 text-dark"><i class="bi bi-bar-chart text-primary me-2"></i>อัตราการเข้าพักรายเดือน</h5>
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="occupancyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "../includes/footer.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'รายได้ (บาท)',
                    data: <?php echo json_encode($monthlyRevenue); ?>,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 6,
                    pointHoverRadius: 8
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
                        beginAtZero: false,
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            font: { family: "'League Spartan', sans-serif" },
                            callback: function(value) { return '฿' + value.toLocaleString(); }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: "'IBM Plex Sans Thai', sans-serif" } }
                    }
                }
            }
        });

        // Room Type Chart
        const roomTypeCtx = document.getElementById('roomTypeChart').getContext('2d');
        new Chart(roomTypeCtx, {
            type: 'doughnut',
            data: {
                labels: ['Standard', 'VIP', 'Suite'],
                datasets: [{
                    data: [60, 30, 10],
                    backgroundColor: ['#3b82f6', '#06b6d4', '#f59e0b'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                cutout: '70%'
            }
        });

        // Occupancy Chart
        const occupancyCtx = document.getElementById('occupancyChart').getContext('2d');
        new Chart(occupancyCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'อัตราการเข้าพัก (%)',
                    data: <?php echo json_encode($occupancyRate); ?>,
                    backgroundColor: '#10b981',
                    borderRadius: 6,
                    barThickness: 30
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
                        max: 100,
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            font: { family: "'League Spartan', sans-serif" },
                            callback: function(value) { return value + '%'; }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: "'IBM Plex Sans Thai', sans-serif" } }
                    }
                }
            }
        });
    </script>
</body>
</html>
