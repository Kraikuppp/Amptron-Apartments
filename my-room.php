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
    'start_date' => '2025-01-01',
    'end_date' => '2025-12-31',
    'rent_price' => 5500,
    'deposit' => 11000,
    'status' => 'Active'
];

$billData = [
    'month' => 'พฤศจิกายน 2025',
    'status' => 'Pending', // Pending, Paid
    'due_date' => '2025-11-05',
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
    ['month' => 'ตุลาคม 2025', 'amount' => 7350, 'date' => '2025-10-03', 'status' => 'ชำระแล้ว'],
    ['month' => 'กันยายน 2025', 'amount' => 7200, 'date' => '2025-09-05', 'status' => 'ชำระแล้ว'],
    ['month' => 'สิงหาคม 2025', 'amount' => 7500, 'date' => '2025-08-04', 'status' => 'ชำระแล้ว'],
];

// Mock Data for Parcels
$parcelData = [
    [
        'id' => 'P001',
        'tracking' => 'TH123456789',
        'sender' => 'Shopee Express',
        'arrived_date' => '2025-11-20 10:30',
        'status' => 'received', // received, pending
        'receiver' => 'สมศักดิ์ ผู้เช่า',
        'received_date' => '2025-11-20 18:45',
        'signature_image' => 'https://placehold.co/300x150/e9ecef/6c757d?text=Signature'
    ],
    [
        'id' => 'P002',
        'tracking' => 'KER987654321',
        'sender' => 'Kerry Express',
        'arrived_date' => '2025-11-22 14:15',
        'status' => 'pending',
        'receiver' => '-',
        'received_date' => '-',
        'signature_image' => ''
    ],
    [
        'id' => 'P003',
        'tracking' => 'J&T555666777',
        'sender' => 'J&T Express',
        'arrived_date' => '2025-11-23 09:00',
        'status' => 'pending',
        'receiver' => '-',
        'received_date' => '-',
        'signature_image' => ''
    ]
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
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.85) 0%, rgba(59, 130, 246, 0.85) 100%), 
                        url('https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?q=80&w=2070&auto=format&fit=crop') center/cover;
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

                    <!-- Documents Section -->
                    <div class="mt-4 pt-4 border-top">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-folder-fill me-2" style="color: #6366f1;"></i>
                            เอกสารประกอบ
                        </h6>

                        <div class="list-group list-group-flush">
                            <a href="documents/rental-contract.html" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 border-0 border-bottom">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded p-2" style="background-color: #fee2e2;">
                                        <i class="bi bi-file-earmark-pdf-fill fs-5 text-danger"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-semibold" style="font-size: 0.95rem;">สัญญาเช่าห้องพัก</h6>
                                        <small class="text-muted">เลขที่สัญญา: AC-2024-0305</small>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-primary" onclick="window.open('documents/rental-contract.html', '_blank'); return false;">
                                        <i class="bi bi-eye"></i> ดู
                                    </button>
                                    <button class="btn btn-sm btn-primary" onclick="window.open('documents/rental-contract.html', '_blank'); return false;">
                                        <i class="bi bi-download"></i>
                                    </button>
                                </div>
                            </a>

                            <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-0">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded p-2" style="background-color: #dbeafe;">
                                        <i class="bi bi-person-vcard-fill fs-5 text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-semibold" style="font-size: 0.95rem;">สำเนาบัตรประชาชน</h6>
                                        <small class="text-muted">เลขที่: 1-2345-67890-12-3</small>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-primary" onclick="alert('ดูสำเนาบัตรประชาชน'); return false;">
                                        <i class="bi bi-eye"></i> ดู
                                    </button>
                                    <button class="btn btn-sm btn-primary" onclick="alert('ดาวน์โหลดสำเนาบัตรประชาชน'); return false;">
                                        <i class="bi bi-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
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
                        <button class="btn w-100 pay-btn" data-bs-toggle="modal" data-bs-target="#qrPaymentModal">
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
                                    <th class="pb-3 text-center">สถานะ</th>
                                    <th class="pb-3 pe-0 text-end">เอกสาร</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($paymentHistory as $index => $history): ?>
                                <tr>
                                    <td class="py-3 ps-0 fw-medium"><?php echo $history['month']; ?></td>
                                    <td class="py-3 text-muted small"><?php echo date('d/m/Y', strtotime($history['date'])); ?></td>
                                    <td class="py-3 text-end fw-bold text-primary"><?php echo number_format($history['amount']); ?></td>
                                    <td class="py-3 text-center">
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">
                                            <?php echo $history['status']; ?>
                                        </span>
                                    </td>
                                    <td class="py-3 pe-0 text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-secondary" onclick="viewPastBill('<?php echo $history['month']; ?>', <?php echo $history['amount']; ?>)" title="ดูบิล">
                                                <i class="bi bi-receipt"></i>
                                            </button>
                                            <button class="btn btn-outline-secondary" onclick="viewPastSlip('<?php echo $history['date']; ?>', <?php echo $history['amount']; ?>)" title="ดูสลิป">
                                                <i class="bi bi-image"></i>
                                            </button>
                                        </div>
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

                <!-- My Parcels Section -->
                <div class="glass-card p-4 mb-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="rounded-circle p-3" style="background-color: rgba(245, 158, 11, 0.1);">
                            <i class="bi bi-box-seam-fill text-warning fs-4"></i>
                        </div>
                        <h4 class="mb-0 fw-bold">พัสดุของฉัน</h4>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="text-muted border-bottom">
                                <tr>
                                    <th class="pb-3 ps-0">วันที่มาถึง</th>
                                    <th class="pb-3">ผู้ส่ง</th>
                                    <th class="pb-3">เลขพัสดุ</th>
                                    <th class="pb-3 text-center">สถานะ</th>
                                    <th class="pb-3 pe-0 text-end">รายละเอียด</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($parcelData as $parcel): ?>
                                <tr>
                                    <td class="py-3 ps-0">
                                        <div class="fw-medium text-dark"><?php echo date('d/m/Y', strtotime($parcel['arrived_date'])); ?></div>
                                        <div class="small text-muted"><?php echo date('H:i', strtotime($parcel['arrived_date'])); ?> น.</div>
                                    </td>
                                    <td class="py-3 fw-medium"><?php echo $parcel['sender']; ?></td>
                                    <td class="py-3 text-muted small font-monospace"><?php echo $parcel['tracking']; ?></td>
                                    <td class="py-3 text-center">
                                        <?php if ($parcel['status'] === 'received'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">
                                                <i class="bi bi-check-circle-fill me-1"></i> รับแล้ว
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3">
                                                <i class="bi bi-clock-fill me-1"></i> รอรับ
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 pe-0 text-end">
                                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3" 
                                                onclick='viewParcelDetail(<?php echo json_encode($parcel); ?>)'>
                                            ดูรายละเอียด
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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
                        <a href="#" class="service-btn" data-bs-toggle="modal" data-bs-target="#serviceModal" onclick="setServiceType('เช่าไมโครเวฟ', 300)">
                            <i class="bi bi-plug"></i>
                            <span>เช่าไมโครเวฟ</span>
                        </a>
                        <a href="#" class="service-btn" data-bs-toggle="modal" data-bs-target="#serviceModal" onclick="setServiceType('เช่าตู้เย็น', 500)">
                            <i class="bi bi-box-seam"></i>
                            <span>เช่าตู้เย็น</span>
                        </a>
                        <a href="#" class="service-btn" data-bs-toggle="modal" data-bs-target="#serviceModal" onclick="setServiceType('เช่าทีวี', 400)">
                            <i class="bi bi-tv"></i>
                            <span>เช่าทีวี</span>
                        </a>
                        <a href="#" class="service-btn" data-bs-toggle="modal" data-bs-target="#airCleanModal">
                            <i class="bi bi-wind"></i>
                            <span>ล้างแอร์</span>
                        </a>
                        <a href="#" class="service-btn" onclick="alert('แจ้งซ่อมเรียบร้อยแล้ว\nเจ้าของหอพักได้รับการแจ้งเตือนแล้ว'); return false;">
                            <i class="bi bi-tools"></i>
                            <span>แจ้งซ่อม</span>
                        </a>
                        <a href="#" class="service-btn" onclick="alert('รายงานปัญหาเรียบร้อยแล้ว\nเจ้าของหอพักได้รับการแจ้งเตือนแล้ว'); return false;">
                            <i class="bi bi-exclamation-circle"></i>
                            <span>รายงานปัญหา</span>
                        </a>
                        <a href="#" class="service-btn" onclick="alert('ลงทะเบียนที่จอดรถเรียบร้อยแล้ว\nเจ้าของหอพักได้รับการแจ้งเตือนแล้ว'); return false;">
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

    <!-- Service Rental Modal (เช่าอุปกรณ์) -->
    <div class="modal fade" id="serviceModal" tabindex="-1" aria-labelledby="serviceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; border: none;">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="serviceModalLabel">
                        <i class="bi bi-cart-check text-primary me-2"></i>
                        <span id="serviceTitle">บริการเช่า</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-success mb-3">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        เจ้าของหอพักได้รับการแจ้งเตือนแล้ว
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">ค่าบริการ</label>
                        <div class="p-3" style="background: #f8f9fa; border-radius: 10px;">
                            <h4 class="text-primary mb-0">
                                <span id="servicePrice">300</span> บาท/เดือน
                            </h4>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">เลือกวิธีชำระเงิน</label>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-primary text-start" onclick="confirmService('จ่ายเลย')">
                                <i class="bi bi-credit-card me-2"></i>
                                จ่ายเลย
                                <small class="d-block text-muted">ชำระผ่าน QR Code ทันที</small>
                            </button>
                            <button type="button" class="btn btn-outline-primary text-start" onclick="confirmService('จ่ายรวมบิลรายเดือน')">
                                <i class="bi bi-calendar-check me-2"></i>
                                จ่ายรวมบิลรายเดือน
                                <small class="d-block text-muted">รวมกับค่าเช่าประจำเดือน</small>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Air Cleaning Modal (ล้างแอร์) -->
    <div class="modal fade" id="airCleanModal" tabindex="-1" aria-labelledby="airCleanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; border: none;">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="airCleanModalLabel">
                        <i class="bi bi-wind text-primary me-2"></i>
                        บริการล้างแอร์
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-success mb-3">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        เจ้าของหอพักได้รับการแจ้งเตือนแล้ว
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">ค่าบริการล้างแอร์</label>
                        <div class="p-3" style="background: #f8f9fa; border-radius: 10px;">
                            <h4 class="text-primary mb-0">600 บาท/เครื่อง</h4>
                            <small class="text-muted">รวมน้ำยาและอุปกรณ์</small>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">เลือกวิธีชำระเงิน</label>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-primary text-start" onclick="confirmAirClean('จ่ายเอง')">
                                <i class="bi bi-wallet2 me-2"></i>
                                จ่ายเอง
                                <small class="d-block text-muted">ชำระเงินสดกับช่างโดยตรง</small>
                            </button>
                            <button type="button" class="btn btn-outline-primary text-start" onclick="confirmAirClean('ให้หอพักจ่าย')">
                                <i class="bi bi-building me-2"></i>
                                ให้หอพักจ่าย
                                <small class="d-block text-muted">หอพักจ่ายให้ก่อน</small>
                            </button>
                            <button type="button" class="btn btn-outline-primary text-start" onclick="confirmAirClean('จ่ายกับบิลรายเดือน')">
                                <i class="bi bi-calendar-check me-2"></i>
                                จ่ายกับบิลรายเดือน
                                <small class="d-block text-muted">รวมกับค่าเช่าประจำเดือน</small>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Past Bill Modal (บิลย้อนหลัง) -->
    <div class="modal fade" id="pastBillModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; border: none;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-receipt text-primary me-2"></i>
                        รายละเอียดบิลย้อนหลัง
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <h6 class="text-muted mb-1">รอบบิลเดือน</h6>
                        <h4 class="fw-bold text-primary" id="pastBillMonth">ตุลาคม 2568</h4>
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 mt-2">ชำระแล้ว</span>
                    </div>
                    
                    <div class="card bg-light border-0 rounded-3 p-3 mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">ค่าเช่าห้อง</span>
                            <span class="fw-bold">5,500 บาท</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">ค่าไฟฟ้า (150 หน่วย)</span>
                            <span class="fw-bold">1,050 บาท</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">ค่าน้ำประปา (เหมาจ่าย)</span>
                            <span class="fw-bold">150 บาท</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">ค่าส่วนกลาง</span>
                            <span class="fw-bold">300 บาท</span>
                        </div>
                        <div class="d-flex justify-content-between mb-0">
                            <span class="text-muted">ค่าอินเทอร์เน็ต</span>
                            <span class="fw-bold">350 บาท</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold">ยอดรวมทั้งสิ้น</span>
                            <span class="fs-4 fw-bold text-primary" id="pastBillAmount">7,350 บาท</span>
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-outline-primary w-100">
                        <i class="bi bi-download me-2"></i>
                        ดาวน์โหลด PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Slip Modal (ดูสลิป) -->
    <div class="modal fade" id="viewSlipModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; border: none;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-image text-primary me-2"></i>
                        หลักฐานการโอนเงิน
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <p class="text-muted mb-3">วันที่ชำระ: <span id="slipDate" class="fw-bold text-dark"></span></p>
                    <div class="slip-container mb-3" style="background: #f8f9fa; padding: 10px; border-radius: 10px;">
                        <img src="https://placehold.co/400x600/e9ecef/6c757d?text=Payment+Slip+Image" alt="Payment Slip" class="img-fluid rounded shadow-sm" style="max-height: 500px;">
                    </div>
                    <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">
                        ปิด
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Parcel Detail Modal (รายละเอียดพัสดุ) -->
    <div class="modal fade" id="parcelModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; border: none;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-box-seam text-primary me-2"></i>
                        รายละเอียดพัสดุ
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <div id="parcelStatusBadge" class="badge rounded-pill px-3 py-2 mb-2"></div>
                        <h5 class="fw-bold mb-1" id="parcelSender"></h5>
                        <p class="text-muted small font-monospace mb-0" id="parcelTracking"></p>
                    </div>

                    <div class="card bg-light border-0 rounded-3 p-3 mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">วันที่พัสดุมาถึง</span>
                            <span class="fw-bold" id="parcelArrivedDate"></span>
                        </div>
                        
                        <div id="receiverInfo" style="display: none;">
                            <hr class="my-2">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">ผู้รับพัสดุ</span>
                                <span class="fw-bold text-primary" id="parcelReceiver"></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">วันที่รับพัสดุ</span>
                                <span class="fw-bold" id="parcelReceivedDate"></span>
                            </div>
                            
                            <div class="mt-3">
                                <p class="text-muted small mb-2">ลายเซ็นผู้รับ</p>
                                <div class="bg-white p-2 rounded border text-center">
                                    <img id="parcelSignature" src="" alt="Signature" class="img-fluid" style="max-height: 100px;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">
                        ปิด
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- QR Payment Modal -->
    <div class="modal fade" id="qrPaymentModal" tabindex="-1" aria-labelledby="qrPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; border: none;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="qrPaymentModalLabel">
                        <i class="bi bi-qr-code-scan text-primary me-2"></i>
                        ชำระเงินผ่าน QR Code
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <!-- QR Code Section -->
                    <div id="qrCodeSection">
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-clock-history me-2"></i>
                            เวลาที่เหลือ: <strong id="countdown">10:00</strong> นาที
                        </div>
                        
                        <div class="qr-code-container mb-3 text-center">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=00020101021129370016A000000677010111011300668123456780208AMPTRON5303764540573505802TH5925AMPTRON%20APARTMENTS6304" alt="QR Code PromptPay" class="img-fluid" style="max-width: 280px;">
                            <div class="mt-2">
                                <small class="text-muted d-block">PromptPay QR Code</small>
                                <strong class="text-primary">สแกนเพื่อชำระเงิน</strong>
                            </div>
                        </div>
                        
                        <div class="payment-info p-3 mb-3" style="background: #f8f9fa; border-radius: 10px;">
                            <p class="mb-2"><strong>ยอดชำระ:</strong> <span class="text-primary fs-4"><?php echo number_format($totalAmount); ?> บาท</span></p>
                            <p class="mb-2"><small class="text-muted">บัญชี: บริษัท แอมพ์ตรอน อพาร์ทเมนท์ จำกัด</small></p>
                            <p class="mb-0"><small class="text-muted">เลขที่บัญชี: 123-4-56789-0</small></p>
                        </div>
                        
                        <button type="button" class="btn btn-success w-100 mb-2" onclick="showSlipUpload()">
                            <i class="bi bi-check-circle me-2"></i>
                            ชำระเงินเรียบร้อยแล้ว
                        </button>
                        <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">
                            ยกเลิก
                        </button>
                    </div>

                    <!-- Slip Upload Section (Hidden by default) -->
                    <div id="slipUploadSection" style="display: none;">
                        <div class="alert alert-success mb-3">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            กรุณาแนบสลิปการโอนเงิน
                        </div>
                        
                        <form id="slipUploadForm" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="slipFile" class="form-label fw-bold">อัพโหลดสลิปการโอนเงิน</label>
                                <input type="file" class="form-control" id="slipFile" accept="image/*" required onchange="previewSlip(event)">
                                <small class="text-muted">รองรับไฟล์: JPG, PNG (ขนาดไม่เกิน 5MB)</small>
                            </div>
                            
                            <div id="slipPreview" class="mb-3" style="display: none;">
                                <img id="slipImage" src="" alt="Slip Preview" class="img-fluid" style="max-height: 300px; border-radius: 10px; border: 2px solid #dee2e6;">
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-2">
                                <i class="bi bi-upload me-2"></i>
                                ส่งสลิปการโอนเงิน
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100" onclick="backToQR()">
                                <i class="bi bi-arrow-left me-2"></i>
                                กลับ
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let countdownTimer;
        let timeLeft = 600; // 10 minutes in seconds

        // Start countdown when modal opens
        document.getElementById('qrPaymentModal').addEventListener('show.bs.modal', function () {
            timeLeft = 600;
            startCountdown();
        });

        // Clear countdown when modal closes
        document.getElementById('qrPaymentModal').addEventListener('hide.bs.modal', function () {
            clearInterval(countdownTimer);
            resetModal();
        });

        function startCountdown() {
            updateCountdownDisplay();
            countdownTimer = setInterval(function() {
                timeLeft--;
                updateCountdownDisplay();
                
                if (timeLeft <= 0) {
                    clearInterval(countdownTimer);
                    alert('หมดเวลาชำระเงิน กรุณาลองใหม่อีกครั้ง');
                    bootstrap.Modal.getInstance(document.getElementById('qrPaymentModal')).hide();
                }
            }, 1000);
        }

        function updateCountdownDisplay() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            document.getElementById('countdown').textContent = 
                `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            // Change color when time is running out
            const countdownElement = document.getElementById('countdown');
            if (timeLeft <= 60) {
                countdownElement.style.color = '#dc3545'; // Red
            } else if (timeLeft <= 180) {
                countdownElement.style.color = '#ffc107'; // Yellow
            } else {
                countdownElement.style.color = '#0d6efd'; // Blue
            }
        }

        function showSlipUpload() {
            document.getElementById('qrCodeSection').style.display = 'none';
            document.getElementById('slipUploadSection').style.display = 'block';
            clearInterval(countdownTimer);
        }

        function backToQR() {
            document.getElementById('slipUploadSection').style.display = 'none';
            document.getElementById('qrCodeSection').style.display = 'block';
            startCountdown();
        }

        function resetModal() {
            document.getElementById('qrCodeSection').style.display = 'block';
            document.getElementById('slipUploadSection').style.display = 'none';
            document.getElementById('slipUploadForm').reset();
            document.getElementById('slipPreview').style.display = 'none';
        }

        function previewSlip(event) {
            const file = event.target.files[0];
            if (file) {
                // Check file size (5MB max)
                if (file.size > 5 * 1024 * 1024) {
                    alert('ไฟล์มีขนาดใหญ่เกินไป กรุณาเลือกไฟล์ที่มีขนาดไม่เกิน 5MB');
                    event.target.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('slipImage').src = e.target.result;
                    document.getElementById('slipPreview').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }

        // Handle slip upload form submission
        document.getElementById('slipUploadForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const fileInput = document.getElementById('slipFile');
            if (!fileInput.files[0]) {
                alert('กรุณาเลือกไฟล์สลิปการโอนเงิน');
                return;
            }

            // Simulate upload (in real app, send to server)
            const formData = new FormData();
            formData.append('slip', fileInput.files[0]);
            formData.append('amount', '<?php echo $totalAmount; ?>');
            formData.append('bill_month', '<?php echo $billData['month']; ?>');

            // Show loading
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>กำลังส่งข้อมูล...';

            // Simulate API call
            setTimeout(function() {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                
                alert('ส่งสลิปการโอนเงินเรียบร้อยแล้ว!\nระบบจะตรวจสอบการชำระเงินภายใน 5-10 นาที');
                bootstrap.Modal.getInstance(document.getElementById('qrPaymentModal')).hide();
                
                // In real app, you would reload the page or update the bill status
                // location.reload();
            }, 2000);
        });

        // Service Rental Functions
        let currentServiceType = '';
        let currentServicePrice = 0;

        function setServiceType(serviceName, price) {
            currentServiceType = serviceName;
            currentServicePrice = price;
            document.getElementById('serviceTitle').textContent = serviceName;
            document.getElementById('servicePrice').textContent = price.toLocaleString();
        }

        function confirmService(paymentMethod) {
            if (paymentMethod === 'จ่ายเลย') {
                // Close service modal and open QR payment modal
                bootstrap.Modal.getInstance(document.getElementById('serviceModal')).hide();
                
                // Update QR payment modal with service price
                setTimeout(() => {
                    // You can update the QR code amount here if needed
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('qrPaymentModal')).show();
                }, 300);
            } else {
                // Add to monthly bill
                addToBill(currentServiceType, currentServicePrice);
                
                alert(`เพิ่มรายการเรียบร้อยแล้ว!\n\n${currentServiceType}\nค่าบริการ: ${currentServicePrice.toLocaleString()} บาท\n\nรายการนี้จะถูกเพิ่มในบิลค่าใช้จ่ายประจำเดือน`);
                bootstrap.Modal.getInstance(document.getElementById('serviceModal')).hide();
            }
        }

        function confirmAirClean(paymentMethod) {
            const serviceName = 'ค่าล้างแอร์';
            const servicePrice = 600;
            
            if (paymentMethod === 'จ่ายเอง') {
                alert(`ยืนยันการล้างแอร์\n\nค่าบริการ: 600 บาท/เครื่อง\nวิธีชำระเงิน: จ่ายเอง (ชำระเงินสดกับช่าง)\n\nเจ้าของหอพักจะติดต่อกลับเพื่อนัดหมายภายใน 24 ชั่วโมง`);
            } else if (paymentMethod === 'ให้หอพักจ่าย') {
                alert(`ยืนยันการล้างแอร์\n\nค่าบริการ: 600 บาท/เครื่อง\nวิธีชำระเงิน: ให้หอพักจ่าย\n\nเจ้าของหอพักจะติดต่อกลับเพื่อนัดหมายภายใน 24 ชั่วโมง`);
            } else {
                // Add to monthly bill
                addToBill(serviceName, servicePrice);
                
                alert(`เพิ่มรายการเรียบร้อยแล้ว!\n\nค่าล้างแอร์\nค่าบริการ: 600 บาท\n\nรายการนี้จะถูกเพิ่มในบิลค่าใช้จ่ายประจำเดือน\n\nเจ้าของหอพักจะติดต่อกลับเพื่อนัดหมายภายใน 24 ชั่วโมง`);
            }
            
            bootstrap.Modal.getInstance(document.getElementById('airCleanModal')).hide();
        }

        function addToBill(itemName, amount) {
            // Find the bill table
            const billTable = document.querySelector('.table-borderless tbody');
            
            // Create new row
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td class="py-3 ps-0 text-dark fw-medium">${itemName}</td>
                <td class="py-3 pe-0 text-end fw-bold text-dark">${amount.toLocaleString()} บาท</td>
            `;
            
            // Add row to table
            billTable.appendChild(newRow);
            
            // Update total amount
            const currentTotal = parseInt(document.querySelector('.display-6').textContent.replace(/,/g, ''));
            const newTotal = currentTotal + amount;
            document.querySelector('.display-6').textContent = newTotal.toLocaleString();
            
            // Add highlight animation
            newRow.style.backgroundColor = '#d1fae5';
            setTimeout(() => {
                newRow.style.transition = 'background-color 1s ease';
                newRow.style.backgroundColor = '';
            }, 100);
        }

        // View Past Bill Function
        function viewPastBill(month, amount) {
            document.getElementById('pastBillMonth').textContent = month;
            document.getElementById('pastBillAmount').textContent = amount.toLocaleString() + ' บาท';
            
            // Open modal
            new bootstrap.Modal(document.getElementById('pastBillModal')).show();
        }

        // View Past Slip Function
        function viewPastSlip(date, amount) {
            // Format date to Thai format
            const dateObj = new Date(date);
            const thaiDate = dateObj.toLocaleDateString('th-TH', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            document.getElementById('slipDate').textContent = thaiDate;
            
            // In a real app, you would set the src to the actual slip image URL
            // document.querySelector('#viewSlipModal img').src = 'path/to/slip/' + slipId + '.jpg';
            
            // Open modal
            new bootstrap.Modal(document.getElementById('viewSlipModal')).show();
        }

        // View Parcel Detail Function
        function viewParcelDetail(parcel) {
            document.getElementById('parcelSender').textContent = parcel.sender;
            document.getElementById('parcelTracking').textContent = parcel.tracking;
            
            // Format arrived date
            const arrivedDate = new Date(parcel.arrived_date);
            document.getElementById('parcelArrivedDate').textContent = arrivedDate.toLocaleDateString('th-TH', {
                year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
            }) + ' น.';

            const statusBadge = document.getElementById('parcelStatusBadge');
            const receiverInfo = document.getElementById('receiverInfo');

            if (parcel.status === 'received') {
                statusBadge.className = 'badge rounded-pill px-3 py-2 mb-2 bg-success bg-opacity-10 text-success';
                statusBadge.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> รับแล้ว';
                
                receiverInfo.style.display = 'block';
                document.getElementById('parcelReceiver').textContent = parcel.receiver;
                
                const receivedDate = new Date(parcel.received_date); // Assuming date string format is compatible
                // Handle case where received_date might be '-' or invalid in mock data
                if (parcel.received_date !== '-') {
                     // For mock data format "YYYY-MM-DD HH:mm", we might need to parse manually if Date constructor fails on some browsers, 
                     // but standard ISO format usually works. Let's assume standard format or simple string display if needed.
                     // Actually, let's just display the string from mock data with simple formatting if it's not a standard date object
                     document.getElementById('parcelReceivedDate').textContent = parcel.received_date + ' น.';
                } else {
                     document.getElementById('parcelReceivedDate').textContent = '-';
                }

                document.getElementById('parcelSignature').src = parcel.signature_image;
            } else {
                statusBadge.className = 'badge rounded-pill px-3 py-2 mb-2 bg-warning bg-opacity-10 text-warning';
                statusBadge.innerHTML = '<i class="bi bi-clock-fill me-1"></i> รอรับ';
                
                receiverInfo.style.display = 'none';
            }

            new bootstrap.Modal(document.getElementById('parcelModal')).show();
        }
    </script>
</body>
</html>
