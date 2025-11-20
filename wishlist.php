<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$db = getDB();

// ดึงรายการโปรด
$wishlistRooms = $db->prepare("SELECT w.*, r.*, bp.business_name FROM wishlists w JOIN rooms r ON w.room_id = r.id JOIN business_profiles bp ON r.business_id = bp.id WHERE w.user_id = ? AND w.room_id IS NOT NULL");
$wishlistRooms->execute([$_SESSION['user_id']]);
$rooms = $wishlistRooms->fetchAll();

$wishlistProducts = $db->prepare("SELECT w.*, p.*, pc.name as category_name, bp.business_name FROM wishlists w JOIN products p ON w.product_id = p.id JOIN product_categories pc ON p.category_id = pc.id JOIN business_profiles bp ON p.business_id = bp.id WHERE w.user_id = ? AND w.product_id IS NOT NULL");
$wishlistProducts->execute([$_SESSION['user_id']]);
$products = $wishlistProducts->fetchAll();
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
        <h2 class="mb-4">รายการโปรด</h2>
        
        <!-- Rooms -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>ห้องเช่า (<?php echo count($rooms); ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($rooms)): ?>
                <div class="row g-4">
                    <?php foreach ($rooms as $room): ?>
                    <div class="col-md-4">
                        <div class="card room-card h-100">
                            <?php
                            $roomImages = getRoomImages($room['room_id']);
                            $primaryImage = !empty($roomImages) ? $roomImages[0]['image_path'] : 'assets/images/room-placeholder.jpg';
                            ?>
                            <img src="<?php echo htmlspecialchars($primaryImage); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($room['title']); ?>" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($room['title']); ?></h5>
                                <p class="text-primary fw-bold">฿<?php echo number_format($room['price'], 2); ?>/เดือน</p>
                                <div class="d-flex justify-content-between">
                                    <a href="room-detail.php?id=<?php echo $room['room_id']; ?>" class="btn btn-sm btn-primary">ดูรายละเอียด</a>
                                    <form method="POST" action="actions/wishlist.php" class="d-inline">
                                        <input type="hidden" name="room_id" value="<?php echo $room['room_id']; ?>">
                                        <input type="hidden" name="action" value="remove">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-heart-fill"></i> ลบ
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-muted text-center">ยังไม่มีห้องเช่าในรายการโปรด</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Products -->
        <div class="card">
            <div class="card-header">
                <h5>สินค้า (<?php echo count($products); ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($products)): ?>
                <div class="row g-4">
                    <?php foreach ($products as $product): ?>
                    <div class="col-md-4">
                        <div class="card product-card h-100">
                            <img src="<?php echo htmlspecialchars($product['image'] ?: 'assets/images/product-placeholder.jpg'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p class="text-primary fw-bold">฿<?php echo number_format($product['price'], 2); ?></p>
                                <div class="d-flex justify-content-between">
                                    <a href="product-detail.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-primary">ดูรายละเอียด</a>
                                    <form method="POST" action="actions/wishlist.php" class="d-inline">
                                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                        <input type="hidden" name="action" value="remove">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-heart-fill"></i> ลบ
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-muted text-center">ยังไม่มีสินค้าในรายการโปรด</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

