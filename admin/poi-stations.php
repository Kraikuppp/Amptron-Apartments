<?php
session_start();
require_once '../config/database.php';

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// สร้างตาราง POI Stations ถ้ายังไม่มี
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS poi_stations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(200) NOT NULL,
        name_en VARCHAR(200),
        line_type ENUM('BTS', 'MRT', 'ARL', 'BRT') NOT NULL,
        line_name VARCHAR(100),
        latitude DECIMAL(10,8) NOT NULL,
        longitude DECIMAL(11,8) NOT NULL,
        address TEXT,
        description TEXT,
        status ENUM('active', 'inactive', 'under_construction') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (PDOException $e) {
    // Table might already exist
}

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM poi_stations WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $_SESSION['success'] = 'ลบสถานีเรียบร้อยแล้ว!';
        header('Location: poi-stations.php');
        exit();
    } catch (PDOException $e) {
        $error = 'ไม่สามารถลบได้: ' . $e->getMessage();
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = $_POST['id'] ?? null;
        $name = $_POST['name'];
        $name_en = $_POST['name_en'] ?? '';
        $line_type = $_POST['line_type'];
        $line_name = $_POST['line_name'] ?? '';
        $latitude = $_POST['latitude'];
        $longitude = $_POST['longitude'];
        $address = $_POST['address'] ?? '';
        $description = $_POST['description'] ?? '';
        $status = $_POST['status'] ?? 'active';

        if ($id) {
            // Update
            $sql = "UPDATE poi_stations SET name = ?, name_en = ?, line_type = ?, line_name = ?,
                    latitude = ?, longitude = ?, address = ?, description = ?, status = ?
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $name_en, $line_type, $line_name, $latitude, $longitude,
                          $address, $description, $status, $id]);
            $_SESSION['success'] = 'อัปเดตสถานีเรียบร้อยแล้ว!';
        } else {
            // Insert
            $sql = "INSERT INTO poi_stations (name, name_en, line_type, line_name, latitude, longitude,
                    address, description, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $name_en, $line_type, $line_name, $latitude, $longitude,
                          $address, $description, $status]);
            $_SESSION['success'] = 'เพิ่มสถานีเรียบร้อยแล้ว!';
        }

        header('Location: poi-stations.php');
        exit();
    } catch (PDOException $e) {
        $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    }
}

// Get filter
$line_filter = $_GET['line'] ?? 'all';
$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$where_conditions = [];
$params = [];

if ($line_filter !== 'all') {
    $where_conditions[] = "line_type = :line";
    $params[':line'] = $line_filter;
}

if ($status_filter !== 'all') {
    $where_conditions[] = "status = :status";
    $params[':status'] = $status_filter;
}

if (!empty($search)) {
    $where_conditions[] = "(name LIKE :search OR name_en LIKE :search OR line_name LIKE :search)";
    $params[':search'] = "%{$search}%";
}

$where_sql = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get stations
$sql = "SELECT * FROM poi_stations $where_sql ORDER BY line_type, name";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$stations = $stmt->fetchAll();

