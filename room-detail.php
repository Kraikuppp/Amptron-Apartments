<?php
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/mock_apartments.php';

$roomId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch room from mock data
$allApartments = mock_get_all_apartments();
$room = null;

foreach ($allApartments as $apt) {
    if ($apt['id'] == $roomId) {
        $room = $apt;
        break;
    }
}

if (!$room) {
    header('Location: index.php');
    exit;
}

// Mock images - use thumbnail as main image
$roomImages = [
    ['image_path' => $room['thumbnail']],
];

// Parse amenities from mock data
$amenities = $room['amenities'] ?? [];

// Mock reviews
$reviews = [];
$rating = [
    'avg_rating' => $room['rating'] ?? 4.5,
    'total_reviews' => 0
];

// Check wishlist
$inWishlist = false;
if (isLoggedIn()) {
    // $inWishlist = isInWishlist($_SESSION['user_id'], $roomId);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($room['name']); ?> - <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/img/amptron-apartments.png?v=2">
    <link rel="apple-touch-icon" href="<?php echo SITE_URL; ?>/img/amptron-apartments.png?v=2">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .amenity-icon {
            color: var(--primary-blue);
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container my-4">
        <div class="row">
            <div class="col-md-8">
                <!-- Image Gallery -->
                <div class="card mb-4">
                    <div class="card-body p-0">
                        <?php if (!empty($roomImages)): ?>
                        <div id="imageCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                <?php foreach ($roomImages as $index => $image): ?>
                                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                    <?php 
                                    $imgSrc = $image['image_path'];
                                    if (!empty($imgSrc) && !str_starts_with($imgSrc, 'http') && !str_starts_with($imgSrc, 'uploads/')) {
                                        $imgSrc = 'uploads/' . $imgSrc;
                                    }
                                    ?>
                                    <img src="<?php echo htmlspecialchars($imgSrc); ?>" class="d-block w-100" alt="Room Image" style="height: 500px; object-fit: cover;">
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($roomImages) > 1): ?>
                            <button class="carousel-control-prev" type="button" data-bs-target="#imageCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#imageCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </button>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <img src="https://via.placeholder.com/800x500?text=No+Image" class="img-fluid" alt="Room Image" style="height: 500px; width: 100%; object-fit: cover;">
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Room Details -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h2><?php echo htmlspecialchars($room['name']); ?></h2>
                        <p class="text-muted mb-3">
                            <i class="bi bi-geo-alt-fill"></i> 
                            <?php echo htmlspecialchars($room['district']); ?>, 
                            <?php echo htmlspecialchars($room['province']); ?>
                        </p>
                        
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <strong>ราคาเช่า:</strong><br>
                                <?php if (!empty($room['price_monthly'])): ?>
                                    <span class="text-primary fs-4">฿<?php echo number_format($room['price_monthly']); ?></span>/เดือน
                                <?php elseif (!empty($room['price_daily'])): ?>
                                    <span class="text-primary fs-4">฿<?php echo number_format($room['price_daily']); ?></span>/คืน
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4">
                                <strong>ประเภท:</strong><br>
                                <span class="badge bg-primary"><?php echo htmlspecialchars($room['type']); ?></span>
                            </div>
                            <div class="col-md-4">
                                <strong>คะแนนรีวิว:</strong><br>
                                <i class="bi bi-star-fill text-warning"></i> <?php echo number_format($room['rating'], 1); ?>/5.0
                                <small class="text-muted">(<?php echo (int)$room['views']; ?> views)</small>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h5>รายละเอียด</h5>
                        <p><?php echo nl2br(htmlspecialchars($room['description'])); ?></p>
                        
                        <?php if (!empty($amenities)): ?>
                        <h5 class="mt-4">สิ่งอำนวยความสะดวก</h5>
                        <div class="row">
                            <?php 
                            $amenityIcons = [
                                'WiFi' => 'bi-wifi',
                                'ที่จอดรถ' => 'bi-car-front-fill',
                                'ฟิตเนส' => 'bi-heart-pulse-fill',
                                'สระว่ายน้ำ' => 'bi-water',
                                'วิวแม่น้ำ' => 'bi-water',
                                'ครัว' => 'bi-cup-hot-fill',
                                'กล้องวงจรปิด' => 'bi-camera-video-fill',
                                'รูมเซอร์วิส' => 'bi-bell-fill',
                                'ห้องประชุม' => 'bi-briefcase-fill',
                                'สวนส่วนกลาง' => 'bi-tree-fill',
                                'Co-working space' => 'bi-laptop',
                                'คาเฟ่' => 'bi-cup-straw',
                                'เลี้ยงสัตว์ได้' => 'bi-heart-fill',
                                'ใกล้สถานที่ท่องเที่ยว' => 'bi-geo-alt-fill',
                                'ใกล้ห้างสรรพสินค้า' => 'bi-shop',
                                'ใกล้สนามบิน' => 'bi-airplane-fill',
                                'ใกล้มหาวิทยาลัย' => 'bi-book-fill',
                                'เครื่องซักผ้าหยอดเหรียญ' => 'bi-droplet-fill',
                                'คาเฟ่ชั้นล่าง' => 'bi-cup-straw',
                            ];
                            foreach ($amenities as $amenity): 
                                $icon = $amenityIcons[$amenity] ?? 'bi-check-circle-fill';
                            ?>
                                <div class="col-md-6 mb-3">
                                    <i class="<?php echo $icon; ?> amenity-icon"></i> 
                                    <?php echo htmlspecialchars($amenity); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($room['pet_friendly']): ?>
                        <div class="alert alert-info mt-3">
                            <i class="bi bi-heart-fill"></i> <strong>เลี้ยงสัตว์ได้!</strong> ห้องนี้รองรับการเลี้ยงสัตว์เลี้ยง
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <!-- Quick Info Card -->
                <div class="card mb-4 sticky-top" style="top: 20px;">
                    <div class="card-body">
                        <h5 class="mb-3">ข้อมูลติดต่อ</h5>
                        
                        <div class="d-grid gap-2">
                            <?php if (isLoggedIn()): ?>
                                <a href="tel:02-xxx-xxxx" class="btn btn-primary">
                                    <i class="bi bi-telephone-fill"></i> โทรติดต่อ
                                </a>
                                <a href="mailto:contact@example.com" class="btn btn-outline-primary">
                                    <i class="bi bi-envelope-fill"></i> ส่งอีเมล
                                </a>
                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#contactModal">
                                    <i class="bi bi-chat-dots-fill"></i> ส่งข้อความ
                                </button>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-in-right"></i> เข้าสู่ระบบเพื่อติดต่อ
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <hr>
                        
                        <div class="text-center">
                            <small class="text-muted">
                                <i class="bi bi-eye"></i> ถูกดู <?php echo number_format($room['views']); ?> ครั้ง
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Back Button -->
                <div class="card">
                    <div class="card-body">
                        <a href="index.php" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-arrow-left"></i> กลับไปหน้าแรก
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
