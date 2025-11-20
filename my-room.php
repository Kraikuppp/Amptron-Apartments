<?php
require_once "config/config.php";
require_once "includes/auth.php";

// Ensure user is logged in
if (!isLoggedIn()) {
    redirect("login.php");
}

// Mock Data for "My Room"
$roomData = [
    'room_number' => 'A-305',
    'building' => 'อาคาร A',
    'floor' => '3',
    'type' => 'Studio Suite',
    'size' => '32 ตร.ม.',
    'status' => 'Active'
];

$contractData = [
    'start_date' => '2024-01-01',
    'end_date' => '2024-12-31',
    'rent_price' => 5500,
    'deposit' => 11000,
    'status' => 'Active'
];

$billData = [
    'month' => 'พฤศจิกายน 2024',
    'status' => 'Pending', // Pending, Paid
    'due_date' => '2024-11-05',
    'items' => [
        ['name' => 'ค่าเช่าห้อง', 'amount' => 5500],
        ['name' => 'ค่าไฟฟ้า (150 หน่วย x 7 บาท)', 'amount' => 1050],
        ['name' => 'ค่าน้ำประปา (เหมาจ่าย)', 'amount' => 150],
        ['name' => 'ค่าส่วนกลาง', 'amount' => 300],
        ['name' => 'ค่าอินเทอร์เน็ต', 'amount' => 350]
    ]
];

$totalAmount = 0;
foreach ($billData['items'] as $item) {
    $totalAmount += $item['amount'];
}

// Mock Data for Payment History
$paymentHistory = [
    ['month' => 'ตุลาคม 2024', 'amount' => 7350, 'date' => '2024-10-03', 'status' => 'ชำระแล้ว'],
    ['month' => 'กันยายน 2024', 'amount' => 7200, 'date' => '2024-09-05', 'status' => 'ชำระแล้ว'],
    ['month' => 'สิงหาคม 2024', 'amount' => 7500, 'date' => '2024-08-04', 'status' => 'ชำระแล้ว'],
];

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ห้องของฉัน - <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/img/amptron-apartments.png?v=2">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@100;200;300;400;500;600;700&family=League+Spartan:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        /* Page Specific Styles that complement style.css */
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

        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 60px rgba(37, 99, 235, 0.15);
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: var(--medium-gray);
            font-weight: 500;
        }

        .info-value {
            color: var(--dark-gray);
            font-weight: 600;
            font-family: var(--font-thai);
        }

        .bill-status-badge {
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .status-pending {
            background: #fff7ed;
            color: #ea580c;
            border: 1px solid #ffedd5;
        }

        .status-paid {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #dcfce7;
        }

        .service-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 16px;
        }

        .service-btn {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 24px 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--medium-gray);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .service-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 0;
        }

        .service-btn:hover {
            transform: translateY(-5px);
            border-color: transparent;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.15);
        }

        .service-btn:hover::before {
            opacity: 0.05;
        }

        .service-btn i {
            font-size: 2rem;
            color: var(--primary-blue);
            transition: transform 0.3s ease;
            z-index: 1;
        }

        .service-btn:hover i {
            transform: scale(1.1);
        }

        .service-btn span {
            font-weight: 600;
            font-size: 0.95rem;
            z-index: 1;
            color: var(--dark-gray);
        }

        .total-amount-box {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 16px;
            padding: 24px;
            margin-top: 24px;
            border: 1px solid #e2e8f0;
        }

        .pay-btn {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 16px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        }

        .pay-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.4);
            color: white;
        }
    </style>