// Get statistics
$stats_stmt = $pdo->query("SELECT
    COUNT(*) as total,
    SUM(CASE WHEN line_type = 'BTS' THEN 1 ELSE 0 END) as bts,
    SUM(CASE WHEN line_type = 'MRT' THEN 1 ELSE 0 END) as mrt,
    SUM(CASE WHEN line_type = 'ARL' THEN 1 ELSE 0 END) as arl,
    SUM(CASE WHEN line_type = 'BRT' THEN 1 ELSE 0 END) as brt
    FROM poi_stations WHERE status = 'active'");
$stats = $stats_stmt->fetch();

// Edit mode
$edit_station = null;
if (isset($_GET['edit']) && $_GET['edit']) {
    $stmt = $pdo->prepare("SELECT * FROM poi_stations WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_station = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสถานีรถไฟฟ้า - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }

        .dashboard-container {
            display: flex;
        }

        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 30px;
        }

        .page-header {
            background: white;
            padding: 25px 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .page-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 10px;
        }

        .breadcrumb {
            background: none;
            padding: 0;
            margin: 0;
            font-size: 14px;
        }

        .breadcrumb-item a {
            color: #667eea;
            text-decoration: none;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            text-align: center;
        }

        .stat-box .icon {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .stat-box h3 {
            font-size: 28px;
            font-weight: 700;
            color: #2d3748;
            margin: 0;
        }

        .stat-box p {
            font-size: 13px;
            color: #718096;
            margin: 5px 0 0;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 25px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            padding: 25px;
        }

        .card h3 {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }

        .filter-bar {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 15px;
            margin-bottom: 20px;
        }

        .form-control, .form-select {
            border: 2px solid #e2e8f0;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 14px;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .btn-filter {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-filter:hover {
            background: #5568d3;
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table thead th {
            background: #f7fafc;
            color: #4a5568;
            font-weight: 600;
            font-size: 13px;
            padding: 12px;
            border-bottom: 2px solid #e2e8f0;
        }

        .table tbody td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #2d3748;
            font-size: 14px;
        }

        .table tbody tr:hover {
            background: #f7fafc;
        }

        .line-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .line-badge.bts { background: #90EE90; color: #155724; }
        .line-badge.mrt { background: #4169E1; color: white; }
        .line-badge.arl { background: #FF6347; color: white; }
        .line-badge.brt { background: #FFD700; color: #856404; }

        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-badge.active { background: #d4edda; color: #155724; }
        .status-badge.inactive { background: #f8d7da; color: #721c24; }
        .status-badge.under_construction { background: #fff3cd; color: #856404; }

        .btn-action {
            padding: 6px 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 12px;
            margin-right: 5px;
        }

        .btn-edit { background: #48bb78; color: white; }
        .btn-edit:hover { background: #38a169; }

        .btn-delete { background: #f56565; color: white; }
        .btn-delete:hover { background: #e53e3e; }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            font-size: 13px;
            font-weight: 600;
            color: #4a5568;
            display: block;
            margin-bottom: 5px;
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-cancel {
            background: #e2e8f0;
            color: #4a5568;
            padding: 10px 25px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
        }

        #map {
            height: 300px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 2px solid #e2e8f0;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 1200px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .filter-bar {
                grid-template-columns: 1fr;
            }

            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1><i class="fas fa-subway"></i> จัดการสถานีรถไฟฟ้า</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">สถานีรถไฟฟ้า</li>
                    </ol>
                </nav>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats-row">
                <div class="stat-box">
                    <div class="icon">🚇</div>
                    <h3><?php echo $stats['total'] ?? 0; ?></h3>
                    <p>สถานีทั้งหมด</p>
                </div>
                <div class="stat-box">
                    <div class="icon" style="color: #90EE90;">🚊</div>
                    <h3><?php echo $stats['bts'] ?? 0; ?></h3>
                    <p>BTS</p>
                </div>
                <div class="stat-box">
                    <div class="icon" style="color: #4169E1;">🚇</div>
                    <h3><?php echo $stats['mrt'] ?? 0; ?></h3>
                    <p>MRT</p>
                </div>
                <div class="stat-box">
                    <div class="icon" style="color: #FF6347;">✈️</div>
                    <h3><?php echo $stats['arl'] ?? 0; ?></h3>
                    <p>ARL</p>
                </div>
                <div class="stat-box">
                    <div class="icon" style="color: #FFD700;">🚌</div>
                    <h3><?php echo $stats['brt'] ?? 0; ?></h3>
                    <p>BRT</p>
                </div>
            </div>

            <div class="content-grid">
                <!-- Station List -->
                <div class="card">
                    <h3>รายการสถานี</h3>

                    <!-- Filter -->
                    <form method="GET" class="filter-bar">
                        <input type="text" name="search" class="form-control" placeholder="ค้นหาสถานี..." value="<?php echo htmlspecialchars($search); ?>">

                        <select name="line" class="form-select">
                            <option value="all">ทุกสาย</option>
                            <option value="BTS" <?php echo $line_filter === 'BTS' ? 'selected' : ''; ?>>BTS</option>
                            <option value="MRT" <?php echo $line_filter === 'MRT' ? 'selected' : ''; ?>>MRT</option>
                            <option value="ARL" <?php echo $line_filter === 'ARL' ? 'selected' : ''; ?>>ARL</option>
                            <option value="BRT" <?php echo $line_filter === 'BRT' ? 'selected' : ''; ?>>BRT</option>
                        </select>

                        <select name="status" class="form-select">
                            <option value="all">ทุกสถานะ</option>
                            <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>ใช้งาน</option>
                            <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>ไม่ใช้งาน</option>
                            <option value="under_construction" <?php echo $status_filter === 'under_construction' ? 'selected' : ''; ?>>กำลังสร้าง</option>
                        </select>

                        <button type="submit" class="btn-filter">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>

                    <!-- Table -->
                    <div style="overflow-x: auto;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>ชื่อสถานี</th>
                                    <th>สาย</th>
                                    <th>สายที่</th>
                                    <th>พิกัด</th>
                                    <th>สถานะ</th>
                                    <th>การจัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($stations)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 40px; color: #718096;">
                                        ไม่พบข้อมูลสถานี
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($stations as $station): ?>
                                    <tr>
                                        <td>#<?php echo $station['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($station['name']); ?></strong>
                                            <?php if ($station['name_en']): ?>
                                            <br><small style="color: #718096;"><?php echo htmlspecialchars($station['name_en']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="line-badge <?php echo strtolower($station['line_type']); ?>">
                                                <?php echo $station['line_type']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($station['line_name'] ?: '-'); ?></td>
                                        <td>
                                            <small>
                                                <?php echo number_format($station['latitude'], 6); ?>,<br>
                                                <?php echo number_format($station['longitude'], 6); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $station['status']; ?>">
                                                <?php
                                                $status_text = [
                                                    'active' => 'ใช้งาน',
                                                    'inactive' => 'ไม่ใช้งาน',
                                                    'under_construction' => 'กำลังสร้าง'
                                                ];
                                                echo $status_text[$station['status']] ?? $station['status'];
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn-action btn-edit" onclick="location.href='?edit=<?php echo $station['id']; ?>'">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-action btn-delete" onclick="deleteStation(<?php echo $station['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Add/Edit Form -->
                <div class="card">
                    <h3><?php echo $edit_station ? 'แก้ไขสถานี' : 'เพิ่มสถานีใหม่'; ?></h3>

                    <form method="POST" action="">
                        <?php if ($edit_station): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_station['id']; ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label>ชื่อสถานี (ไทย) <span style="color: red;">*</span></label>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($edit_station['name'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>ชื่อสถานี (อังกฤษ)</label>
                            <input type="text" name="name_en" class="form-control" value="<?php echo htmlspecialchars($edit_station['name_en'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label>ประเภทสาย <span style="color: red;">*</span></label>
                            <select name="line_type" class="form-select" required>
                                <option value="BTS" <?php echo ($edit_station['line_type'] ?? '') === 'BTS' ? 'selected' : ''; ?>>BTS</option>
                                <option value="MRT" <?php echo ($edit_station['line_type'] ?? '') === 'MRT' ? 'selected' : ''; ?>>MRT</option>
                                <option value="ARL" <?php echo ($edit_station['line_type'] ?? '') === 'ARL' ? 'selected' : ''; ?>>ARL</option>
                                <option value="BRT" <?php echo ($edit_station['line_type'] ?? '') === 'BRT' ? 'selected' : ''; ?>>BRT</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>ชื่อสาย</label>
                            <input type="text" name="line_name" class="form-control" placeholder="เช่น: สุขุมวิท, สีเขียว" value="<?php echo htmlspecialchars($edit_station['line_name'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label>ที่อยู่</label>
                            <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($edit_station['address'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>คำอธิบาย</label>
                            <textarea name="description" class="form-control" rows="2"><?php echo htmlspecialchars($edit_station['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>สถานะ</label>
                            <select name="status" class="form-select">
                                <option value="active" <?php echo ($edit_station['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>ใช้งาน</option>
                                <option value="inactive" <?php echo ($edit_station['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>ไม่ใช้งาน</option>
                                <option value="under_construction" <?php echo ($edit_station['status'] ?? '') === 'under_construction' ? 'selected' : ''; ?>>กำลังสร้าง</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>ปักหมุดบนแผนที่</label>
                            <div id="map"></div>
                        </div>

                        <div class="form-group">
                            <label>Latitude <span style="color: red;">*</span></label>
                            <input type="text" name="latitude" id="latitude" class="form-control" value="<?php echo $edit_station['latitude'] ?? '13.7563'; ?>" required readonly>
                        </div>

                        <div class="form-group">
                            <label>Longitude <span style="color: red;">*</span></label>
                            <input type="text" name="longitude" id="longitude" class="form-control" value="<?php echo $edit_station['longitude'] ?? '100.5018'; ?>" required readonly>
                        </div>

                        <button type="submit" class="btn-submit">
                            <i class="fas fa-save"></i> <?php echo $edit_station ? 'อัปเดต' : 'เพิ่มสถานี'; ?>
                        </button>

                        <?php if ($edit_station): ?>
                        <button type="button" class="btn-cancel" onclick="location.href='poi-stations.php'">
                            <i class="fas fa-times"></i> ยกเลิก
                        </button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&language=th"></script>
    <script>
        let map;
        let marker;

        function initMap() {
            const lat = parseFloat(document.getElementById('latitude').value) || 13.7563;
            const lng = parseFloat(document.getElementById('longitude').value) || 100.5018;
            const position = { lat: lat, lng: lng };

            map = new google.maps.Map(document.getElementById('map'), {
                center: position,
                zoom: 13
            });

            marker = new google.maps.Marker({
                position: position,
                map: map,
                draggable: true
            });

            map.addListener('click', function(event) {
                placeMarker(event.latLng);
            });

            marker.addListener('dragend', function(event) {
                updateLatLng(event.latLng.lat(), event.latLng.lng());
            });
        }

        function placeMarker(location) {
            marker.setPosition(location);
            updateLatLng(location.lat(), location.lng());
        }

        function updateLatLng(lat, lng) {
            document.getElementById('latitude').value = lat.toFixed(8);
            document.getElementById('longitude').value = lng.toFixed(8);
        }

        function deleteStation(id) {
            Swal.fire({
                title: 'ยืนยันการลบ?',
                text: "คุณต้องการลบสถานีนี้หรือไม่?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f56565',
                cancelButtonColor: '#718096',
                confirmButtonText: 'ใช่, ลบเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    location.href = '?action=delete&id=' + id;
                }
            });
        }

        window.onload = initMap;
    </script>
</body>
</html>
