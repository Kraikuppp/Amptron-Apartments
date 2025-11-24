<?php
require_once "config/config.php";
require_once "includes/auth.php";
require_once "includes/mock_apartments.php";

// Load all mock apartments / condos
$allApartments = mock_get_all_apartments();

// Prepare sections
$popularApts = mock_get_popular_apartments(20);
$monthlyApts = mock_get_monthly_apartments(20);
$dailyApts   = mock_get_daily_apartments(20);

// Approximate coordinates per district (Bangkok area)
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

// Attach approximate lat/lng to each apartment (for map demo)
foreach ($allApartments as &$apt) {
    $district = $apt['district'] ?? '';
    if (isset($districtLocations[$district])) {
        $base = $districtLocations[$district];
        // Small deterministic jitter based on ID so markers do not overlap exactly
        $id = (int) ($apt['id'] ?? 0);
        $latJitter = (($id % 7) - 3) * 0.001;  // ~ +/- 300m
        $lngJitter = ((($id * 3) % 7) - 3) * 0.001;
        $apt['lat'] = $base['lat'] + $latJitter;
        $apt['lng'] = $base['lng'] + $lngJitter;
    } else {
        // Fallback: central Bangkok
        $apt['lat'] = 13.7563;
        $apt['lng'] = 100.5018;
    }
}
unset($apt); // break reference

// Collect unique amenities for filter dropdown
$amenityLabels = [];
foreach ($allApartments as $aptItem) {
    if (!empty($aptItem['amenities']) && is_array($aptItem['amenities'])) {
        foreach ($aptItem['amenities'] as $amenity) {
            $amenityLabels[$amenity] = true;
        }
    }
}
$amenityLabels = array_keys($amenityLabels);
sort($amenityLabels, SORT_NATURAL | SORT_FLAG_CASE);

