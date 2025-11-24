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
            "image" => "https://via.placeholder.com/100",
            "contract_file" => "https://placehold.co/600x800/e9ecef/6c757d?text=Rental+Contract",
            "id_card_file" => "https://placehold.co/600x400/e9ecef/6c757d?text=ID+Card"
        ],
        [
            "id" => 2,
            "title" => "ห้อง 102 (Standard)",
            "room_number" => "102",
            "price" => 4500,
            "status" => "available",
            "tenant" => "-",
            "contract_end" => "-",
            "image" => "https://via.placeholder.com/100",
            "contract_file" => "",
            "id_card_file" => ""
        ],
        [
            "id" => 3,
            "title" => "ห้อง 201 (VIP)",
            "room_number" => "201",
            "price" => 6500,
            "status" => "rented",
            "tenant" => "วิภา สุขใจ",
            "contract_end" => "2024-05-20",
            "image" => "https://via.placeholder.com/100",
            "contract_file" => "https://placehold.co/600x800/e9ecef/6c757d?text=Rental+Contract",
            "id_card_file" => "https://placehold.co/600x400/e9ecef/6c757d?text=ID+Card"
        ],
        [
            "id" => 4,
            "title" => "ห้อง 202 (VIP)",
            "room_number" => "202",
            "price" => 6500,
            "status" => "maintenance",
            "tenant" => "-",
            "contract_end" => "-",
            "image" => "https://via.placeholder.com/100",
            "contract_file" => "",
            "id_card_file" => ""
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
                                    <th class="border-0 rounded-start-3 cursor-pointer" onclick="sortTable(0)">ห้อง <i class="bi bi-arrow-down-up small text-muted ms-1"></i></th>
                                    <th class="border-0 cursor-pointer" onclick="sortTable(1)">ราคา/เดือน <i class="bi bi-arrow-down-up small text-muted ms-1"></i></th>
                                    <th class="border-0 cursor-pointer" onclick="sortTable(2)">สถานะ <i class="bi bi-arrow-down-up small text-muted ms-1"></i></th>
                                    <th class="border-0 cursor-pointer" onclick="sortTable(3)">ผู้เช่า <i class="bi bi-arrow-down-up small text-muted ms-1"></i></th>
                                    <th class="border-0 cursor-pointer" onclick="sortTable(4)">หมดสัญญา <i class="bi bi-arrow-down-up small text-muted ms-1"></i></th>
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
                                        <button class="btn btn-sm btn-light rounded-circle text-info me-1" 
                                                onclick="viewDocuments('<?php echo $prop['room_number']; ?>', '<?php echo $prop['contract_file']; ?>', '<?php echo $prop['id_card_file']; ?>')"
                                                title="ดูเอกสาร">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </button>
                                        <button class="btn btn-sm btn-light rounded-circle text-primary me-1" 
                                                onclick="editPrice(<?php echo $prop['id']; ?>, '<?php echo $prop['room_number']; ?>', <?php echo $prop['price']; ?>)"
                                                title="แก้ไขราคา">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-light rounded-circle text-danger" title="ลบห้องพัก"><i class="bi bi-trash"></i></button>
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
    <!-- Edit Price Modal -->
    <div class="modal fade" id="editPriceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; border: none;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">แก้ไขราคาห้องพัก</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="editPriceForm">
                        <input type="hidden" id="editRoomId">
                        <div class="mb-3">
                            <label class="form-label text-muted">ห้องเลขที่</label>
                            <input type="text" class="form-control" id="editRoomNumber" readonly>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted">ราคาต่อเดือน (บาท)</label>
                            <input type="number" class="form-control form-control-lg fw-bold text-primary" id="editRoomPrice" min="0" step="100">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold">บันทึกการเปลี่ยนแปลง</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- View Documents Modal -->
    <div class="modal fade" id="viewDocsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border-radius: 20px; border: none;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">เอกสารห้อง <span id="docsRoomNumber"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <ul class="nav nav-pills nav-fill mb-4 p-1 bg-light rounded-pill" id="docsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active rounded-pill" id="contract-tab" data-bs-toggle="tab" data-bs-target="#contract-pane" type="button" role="tab">สัญญาเช่า</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill" id="idcard-tab" data-bs-toggle="tab" data-bs-target="#idcard-pane" type="button" role="tab">บัตรประชาชน</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="docsTabContent">
                        <div class="tab-pane fade show active text-center" id="contract-pane" role="tabpanel">
                            <div id="contractContent">
                                <img src="" id="contractImage" class="img-fluid rounded shadow-sm mb-3" style="max-height: 600px;">
                                <a href="#" id="contractDownload" class="btn btn-outline-primary rounded-pill" download>
                                    <i class="bi bi-download me-2"></i> ดาวน์โหลดสัญญา
                                </a>
                            </div>
                            <div id="noContract" class="py-5 text-muted">
                                <i class="bi bi-file-earmark-x fs-1 d-block mb-2"></i>
                                ไม่พบเอกสารสัญญาเช่า
                            </div>
                        </div>
                        <div class="tab-pane fade text-center" id="idcard-pane" role="tabpanel">
                            <div id="idCardContent">
                                <img src="" id="idCardImage" class="img-fluid rounded shadow-sm mb-3" style="max-height: 400px;">
                            </div>
                            <div id="noIdCard" class="py-5 text-muted">
                                <i class="bi bi-person-badge fs-1 d-block mb-2"></i>
                                ไม่พบเอกสารบัตรประชาชน
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editPrice(id, roomNumber, price) {
            document.getElementById('editRoomId').value = id;
            document.getElementById('editRoomNumber').value = roomNumber;
            document.getElementById('editRoomPrice').value = price;
            new bootstrap.Modal(document.getElementById('editPriceModal')).show();
        }

        function viewDocuments(roomNumber, contractUrl, idCardUrl) {
            document.getElementById('docsRoomNumber').textContent = roomNumber;
            
            // Contract
            if (contractUrl) {
                document.getElementById('contractContent').style.display = 'block';
                document.getElementById('noContract').style.display = 'none';
                document.getElementById('contractImage').src = contractUrl;
                document.getElementById('contractDownload').href = contractUrl;
            } else {
                document.getElementById('contractContent').style.display = 'none';
                document.getElementById('noContract').style.display = 'block';
            }

            // ID Card
            if (idCardUrl) {
                document.getElementById('idCardContent').style.display = 'block';
                document.getElementById('noIdCard').style.display = 'none';
                document.getElementById('idCardImage').src = idCardUrl;
            } else {
                document.getElementById('idCardContent').style.display = 'none';
                document.getElementById('noIdCard').style.display = 'block';
            }

            new bootstrap.Modal(document.getElementById('viewDocsModal')).show();
        }

        document.getElementById('editPriceForm').addEventListener('submit', function(e) {
            e.preventDefault();
            // In a real app, you would send an AJAX request to update the price
            alert('บันทึกราคาใหม่เรียบร้อยแล้ว');
            bootstrap.Modal.getInstance(document.getElementById('editPriceModal')).hide();
            location.reload(); // Refresh to see changes (mock)
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
                    
                    // Special handling for Price column (index 1) - remove currency symbol and commas
                    if (n === 1) {
                        xContent = parseFloat(xContent.replace(/[^0-9.-]+/g,""));
                        yContent = parseFloat(yContent.replace(/[^0-9.-]+/g,""));
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
