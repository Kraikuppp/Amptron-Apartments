<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

$productId = $_GET['id'] ?? 0;
$product = getProductById($productId);

if (!$product) {
    redirect('products.php');
}

// เพิ่มจำนวนผู้ชม
incrementViews('products', $productId);

// ดึงรีวิว
$reviews = getReviews(null, $productId, 10);
$rating = getAverageRating(null, $productId);

// ตรวจสอบ wishlist
$inWishlist = false;
if (isLoggedIn()) {
    $inWishlist = isInWishlist($_SESSION['user_id'], null, $productId);
}
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
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <img src="<?php echo htmlspecialchars($product['image'] ?: 'assets/images/product-placeholder.jpg'); ?>" 
                             class="img-fluid mb-4" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                        
                        <span class="badge bg-secondary mb-2"><?php echo htmlspecialchars($product['category_name']); ?></span>
                        <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                        <p class="text-primary fw-bold fs-3 mb-3">฿<?php echo number_format($product['price'], 2); ?></p>
                        
                        <h5>รายละเอียดสินค้า</h5>
                        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                        
                        <?php if ($product['specifications']): ?>
                        <h5 class="mt-4">คุณสมบัติ</h5>
                        <div class="bg-light p-3 rounded">
                            <pre><?php echo htmlspecialchars($product['specifications']); ?></pre>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($product['stock'] > 0): ?>
                        <p class="text-success mt-3">
                            <i class="bi bi-check-circle"></i> มีสินค้าในสต็อก (<?php echo $product['stock']; ?> ชิ้น)
                        </p>
                        <?php else: ?>
                        <p class="text-danger mt-3">
                            <i class="bi bi-x-circle"></i> สินค้าหมด
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Reviews -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5>รีวิว</h5>
                        <?php if ($rating['total_reviews'] > 0): ?>
                        <div class="mb-3">
                            <span class="fs-3"><?php echo number_format($rating['avg_rating'], 1); ?></span>
                            <div class="d-inline-block">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi bi-star<?php echo $i <= round($rating['avg_rating']) ? '-fill' : ''; ?> text-warning"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="text-muted">(<?php echo $rating['total_reviews']; ?> รีวิว)</span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isLoggedIn()): ?>
                        <form method="POST" action="actions/add-review.php" class="mb-4">
                            <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                            <div class="mb-3">
                                <label class="form-label">ให้คะแนน</label>
                                <select name="rating" class="form-select" required>
                                    <option value="">เลือกคะแนน</option>
                                    <option value="5">5 ดาว</option>
                                    <option value="4">4 ดาว</option>
                                    <option value="3">3 ดาว</option>
                                    <option value="2">2 ดาว</option>
                                    <option value="1">1 ดาว</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">ความคิดเห็น</label>
                                <textarea name="comment" class="form-control" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">ส่งรีวิว</button>
                        </form>
                        <?php endif; ?>
                        
                        <div class="reviews-list">
                            <?php foreach ($reviews as $review): ?>
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between">
                                    <strong><?php echo htmlspecialchars($review['full_name']); ?></strong>
                                    <div>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star<?php echo $i <= $review['rating'] ? '-fill' : ''; ?> text-warning"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="text-muted small"><?php echo date('d/m/Y H:i', strtotime($review['created_at'])); ?></p>
                                <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <!-- Contact Card -->
                <div class="card mb-4 sticky-top" style="top: 20px;">
                    <div class="card-body">
                        <h5>ติดต่อผู้ขาย</h5>
                        <p class="text-muted"><?php echo htmlspecialchars($product['business_name']); ?></p>
                        
                        <?php if (isLoggedIn()): ?>
                        <form method="POST" action="actions/contact.php">
                            <input type="hidden" name="business_id" value="<?php echo $product['business_id']; ?>">
                            <input type="hidden" name="type" value="product">
                            <input type="hidden" name="item_id" value="<?php echo $productId; ?>">
                            
                            <div class="mb-3">
                                <input type="text" name="name" class="form-control" placeholder="ชื่อของคุณ" required>
                            </div>
                            <div class="mb-3">
                                <input type="email" name="email" class="form-control" placeholder="Email" required>
                            </div>
                            <div class="mb-3">
                                <input type="tel" name="phone" class="form-control" placeholder="เบอร์โทรศัพท์">
                            </div>
                            <div class="mb-3">
                                <textarea name="message" class="form-control" rows="3" placeholder="ข้อความ" required>สนใจสินค้านี้ ต้องการข้อมูลเพิ่มเติม</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mb-2">
                                <i class="bi bi-envelope"></i> ส่งข้อความ
                            </button>
                        </form>
                        <?php else: ?>
                        <p class="text-muted">กรุณาเข้าสู่ระบบเพื่อติดต่อผู้ขาย</p>
                        <a href="login.php" class="btn btn-primary w-100">เข้าสู่ระบบ</a>
                        <?php endif; ?>
                        
                        <hr>
                        
                        <?php if (isLoggedIn()): ?>
                        <form method="POST" action="actions/wishlist.php" class="mb-2">
                            <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                            <input type="hidden" name="action" value="<?php echo $inWishlist ? 'remove' : 'add'; ?>">
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="bi bi-heart<?php echo $inWishlist ? '-fill' : ''; ?>"></i> 
                                <?php echo $inWishlist ? 'ลบจากรายการโปรด' : 'เพิ่มในรายการโปรด'; ?>
                            </button>
                        </form>
                        <?php endif; ?>
                        
                        <div class="text-center text-muted small">
                            <i class="bi bi-eye"></i> ถูกดู <?php echo $product['views']; ?> ครั้ง
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

