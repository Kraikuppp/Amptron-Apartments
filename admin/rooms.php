<?php
session_start();
require_once "../config/database.php";

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

// รับค่า filter
$status_filter = $_GET["status"] ?? "all";
$district_filter = $_GET["district"] ?? "all";
$search = $_GET["search"] ?? "";
$sort = $_GET["sort"] ?? "latest";
$page = isset($_GET["page"]) ? (int) $_GET["page"] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// สร้าง query
$where_conditions = [];
$params = [];

if ($status_filter !== "all") {
    $where_conditions[] = "r.status = :status";
    $params[":status"] = $status_filter;
}

if ($district_filter !== "all") {
    $where_conditions[] = "r.district = :district";
    $params[":district"] = $district_filter;
}

if (!empty($search)) {
    $where_conditions[] =
        "(r.title LIKE :search OR r.address LIKE :search OR r.description LIKE :search)";
    $params[":search"] = "%{$search}%";
}

$where_sql = !empty($where_conditions)
    ? "WHERE " . implode(" AND ", $where_conditions)
    : "";

// การเรียงลำดับ
$order_by = match ($sort) {
    "oldest" => "r.created_at ASC",
    "price_low" => "r.price ASC",
    "price_high" => "r.price DESC",
    "popular" => "r.views DESC",
    default => "r.created_at DESC",
};

