<?php
require_once '../config/config.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || !isBusiness()) {
    redirect('../login.php');
}

$db = getDB();
$businessProfile = getBusinessProfile($_SESSION['user_id']);

// ดึงโฆษณาของผู้ประกอบการ
$stmt = $db->prepare("SELECT * FROM advertisements WHERE business_id = ? ORDER BY created_at DESC");
$stmt->execute([$businessProfile['id']]);
$ads = $stmt->fetchAll();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $link_url = $_POST['link_url'] ?? '';
    $ad_type = $_POST['ad_type'] ?? 'banner';
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;
    
    if (empty($title) || !isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $error = 'กรุณากรอกข้อมูลและอัปโหลดรูปภาพ';
    } else {
        $imagePath = uploadFile($_FILES['image'], 'ads');
        if ($imagePath) {
            try {
                $stmt = $db->prepare("INSERT INTO advertisements (business_id, title, image, link_url, ad_type, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
                $stmt->execute([$businessProfile['id'], $title, $imagePath, $link_url, $ad_type, $start_date ?: null, $end_date ?: null]);
                logActivity($_SESSION['user_id'], 'create', 'advertisements', $db->lastInsertId(), 'เพิ่มโฆษณา: ' . $title);
                $success = 'เพิ่มโฆษณาสำเร็จ! รอการอนุมัติจากผู้ดูแลระบบ';
            } catch (PDOException $e) {
                $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
            }
        } else {
            $error = 'ไม่สามารถอัปโหลดรูปภาพได้';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการโฆษณา - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container my-4">
        <div class="row">
            <div class="col-md-3">
                <?php include 'sidebar.php'; ?>
            </div>
            
            <div class="col-md-9">
                <h2 class="mb-4">จัดการโฆษณา</h2>
                
                <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <!-- Add Ad Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>เพิ่มโฆษณาใหม่</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">ชื่อโฆษณา <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">ประเภทโฆษณา</label>
                                <select name="ad_type" class="form-select">
                                    <option value="banner">Banner</option>
                                    <option value="sidebar">Sidebar</option>
                                    <option value="popup">Popup</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">URL ลิงก์</label>
                                <input type="url" name="link_url" class="form-control" placeholder="https://...">
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">วันเริ่มต้น</label>
                                    <input type="date" name="start_date" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">วันสิ้นสุด</label>
                                    <input type="date" name="end_date" class="form-control">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">รูปภาพ <span class="text-danger">*</span></label>
                                <input type="file" name="image" class="form-control" accept="image/*" required>
                                <small class="text-muted">แนะนำขนาด: Banner (1200x300px), Sidebar (300x600px)</small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> เพิ่มโฆษณา
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Ads List -->
                <div class="card">
                    <div class="card-header">
                        <h5>โฆษณาของฉัน (<?php echo count($ads); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>รูปภาพ</th>
                                        <th>ชื่อ</th>
                                        <th>ประเภท</th>
                                        <th>สถานะ</th>
                                        <th>วันที่เริ่ม</th>
                                        <th>วันที่สิ้นสุด</th>
                                        <th>คลิก</th>
                                        <th>จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ads as $ad): ?>
                                    <tr>
                                        <td>
                                            <img src="../<?php echo htmlspecialchars($ad['image']); ?>" alt="<?php echo htmlspecialchars($ad['title']); ?>" style="width: 100px; height: auto;">
                                        </td>
                                        <td><?php echo htmlspecialchars($ad['title']); ?></td>
                                        <td><?php echo htmlspecialchars($ad['ad_type']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $ad['status'] === 'active' ? 'success' : 
                                                    ($ad['status'] === 'pending' ? 'warning' : 'secondary'); 
                                            ?>">
                                                <?php 
                                                echo $ad['status'] === 'active' ? 'ใช้งาน' : 
                                                    ($ad['status'] === 'pending' ? 'รออนุมัติ' : $ad['status']); 
                                                ?>
                                            </span>
                                        </td>
                                        <td><?php echo $ad['start_date'] ? date('d/m/Y', strtotime($ad['start_date'])) : '-'; ?></td>
                                        <td><?php echo $ad['end_date'] ? date('d/m/Y', strtotime($ad['end_date'])) : '-'; ?></td>
                                        <td><?php echo $ad['clicks']; ?></td>
                                        <td>
                                            <a href="delete-ad.php?id=<?php echo $ad['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('คุณแน่ใจหรือไม่?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
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
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

