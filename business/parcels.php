<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

// ตรวจสอบว่าเป็น Business User
if (!isLoggedIn() || !isBusiness()) {
    redirect("../login.php");
}

$userId = $_SESSION["user_id"];

// Mock Data สำหรับพัสดุ
$parcels = [
    [
        "id" => 1,
        "room_number" => "A101",
        "tenant_name" => "คุณสมชาย ใจดี",
        "sender" => "Shopee Express",
        "tracking_number" => "TH123456789",
        "arrived_at" => date("Y-m-d H:i", strtotime("-2 hours")),
        "status" => "pending", // pending, picked_up
        "picked_up_at" => null,
        "signature" => null
    ],
    [
        "id" => 2,
        "room_number" => "B205",
        "tenant_name" => "คุณวิภา สุขใจ",
        "sender" => "Kerry Express",
        "tracking_number" => "KER987654321",
        "arrived_at" => date("Y-m-d H:i", strtotime("-5 hours")),
        "status" => "pending",
        "picked_up_at" => null,
        "signature" => null
    ],
    [
        "id" => 3,
        "room_number" => "A102",
        "tenant_name" => "คุณนภา รักเรียน",
        "sender" => "Lazada",
        "tracking_number" => "LZD11223344",
        "arrived_at" => date("Y-m-d H:i", strtotime("-1 day")),
        "status" => "picked_up",
        "picked_up_at" => date("Y-m-d H:i", strtotime("-1 hour")),
        "signature" => "signature_mock.png"
    ]
];

// สถิติ
$pendingCount = count(array_filter($parcels, fn($p) => $p['status'] === 'pending'));
$pickedUpCount = count(array_filter($parcels, fn($p) => $p['status'] === 'picked_up'));
$todayCount = count(array_filter($parcels, fn($p) => date("Y-m-d", strtotime($p['arrived_at'])) === date("Y-m-d")));

