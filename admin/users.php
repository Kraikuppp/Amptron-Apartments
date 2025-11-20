<?php
session_start();
require_once '../config/database.php';

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Handle Actions
if (isset($_GET['action'])) {
    try {
        switch ($_GET['action']) {
            case 'delete':
                if (isset($_GET['id']) && $_GET['id'] != $_SESSION['user_id']) {
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$_GET['id']]);
                    $_SESSION['success'] = 'ลบผู้ใช้เรียบร้อยแล้ว!';
                }
                break;

            case 'toggle_status':
                if (isset($_GET['id'])) {
                    $stmt = $pdo->prepare("UPDATE users SET status = IF(status = 'active', 'inactive', 'active') WHERE id = ?");
                    $stmt->execute([$_GET['id']]);
                    $_SESSION['success'] = 'เปลี่ยนสถานะเรียบร้อยแล้ว!';
                }
                break;

            case 'ban':
                if (isset($_GET['id'])) {
                    $stmt = $pdo->prepare("UPDATE users SET status = 'banned' WHERE id = ?");
                    $stmt->execute([$_GET['id']]);
                    $_SESSION['success'] = 'แบนผู้ใช้เรียบร้อยแล้ว!';
                }
                break;
        }
        header('Location: users.php');
        exit();
    } catch (PDOException $e) {
        $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    }
}

// Handle Edit/Add
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = $_POST['id'] ?? null;
        $username = $_POST['username'];
        $email = $_POST['email'];
        $full_name = $_POST['full_name'];
        $phone = $_POST['phone'] ?? '';
        $role = $_POST['role'];
        $status = $_POST['status'];

        if ($id) {
            // Update
            $sql = "UPDATE users SET username = ?, email = ?, full_name = ?, phone = ?, role = ?, status = ?";
            $params = [$username, $email, $full_name, $phone, $role, $status];

            // Update password if provided
            if (!empty($_POST['password'])) {
                $sql .= ", password = ?";
                $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }

            $sql .= " WHERE id = ?";
            $params[] = $id;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $_SESSION['success'] = 'อัปเดตผู้ใช้เรียบร้อยแล้ว!';
        } else {
            // Insert
            if (empty($_POST['password'])) {
                throw new Exception('กรุณาระบุรหัสผ่าน');
            }

            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, email, password, full_name, phone, role, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $email, $password, $full_name, $phone, $role, $status]);
            $_SESSION['success'] = 'เพิ่มผู้ใช้เรียบร้อยแล้ว!';
        }

        header('Location: users.php');
        exit();
    } catch (Exception $e) {
        $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    }
}

// Get filters
$role_filter = $_GET['role'] ?? 'all';
$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build query
$where_conditions = [];
$params = [];

if ($role_filter !== 'all') {
    $where_conditions[] = "role = :role";
    $params[':role'] = $role_filter;
}

if ($status_filter !== 'all') {
    $where_conditions[] = "status = :status";
    $params[':status'] = $status_filter;
}

if (!empty($search)) {
    $where_conditions[] = "(username LIKE :search OR email LIKE :search OR full_name LIKE :search)";
    $params[':search'] = "%{$search}%";
}

$where_sql = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM users $where_sql";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_users = $count_stmt->fetch()['total'];
$total_pages = ceil($total_users / $per_page);

// Get users
$sql = "SELECT u.*,
        (SELECT COUNT(*) FROM rooms r LEFT JOIN business_profiles bp ON r.business_id = bp.id WHERE bp.user_id = u.id) as room_count,
        (SELECT COUNT(*) FROM business_profiles WHERE user_id = u.id) as has_business
        FROM users u
        $where_sql
        ORDER BY u.created_at DESC
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll();

// Get statistics
$stats_stmt = $pdo->query("SELECT
    COUNT(*) as total,
    SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) as users,
    SUM(CASE WHEN role = 'business' THEN 1 ELSE 0 END) as business,
    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN status = 'banned' THEN 1 ELSE 0 END) as banned
    FROM users");
$stats = $stats_stmt->fetch();