// ดึงข้อมูลห้อง
try {
    // นับจำนวนทั้งหมด
    $count_sql = "SELECT COUNT(*) as total FROM rooms r $where_sql";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_rooms = $count_stmt->fetch()["total"];
    $total_pages = ceil($total_rooms / $per_page);

    // ดึงข้อมูล
    $sql = "SELECT r.*,
            bp.business_name,
            u.username, u.email,
            (SELECT COUNT(*) FROM room_images WHERE room_id = r.id) as image_count
            FROM rooms r
            LEFT JOIN business_profiles bp ON r.business_id = bp.id
            LEFT JOIN users u ON bp.user_id = u.id
            $where_sql
            ORDER BY $order_by
            LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(":limit", $per_page, PDO::PARAM_INT);
    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
    $stmt->execute();
    $rooms = $stmt->fetchAll();

    // ดึงรายการเขตทั้งหมด
    $districts_stmt = $pdo->query(
        "SELECT DISTINCT district FROM rooms WHERE district IS NOT NULL ORDER BY district",
    );
    $districts = $districts_stmt->fetchAll();

    // สถิติ
    $stats_stmt = $pdo->query("SELECT
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
        SUM(CASE WHEN status = 'rented' THEN 1 ELSE 0 END) as rented,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved
        FROM rooms");
    $stats = $stats_stmt->fetch();
} catch (PDOException $e) {
    $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
    $rooms = [];
    $districts = [];
    $stats = [
        "total" => 0,
        "pending" => 0,
        "available" => 0,
        "rented" => 0,
        "approved" => 0,
    ];
    $total_pages = 1;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการห้องเช่า - Admin Panel</title>
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
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .stat-icon.all { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .stat-icon.pending { background: linear-gradient(135deg, #f2994a 0%, #f2c94c 100%); color: white; }
        .stat-icon.available { background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%); color: white; }
        .stat-icon.rented { background: linear-gradient(135deg, #00c6ff 0%, #0072ff 100%); color: white; }

        .stat-info h3 {
            font-size: 24px;
            font-weight: 700;
            color: #2d3748;
            margin: 0;
        }

        .stat-info p {
            font-size: 13px;
            color: #718096;
            margin: 0;
        }

        .filter-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .filter-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }

        .form-group label {
            font-size: 13px;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 8px;
            display: block;
        }

        .form-control, .form-select {
            border: 2px solid #e2e8f0;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .btn-filter {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 11px 25px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-reset {
            background: #e2e8f0;
            color: #4a5568;
            border: none;
            padding: 11px 25px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-reset:hover {
            background: #cbd5e0;
        }

        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .btn-add {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(86, 171, 47, 0.3);
            color: white;
        }

        .rooms-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .table {
            margin: 0;
        }

        .table thead th {
            background: #f7fafc;
            color: #4a5568;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            padding: 15px;
            border: none;
        }

        .table tbody td {
            padding: 15px;
            vertical-align: middle;
            border-bottom: 1px solid #e2e8f0;
            color: #2d3748;
        }

        .table tbody tr:hover {
            background: #f7fafc;
        }

        .room-info {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .room-thumbnail {
            width: 80px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            background: #e2e8f0;
        }

        .room-details h4 {
            font-size: 14px;
            font-weight: 600;
            color: #2d3748;
            margin: 0 0 5px 0;
        }

        .room-details p {
            font-size: 12px;
            color: #718096;
            margin: 0;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .status-badge.pending { background: #fff3cd; color: #856404; }
        .status-badge.available { background: #d4edda; color: #155724; }
        .status-badge.rented { background: #cce5ff; color: #004085; }
        .status-badge.approved { background: #d1ecf1; color: #0c5460; }
        .status-badge.rejected { background: #f8d7da; color: #721c24; }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-action {
            padding: 8px 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-view { background: #667eea; color: white; }
        .btn-view:hover { background: #5568d3; }

        .btn-edit { background: #48bb78; color: white; }
        .btn-edit:hover { background: #38a169; }

        .btn-delete { background: #f56565; color: white; }
        .btn-delete:hover { background: #e53e3e; }

        .pagination-wrapper {
            display: flex;
            justify-content: center;
            margin-top: 30px;
        }

        .pagination {
            display: flex;
            gap: 8px;
        }

        .page-link {
            padding: 10px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            color: #4a5568;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .page-link:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .page-link.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #718096;
        }

        .empty-state i {
            font-size: 64px;
            color: #cbd5e0;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 20px;
            color: #4a5568;
            margin-bottom: 10px;
        }

        .featured-badge {
            background: #ffd700;
            color: #856404;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 8px;
        }

        @media (max-width: 1024px) {
            .filter-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }

            .stats-row {
                grid-template-columns: 1fr;
            }

            .table {
                font-size: 12px;
            }

            .room-info {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include "sidebar.php"; ?>

        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1><i class="fas fa-door-open"></i> จัดการห้องเช่า</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">ห้องเช่า</li>
                    </ol>
                </nav>
            </div>

            <!-- Statistics -->
            <div class="stats-row">
                <div class="stat-box">
                    <div class="stat-icon all">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats["total"]); ?></h3>
                        <p>ห้องทั้งหมด</p>
                    </div>
                </div>

                <div class="stat-box">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats["pending"]); ?></h3>
                        <p>รออนุมัติ</p>
                    </div>
                </div>

                <div class="stat-box">
                    <div class="stat-icon available">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format(
                            $stats["available"],
                        ); ?></h3>
                        <p>พร้อมให้เช่า</p>
                    </div>
                </div>

                <div class="stat-box">
                    <div class="stat-icon rented">
                        <i class="fas fa-key"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats["rented"]); ?></h3>
                        <p>เช่าแล้ว</p>
                    </div>
                </div>
            </div>

            <!-- Filter -->
            <div class="filter-card">
                <form method="GET" action="">
                    <div class="filter-grid">
                        <div class="form-group">
                            <label>ค้นหา</label>
                            <input type="text" name="search" class="form-control" placeholder="ชื่อห้อง, ที่อยู่, คำอธิบาย..." value="<?php echo htmlspecialchars(
                                $search,
                            ); ?>">
                        </div>

                        <div class="form-group">
                            <label>สถานะ</label>
                            <select name="status" class="form-select">
                                <option value="all" <?php echo $status_filter ===
                                "all"
                                    ? "selected"
                                    : ""; ?>>ทั้งหมด</option>
                                <option value="pending" <?php echo $status_filter ===
                                "pending"
                                    ? "selected"
                                    : ""; ?>>รออนุมัติ</option>
                                <option value="available" <?php echo $status_filter ===
                                "available"
                                    ? "selected"
                                    : ""; ?>>พร้อมให้เช่า</option>
                                <option value="rented" <?php echo $status_filter ===
                                "rented"
                                    ? "selected"
                                    : ""; ?>>เช่าแล้ว</option>
                                <option value="approved" <?php echo $status_filter ===
                                "approved"
                                    ? "selected"
                                    : ""; ?>>อนุมัติแล้ว</option>
                                <option value="rejected" <?php echo $status_filter ===
                                "rejected"
                                    ? "selected"
                                    : ""; ?>>ไม่อนุมัติ</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>เขต</label>
                            <select name="district" class="form-select">
                                <option value="all">ทุกเขต</option>
                                <?php foreach ($districts as $d): ?>
                                <option value="<?php echo htmlspecialchars(
                                    $d["district"],
                                ); ?>" <?php echo $district_filter ===
$d["district"]
    ? "selected"
    : ""; ?>>
                                    <?php echo htmlspecialchars(
                                        $d["district"],
                                    ); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>เรียงตาม</label>
                            <select name="sort" class="form-select">
                                <option value="latest" <?php echo $sort ===
                                "latest"
                                    ? "selected"
                                    : ""; ?>>ล่าสุด</option>
                                <option value="oldest" <?php echo $sort ===
                                "oldest"
                                    ? "selected"
                                    : ""; ?>>เก่าสุด</option>
                                <option value="price_low" <?php echo $sort ===
                                "price_low"
                                    ? "selected"
                                    : ""; ?>>ราคาต่ำ-สูง</option>
                                <option value="price_high" <?php echo $sort ===
                                "price_high"
                                    ? "selected"
                                    : ""; ?>>ราคาสูง-ต่ำ</option>
                                <option value="popular" <?php echo $sort ===
                                "popular"
                                    ? "selected"
                                    : ""; ?>>ยอดนิยม</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn-filter">
                                <i class="fas fa-search"></i> ค้นหา
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Action Bar -->
            <div class="action-bar">
                <div>
                    แสดง <?php echo number_format(
                        count($rooms),
                    ); ?> จาก <?php echo number_format($total_rooms); ?> ห้อง
                </div>
                <a href="rooms-add.php" class="btn-add">
                    <i class="fas fa-plus"></i> เพิ่มห้องเช่าใหม่
                </a>
            </div>

            <!-- Rooms Table -->
            <div class="rooms-table">
                <?php if (empty($rooms)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>ไม่พบข้อมูลห้องเช่า</h3>
                    <p>ลองเปลี่ยนเงื่อนไขการค้นหาหรือเพิ่มห้องเช่าใหม่</p>
                </div>
                <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ข้อมูลห้อง</th>
                            <th>ราคา</th>
                            <th>เขต</th>
                            <th>ผู้ลงประกาศ</th>
                            <th>วิว</th>
                            <th>สถานะ</th>
                            <th>วันที่เพิ่ม</th>
                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rooms as $room): ?>
                        <tr>
                            <td>#<?php echo $room["id"]; ?></td>
                            <td>
                                <div class="room-info">
                                    <img src="https://via.placeholder.com/80x60" alt="Room" class="room-thumbnail">
                                    <div class="room-details">
                                        <h4>
                                            <?php echo htmlspecialchars(
                                                $room["title"],
                                            ); ?>
                                            <?php if ($room["featured"]): ?>
                                            <span class="featured-badge">⭐ Featured</span>
                                            <?php endif; ?>
                                        </h4>
                                        <p>
                                            <i class="fas fa-image"></i> <?php echo $room[
                                                "image_count"
                                            ]; ?> รูป
                                            | <i class="fas fa-home"></i> <?php echo htmlspecialchars(
                                                $room["room_type"] ?? "ไม่ระบุ",
                                            ); ?>
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong style="color: #667eea;">฿<?php echo number_format(
                                    $room["price"],
                                ); ?></strong>/เดือน
                            </td>
                            <td><?php echo htmlspecialchars(
                                $room["district"] ?? "-",
                            ); ?></td>
                            <td>
                                <?php echo htmlspecialchars(
                                    $room["business_name"] ??
                                        ($room["username"] ?? "ไม่ระบุ"),
                                ); ?>
                            </td>
                            <td><?php echo number_format(
                                $room["views"],
                            ); ?></td>
                            <td>
                                <span class="status-badge <?php echo $room[
                                    "status"
                                ]; ?>">
                                    <?php
                                    $status_text = [
                                        "pending" => "รออนุมัติ",
                                        "available" => "พร้อมให้เช่า",
                                        "rented" => "เช่าแล้ว",
                                        "approved" => "อนุมัติแล้ว",
                                        "rejected" => "ไม่อนุมัติ",
                                    ];
                                    echo $status_text[$room["status"]] ??
                                        $room["status"];
                                    ?>
                                </span>
                            </td>
                            <td><?php echo date(
                                "d/m/Y",
                                strtotime($room["created_at"]),
                            ); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-action btn-view" onclick="window.open('../room-detail.php?id=<?php echo $room[
                                        "id"
                                    ]; ?>', '_blank')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-action btn-edit" onclick="location.href='rooms-edit.php?id=<?php echo $room[
                                        "id"
                                    ]; ?>'">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-action btn-delete" onclick="deleteRoom(<?php echo $room[
                                        "id"
                                    ]; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination-wrapper">
                <div class="pagination">
                    <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page -
                        1; ?>&status=<?php echo $status_filter; ?>&district=<?php echo $district_filter; ?>&search=<?php echo urlencode(
    $search,
); ?>&sort=<?php echo $sort; ?>" class="page-link">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <?php endif; ?>

                    <?php for (
                        $i = max(1, $page - 2);
                        $i <= min($total_pages, $page + 2);
                        $i++
                    ): ?>
                    <a href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&district=<?php echo $district_filter; ?>&search=<?php echo urlencode(
    $search,
); ?>&sort=<?php echo $sort; ?>"
                       class="page-link <?php echo $i === $page
                           ? "active"
                           : ""; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page +
                        1; ?>&status=<?php echo $status_filter; ?>&district=<?php echo $district_filter; ?>&search=<?php echo urlencode(
    $search,
); ?>&sort=<?php echo $sort; ?>" class="page-link">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function deleteRoom(roomId) {
            Swal.fire({
                title: 'ยืนยันการลบ?',
                text: "คุณต้องการลบห้องเช่านี้หรือไม่?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f56565',
                cancelButtonColor: '#718096',
                confirmButtonText: 'ใช่, ลบเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send delete request
                    fetch('actions/delete-room.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ room_id: roomId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire(
                                'ลบแล้ว!',
                                'ลบห้องเช่าเรียบร้อยแล้ว',
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire(
                                'เกิดข้อผิดพลาด!',
                                data.message || 'ไม่สามารถลบห้องเช่าได้',
                                'error'
                            );
