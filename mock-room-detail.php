<?php
require_once "config/config.php";
require_once "includes/auth.php";
require_once "includes/mock_apartments.php";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$all = mock_get_all_apartments();
$apartment = null;

foreach ($all as $apt) {
    if ((int) $apt['id'] === $id) {
        $apartment = $apt;
        break;
    }
}

if (!$apartment) {
    redirect('room.php');
}

// Approximate coordinates (same logic as room.php)
$districtLocations = [
    'ห้วยขวาง'   => ['lat' => 13.7690, 'lng' => 100.5730],
    'คลองสาน'   => ['lat' => 13.7290, 'lng' => 100.5100],
    'ลาดพร้าว'  => ['lat' => 13.8060, 'lng' => 100.6090],
    'วัฒนา'     => ['lat' => 13.7330, 'lng' => 100.5770],
    'พระนคร'    => ['lat' => 13.7560, 'lng' => 100.4910],
    'ดินแดง'    => ['lat' => 13.7700, 'lng' => 100.5590],
    'บางนา'     => ['lat' => 13.6680, 'lng' => 100.6040],
    'พญาไท'     => ['lat' => 13.7720, 'lng' => 100.5420],
    'บางรัก'    => ['lat' => 13.7290, 'lng' => 100.5260],
    'สวนหลวง'   => ['lat' => 13.7270, 'lng' => 100.6460],
    'จตุจักร'   => ['lat' => 13.8170, 'lng' => 100.5600],
    'ธนบุรี'    => ['lat' => 13.7200, 'lng' => 100.4760],
    'ปทุมวัน'   => ['lat' => 13.7460, 'lng' => 100.5320],
    'ลาดกระบัง' => ['lat' => 13.7270, 'lng' => 100.7780],
    'สาทร'      => ['lat' => 13.7200, 'lng' => 100.5290],
    'บางกะปิ'   => ['lat' => 13.7660, 'lng' => 100.6420],
];

$district = $apartment['district'] ?? '';
if (isset($districtLocations[$district])) {
    $base = $districtLocations[$district];
    $idVal = (int) $apartment['id'];
    $latJitter = (($idVal % 7) - 3) * 0.001;
    $lngJitter = ((($idVal * 3) % 7) - 3) * 0.001;
    $lat = $base['lat'] + $latJitter;
    $lng = $base['lng'] + $lngJitter;
} else {
    $lat = 13.7563;
    $lng = 100.5018;
}

$typeLabel = $apartment['type'] === 'condo' ? 'คอนโด' : 'อพาร์ตเม้นท์';
$rentalTypes = $apartment['rental_type'] ?? [];
$hasMonthly = in_array('monthly', $rentalTypes, true) || !empty($apartment['price_monthly']);
$hasDaily   = in_array('daily', $rentalTypes, true) || !empty($apartment['price_daily']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/img/amptron-apartments.png?v=2">
    <link rel="apple-touch-icon" href="<?php echo SITE_URL; ?>/img/amptron-apartments.png?v=2">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@100;200;300;400;500;600;700&family=League+Spartan:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        :root {
            --primary-color: #3b82f6;
            --secondary-color: #8b5cf6;
            --dark-gray: #1e293b;
            --medium-gray: #64748b;
            --light-gray: #f1f5f9;
            --sky-blue: #0ea5e9;
            --font-english: 'League Spartan', sans-serif;
            --font-thai: 'IBM Plex Sans Thai', sans-serif;
        }

        * { font-family: var(--font-english); }
        body, html, p, span, div, label, input, textarea, select, button,
        .form-control, .form-label, .btn {
            font-family: var(--font-thai), var(--font-english);
        }
        h1, h2, h3, h4, h5, h6, .card-title, .card-header h5, .card-body h5, .card-body h6 {
            font-family: var(--font-thai), var(--font-english);
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .section-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-gray);
            margin-bottom: 1rem;
            font-family: var(--font-thai), var(--font-english);
        }
        #detailMap {
            height: 380px;
            width: 100%;
            border-radius: 16px;
        }
        .badge-type {
            background: rgba(15, 23, 42, 0.85);
        }
        .badge-price {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
        }
    </style>
</head>
<body>
<?php include "includes/header.php"; ?>