// Mock Business Profile (ถ้าไม่มีใน Session หรือ DB)
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
    <title>จัดการพัสดุ - <?php echo SITE_NAME; ?></title>
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
        
        .stat-card {
            border-radius: 20px;
            padding: 20px;
            color: white;
            height: 100%;
            position: relative;
            overflow: hidden;
            border: none;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .status-pending { background: #fff7ed; color: #ea580c; }
        .status-picked_up { background: #ecfdf5; color: #059669; }

        .signature-pad {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            cursor: crosshair;
            position: relative;
        }
        
        .cursor-pointer { cursor: pointer; }
        .cursor-pointer:hover { background-color: #e2e8f0 !important; }
    </style>
</head>
<body>
    <?php include "../includes/header.php"; ?>

    <!-- Page Header -->
    <div class="page-header-section">
        <div class="page-header-pattern"></div>
        <div class="container position-relative z-2 text-center">
            <h1 class="display-4 fw-bold mb-3">จัดการพัสดุ</h1>
            <p class="lead opacity-90 mb-0">ระบบรับฝากและแจ้งเตือนพัสดุสำหรับลูกบ้าน</p>
            <button class="btn btn-light rounded-pill px-4 mt-3 fw-bold text-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addParcelModal">
                <i class="bi bi-plus-lg me-2"></i>เพิ่มพัสดุใหม่
            </button>
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
                
                <!-- Stats -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="stat-card" style="background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h2 class="fw-bold mb-0"><?php echo $pendingCount; ?></h2>
                                    <p class="mb-0 opacity-75">พัสดุรอรับ</p>
                                </div>
                                <i class="bi bi-box-seam fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card" style="background: linear-gradient(135deg, #10B981 0%, #059669 100%);">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h2 class="fw-bold mb-0"><?php echo $pickedUpCount; ?></h2>
                                    <p class="mb-0 opacity-75">รับแล้วทั้งหมด</p>
                                </div>
                                <i class="bi bi-check-circle fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h2 class="fw-bold mb-0"><?php echo $todayCount; ?></h2>
                                    <p class="mb-0 opacity-75">พัสดุเข้าวันนี้</p>
                                </div>
                                <i class="bi bi-calendar-check fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Parcel List -->
                <div class="glass-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0"><i class="bi bi-list-ul text-primary me-2"></i>รายการพัสดุ</h5>
                        <div class="d-flex gap-2">
                            <input type="text" class="form-control form-control-sm rounded-pill" placeholder="ค้นหาห้อง/เลขพัสดุ..." style="width: 200px;">
                            <select class="form-select form-select-sm rounded-pill w-auto">
                                <option value="all">ทั้งหมด</option>
                                <option value="pending">รอรับ</option>
                                <option value="picked_up">รับแล้ว</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 rounded-start-3 cursor-pointer" onclick="sortTable(0)">วันที่มาถึง <i class="bi bi-arrow-down-up small text-muted ms-1"></i></th>
                                    <th class="border-0 cursor-pointer" onclick="sortTable(1)">ห้อง <i class="bi bi-arrow-down-up small text-muted ms-1"></i></th>
                                    <th class="border-0 cursor-pointer" onclick="sortTable(2)">ผู้ส่ง/ขนส่ง <i class="bi bi-arrow-down-up small text-muted ms-1"></i></th>
                                    <th class="border-0 cursor-pointer" onclick="sortTable(3)">เลขพัสดุ <i class="bi bi-arrow-down-up small text-muted ms-1"></i></th>
                                    <th class="border-0 cursor-pointer" onclick="sortTable(4)">สถานะ <i class="bi bi-arrow-down-up small text-muted ms-1"></i></th>
                                    <th class="border-0 rounded-end-3 text-end">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($parcels as $parcel): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?php echo date("d/m/Y", strtotime($parcel['arrived_at'])); ?></div>
                                        <small class="text-muted"><?php echo date("H:i", strtotime($parcel['arrived_at'])); ?> น.</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?php echo $parcel['room_number']; ?></span>
                                        <div class="small text-muted mt-1"><?php echo $parcel['tenant_name']; ?></div>
                                    </td>
                                    <td><?php echo $parcel['sender']; ?></td>
                                    <td class="text-muted font-monospace"><?php echo $parcel['tracking_number']; ?></td>
                                    <td>
                                        <?php if ($parcel['status'] === 'pending'): ?>
                                            <span class="status-badge status-pending">
                                                <i class="bi bi-clock"></i> รอรับ
                                            </span>
                                        <?php else: ?>
                                            <span class="status-badge status-picked_up">
                                                <i class="bi bi-check-all"></i> รับแล้ว
                                            </span>
                                            <div class="small text-muted mt-1">
                                                <?php echo date("d/m H:i", strtotime($parcel['picked_up_at'])); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($parcel['status'] === 'pending'): ?>
                                            <button class="btn btn-sm btn-primary rounded-pill px-3" onclick="openPickupModal(<?php echo $parcel['id']; ?>, '<?php echo $parcel['room_number']; ?>')">
                                                <i class="bi bi-box-arrow-up me-1"></i> รับของ
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary rounded-circle ms-1" title="แจ้งเตือนซ้ำ" onclick="notifyRoom('<?php echo $parcel['room_number']; ?>')">
                                                <i class="bi bi-bell"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-light text-secondary rounded-pill px-3" disabled>
                                                <i class="bi bi-check2 me-1"></i> เรียบร้อย
                                            </button>
                                            <?php if ($parcel['signature']): ?>
                                                <button class="btn btn-sm btn-outline-secondary rounded-circle ms-1" title="ดูลายเซ็น" onclick="viewSignature('<?php echo $parcel['signature']; ?>')">
                                                    <i class="bi bi-pen"></i>
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php include "../includes/footer.php"; ?>

    <!-- Add Parcel Modal -->
    <div class="modal fade" id="addParcelModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">เพิ่มพัสดุใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="addParcelForm">
                        <div class="mb-3">
                            <label class="form-label">ห้องเลขที่</label>
                            <select class="form-select" required>
                                <option value="">เลือกห้อง...</option>
                                <option value="A101">A101 - คุณสมชาย</option>
                                <option value="A102">A102 - คุณนภา</option>
                                <option value="B205">B205 - คุณวิภา</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">บริษัทขนส่ง / ผู้ส่ง</label>
                            <input type="text" class="form-control" placeholder="เช่น Kerry, Shopee, ไปรษณีย์ไทย" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">เลขพัสดุ (Tracking No.)</label>
                            <input type="text" class="form-control font-monospace" placeholder="ระบุเลขพัสดุ (ถ้ามี)">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">รูปถ่ายพัสดุ</label>
                            <input type="file" class="form-control" accept="image/*">
                        </div>
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="notifyCheck" checked>
                            <label class="form-check-label" for="notifyCheck">
                                ส่งแจ้งเตือนไปยังผู้เช่าทันที (LINE / App)
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold">บันทึกและแจ้งเตือน</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Pickup Modal (Signature) -->
    <div class="modal fade" id="pickupModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">ยืนยันการรับพัสดุ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px;">
                            <i class="bi bi-box-seam fs-3 text-primary"></i>
                        </div>
                        <h6 class="fw-bold mb-1" id="pickupRoomNumber">ห้อง A101</h6>
                        <p class="text-muted small mb-0">กรุณาลงลายเซ็นเพื่อยืนยันการรับของ</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small text-muted">ลายเซ็นผู้รับ</label>
                        <div class="signature-pad" id="signaturePad">
                            <span class="text-muted opacity-50"><i class="bi bi-pen me-1"></i> เซ็นชื่อที่นี่</span>
                        </div>
                        <div class="text-end mt-1">
                            <button type="button" class="btn btn-sm btn-link text-secondary text-decoration-none p-0" onclick="clearSignature()">ล้างลายเซ็น</button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small text-muted">หรือแนบรูปถ่ายตอนรับของ</label>
                        <input type="file" class="form-control form-control-sm">
                    </div>

                    <button type="button" class="btn btn-success w-100 rounded-pill py-2 fw-bold" onclick="confirmPickup()">
                        <i class="bi bi-check-lg me-2"></i>ยืนยันการรับของ
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function notifyRoom(room) {
            alert('ส่งแจ้งเตือนไปยังห้อง ' + room + ' เรียบร้อยแล้ว');
        }

        function openPickupModal(id, room) {
            document.getElementById('pickupRoomNumber').innerText = 'ห้อง ' + room;
            // Store ID in a data attribute or variable if needed for real backend
            new bootstrap.Modal(document.getElementById('pickupModal')).show();
        }

        function clearSignature() {
            // Mock clear
            alert('ล้างลายเซ็นเรียบร้อย');
        }

        function confirmPickup() {
            alert('บันทึกการรับพัสดุเรียบร้อยแล้ว');
            bootstrap.Modal.getInstance(document.getElementById('pickupModal')).hide();
            location.reload();
        }
        
        function viewSignature(sig) {
            alert('แสดงรูปภาพลายเซ็น: ' + sig);
        }

        document.getElementById('addParcelForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('เพิ่มพัสดุและส่งแจ้งเตือนเรียบร้อยแล้ว');
            bootstrap.Modal.getInstance(document.getElementById('addParcelModal')).hide();
            location.reload();
        });

        function sortTable(n) {
            var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
            table = document.querySelector(".table");
            switching = true;
            // Set the sorting direction to ascending:
            dir = "asc";
            
            while (switching) {
                switching = false;
                rows = table.rows;
                
                // Loop through all table rows (except the first, which contains table headers):
                for (i = 1; i < (rows.length - 1); i++) {
                    shouldSwitch = false;
                    
                    x = rows[i].getElementsByTagName("TD")[n];
                    y = rows[i + 1].getElementsByTagName("TD")[n];
                    
                    // Get content based on column type
                    let xContent = x.innerText.toLowerCase();
                    let yContent = y.innerText.toLowerCase();
                    
                    // Special handling for Date column (index 0) - if needed, but text sort works for YYYY-MM-DD or similar if formatted correctly.
                    // Here date is DD/MM/YYYY, so text sort might be wrong. Let's try to parse if possible or just use text for now.
                    // Actually, for DD/MM/YYYY, text sort is bad (01/02 comes before 02/01).
                    // But let's keep it simple first as per previous request, or improve it if I can see the format.
                    // The format is DD/MM/YYYY (e.g., 24/11/2025).
                    // Let's try to parse date for column 0.
                    
                    if (n === 0) {
                        // Extract date string from the first div in the cell
                        let xDateStr = x.querySelector('div.fw-bold').innerText;
                        let yDateStr = y.querySelector('div.fw-bold').innerText;
                        
                        // Convert DD/MM/YYYY to YYYYMMDD for comparison
                        function parseDate(str) {
                            const parts = str.split('/');
                            return parts[2] + parts[1] + parts[0];
                        }
                        
                        xContent = parseDate(xDateStr);
                        yContent = parseDate(yDateStr);
                    }
                    
                    if (dir == "asc") {
                        if (xContent > yContent) {
                            shouldSwitch = true;
                            break;
                        }
                    } else if (dir == "desc") {
                        if (xContent < yContent) {
                            shouldSwitch = true;
                            break;
                        }
                    }
                }
                
                if (shouldSwitch) {
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                    switchcount++;
                } else {
                    if (switchcount == 0 && dir == "asc") {
                        dir = "desc";
                        switching = true;
                    }
                }
            }
        }
    </script>
</body>
</html>
