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

// Mock Data (Fallback)
if (!isset($properties)) {
    $properties = [
        [
            "id" => 1,
            "title" => "ห้อง 101 (Standard)",
            "room_number" => "101",
            "price" => 4500,
            "status" => "rented",
            "tenant" => "สมชาย ใจดี",
            "contract_end" => "2024-12-31",
            "image" => "https://via.placeholder.com/100"
        ],
        [
            "id" => 2,
            "title" => "ห้อง 102 (Standard)",
            "room_number" => "102",
            "price" => 4500,
            "status" => "available",
            "tenant" => "-",
            "contract_end" => "-",
            "image" => "https://via.placeholder.com/100"
        ],
        [
            "id" => 3,
            "title" => "ห้อง 201 (VIP)",
            "room_number" => "201",
            "price" => 6500,
            "status" => "rented",
            "tenant" => "วิภา สุขใจ",
            "contract_end" => "2024-05-20",
            "image" => "https://via.placeholder.com/100"
        ],
        [
            "id" => 4,
            "title" => "ห้อง 202 (VIP)",
            "room_number" => "202",
            "price" => 6500,
            "status" => "maintenance",
            "tenant" => "-",
            "contract_end" => "-",
            "image" => "https://via.placeholder.com/100"
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการห้องพัก - <?php echo SITE_NAME; ?></title>
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

        .status-badge {
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-available { background: #ecfdf5; color: #059669; }
        .status-rented { background: #fef2f2; color: #dc2626; }
        .status-maintenance { background: #fffbeb; color: #d97706; }
    </style>
</head>
<body>
    <?php include "../includes/header.php"; ?>

    <!-- Page Header -->
    <div class="page-header-section">
        <div class="page-header-pattern"></div>
        <div class="container position-relative z-2 text-center">
            <h1 class="display-4 fw-bold mb-3">จัดการห้องพัก</h1>
            <p class="lead opacity-90 mb-0">จัดการข้อมูลห้องพักและสถานะการเช่า</p>
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
                        <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-building me-2"></i> รายการห้องพัก</h4>
                        <a href="add-property.php" class="btn btn-primary rounded-pill px-4">
                            <i class="bi bi-plus-lg me-2"></i> เพิ่มห้องใหม่
                        </a>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" class="form-control border-start-0 ps-0" placeholder="ค้นหาห้องพัก...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select">
                                <option value="">สถานะทั้งหมด</option>
                                <option value="available">ว่าง</option>
                                <option value="rented">มีผู้เช่า</option>
                                <option value="maintenance">ปรับปรุง</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 rounded-start-3">ห้อง</th>
                                    <th class="border-0">ราคา/เดือน</th>
                                    <th class="border-0">สถานะ</th>
                                    <th class="border-0">ผู้เช่า</th>
                                    <th class="border-0">หมดสัญญา</th>
                                    <th class="border-0 rounded-end-3 text-end">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($properties as $prop): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo $prop['image']; ?>" class="rounded-3 me-3" width="48" height="48" style="object-fit: cover;">
                                            <div>
                                                <div class="fw-bold text-dark"><?php echo $prop['room_number']; ?></div>
                                                <small class="text-muted"><?php echo $prop['title']; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fw-bold text-primary">฿<?php echo number_format($prop['price']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $prop['status']; ?>">
                                            <?php 
                                                $statusMap = [
                                                    'rented' => 'มีผู้เช่า',
                                                    'available' => 'ว่าง',
                                                    'maintenance' => 'ปรับปรุง'
                                                ];
                                                echo $statusMap[$prop['status']] ?? $prop['status'];
                                            ?>
                                        </span>
                                    </td>
                                    <td><?php echo $prop['tenant']; ?></td>
                                    <td><?php echo $prop['contract_end']; ?></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-light rounded-circle text-primary me-1"><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-sm btn-light rounded-circle text-danger"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item disabled"><a class="page-link rounded-pill px-3 mx-1" href="#">ก่อนหน้า</a></li>
                            <li class="page-item active"><a class="page-link rounded-pill px-3 mx-1" href="#">1</a></li>
                            <li class="page-item"><a class="page-link rounded-pill px-3 mx-1" href="#">2</a></li>
                            <li class="page-item"><a class="page-link rounded-pill px-3 mx-1" href="#">3</a></li>
                            <li class="page-item"><a class="page-link rounded-pill px-3 mx-1" href="#">ถัดไป</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <?php include "../includes/footer.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