<div class="container" style="margin-top: 100px; margin-bottom: 2rem;">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4 border-0 shadow-sm">
                <div class="position-relative">
                    <img src="<?php echo htmlspecialchars($apartment['thumbnail']); ?>" class="img-fluid w-100" style="max-height: 460px; object-fit: cover;" alt="<?php echo htmlspecialchars($apartment['name']); ?>" onerror="this.src='assets/images/room-placeholder.jpg'">
                    <span class="badge badge-type position-absolute top-0 start-0 m-3">
                        <?php echo htmlspecialchars($typeLabel); ?>
                    </span>
                    <?php if ($hasMonthly || $hasDaily): ?>
                        <span class="badge badge-price position-absolute top-0 end-0 m-3">
                            <?php if ($hasMonthly && $apartment['price_monthly']): ?>
                                ฿<?php echo number_format($apartment['price_monthly']); ?>/เดือน
                            <?php elseif ($hasDaily && $apartment['price_daily']): ?>
                                ฿<?php echo number_format($apartment['price_daily']); ?>/คืน
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-4">
                    <h1 class="section-title mb-2"><?php echo htmlspecialchars($apartment['name']); ?></h1>
                    <p class="text-muted mb-2">
                        <i class="bi bi-geo-alt-fill text-primary"></i>
                        <?php echo htmlspecialchars(($apartment['district'] ?? '') . ', ' . ($apartment['province'] ?? '')); ?>
                    </p>
                    <p class="mb-3">
                        <span class="me-3">
                            <i class="bi bi-star-fill text-warning"></i>
                            <?php echo number_format($apartment['rating'], 1); ?> คะแนน
                        </span>
                        <span class="text-muted">
                            <i class="bi bi-eye"></i>
                            <?php echo (int) ($apartment['views'] ?? 0); ?> ครั้งที่ดู
                        </span>
                    </p>

                    <hr>

                    <h5 class="mb-3">รายละเอียดห้องพัก</h5>
                    <p class="mb-3" style="color: var(--medium-gray);">
                        <?php echo nl2br(htmlspecialchars($apartment['description'])); ?>
                    </p>

                    <?php if (!empty($apartment['amenities'])): ?>
                        <h6 class="mb-2">สิ่งอำนวยความสะดวก</h6>
                        <p>
                            <?php foreach ($apartment['amenities'] as $am): ?>
                                <span class="badge rounded-pill bg-light text-dark border me-1 mb-1">
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                    <?php echo htmlspecialchars($am); ?>
                                </span>
                            <?php endforeach; ?>
                        </p>
                    <?php endif; ?>

                    <div class="mt-3">
                        <?php if ($hasMonthly && $apartment['price_monthly']): ?>
                            <p class="mb-1">
                                <strong>ราคาเช่ารายเดือน:</strong>
                                <span class="text-primary fw-bold">฿<?php echo number_format($apartment['price_monthly']); ?></span> / เดือน
                            </p>
                        <?php endif; ?>
                        <?php if ($hasDaily && $apartment['price_daily']): ?>
                            <p class="mb-1">
                                <strong>ราคาเช่ารายวัน:</strong>
                                <span class="text-primary fw-bold">฿<?php echo number_format($apartment['price_daily']); ?></span> / คืน
                            </p>
                        <?php endif; ?>
                        <p class="mb-0 text-muted mt-2">
                            ประเภทการเช่า:
                            <?php if ($hasMonthly): ?>
                                <span class="badge bg-primary-subtle text-primary border">รายเดือน</span>
                            <?php endif; ?>
                            <?php if ($hasDaily): ?>
                                <span class="badge bg-success-subtle text-success border">รายวัน</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-map-fill text-primary"></i> แผนที่ตำแหน่งโดยประมาณ</h5>
                </div>
                <div class="card-body p-0">
                    <div id="detailMap"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="mb-3">สรุปรายละเอียดราคา</h5>
                    <?php if ($hasMonthly && $apartment['price_monthly']): ?>
                        <p class="mb-2">
                            <strong>รายเดือน:</strong>
                            <span class="text-primary fw-bold">฿<?php echo number_format($apartment['price_monthly']); ?></span>
                        </p>
                    <?php endif; ?>
                    <?php if ($hasDaily && $apartment['price_daily']): ?>
                        <p class="mb-2">
                            <strong>รายวัน:</strong>
                            <span class="text-primary fw-bold">฿<?php echo number_format($apartment['price_daily']); ?></span>
                        </p>
                    <?php endif; ?>
                    <p class="mb-0 text-muted mt-2">
                        * ข้อมูลนี้เป็นข้อมูลตัวอย่างจากระบบ mock ไม่มีการเชื่อมต่อเจ้าของห้องจริง
                    </p>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3">ตัวเลือกอื่นที่ใกล้เคียง</h6>
                    <?php $count = 0; foreach ($all as $other): if ($other['id'] === $apartment['id']) continue; if (++$count > 4) break; ?>
                        <div class="d-flex mb-3">
                            <img src="<?php echo htmlspecialchars($other['thumbnail']); ?>" alt="<?php echo htmlspecialchars($other['name']); ?>" style="width: 70px; height: 70px; object-fit: cover;" class="rounded me-2" onerror="this.src='assets/images/room-placeholder.jpg'">
                            <div>
                                <a href="mock-room-detail.php?id=<?php echo (int) $other['id']; ?>" class="fw-semibold d-block text-decoration-none" style="color: var(--dark-gray);">
                                    <?php echo htmlspecialchars($other['name']); ?>
                                </a>
                                <small class="text-muted d-block">
                                    <?php echo htmlspecialchars(($other['district'] ?? '') . ', ' . ($other['province'] ?? '')); ?>
                                </small>
                                <small class="text-primary">
                                    <?php if (!empty($other['price_monthly'])): ?>
                                        ฿<?php echo number_format($other['price_monthly']); ?>/เดือน
                                    <?php elseif (!empty($other['price_daily'])): ?>
                                        ฿<?php echo number_format($other['price_daily']); ?>/คืน
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function initDetailMap() {
        const center = { lat: <?php echo $lat; ?>, lng: <?php echo $lng; ?> };
        const map = new google.maps.Map(document.getElementById('detailMap'), {
            center: center,
            zoom: 15
        });
        new google.maps.Marker({
            position: center,
            map: map,
            title: '<?php echo addslashes($apartment['name']); ?>'
        });
    }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>&callback=initDetailMap" async defer></script>
</body>
</html>