// Edit mode
$edit_user = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_user = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการผู้ใช้งาน - Admin Panel</title>
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
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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

        .stat-icon.total { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .stat-icon.users { background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%); color: white; }
        .stat-icon.business { background: linear-gradient(135deg, #f2994a 0%, #f2c94c 100%); color: white; }
        .stat-icon.admin { background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%); color: white; }

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
            grid-template-columns: 2fr 1fr 1fr auto auto;
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
        }

        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-add {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
            color: white;
            border: none;
            padding: 11px 25px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(86, 171, 47, 0.3);
            color: white;
        }

        .users-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .table {
            margin: 0;
            width: 100%;
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

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
        }

        .user-details h4 {
            font-size: 14px;
            font-weight: 600;
            color: #2d3748;
            margin: 0 0 3px 0;
        }

        .user-details p {
            font-size: 12px;
            color: #718096;
            margin: 0;
        }

        .role-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .role-badge.admin { background: #fed7d7; color: #c53030; }
        .role-badge.business { background: #feebc8; color: #c05621; }
        .role-badge.user { background: #bee3f8; color: #2c5282; }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .status-badge.active { background: #d4edda; color: #155724; }
        .status-badge.inactive { background: #f8d7da; color: #721c24; }
        .status-badge.banned { background: #343a40; color: white; }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .btn-action {
            padding: 6px 10px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
        }

        .btn-view { background: #667eea; color: white; }
        .btn-view:hover { background: #5568d3; }

        .btn-edit { background: #48bb78; color: white; }
        .btn-edit:hover { background: #38a169; }

        .btn-ban { background: #f56565; color: white; }
        .btn-ban:hover { background: #e53e3e; }

        .btn-delete { background: #718096; color: white; }
        .btn-delete:hover { background: #4a5568; }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h3 {
            font-size: 20px;
            font-weight: 600;
            color: #2d3748;
            margin: 0;
        }

        .btn-close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #718096;
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
                grid-template-columns: repeat(2, 1fr);
            }

            .table {
                font-size: 12px;
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
                <h1><i class="fas fa-users"></i> จัดการผู้ใช้งาน</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">ผู้ใช้งาน</li>
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
                    <div class="stat-icon total">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total']); ?></h3>
                        <p>ผู้ใช้ทั้งหมด</p>
                    </div>
                </div>

                <div class="stat-box">
                    <div class="stat-icon users">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['users']); ?></h3>
                        <p>ผู้ใช้ทั่วไป</p>
                    </div>
                </div>

                <div class="stat-box">
                    <div class="stat-icon business">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['business']); ?></h3>
                        <p>ผู้ประกอบการ</p>
                    </div>
                </div>

                <div class="stat-box">
                    <div class="stat-icon admin">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['admin']); ?></h3>
                        <p>ผู้ดูแลระบบ</p>
                    </div>
                </div>
            </div>

            <!-- Filter -->
            <div class="filter-card">
                <form method="GET" action="">
                    <div class="filter-grid">
                        <div class="form-group">
                            <label>ค้นหา</label>
                            <input type="text" name="search" class="form-control" placeholder="ชื่อผู้ใช้, อีเมล, ชื่อ-นามสกุล..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>

                        <div class="form-group">
                            <label>บทบาท</label>
                            <select name="role" class="form-select">
                                <option value="all">ทั้งหมด</option>
                                <option value="user" <?php echo $role_filter === 'user' ? 'selected' : ''; ?>>ผู้ใช้ทั่วไป</option>
                                <option value="business" <?php echo $role_filter === 'business' ? 'selected' : ''; ?>>ผู้ประกอบการ</option>
                                <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>ผู้ดูแลระบบ</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>สถานะ</label>
                            <select name="status" class="form-select">
                                <option value="all">ทั้งหมด</option>
                                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>ใช้งาน</option>
                                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>ไม่ใช้งาน</option>
                                <option value="banned" <?php echo $status_filter === 'banned' ? 'selected' : ''; ?>>ถูกแบน</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn-filter">
                                <i class="fas fa-search"></i> ค้นหา
                            </button>
                        </div>

                        <div class="form-group">
                            <button type="button" class="btn-add" onclick="openModal()">
                                <i class="fas fa-plus"></i> เพิ่มผู้ใช้
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Users Table -->
            <div class="users-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ผู้ใช้งาน</th>
                            <th>บทบาท</th>
                            <th>สถานะ</th>
                            <th>เบอร์โทร</th>
                            <th>ห้องเช่า</th>
                            <th>วันที่สมัคร</th>
                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: #718096;">
                                ไม่พบข้อมูลผู้ใช้งาน
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td>#<?php echo $user['id']; ?></td>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                        </div>
                                        <div class="user-details">
                                            <h4><?php echo htmlspecialchars($user['full_name']); ?></h4>
                                            <p>@<?php echo htmlspecialchars($user['username']); ?> • <?php echo htmlspecialchars($user['email']); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="role-badge <?php echo $user['role']; ?>">
                                        <?php
                                        $role_text = [
                                            'admin' => '👑 Admin',
                                            'business' => '💼 Business',
                                            'user' => '👤 User'
                                        ];
                                        echo $role_text[$user['role']] ?? $user['role'];
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $user['status']; ?>">
                                        <?php
                                        $status_text = [
                                            'active' => 'ใช้งาน',
                                            'inactive' => 'ไม่ใช้งาน',
                                            'banned' => 'ถูกแบน'
                                        ];
                                        echo $status_text[$user['status']] ?? $user['status'];
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($user['phone'] ?: '-'); ?></td>
                                <td>
                                    <?php if ($user['role'] === 'business'): ?>
                                        <span style="color: #667eea; font-weight: 600;">
                                            <?php echo number_format($user['room_count']); ?> ห้อง
                                        </span>
                                    <?php else: ?>
                                        <span style="color: #cbd5e0;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action btn-edit" onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <button class="btn-action btn-ban" onclick="banUser(<?php echo $user['id']; ?>)">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                        <button class="btn-action btn-delete" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination-wrapper">
                <div class="pagination">
                    <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&role=<?php echo $role_filter; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>" class="page-link">
                        <i class="fas fa-chevron-left"></i>