// Fetch active train stations (BTS/MRT/ARL/BRT) from poi_stations if DB is available
$stations = [];
if (function_exists('isDBConnected') && isDBConnected()) {
    $db = getDB();
    if ($db) {
        try {
            $stmt = $db->prepare("SELECT id, name, name_en, line_type, line_name, latitude, longitude
                                   FROM poi_stations
                                   WHERE status = 'active'");
            $stmt->execute();
            $stations = $stmt->fetchAll();
        } catch (PDOException $e) {
            $stations = [];
        }
    }
}

// Optional highlight from index/search (not required, but we keep for future use)
$highlightId = isset($_GET['highlight']) ? (int) $_GET['highlight'] : 0;
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/img/amptron-apartments.png?v=2">
    <link rel="apple-touch-icon" href="<?php echo SITE_URL; ?>/img/amptron-apartments.png?v=2">
    <!-- Google Fonts (same as index.php) -->
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

        * {
            font-family: var(--font-english);
        }

        body,
        html,
        p,
        span,
        div,
        label,
        input,
        textarea,
        select,
        button,
        .form-control,
        .form-label,
        .btn {
            font-family: var(--font-thai), var(--font-english);
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-english);
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

        .bg-section-light {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }

        /* Map + search layout */
        #map {
            height: 60vh;
            min-height: 360px;
            width: 100%;
            border-radius: 18px;
            overflow: hidden;
        }

        .map-card {
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.25);
            overflow: hidden;
            border: 1px solid rgba(148, 163, 184, 0.4);
            background: #0f172a;
        }

        .map-card.map-sticky {
            position: sticky;
            top: 90px; /* below fixed navbar */
        }

        .map-header {
            padding: 16px 24px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #e5e7eb;
        }

        .map-header-title {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .map-header-title i {
            font-size: 1.3rem;
            color: var(--sky-blue);
        }

        .map-header-actions .btn {
            border-radius: 999px;
            font-size: 0.85rem;
        }

        .map-header-actions .btn-outline-light {
            border-color: rgba(148, 163, 184, 0.6);
            color: #e5e7eb;
        }

        .map-header-actions .btn-outline-light:hover {
            background: rgba(148, 163, 184, 0.15);
        }

        .search-panel {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 12px 40px rgba(15, 23, 42, 0.15);
            border: 1px solid #e2e8f0;
        }

        .search-input-row {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) repeat(2, minmax(0, 0.8fr)) auto;
            gap: 12px;
            align-items: end;
        }

        @media (max-width: 992px) {
            .search-input-row {
                grid-template-columns: 1fr;
            }
        }

        .search-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--dark-gray);
            margin-bottom: 4px;
        }

        .input-with-icon {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            background: white;
            transition: all 0.25s ease;
        }

        .input-with-icon:focus-within {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .input-with-icon input,
        .input-with-icon select {
            border: none;
            outline: none;
            flex: 1;
            font-size: 0.95rem;
            background: transparent;
        }

        .btn-search-main {
            padding: 10px 20px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            white-space: nowrap;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .btn-search-main.btn-sm {
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 0.85rem;
        }

        .btn-search-main:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
        }

        .chip-radius {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.7);
            color: #e5e7eb;
            font-size: 0.8rem;
        }

        .chip-radius i {
            color: var(--sky-blue);
        }

        .legend-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
            margin-left: 8px;
        }

        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            display: inline-block;
        }

        /* Room cards (reuse style similar to index) */
        .room-card {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            background: white;
            height: 100%;
        }

        .room-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.18);
        }

        .room-card img {
            width: 100%;
            height: 210px;
            object-fit: cover;
        }

        .badge-price {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
        }

        .badge-type {
            background: rgba(15, 23, 42, 0.85);
        }

        .apt-list-card-title {
            color: var(--dark-gray);
            font-weight: 600;
        }

        .apt-location-text {
            color: var(--medium-gray);
            font-size: 0.9rem;
        }

        .apt-desc-text {
            color: var(--medium-gray);
            font-size: 0.9rem;
        }

        .map-section {
            padding-top: 30px;
            padding-bottom: 40px;
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .results-header small {
            color: var(--medium-gray);
        }

        @media (max-width: 768px) {
            .section-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
<?php include "includes/header.php"; ?>

<section class="map-section mt-4">
    <div class="container">
        <div class="mb-4">
            <h1 class="section-title mb-1">ค้นหาอพาร์ตเม้น &amp; คอนโด รอบตัวคุณ</h1>
            <p class="text-muted mb-0">เลือกตำแหน่งปัจจุบัน คลิกบนแผนที่ หรือเลือกสถานีรถไฟฟ้า เพื่อดูห้องพักในรัศมีใกล้เคียง</p>
        </div>

        <!-- TOP HORIZONTAL SEARCH BAR -->
        <div class="search-panel mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="search-label">ค้นหาชื่ออพาร์ตเม้น / ทำเล</label>
                    <div class="input-with-icon">
                        <i class="bi bi-search text-primary"></i>
                        <input type="text" id="keyword" placeholder="ชื่อ, เขต, จังหวัด หรือคำอธิบาย" onkeyup="applyFilters()">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="search-label">ประเภทการเช่า</label>
                    <div class="input-with-icon">
                        <i class="bi bi-calendar2-week text-primary"></i>
                        <select id="rentalType" onchange="applyFilters()">
                            <option value="">ทั้งหมด</option>
                            <option value="monthly">รายเดือน</option>
                            <option value="daily">รายวัน</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="search-label">ช่วงราคา (บาท)</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="range" id="priceRange" class="form-range" min="0" max="30000" step="1000" value="0" oninput="updatePriceRangeLabel()" onchange="applyFilters()">
                        <span id="priceRangeLabel" class="small text-muted">ทุกช่วงราคา</span>
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="search-label">ตัวกรองเพิ่มเติม</label>
                    <div class="d-flex align-items-center gap-2">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="advancedFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-sliders"></i> เลือกตัวกรอง
                            </button>
                            <div class="dropdown-menu p-3" aria-labelledby="advancedFilterDropdown" style="min-width: 260px; max-height: 260px; overflow-y: auto;">
                                <div class="form-check mb-2">
                                    <input class="form-check-input amenity-filter" type="checkbox" value="__petFriendly" id="filterPetFriendly">
                                    <label class="form-check-label" for="filterPetFriendly">
                                        <i class="bi bi-heart-pulse text-danger"></i> เลี้ยงสัตว์ได้
                                    </label>
                                </div>
                                <?php if (!empty($amenityLabels)): ?>
                                    <hr class="my-2">
                                    <small class="text-muted d-block mb-2">สิ่งอำนวยความสะดวก</small>
                                    <?php foreach ($amenityLabels as $idx => $label): ?>
                                        <?php $inputId = 'amenity_filter_' . $idx; ?>
                                        <div class="form-check mb-1">
                                            <input class="form-check-input amenity-filter" type="checkbox" value="<?php echo htmlspecialchars($label); ?>" id="<?php echo $inputId; ?>">
                                            <label class="form-check-label" for="<?php echo $inputId; ?>">
                                                <?php echo htmlspecialchars($label); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="d-flex gap-2 ms-auto">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetFilters()">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                            <button type="button" class="btn-search-main btn-sm" onclick="applyFilters()">
                                <i class="bi bi-search"></i>
                                ค้นหาผลลัพธ์
                            </button>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MAP + ROOM LIST TWO COLUMNS -->
        <div class="row g-4">
            <!-- Column 1: Map with tabs -->
            <div class="col-lg-8">
                <div class="map-card map-sticky">
                    <div class="map-header">
                        <div class="map-header-title">
                            <i class="bi bi-geo-alt-fill"></i>
                            <div>
                                <div class="fw-semibold">แผนที่ห้องพัก &amp; รถไฟฟ้า</div>
                                <small class="text-muted">เลือกตำแหน่งบนแผนที่ หรือดูสถานี BTS / MRT ใกล้เคียง</small>
                            </div>
                        </div>
                        <div class="map-header-actions">
                            <div class="d-flex align-items-center me-3 gap-2">
                                <label for="radiusRange" class="text-white small mb-0 text-nowrap">รัศมี:</label>
                                <input type="range" class="form-range" id="radiusRange" min="1" max="20" step="1" value="3" style="width: 100px;" oninput="updateRadiusLabel()" onchange="updateRadius()">
                                <span class="chip-radius" id="radiusInfo">
                                    <i class="bi bi-radar"></i>
                                    <span id="radiusValue">3 กม.</span>
                                </span>
                            </div>
                            <button type="button" class="btn btn-outline-light btn-sm" onclick="useMyLocation()">
                                <i class="bi bi-crosshair"></i> ใกล้ฉัน
                            </button>
                        </div>
                    </div>
                    <div class="px-3 pt-2">
                        <ul class="nav nav-tabs map-tabs" id="mapTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="tabMapBtn" type="button" onclick="switchMapMode('google')">
                                    <i class="bi bi-map"></i> Google Map
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tabTransitBtn" type="button" onclick="switchMapMode('transit')">
                                    <i class="bi bi-train-front"></i> BTS / MRT
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="p-0 position-relative">
                        <!-- Google Map -->
                        <div id="map" style="height: 500px; width: 100%; border-bottom-left-radius: 18px; border-bottom-right-radius: 18px;"></div>
                        
                        <!-- Schematic Map (Hidden by default) -->
                        <div id="schematic-map" class="d-none" style="height: 500px; width: 100%; background: #f8f9fa; border-bottom-left-radius: 18px; border-bottom-right-radius: 18px; overflow: auto; position: relative;">
                            <div class="d-flex justify-content-center align-items-center h-100 text-muted" id="schematic-loading">
                                <span>กำลังโหลดแผนที่รถไฟฟ้า...</span>
                            </div>
                            <div id="schematic-svg-container" style="min-width: 800px; min-height: 600px; position: relative; display: none;">
                                <!-- SVG will be injected here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Column 2: Room list -->
            <div class="col-lg-4 d-flex flex-column">
                <div class="results-header mb-2">
                    <h2 class="section-title mb-0">ห้องพักรอบตำแหน่งที่เลือก</h2>
                    <small id="resultSummary">พบ <?php echo count($allApartments); ?> ห้อง จากข้อมูลทั้งหมด</small>
                </div>
                <div class="row g-3 flex-grow-1" id="nearbyList"></div>
                <div id="noResults" class="alert alert-info mt-3 d-none">
                    ไม่พบห้องพักที่ตรงกับเงื่อนไขในรัศมีที่กำหนด ลองขยับแผนที่หรือปรับตัวกรองใหม่อีกครั้ง
                </div>
            </div>
        </div>
    </div>
</section>


<?php include "includes/footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const APARTMENTS = <?php echo json_encode($allApartments, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const STATIONS = <?php echo json_encode($stations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const RADIUS_KM = 3;

    let map;
    let apartmentMarkers = [];
    let stationMarkers = [];
    let centerMarker = null;
    let radiusCircle = null;
            font-size: 0.9rem;
        }

        .map-section {
            padding-top: 30px;
            padding-bottom: 40px;
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .results-header small {
            color: var(--medium-gray);
        }

        @media (max-width: 768px) {
            .section-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
<?php include "includes/header.php"; ?>

<section class="map-section mt-4">
    <div class="container">
        <div class="mb-4">
            <h1 class="section-title mb-1">ค้นหาอพาร์ตเม้น &amp; คอนโด รอบตัวคุณ</h1>
            <p class="text-muted mb-0">เลือกตำแหน่งปัจจุบัน คลิกบนแผนที่ หรือเลือกสถานีรถไฟฟ้า เพื่อดูห้องพักในรัศมีใกล้เคียง</p>
        </div>

        <!-- TOP HORIZONTAL SEARCH BAR -->
        <div class="search-panel mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="search-label">ค้นหาชื่ออพาร์ตเม้น / ทำเล</label>
                    <div class="input-with-icon">
                        <i class="bi bi-search text-primary"></i>
                        <input type="text" id="keyword" placeholder="ชื่อ, เขต, จังหวัด หรือคำอธิบาย" onkeyup="applyFilters()">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="search-label">ประเภทการเช่า</label>
                    <div class="input-with-icon">
                        <i class="bi bi-calendar2-week text-primary"></i>
                        <select id="rentalType" onchange="applyFilters()">
                            <option value="">ทั้งหมด</option>
                            <option value="monthly">รายเดือน</option>
                            <option value="daily">รายวัน</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="search-label">ช่วงราคา (บาท)</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="range" id="priceRange" class="form-range" min="0" max="30000" step="1000" value="0" oninput="updatePriceRangeLabel()" onchange="applyFilters()">
                        <span id="priceRangeLabel" class="small text-muted">ทุกช่วงราคา</span>
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="search-label">ตัวกรองเพิ่มเติม</label>
                    <div class="d-flex align-items-center gap-2">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="advancedFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-sliders"></i> เลือกตัวกรอง
                            </button>
                            <div class="dropdown-menu p-3" aria-labelledby="advancedFilterDropdown" style="min-width: 260px; max-height: 260px; overflow-y: auto;">
                                <div class="form-check mb-2">
                                    <input class="form-check-input amenity-filter" type="checkbox" value="__petFriendly" id="filterPetFriendly">
                                    <label class="form-check-label" for="filterPetFriendly">
                                        <i class="bi bi-heart-pulse text-danger"></i> เลี้ยงสัตว์ได้
                                    </label>
                                </div>
                                <?php if (!empty($amenityLabels)): ?>
                                    <hr class="my-2">
                                    <small class="text-muted d-block mb-2">สิ่งอำนวยความสะดวก</small>
                                    <?php foreach ($amenityLabels as $idx => $label): ?>
                                        <?php $inputId = 'amenity_filter_' . $idx; ?>
                                        <div class="form-check mb-1">
                                            <input class="form-check-input amenity-filter" type="checkbox" value="<?php echo htmlspecialchars($label); ?>" id="<?php echo $inputId; ?>">
                                            <label class="form-check-label" for="<?php echo $inputId; ?>">
                                                <?php echo htmlspecialchars($label); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="d-flex gap-2 ms-auto">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetFilters()">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                            <button type="button" class="btn-search-main btn-sm" onclick="applyFilters()">
                                <i class="bi bi-search"></i>
                                ค้นหาผลลัพธ์
                            </button>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MAP + ROOM LIST TWO COLUMNS -->
        <div class="row g-4">
            <!-- Column 1: Map with tabs -->
            <div class="col-lg-8">
                <div class="map-card map-sticky">
                    <div class="map-header">
                        <div class="map-header-title">
                            <i class="bi bi-geo-alt-fill"></i>
                            <div>
                                <div class="fw-semibold">แผนที่ห้องพัก &amp; รถไฟฟ้า</div>
                                <small class="text-muted">เลือกตำแหน่งบนแผนที่ หรือดูสถานี BTS / MRT ใกล้เคียง</small>
                            </div>
                        </div>
                        <div class="map-header-actions">
                            <span class="chip-radius me-2" id="radiusInfo">
                                <i class="bi bi-radar"></i>
                                <span>กำลังแสดงรัศมี 3 กม.</span>
                            </span>
                            <button type="button" class="btn btn-outline-light btn-sm" onclick="useMyLocation()">
                                <i class="bi bi-crosshair"></i> ใกล้ฉัน
                            </button>
                        </div>
                    </div>
                    <div class="px-3 pt-2">
                        <ul class="nav nav-tabs map-tabs" id="mapTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="tabMapBtn" type="button" onclick="switchMapMode('google')">
                                    <i class="bi bi-map"></i> Google Map
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tabTransitBtn" type="button" onclick="switchMapMode('transit')">
                                    <i class="bi bi-train-front"></i> BTS / MRT
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="p-0 position-relative">
                        <!-- Google Map -->
                        <div id="map" style="height: 500px; width: 100%; border-bottom-left-radius: 18px; border-bottom-right-radius: 18px;"></div>
                        
                        <!-- Schematic Map (Hidden by default) -->
                        <div id="schematic-map" class="d-none" style="height: 500px; width: 100%; background: #f8f9fa; border-bottom-left-radius: 18px; border-bottom-right-radius: 18px; overflow: auto; position: relative;">
                            <div class="d-flex justify-content-center align-items-center h-100 text-muted" id="schematic-loading">
                                <span>กำลังโหลดแผนที่รถไฟฟ้า...</span>
                            </div>
                            <div id="schematic-svg-container" style="min-width: 800px; min-height: 600px; position: relative; display: none;">
                                <!-- SVG will be injected here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Column 2: Room list -->
            <div class="col-lg-4 d-flex flex-column">
                <div class="results-header mb-2">
                    <h2 class="section-title mb-0">ห้องพักรอบตำแหน่งที่เลือก</h2>
                    <small id="resultSummary">พบ <?php echo count($allApartments); ?> ห้อง จากข้อมูลทั้งหมด</small>
                </div>
                <div class="row g-3 flex-grow-1" id="nearbyList"></div>
                <div id="noResults" class="alert alert-info mt-3 d-none">
                    ไม่พบห้องพักที่ตรงกับเงื่อนไขในรัศมีที่กำหนด ลองขยับแผนที่หรือปรับตัวกรองใหม่อีกครั้ง
                </div>
            </div>
        </div>
    </div>
</section>


<?php include "includes/footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const APARTMENTS = <?php echo json_encode($allApartments, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const STATIONS = <?php echo json_encode($stations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    let RADIUS_KM = 3;

    let map;
    let apartmentMarkers = [];
    let stationMarkers = [];
    let centerMarker = null;
    let radiusCircle = null;
    let currentCenter = null;
    let mapMode = 'google';
    let currentFiltered = [...APARTMENTS];

    let transitLayer;
    let schematicInitialized = false;

    // Simplified Schematic Coordinates for Demo (RentHub style)
    // In a real app, this would be a complete JSON of all stations
    const SCHEMATIC_COORDS = {
        // Sukhumvit Line (Light Green)
        'หมอชิต': { x: 400, y: 100 },
        'สะพานควาย': { x: 400, y: 140 },
        'อารีย์': { x: 400, y: 180 },
        'สนามเป้า': { x: 400, y: 220 },
        'อนุสาวรีย์ชัยสมรภูมิ': { x: 400, y: 260 },
        'พญาไท': { x: 400, y: 300 },
        'ราชเทวี': { x: 400, y: 340 },
        'สยาม': { x: 400, y: 380 }, // Interchange
        'ชิดลม': { x: 450, y: 380 },
        'เพลินจิต': { x: 500, y: 380 },
        'นานา': { x: 550, y: 380 },
        'อโศก': { x: 600, y: 380 }, // Interchange with Sukhumvit (MRT)
        'พร้อมพงษ์': { x: 650, y: 380 },
        'ทองหล่อ': { x: 700, y: 380 },
        'เอกมัย': { x: 750, y: 380 },
        'พระโขนง': { x: 800, y: 380 },
        'อ่อนนุช': { x: 850, y: 380 },

        // Silom Line (Dark Green)
        'สนามกีฬาแห่งชาติ': { x: 350, y: 380 },
        'ราชดำริ': { x: 400, y: 420 },
        'ศาลาแดง': { x: 400, y: 460 }, // Interchange with Silom (MRT)
        'ช่องนนทรี': { x: 400, y: 500 },
        'เซนต์หลุยส์': { x: 400, y: 540 },
        'สุรศักดิ์': { x: 360, y: 580 },
        'สะพานตากสิน': { x: 320, y: 580 },
        'กรุงธนบุรี': { x: 280, y: 580 },
        'วงเวียนใหญ่': { x: 240, y: 580 },

        // MRT Blue Line
        'จตุจักร': { x: 420, y: 100 }, // Near Mo Chit
        'กำแพงเพชร': { x: 360, y: 100 },
        'บางซื่อ': { x: 320, y: 120 },
        'เตาปูน': { x: 280, y: 150 },
        'บางพลัด': { x: 240, y: 200 },
        'สิรินธร': { x: 240, y: 250 },
        'บางขุนนนท์': { x: 240, y: 300 },
        'ไฟฉาย': { x: 240, y: 350 },
        'จรัญฯ 13': { x: 240, y: 400 },
        'ท่าพระ': { x: 240, y: 450 },
        'อิสรภาพ': { x: 280, y: 500 },
        'สนามไชย': { x: 320, y: 500 },
        'สามยอด': { x: 360, y: 500 },
        'หัวลำโพง': { x: 440, y: 500 },
        'สามย่าน': { x: 440, y: 460 },
        'สีลม': { x: 440, y: 420 }, // Interchange with Sala Daeng
        'ลุมพินี': { x: 480, y: 420 },
        'คลองเตย': { x: 520, y: 420 },
        'ศูนย์การประชุมแห่งชาติสิริกิติ์': { x: 560, y: 420 },
        'สุขุมวิท': { x: 600, y: 420 }, // Interchange with Asok
        'เพชรบุรี': { x: 600, y: 340 },
        'พระราม 9': { x: 600, y: 300 },
        'ศูนย์วัฒนธรรมแห่งประเทศไทย': { x: 600, y: 260 },
        'ห้วยขวาง': { x: 600, y: 220 },
        'สุทธิสาร': { x: 600, y: 180 },
        'รัชดาภิเษก': { x: 560, y: 140 },
        'ลาดพร้าว': { x: 520, y: 120 },
        'พหลโยธิน': { x: 480, y: 120 },
    };

    function initMap() {
        map = new google.maps.Map(document.getElementById('map'), {
            center: { lat: 13.7563, lng: 100.5018 },
            zoom: 12,
            styles: [
                {
                    featureType: 'poi',
                    stylers: [{ visibility: 'off' }]
                }
            ]
        });

        transitLayer = new google.maps.TransitLayer();

        // Apartment markers
        APARTMENTS.forEach(apt => {
            if (!apt.lat || !apt.lng) return;
            const marker = new google.maps.Marker({
                position: { lat: apt.lat, lng: apt.lng },
                map: map,
                title: apt.name,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    fillColor: '#3b82f6',
                    fillOpacity: 0.9,
                    strokeColor: '#e5e7eb',
                    strokeWeight: 1.5,
                    scale: 6
                }
            });

            const priceText = apt.price_monthly ? `฿${Number(apt.price_monthly).toLocaleString()}/เดือน` : (apt.price_daily ? `฿${Number(apt.price_daily).toLocaleString()}/คืน` : '');
            const content = `
                <div style="min-width:200px;">
                    <strong>${apt.name}</strong><br>
                    <small>${apt.district || ''}, ${apt.province || ''}</small><br>
                    <span style="color:#2563eb; font-weight:600;">${priceText}</span>
                </div>`;
            const info = new google.maps.InfoWindow({ content });
            marker.addListener('click', () => {
                info.open(map, marker);
            });

            apartmentMarkers.push({ marker, apt });
        });

        // Station markers (for Google Map view)
        STATIONS.forEach(st => {
            if (!st.latitude || !st.longitude) return;
            let color = '#22c55e'; // default BTS
            if (st.line_type === 'MRT') color = '#6366f1';
            else if (st.line_type === 'ARL') color = '#f97316';
            else if (st.line_type === 'BRT') color = '#eab308';

            const marker = new google.maps.Marker({
                position: { lat: parseFloat(st.latitude), lng: parseFloat(st.longitude) },
                map: map,
                title: st.name,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    fillColor: color,
                    fillOpacity: 0.95,
                    strokeColor: '#0f172a',
                    strokeWeight: 2,
                    scale: 6
                }
            });

            const info = new google.maps.InfoWindow();
            marker.addListener('click', () => handleStationClick(st, info, marker));
            stationMarkers.push(marker);
        });

        updateStationMarkers();

        // Click on map to change center
        map.addListener('click', (e) => {
            setCenter(e.latLng.lat(), e.latLng.lng(), 'ตำแหน่งที่เลือก');
        });

        // Auto-detect user location
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    setCenter(pos.coords.latitude, pos.coords.longitude, 'ตำแหน่งของคุณ');
                },
                (err) => {
                    console.log('Geolocation access denied or failed');
                }
            );
        }

        // Initial display
        applyFilters();
    }

    function handleStationClick(st, infoWindow = null, marker = null) {
        // Find nearby apartments
        const lat = parseFloat(st.latitude);
        const lng = parseFloat(st.longitude);
        const nearbyApts = APARTMENTS.filter(apt => {
            if (!apt.lat || !apt.lng) return false;
            const d = calculateDistanceKm(lat, lng, apt.lat, apt.lng);
            return d <= RADIUS_KM;
        });

        // Build content
        let content = `<div style="min-width:250px; max-height:300px; overflow-y:auto;">
            <h6 class="mb-2 border-bottom pb-2">${st.name} (${st.line_type})</h6>
            <small class="text-muted d-block mb-2">พบ ${nearbyApts.length} หอพักในรัศมี ${RADIUS_KM} กม.</small>
            <ul class="list-unstyled mb-0">`;

        if (nearbyApts.length > 0) {
            nearbyApts.forEach(apt => {
                 const price = apt.price_monthly ? `฿${Number(apt.price_monthly).toLocaleString()}` : (apt.price_daily ? `฿${Number(apt.price_daily).toLocaleString()}` : '');
                 content += `
                    <li class="mb-2 pb-2 border-bottom">
                        <a href="mock-room-detail.php?id=${apt.id}" target="_blank" class="text-decoration-none fw-bold text-primary">
                            ${apt.name}
                        </a>
                        <div class="d-flex justify-content-between small">
                            <span class="text-muted">${apt.district || ''}</span>
                            <span class="fw-semibold text-dark">${price}</span>
                        </div>
                    </li>
                 `;
            });
        } else {
            content += `<li class="text-muted small">ไม่พบหอพักในบริเวณนี้</li>`;
        }

        content += `</ul></div>`;

        // If triggered from Google Map
        if (infoWindow && marker) {
            infoWindow.setContent(content);
            infoWindow.open(map, marker);
            setCenter(lat, lng, `${st.name} (${st.line_type})`);
        } 
        // If triggered from Schematic Map
        else {
            // Show a modal or update the result list directly
            // For now, let's just update the map center and results
            setCenter(lat, lng, `${st.name} (${st.line_type})`);
            
            // Optional: Show an alert or custom popup for schematic view
            // alert(`Selected Station: ${st.name}\nFound ${nearbyApts.length} nearby rooms.`);
            
            // Better: switch back to map to show results? Or just update the list below
            // Let's just update the list below (already done by setCenter -> applyFilters)
            document.getElementById('resultSummary').scrollIntoView({ behavior: 'smooth' });
        }
    }

    function switchMapMode(mode) {
        mapMode = mode;
        
        const mapDiv = document.getElementById('map');
        const schematicDiv = document.getElementById('schematic-map');
        const tabMap = document.getElementById('tabMapBtn');
        const tabTransit = document.getElementById('tabTransitBtn');

        if (mode === 'google') {
            mapDiv.classList.remove('d-none');
            schematicDiv.classList.add('d-none');
            tabMap.classList.add('active');
            tabTransit.classList.remove('active');
            
            if (transitLayer) transitLayer.setMap(null);
            if (map) google.maps.event.trigger(map, 'resize');
            
        } else if (mode === 'transit') {
            mapDiv.classList.add('d-none');
            schematicDiv.classList.remove('d-none');
            tabMap.classList.remove('active');
            tabTransit.classList.add('active');

            if (!schematicInitialized) {
                initSchematicMap();
            }
        }
    }

    // Zoom & Pan State
    let schematicZoom = 1.2; // Start slightly zoomed in
    let schematicPanX = 0;
    let schematicPanY = 0;
    let isDragging = false;
    let startX, startY;

    function initSchematicMap() {
        const container = document.getElementById('schematic-svg-container');
        const wrapper = document.getElementById('schematic-map');
        const loading = document.getElementById('schematic-loading');
        
        if (!container || !loading) return;

        try {
            // Disable native scroll on wrapper for custom pan/zoom
            wrapper.style.overflow = 'hidden';
            wrapper.style.cursor = 'grab';

            // Add Zoom Controls
            if (!document.getElementById('schematic-controls')) {
                const controls = document.createElement('div');
                controls.id = 'schematic-controls';
                controls.className = 'position-absolute bottom-0 end-0 m-3 btn-group-vertical';
                controls.style.zIndex = '10';
                controls.innerHTML = `
                    <button class="btn btn-light border shadow-sm" onclick="zoomSchematic(0.2)" title="Zoom In"><i class="bi bi-plus-lg"></i></button>
                    <button class="btn btn-light border shadow-sm" onclick="zoomSchematic(-0.2)" title="Zoom Out"><i class="bi bi-dash-lg"></i></button>
                    <button class="btn btn-light border shadow-sm" onclick="resetSchematicZoom()" title="Reset View"><i class="bi bi-arrows-fullscreen"></i></button>
                `;
                wrapper.appendChild(controls);
            }

            // Mock stations logic
            let displayStations = STATIONS;
            if (!displayStations || displayStations.length === 0) {
                displayStations = Object.keys(SCHEMATIC_COORDS).map((name, index) => ({
                    id: 'mock_' + index,
                    name: name,
                    line_type: ['หมอชิต', 'สยาม', 'อโศก'].includes(name) ? 'BTS' : 'MRT'
                }));
            }

            // SVG Content with Viewport Group
            let svgContent = `
                <svg id="schematic-svg" width="100%" height="100%" viewBox="0 0 1000 800" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" style="background: #f8f9fa;">
                    <g id="schematic-viewport" transform="scale(${schematicZoom})">
                        <!-- Lines -->
                        <path d="M 400 100 L 400 380 L 850 380" stroke="#77b800" stroke-width="8" fill="none" stroke-linecap="round" stroke-linejoin="round" /> <!-- Sukhumvit Line -->
                        <path d="M 350 380 L 400 380 L 400 540 L 360 580 L 240 580" stroke="#005d5d" stroke-width="8" fill="none" stroke-linecap="round" stroke-linejoin="round" /> <!-- Silom Line -->
                        <path d="M 420 100 L 360 100 L 320 120 L 280 150 L 240 200 L 240 450 L 280 500 L 440 500 L 440 420 L 600 420 L 600 180 L 560 140 L 480 120 Z" stroke="#00227b" stroke-width="8" fill="none" stroke-linecap="round" stroke-linejoin="round" /> <!-- MRT Blue Loop -->
            `;

            // Draw Stations
            displayStations.forEach(st => {
                const coords = SCHEMATIC_COORDS[st.name];
                if (coords) {
                    let color = '#77b800';
                    if (st.line_type === 'MRT') color = '#00227b';
                    else if (st.line_type === 'Silom') color = '#005d5d'; 
                    
                    if (['สนามกีฬาแห่งชาติ', 'ราชดำริ', 'ศาลาแดง', 'ช่องนนทรี', 'สุรศักดิ์', 'สะพานตากสิน', 'วงเวียนใหญ่', 'กรุงธนบุรี'].includes(st.name)) color = '#005d5d';
                    if (['บางซื่อ', 'สุขุมวิท', 'สีลม', 'จตุจักร', 'พระราม 9'].includes(st.name)) color = '#00227b';

                    svgContent += `
                        <g class="schematic-station" style="cursor: pointer; transition: all 0.2s;" onclick="onSchematicStationClick('${st.id}')" onmouseover="this.querySelector('circle').setAttribute('r', '10')" onmouseout="this.querySelector('circle').setAttribute('r', '6')">
                            <circle cx="${coords.x}" cy="${coords.y}" r="6" fill="white" stroke="${color}" stroke-width="3" />
                            <text x="${coords.x + 12}" y="${coords.y + 5}" font-family="sans-serif" font-size="14" font-weight="bold" fill="#333" style="text-shadow: 1px 1px 0 #fff; pointer-events: none;">${st.name}</text>
                        </g>
                    `;
                }
            });

            svgContent += `</g></svg>`;
            container.innerHTML = svgContent;

            // Event Listeners for Pan/Zoom
            const svg = document.getElementById('schematic-svg');
            
            wrapper.addEventListener('wheel', (e) => {
                e.preventDefault();
                const delta = e.deltaY > 0 ? -0.1 : 0.1;
                zoomSchematic(delta);
            });

            wrapper.addEventListener('mousedown', (e) => {
                if (e.target.tagName === 'BUTTON' || e.target.closest('button')) return;
                isDragging = true;
                startX = e.clientX - schematicPanX;
                startY = e.clientY - schematicPanY;
                wrapper.style.cursor = 'grabbing';
            });

            window.addEventListener('mousemove', (e) => {
                if (!isDragging) return;
                e.preventDefault();
                schematicPanX = e.clientX - startX;
                schematicPanY = e.clientY - startY;
                updateSchematicTransform();
            });

            window.addEventListener('mouseup', () => {
                isDragging = false;
                if(wrapper) wrapper.style.cursor = 'grab';
            });
            
        } catch (e) {
            console.error('Error rendering schematic map:', e);
            container.innerHTML = '<div class="text-danger text-center mt-5">เกิดข้อผิดพลาดในการโหลดแผนที่</div>';
        } finally {
            loading.style.display = 'none';
            container.style.display = 'block';
            schematicInitialized = true;
            updateSchematicTransform(); // Apply initial zoom
        }
    }

    function zoomSchematic(delta) {
        const newZoom = schematicZoom + delta;
        if (newZoom > 0.4 && newZoom < 4) {
            schematicZoom = newZoom;
            updateSchematicTransform();
        }
    }

    function resetSchematicZoom() {
        schematicZoom = 1;
        schematicPanX = 0;
        schematicPanY = 0;
        updateSchematicTransform();
    }

    function updateSchematicTransform() {
        const viewport = document.getElementById('schematic-viewport');
        if (viewport) {
            viewport.setAttribute('transform', `translate(${schematicPanX}, ${schematicPanY}) scale(${schematicZoom})`);
        }
    }

    // Global function for SVG onclick
    window.onSchematicStationClick = function(stationId) {
        const st = STATIONS.find(s => s.id == stationId);
        if (st) {
            handleStationClick(st);
        }
    };
    function updateStationMarkers() {
        // In 'google' mode, we might want to show/hide station markers
        // Currently we show them all the time in Google mode
        stationMarkers.forEach(marker => {
            marker.setVisible(mapMode === 'google');
        });
    }

    function useMyLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    setCenter(pos.coords.latitude, pos.coords.longitude, 'ตำแหน่งของคุณ');
                },
                (err) => {
                    alert('ไม่สามารถระบุตำแหน่งของคุณได้');
                }
            );
        } else {
            alert('เบราว์เซอร์ของคุณไม่รองรับการระบุตำแหน่ง');
        }
    }

    // Ensure updateMarkers is defined if it wasn't already
    function updateMarkers(filteredApts) {
        // Clear existing markers
        apartmentMarkers.forEach(item => item.marker.setMap(null));
        apartmentMarkers = [];

        // Add new markers
        filteredApts.forEach(apt => {
            if (!apt.lat || !apt.lng) return;
            
            const marker = new google.maps.Marker({
                position: { lat: apt.lat, lng: apt.lng },
                map: map,
                title: apt.name,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    fillColor: '#3b82f6',
                    fillOpacity: 0.9,
                    strokeColor: '#e5e7eb',
                    strokeWeight: 1.5,
                    scale: 6
                }
            });

            const priceText = apt.price_monthly ? `฿${Number(apt.price_monthly).toLocaleString()}/เดือน` : (apt.price_daily ? `฿${Number(apt.price_daily).toLocaleString()}/คืน` : '');
            const content = `
                <div style="min-width:200px;">
                    <strong>${apt.name}</strong><br>
                    <small>${apt.district || ''}, ${apt.province || ''}</small><br>
                    <span style="color:#2563eb; font-weight:600;">${priceText}</span>
                </div>`;
            
            const info = new google.maps.InfoWindow({ content });
            marker.addListener('click', () => {
                info.open(map, marker);
            });

            apartmentMarkers.push({ marker, apt });
        });
    }
    function setCenter(lat, lng, title = '') {
        if (!map) return;
        
        const pos = { lat: parseFloat(lat), lng: parseFloat(lng) };
        map.setCenter(pos);
        map.setZoom(14);

        if (centerMarker) centerMarker.setMap(null);
        if (radiusCircle) radiusCircle.setMap(null);

        centerMarker = new google.maps.Marker({
            position: pos,
            map: map,
            title: title,
            icon: {
                path: google.maps.SymbolPath.BACKWARD_CLOSED_ARROW,
                scale: 5,
                fillColor: '#ef4444',
                fillOpacity: 1,
                strokeWeight: 2,
                strokeColor: 'white'
            }
        });

        radiusCircle = new google.maps.Circle({
            strokeColor: "#3b82f6",
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: "#3b82f6",
            fillOpacity: 0.15,
            map: map,
            center: pos,
            radius: RADIUS_KM * 1000,
            clickable: false
        });

        currentCenter = { lat: parseFloat(lat), lng: parseFloat(lng) };
        applyFilters();
    }

    function calculateDistanceKm(lat1, lng1, lat2, lng2) {
        const R = 6371; 
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLng = (lng2 - lng1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
                  Math.sin(dLng/2) * Math.sin(dLng/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    function applyFilters() {
        const keyword = document.getElementById('keyword').value.toLowerCase();
        const rentalType = document.getElementById('rentalType').value;
        const priceMax = parseInt(document.getElementById('priceRange').value) || 0;
        
        // Get selected amenities
        const selectedAmenities = Array.from(document.querySelectorAll('.amenity-filter:checked')).map(cb => cb.value);

        currentFiltered = APARTMENTS.filter(apt => {
            // 1. Keyword
            const textMatch = !keyword || 
                (apt.name && apt.name.toLowerCase().includes(keyword)) ||
                (apt.district && apt.district.toLowerCase().includes(keyword)) ||
                (apt.province && apt.province.toLowerCase().includes(keyword));

            // 2. Rental Type
            let typeMatch = true;
            if (rentalType === 'monthly') typeMatch = !!apt.price_monthly;
            else if (rentalType === 'daily') typeMatch = !!apt.price_daily;

            // 3. Price
            let priceMatch = true;
            if (priceMax > 0) {
                const price = rentalType === 'daily' ? (apt.price_daily || 0) : (apt.price_monthly || 0);
                // If no specific type selected, check if EITHER price fits (simplified logic)
                if (!rentalType) {
                     const pM = apt.price_monthly || 999999;
                     const pD = apt.price_daily || 999999;
                     priceMatch = (pM <= priceMax) || (pD <= priceMax);
                } else {
                    priceMatch = price <= priceMax;
                }
            }

            // 4. Distance (if center is set)
            let distMatch = true;
            if (currentCenter && apt.lat && apt.lng) {
                const dist = calculateDistanceKm(currentCenter.lat, currentCenter.lng, apt.lat, apt.lng);
                distMatch = dist <= RADIUS_KM;
            }

            // 5. Amenities
            let amenityMatch = true;
            if (selectedAmenities.length > 0) {
                // Check pet friendly
                if (selectedAmenities.includes('__petFriendly')) {
                    if (apt.pet_friendly != 1) amenityMatch = false;
                }
                // Check other amenities (mock logic as we don't have full amenity list in JS object yet)
                // In a real app, apt.amenities would be an array of strings
            }

            return textMatch && typeMatch && priceMatch && distMatch && amenityMatch;
        });

        renderResults(currentFiltered);
        updateMarkers(currentFiltered);
    }

    function renderResults(list) {
        const container = document.getElementById('nearbyList');
        const summary = document.getElementById('resultSummary');
        const noResults = document.getElementById('noResults');
        
        container.innerHTML = '';
        summary.innerText = `พบ ${list.length} ห้อง ${currentCenter ? `ในรัศมี ${RADIUS_KM} กม.` : 'จากข้อมูลทั้งหมด'}`;

        if (list.length === 0) {
            noResults.classList.remove('d-none');
            return;
        }
        noResults.classList.add('d-none');

        list.forEach(apt => {
            const price = apt.price_monthly ? `฿${Number(apt.price_monthly).toLocaleString()}/ด` : (apt.price_daily ? `฿${Number(apt.price_daily).toLocaleString()}/ว` : '-');
            
            // Use thumbnail from mock data (it's a full URL)
            let imgPath = 'https://via.placeholder.com/300x200?text=No+Image';
            if (apt.thumbnail && apt.thumbnail.trim() !== '') {
                imgPath = apt.thumbnail;
            } else if (apt.main_image && apt.main_image.trim() !== '') {
                // Fallback to main_image if thumbnail doesn't exist
                if (apt.main_image.startsWith('http')) {
                    imgPath = apt.main_image;
                } else if (apt.main_image.startsWith('uploads/')) {
                    imgPath = apt.main_image;
                } else {
                    imgPath = `uploads/${apt.main_image}`;
                }
            }
            
            // Amenities icons mapping
            const amenityIcons = {
                'WiFi': 'bi-wifi',
                'ที่จอดรถ': 'bi-car-front-fill',
                'ฟิตเนส': 'bi-heart-pulse-fill',
                'สระว่ายน้ำ': 'bi-water',
                'วิวแม่น้ำ': 'bi-water',
                'ครัว': 'bi-cup-hot-fill',
                'กล้องวงจรปิด': 'bi-camera-video-fill',
                'รูมเซอร์วิส': 'bi-bell-fill',
                'ห้องประชุม': 'bi-briefcase-fill',
                'สวนส่วนกลาง': 'bi-tree-fill',
                'Co-working space': 'bi-laptop',
                'คาเฟ่': 'bi-cup-straw',
                'เลี้ยงสัตว์ได้': 'bi-heart-fill',
                'ใกล้สถานที่ท่องเที่ยว': 'bi-geo-alt-fill',
                'ใกล้ห้างสรรพสินค้า': 'bi-shop',
                'ใกล้สนามบิน': 'bi-airplane-fill',
                'ใกล้มหาวิทยาลัย': 'bi-book-fill',
                'เครื่องซักผ้าหยอดเหรียญ': 'bi-droplet-fill',
                'คาเฟ่ชั้นล่าง': 'bi-cup-straw',
            };
            
            // Build amenities HTML (show max 3, then "+X more")
            let amenitiesHtml = '';
            if (apt.amenities && apt.amenities.length > 0) {
                const maxShow = 3;
                const amenitiesSlice = apt.amenities.slice(0, maxShow);
                amenitiesHtml = amenitiesSlice.map(amenity => {
                    const icon = amenityIcons[amenity] || 'bi-check-circle';
                    return `<span class="badge bg-light text-dark me-1 mb-1" style="font-size: 0.7rem;"><i class="${icon}"></i> ${amenity}</span>`;
                }).join('');
                
                if (apt.amenities.length > maxShow) {
                    const remaining = apt.amenities.length - maxShow;
                    amenitiesHtml += `<span class="badge bg-secondary me-1 mb-1" style="font-size: 0.7rem;">+${remaining}</span>`;
                }
            }
            
            const col = document.createElement('div');
            col.className = 'col-12';
            col.innerHTML = `
                <div class="card h-100 border-0 shadow-sm flex-row overflow-hidden" style="min-height: 120px;">
                    <img src="${imgPath}" class="object-fit-cover" style="width: 140px; height: 100%;" alt="${apt.name}">
                    <div class="card-body p-2 d-flex flex-column justify-content-between">
                        <div>
                            <h6 class="card-title mb-1 text-truncate"><a href="mock-room-detail.php?id=${apt.id}" target="_blank" class="text-decoration-none text-dark">${apt.name}</a></h6>
                            <small class="text-muted d-block text-truncate"><i class="bi bi-geo-alt"></i> ${apt.district || ''}</small>
                            ${amenitiesHtml ? `<div class="mt-1">${amenitiesHtml}</div>` : ''}
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <span class="text-primary fw-bold">${price}</span>
                            <a href="mock-room-detail.php?id=${apt.id}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">ดูรายละเอียด</a>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(col);
        });
    }

    function updatePriceRangeLabel() {
        const val = document.getElementById('priceRange').value;
        document.getElementById('priceRangeLabel').innerText = val > 0 ? `ไม่เกิน ${Number(val).toLocaleString()} บาท` : 'ทุกช่วงราคา';
    }

    function updateRadiusLabel() {
        const val = document.getElementById('radiusRange').value;
        document.getElementById('radiusValue').innerText = `${val} กม.`;
    }

    function updateRadius() {
        const val = parseInt(document.getElementById('radiusRange').value);
        RADIUS_KM = val;
        
        // Update circle if exists
        if (radiusCircle) {
            radiusCircle.setRadius(RADIUS_KM * 1000);
        }
        
        // Re-apply filters to update list and markers
        applyFilters();
    }

    function resetFilters() {
        document.getElementById('keyword').value = '';
        document.getElementById('rentalType').value = '';
        document.getElementById('priceRange').value = 0;
        updatePriceRangeLabel();
        
        // Reset Radius
        document.getElementById('radiusRange').value = 3;
        updateRadiusLabel();
        RADIUS_KM = 3;
        
        document.querySelectorAll('.amenity-filter').forEach(cb => cb.checked = false);
        
        currentCenter = null;
        if (centerMarker) centerMarker.setMap(null);
        if (radiusCircle) radiusCircle.setMap(null);
        map.setZoom(12);
        map.setCenter({ lat: 13.7563, lng: 100.5018 }); // Reset to BKK center

        applyFilters();
    }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>&callback=initMap" async defer></script>
</body>
</html>
