<?php
require_once '../config/config.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || !isBusiness()) {
    redirect('../login.php');
}

$db = getDB();
$businessProfile = getBusinessProfile($_SESSION['user_id']);
$error = '';
$success = '';

// ดึงหมวดหมู่
$categories = $db->query("SELECT * FROM product_categories ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $category_id = $_POST['category_id'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $sku = $_POST['sku'] ?? '';
    $specifications = $_POST['specifications'] ?? '';
    
    if (empty($name) || empty($description) || empty($price) || empty($category_id)) {
        $error = 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน';
    } else {
        try {
            // สร้าง slug
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
            
            // อัปโหลดรูปภาพ
            $imagePath = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imagePath = uploadFile($_FILES['image'], 'products');
            }
            
            $stmt = $db->prepare("INSERT INTO products (business_id, category_id, name, slug, description, price, stock, sku, image, specifications, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$businessProfile['id'], $category_id, $name, $slug, $description, $price, $stock, $sku, $imagePath, $specifications]);
            
            $productId = $db->lastInsertId();
            logActivity($_SESSION['user_id'], 'create', 'products', $productId, 'เพิ่มสินค้า: ' . $name);
            $success = 'เพิ่มสินค้าสำเร็จ! รอการอนุมัติจากผู้ดูแลระบบ';
            
        } catch (PDOException $e) {
            $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มสินค้า - <?php echo SITE_NAME; ?></title>
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
                <h2 class="mb-4">เพิ่มสินค้า</h2>
                
                <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="card mb-3">
                        <div class="card-header">ข้อมูลสินค้า</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">ชื่อสินค้า <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">หมวดหมู่ <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">เลือกหมวดหมู่</option>
                                    <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">รายละเอียด <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control" rows="5" required></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">ราคา (บาท) <span class="text-danger">*</span></label>
                                    <input type="number" name="price" class="form-control" step="0.01" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">จำนวนสต็อก</label>
                                    <input type="number" name="stock" class="form-control" value="0">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">SKU</label>
                                    <input type="text" name="sku" class="form-control">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">คุณสมบัติ/สเปก</label>
                                <textarea name="specifications" class="form-control" rows="5" placeholder="ระบุรายละเอียดคุณสมบัติของสินค้า"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">รูปภาพ</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> บันทึก
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary">ยกเลิก</a>
                </form>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