</head>
<body>

    <?php include "includes/header.php"; ?>

    <!-- Page Header -->
    <div class="page-header-section">
        <div class="page-header-pattern"></div>
        <div class="container position-relative z-2 text-center">
            <h1 class="display-4 fw-bold mb-3">ห้องของฉัน</h1>
            <p class="lead opacity-90 mb-0">จัดการข้อมูลห้องพักและบิลค่าใช้จ่ายของคุณได้ง่ายๆ ที่นี่</p>
        </div>
    </div>

    <div class="container pb-5" style="margin-top: -20px;">
        <div class="row g-4">
            <!-- Left Column: Room Info & Contract -->
            <div class="col-lg-4">
                <!-- Room Details -->
                <div class="glass-card p-4 mb-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="rounded-circle bg-blue-light p-3 bg-opacity-10" style="background-color: rgba(37, 99, 235, 0.1);">
                            <i class="bi bi-door-open-fill text-primary fs-4"></i>
                        </div>
                        <h4 class="mb-0 fw-bold">ข้อมูลห้องพัก</h4>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">เลขห้อง</span>
                        <span class="info-value fs-5 text-primary"><?php echo $roomData['room_number']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">อาคาร</span>
                        <span class="info-value"><?php echo $roomData['building']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">ชั้น</span>
                        <span class="info-value"><?php echo $roomData['floor']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">รูปแบบ</span>
                        <span class="info-value"><?php echo $roomData['type']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">ขนาด</span>
                        <span class="info-value"><?php echo $roomData['size']; ?></span>
                    </div>
                </div>

                <!-- Contract Details -->
                <div class="glass-card p-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="rounded-circle p-3" style="background-color: rgba(16, 185, 129, 0.1);">
                            <i class="bi bi-file-earmark-text-fill text-success fs-4"></i>
                        </div>
                        <h4 class="mb-0 fw-bold">สัญญาเช่า</h4>
                    </div>

                    <div class="info-row">
                        <span class="info-label">สถานะ</span>
                        <span class="badge bg-success rounded-pill px-3">Active</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">วันเริ่มสัญญา</span>
                        <span class="info-value"><?php echo date('d/m/Y', strtotime($contractData['start_date'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">วันสิ้นสุดสัญญา</span>
                        <span class="info-value"><?php echo date('d/m/Y', strtotime($contractData['end_date'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">ค่าเช่า/เดือน</span>
                        <span class="info-value text-primary"><?php echo number_format($contractData['rent_price']); ?> บาท</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">เงินประกัน</span>
                        <span class="info-value"><?php echo number_format($contractData['deposit']); ?> บาท</span>
                    </div>
                </div>
            </div>

            <!-- Right Column: Billing & Services -->
            <div class="col-lg-8">
                <!-- Current Bill -->
                <div class="glass-card p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle p-3" style="background-color: rgba(245, 158, 11, 0.1);">
                                <i class="bi bi-receipt-cutoff text-warning fs-4"></i>
                            </div>
                            <div>
                                <h4 class="mb-1 fw-bold">บิลค่าใช้จ่าย</h4>
                                <span class="text-muted small">ประจำเดือน <?php echo $billData['month']; ?></span>
                            </div>
                        </div>
                        <span class="bill-status-badge <?php echo $billData['status'] === 'Pending' ? 'status-pending' : 'status-paid'; ?>">
                            <i class="bi <?php echo $billData['status'] === 'Pending' ? 'bi-clock-history' : 'bi-check-circle-fill'; ?>"></i>
                            <?php echo $billData['status'] === 'Pending' ? 'รอชำระเงิน' : 'ชำระแล้ว'; ?>
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0">
                            <thead class="text-muted border-bottom">
                                <tr>
                                    <th class="pb-3 ps-0">รายการ</th>
                                    <th class="pb-3 pe-0 text-end">จำนวนเงิน</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($billData['items'] as $item): ?>
                                <tr>
                                    <td class="py-3 ps-0 text-dark fw-medium"><?php echo $item['name']; ?></td>
                                    <td class="py-3 pe-0 text-end fw-bold text-dark"><?php echo number_format($item['amount']); ?> บาท</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="total-amount-box">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-secondary fw-medium">ยอดรวมทั้งสิ้น</span>
                            <span class="display-6 fw-bold text-primary"><?php echo number_format($totalAmount); ?> <span class="fs-5 text-muted">บาท</span></span>
                        </div>
                        <div class="text-end text-muted small">
                            กำหนดชำระภายใน: <?php echo date('d/m/Y', strtotime($billData['due_date'])); ?>
                        </div>
                    </div>

                    <?php if ($billData['status'] === 'Pending'): ?>
                    <div class="mt-4">
                        <button class="btn w-100 pay-btn">
                            <i class="bi bi-qr-code-scan me-2"></i>
                            ชำระเงินผ่าน QR Code
                        </button>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Payment History -->
                <div class="glass-card p-4 mb-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="rounded-circle p-3" style="background-color: rgba(16, 185, 129, 0.1);">
                            <i class="bi bi-clock-history text-success fs-4"></i>
                        </div>
                        <h4 class="mb-0 fw-bold">ประวัติการชำระเงิน</h4>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="text-muted border-bottom">
                                <tr>
                                    <th class="pb-3 ps-0">รอบบิล</th>
                                    <th class="pb-3">วันที่ชำระ</th>
                                    <th class="pb-3 text-end">ยอดชำระ</th>
                                    <th class="pb-3 pe-0 text-end">สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($paymentHistory as $history): ?>
                                <tr>
                                    <td class="py-3 ps-0 fw-medium"><?php echo $history['month']; ?></td>
                                    <td class="py-3 text-muted small"><?php echo date('d/m/Y', strtotime($history['date'])); ?></td>
                                    <td class="py-3 text-end fw-bold text-primary"><?php echo number_format($history['amount']); ?></td>
                                    <td class="py-3 pe-0 text-end">
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">
                                            <?php echo $history['status']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="#" class="text-decoration-none text-muted small">
                            ดูประวัติทั้งหมด <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Additional Services -->
                <div class="glass-card p-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="rounded-circle p-3" style="background-color: rgba(99, 102, 241, 0.1);">
                            <i class="bi bi-grid-fill text-primary fs-4" style="color: #6366f1 !important;"></i>
                        </div>
                        <h4 class="mb-0 fw-bold">บริการเพิ่มเติม</h4>
                    </div>

                    <div class="service-grid">
                        <a href="#" class="service-btn" onclick="alert('แจ้งขอเช่าไมโครเวฟเรียบร้อยแล้ว เจ้าหน้าที่จะติดต่อกลับ'); return false;">
                            <i class="bi bi-plug"></i>
                            <span>เช่าไมโครเวฟ</span>
                        </a>
                        <a href="#" class="service-btn" onclick="alert('แจ้งขอเช่าตู้เย็นเรียบร้อยแล้ว เจ้าหน้าที่จะติดต่อกลับ'); return false;">
                            <i class="bi bi-box-seam"></i>
                            <span>เช่าตู้เย็น</span>
                        </a>
                        <a href="#" class="service-btn" onclick="alert('แจ้งขอเช่าทีวีเรียบร้อยแล้ว เจ้าหน้าที่จะติดต่อกลับ'); return false;">
                            <i class="bi bi-tv"></i>
                            <span>เช่าทีวี</span>
                        </a>
                        <a href="#" class="service-btn" onclick="alert('แจ้งล้างแอร์เรียบร้อยแล้ว เจ้าหน้าที่จะติดต่อกลับเพื่อนัดหมาย'); return false;">
                            <i class="bi bi-wind"></i>
                            <span>ล้างแอร์</span>
                        </a>
                        <a href="#" class="service-btn" onclick="alert('แจ้งซ่อมเรียบร้อยแล้ว'); return false;">
                            <i class="bi bi-tools"></i>
                            <span>แจ้งซ่อม</span>
                        </a>
                        <a href="#" class="service-btn" onclick="alert('รายงานปัญหาเรียบร้อยแล้ว'); return false;">
                            <i class="bi bi-exclamation-circle"></i>
                            <span>รายงานปัญหา</span>
                        </a>
                        <a href="#" class="service-btn" onclick="alert('ลงทะเบียนที่จอดรถเรียบร้อยแล้ว'); return false;">
                            <i class="bi bi-car-front"></i>
                            <span>ลงทะเบียนจอดรถ</span>
                        </a>
                        <a href="#" class="service-btn">
                            <i class="bi bi-three-dots"></i>
                            <span>อื่นๆ</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
