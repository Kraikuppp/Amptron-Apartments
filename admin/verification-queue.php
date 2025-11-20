<?php
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
$hasDB = isDBConnected();
$pdo = $hasDB ? getDB() : null;

// Handle Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    try {
        if (!$pdo) {
            $_SESSION['error'] = 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้';
            header('Location: verification-queue.php');
            exit();
        }

        $room_id = (int)$_GET['id'];

        switch ($_GET['action']) {
            case 'approve':
                $stmt = $pdo->prepare("UPDATE rooms SET status = 'approved' WHERE id = ?");
                $stmt->execute([$room_id]);
                $_SESSION['success'] = 'อนุมัติห้องเช่าเรียบร้อยแล้ว!';
                break;

            case 'reject':
                $reason = $_GET['reason'] ?? 'ไม่ผ่านการตรวจสอบ';
                $stmt = $pdo->prepare("UPDATE rooms SET status = 'rejected' WHERE id = ?");
                $stmt->execute([$room_id]);
                // TODO: Send notification to business owner
                $_SESSION['success'] = 'ปฏิเสธห้องเช่าแล้ว!';
                break;

            case 'request_changes':
                $stmt = $pdo->prepare("UPDATE rooms SET status = 'pending' WHERE id = ?");
                $stmt->execute([$room_id]);
                $_SESSION['success'] = 'ส่งกลับให้แก้ไขแล้ว!';
                break;
        }

        header('Location: verification-queue.php');
        exit();
    } catch (PDOException $e) {
        $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    }
}

