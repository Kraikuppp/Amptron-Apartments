<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

$categoryId = $_GET['category'] ?? null;
$keyword = $_GET['keyword'] ?? '';

$db = getDB();

// ดึงหมวดหมู่
$categories = $db->query("SELECT * FROM product_categories ORDER BY name")->fetchAll();

// ค้นหาสินค้า
$conditions = ["p.status = 'approved'"];
$params = [];

if ($categoryId) {
    $conditions[] = "p.category_id = ?";
    $params[] = $categoryId;
}

if ($keyword) {
    $conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$keyword%";
    $params[] = "%$keyword%";
}

$whereClause = implode(' AND ', $conditions);
$query = "SELECT p.*, pc.name as category_name, bp.business_name 
          FROM products p 
          JOIN product_categories pc ON p.category_id = pc.id 
          JOIN business_profiles bp ON p.business_id = bp.id 
          WHERE $whereClause 
          ORDER BY p.created_at DESC";
          
$stmt = $db->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/img/amptron-apartments.png?v=2">
    <link rel="apple-touch-icon" href="<?php echo SITE_URL; ?>/img/amptron-apartments.png?v=2">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container my-4">
        <div class="row">
            <!-- Sidebar Categories -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-grid"></i> หมวดหมู่</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="products.php" class="list-group-item list-group-item-action <?php echo !$categoryId ? 'active' : ''; ?>">
                            ทั้งหมด
                        </a>
                        <?php foreach ($categories as $category): ?>
                        <a href="products.php?category=<?php echo $category['id']; ?>" 
                           class="list-group-item list-group-item-action <?php echo $categoryId == $category['id'] ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Products List -->
            <div class="col-md-9">
                <div class="card mb-3">
                    <div class="card-body">
                        <form method="GET" class="row g-2">
                            <div class="col-md-8">
                                <input type="text" name="keyword" class="form-control" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="ค้นหาสินค้า...">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i> ค้นหา
                                </button>
                            </div>
                            <?php if ($categoryId): ?>
                            <input type="hidden" name="category" value="<?php echo $categoryId; ?>">
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                
                <h4 class="mb-4">สินค้าทั้งหมด (<?php echo count($products); ?> รายการ)</h4>
                
                <div class="row g-4">
                    <?php foreach ($products as $product): ?>
                    <div class="col-md-4">
                        <div class="card product-card h-100">
                            <?php if ($product['featured']): ?>
                            <span class="badge bg-warning position-absolute top-0 start-0 m-2">แนะนำ</span>
                            <?php endif; ?>
                            <img src="<?php echo htmlspecialchars($product['image'] ?: 'assets/images/product-placeholder.jpg'); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <span class="badge bg-secondary mb-2"><?php echo htmlspecialchars($product['category_name']); ?></span>
                                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p class="text-primary fw-bold fs-5 mb-2">฿<?php echo number_format($product['price'], 2); ?></p>
                                <p class="card-text text-truncate"><?php echo htmlspecialchars($product['description']); ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="bi bi-eye"></i> <?php echo $product['views']; ?>
                                    </small>
                                    <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">
                                        ดูรายละเอียด
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (empty($products)): ?>
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle"></i> ไม่พบสินค้า
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