// Get pending rooms
try {
    if (!$pdo) {
        throw new Exception('ไม่สามารถเชื่อมต่อฐานข้อมูลได้');
    }

    $sql = "SELECT r.*,
            bp.business_name,
            u.username, u.email, u.phone,
            (SELECT COUNT(*) FROM room_images WHERE room_id = r.id) as image_count,
            (SELECT image_path FROM room_images WHERE room_id = r.id AND is_primary = 1 LIMIT 1) as primary_image
            FROM rooms r
            LEFT JOIN business_profiles bp ON r.business_id = bp.id
            LEFT JOIN users u ON bp.user_id = u.id
            WHERE r.status = 'pending'
            ORDER BY r.created_at ASC";

    $stmt = $pdo->query($sql);
    $pending_rooms = $stmt->fetchAll();

    // Get statistics
    $stats = [
        'pending' => count($pending_rooms),
        'today' => 0,
        'this_week' => 0
    ];

    foreach ($pending_rooms as $room) {
        $created = strtotime($room['created_at']);
        $today = strtotime('today');
        $week_ago = strtotime('-7 days');

        if ($created >= $today) {
            $stats['today']++;
        }
        if ($created >= $week_ago) {
            $stats['this_week']++;
        }
    }

} catch (PDOException $e) {
    $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    $pending_rooms = [];
    $stats = ['pending' => 0, 'today' => 0, 'this_week' => 0];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คิวรออนุมัติ - Admin Panel</title>
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
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            text-align: center;
        }

        .stat-box .icon {
            font-size: 40px;
            margin-bottom: 15px;
        }

        .stat-box h3 {
            font-size: 32px;
            font-weight: 700;
            color: #2d3748;
            margin: 0 0 5px 0;
        }

        .stat-box p {
            font-size: 14px;
            color: #718096;
            margin: 0;
        }

        .queue-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }

        .queue-card:hover {
            box-shadow: 0 5px 20px rgba(0,0,0,0.12);
            transform: translateY(-2px);
        }

        .queue-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }

        .queue-title {
            flex: 1;
        }

        .queue-title h3 {
            font-size: 20px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
        }

        .queue-meta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            font-size: 14px;
            color: #718096;
        }

        .queue-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .queue-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .queue-badge.new {
            background: #fff3cd;
            color: #856404;
        }

        .queue-content {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 25px;
            margin-bottom: 20px;
        }

        .queue-image {
            width: 100%;
            height: 150px;
            border-radius: 10px;
            object-fit: cover;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
        }

        .queue-details {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .detail-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-item label {
            font-size: 12px;
            font-weight: 600;
            color: #718096;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .detail-item span {
            font-size: 14px;
            color: #2d3748;
            font-weight: 500;
        }

        .facilities-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .facility-badge {
            background: #e2e8f0;
            color: #4a5568;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .business-info {
            background: #f7fafc;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .business-info h4 {
            font-size: 14px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 10px;
        }

        .business-info p {
            font-size: 13px;
            color: #718096;
            margin: 3px 0;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 12px 25px;
            border-radius: 8px;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-approve {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
            color: white;
        }

        .btn-approve:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(86, 171, 47, 0.3);
        }

        .btn-reject {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
            color: white;
        }

        .btn-reject:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(235, 51, 73, 0.3);
        }

        .btn-changes {
            background: #e2e8f0;
            color: #4a5568;
        }

        .btn-changes:hover {
            background: #cbd5e0;
        }

        .btn-view {
            background: #667eea;
            color: white;
        }

        .btn-view:hover {
            background: #5568d3;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 14px;
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

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 12px;
        }

        .empty-state i {
            font-size: 80px;
            color: #cbd5e0;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 24px;
            color: #4a5568;
            margin-bottom: 10px;
        }

        .empty-state p {
            font-size: 16px;
            color: #718096;
        }

        .price-highlight {
            font-size: 24px;
            font-weight: 700;
            color: #667eea;
        }

        .time-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .time-badge.urgent {
            background: #fed7d7;
            color: #c53030;
        }

        .time-badge.normal {
            background: #bee3f8;
            color: #2c5282;
        }

        @media (max-width: 1024px) {
            .queue-content {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .stats-row {
                grid-template-columns: 1fr;
            }

            .detail-row {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-action {
                width: 100%;
                justify-content: center;
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
                <h1><i class="fas fa-clock"></i> คิวรออนุมัติ</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">คิวรออนุมัติ</li>
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
                    <div class="icon" style="color: #f2994a;">⏳</div>
                    <h3><?php echo number_format($stats['pending']); ?></h3>
                    <p>รอตรวจสอบทั้งหมด</p>
                </div>

                <div class="stat-box">
                    <div class="icon" style="color: #eb3349;">🔥</div>
                    <h3><?php echo number_format($stats['today']); ?></h3>
                    <p>เพิ่มวันนี้</p>
                </div>

                <div class="stat-box">
                    <div class="icon" style="color: #667eea;">📅</div>
                    <h3><?php echo number_format($stats['this_week']); ?></h3>
                    <p>สัปดาห์นี้</p>
                </div>
            </div>

            <!-- Queue List -->
            <?php if (empty($pending_rooms)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>ไม่มีห้องรออนุมัติ</h3>
                <p>ทุกห้องได้รับการอนุมัติหรือปฏิเสธแล้ว</p>
            </div>
            <?php else: ?>
                <?php foreach ($pending_rooms as $index => $room): ?>
                <?php
                $created_time = strtotime($room['created_at']);
                $hours_ago = floor((time() - $created_time) / 3600);
                $is_urgent = $hours_ago > 24;
                ?>
                <div class="queue-card">
                    <div class="queue-header">
                        <div class="queue-title">
                            <h3>
                                <?php echo htmlspecialchars($room['title']); ?>
                                <?php if ($hours_ago < 24): ?>
                                <span class="queue-badge new">🆕 ใหม่</span>
                                <?php endif; ?>
                            </h3>
                            <div class="queue-meta">
                                <span>
                                    <i class="fas fa-hashtag"></i>
                                    ID: <?php echo $room['id']; ?>
                                </span>
                                <span>
                                    <i class="fas fa-clock"></i>
                                    <?php
                                    if ($hours_ago < 1) {
                                        echo 'เพิ่งเพิ่ม';
                                    } elseif ($hours_ago < 24) {
                                        echo $hours_ago . ' ชั่วโมงที่แล้ว';
                                    } else {
                                        $days = floor($hours_ago / 24);
                                        echo $days . ' วันที่แล้ว';
                                    }
                                    ?>
                                </span>
                                <span>
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($room['created_at'])); ?>
                                </span>
                                <?php if ($is_urgent): ?>
                                <span class="time-badge urgent">
                                    <i class="fas fa-exclamation-triangle"></i> ด่วน
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Business Info -->
                    <div class="business-info">
                        <h4><i class="fas fa-briefcase"></i> ข้อมูลผู้ลงประกาศ</h4>
                        <p><strong>ธุรกิจ:</strong> <?php echo htmlspecialchars($room['business_name'] ?: '-'); ?></p>
                        <p><strong>ผู้ใช้:</strong> <?php echo htmlspecialchars($room['username']); ?> (<?php echo htmlspecialchars($room['email']); ?>)</p>
                        <p><strong>เบอร์โทร:</strong> <?php echo htmlspecialchars($room['phone'] ?: 'ไม่ระบุ'); ?></p>
                    </div>

                    <div class="queue-content">
                        <div>
                            <?php if ($room['primary_image']): ?>
                            <img src="../uploads/<?php echo htmlspecialchars($room['primary_image']); ?>" alt="Room" class="queue-image">
                            <?php else: ?>
                            <div class="queue-image">
                                <i class="fas fa-home"></i>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="queue-details">
                            <div class="detail-row">
                                <div class="detail-item">
                                    <label>ราคา/เดือน</label>
                                    <span class="price-highlight">฿<?php echo number_format($room['price']); ?></span>
                                </div>

                                <div class="detail-item">
                                    <label>เงินประกัน</label>
                                    <span>฿<?php echo number_format($room['deposit']); ?></span>
                                </div>

                                <div class="detail-item">
                                    <label>ประเภท</label>
                                    <span><?php echo htmlspecialchars($room['room_type'] ?: '-'); ?></span>
                                </div>

                                <div class="detail-item">
                                    <label>ขนาด</label>
                                    <span><?php echo $room['area'] ? number_format($room['area']) . ' ตร.ม.' : '-'; ?></span>
                                </div>
                            </div>

                            <div class="detail-row">
                                <div class="detail-item">
                                    <label>ห้องนอน/ห้องน้ำ</label>
                                    <span>
                                        <i class="fas fa-bed"></i> <?php echo $room['bedrooms']; ?>
                                        / <i class="fas fa-bath"></i> <?php echo $room['bathrooms']; ?>
                                    </span>
                                </div>

                                <div class="detail-item">
                                    <label>ชั้น</label>
                                    <span><?php echo $room['floor'] ? 'ชั้น ' . $room['floor'] : '-'; ?></span>
                                </div>

                                <div class="detail-item">
                                    <label>จำนวนรูปภาพ</label>
                                    <span><i class="fas fa-image"></i> <?php echo $room['image_count']; ?> รูป</span>
                                </div>
                            </div>

                            <div class="detail-item">
                                <label>ที่อยู่</label>
                                <span><?php echo htmlspecialchars($room['address']); ?></span>
                            </div>

                            <div class="detail-item">
                                <label>เขต/จังหวัด</label>
                                <span><?php echo htmlspecialchars($room['district'] . ', ' . $room['province']); ?></span>
                            </div>

                            <?php if ($room['facilities']): ?>
                            <div class="detail-item">
                                <label>สิ่งอำนวยความสะดวก</label>
                                <div class="facilities-list">
                                    <?php
                                    $facilities = json_decode($room['facilities'], true);
                                    if (is_array($facilities)) {
                                        $facility_icons = [
                                            'wifi' => '📶 WiFi',
                                            'air' => '❄️ แอร์',
                                            'furniture' => '🛋️ เฟอร์นิเจอร์',
                                            'parking' => '🚗 ที่จอดรถ',
                                            'elevator' => '🛗 ลิฟต์',
                                            'security' => '🔒 รปภ.',
                                            'washing_machine' => '🧺 เครื่องซักผ้า',
                                            'fridge' => '🧊 ตู้เย็น',
                                            'tv' => '📺 ทีวี',
                                            'water_heater' => '🚿 เครื่องทำน้ำอุ่น',
                                            'kitchen' => '🍳 ครัว',
                                            'balcony' => '🌆 ระเบียง'
                                        ];
                                        foreach ($facilities as $facility) {
                                            echo '<span class="facility-badge">' . ($facility_icons[$facility] ?? $facility) . '</span>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($room['description']): ?>
                            <div class="detail-item">
                                <label>คำอธิบาย</label>
                                <span style="line-height: 1.6;"><?php echo nl2br(htmlspecialchars($room['description'])); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button class="btn-action btn-approve" onclick="approveRoom(<?php echo $room['id']; ?>)">
                            <i class="fas fa-check-circle"></i> อนุมัติ
                        </button>
                        <button class="btn-action btn-reject" onclick="rejectRoom(<?php echo $room['id']; ?>)">
                            <i class="fas fa-times-circle"></i> ปฏิเสธ
                        </button>
                        <button class="btn-action btn-changes" onclick="requestChanges(<?php echo $room['id']; ?>)">
                            <i class="fas fa-edit"></i> ขอแก้ไข
                        </button>
                        <button class="btn-action btn-view" onclick="window.open('../room-detail.php?id=<?php echo $room['id']; ?>', '_blank')">
                            <i class="fas fa-eye"></i> ดูรายละเอียด
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function approveRoom(roomId) {
            Swal.fire({
                title: 'อนุมัติห้องเช่า?',
                text: "คุณต้องการอนุมัติห้องเช่านี้หรือไม่?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#56ab2f',
                cancelButtonColor: '#718096',
                confirmButtonText: 'ใช่, อนุมัติ!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    location.href = '?action=approve&id=' + roomId;
                }
            });
        }

        function rejectRoom(roomId) {
            Swal.fire({
                title: 'ปฏิเสธห้องเช่า?',
                text: "คุณต้องการปฏิเสธห้องเช่านี้หรือไม่?",
                icon: 'warning',
                input: 'textarea',
                inputLabel: 'เหตุผล (ถ้ามี)',
                inputPlaceholder: 'ระบุเหตุผลการปฏิเสธ...',
                showCancelButton: true,
                confirmButtonColor: '#eb3349',
                cancelButtonColor: '#718096',
                confirmButtonText: 'ใช่, ปฏิเสธ!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    const reason = result.value || 'ไม่ผ่านการตรวจสอบ';
                    location.href = '?action=reject&id=' + roomId + '&reason=' + encodeURIComponent(reason);
                }
            });
        }

        function requestChanges(roomId) {
            Swal.fire({
                title: 'ขอให้แก้ไข?',
                text: "ส่งกลับให้ผู้ลงประกาศแก้ไข",
                icon: 'info',
                input: 'textarea',
                inputLabel: 'รายละเอียดที่ต้องแก้ไข',
                inputPlaceholder: 'ระบุสิ่งที่ต้องการให้แก้ไข...',
                showCancelButton: true,
                confirmButtonColor: '#667eea',
                cancelButtonColor: '#718096',
                confirmButtonText: 'ส่งกลับ',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    location.href = '?action=request_changes&id=' + roomId;
                }
            });
        }
    </script>
</body>
</html>
