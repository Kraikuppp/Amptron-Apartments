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
                                <button class="nav-link active" id="tabMapBtn" data-bs-toggle="tab" data-bs-target="#tab-map" type="button" role="tab" aria-controls="tab-map" aria-selected="true" data-map-mode="google">
                                    <i class="bi bi-map"></i> Google Map
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tabTransitBtn" data-bs-toggle="tab" data-bs-target="#tab-transit" type="button" role="tab" aria-controls="tab-transit" aria-selected="false" data-map-mode="transit">
                                    <i class="bi bi-train-front"></i> BTS / MRT
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="p-3 pt-2">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="tab-map" role="tabpanel" aria-labelledby="tabMapBtn">
                                <div id="map"></div>
                            </div>
                            <div class="tab-pane fade" id="tab-transit" role="tabpanel" aria-labelledby="tabTransitBtn">
                                <!-- ใช้แผนที่เดียวกัน แต่แสดงเฉพาะสถานี BTS/MRT ผ่าน JS -->
                                <div id="map" style="height: 480px; width: 100%;"></div>
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
    let currentCenter = null;
    let mapMode = 'google';
    let currentFiltered = [...APARTMENTS];

    function initMap() {
        map = new google.maps.Map(document.getElementById('map'), {
            center: { lat: 13.7563, lng: 100.5018 },
            zoom: 12,
            styles: [
                {
                    featureType: 'poi',
                    stylers: [{ visibility: 'off' }]
                },
                {
                    featureType: 'poi.lodging',
                    stylers: [{ visibility: 'on' }]
                }
            ]
        });

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

        // Station markers
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

            const info = new google.maps.InfoWindow({
                content: `<div><strong>${st.name}</strong><br><small>${st.line_type} ${st.line_name || ''}</small></div>`
            });

            marker.addListener('click', () => {
                info.open(map, marker);
                setCenter(marker.getPosition().lat(), marker.getPosition().lng(), `${st.name} (${st.line_type})`);
            });

            stationMarkers.push(marker);
        });

        updateStationMarkers();

        // Click on map to change center
        map.addListener('click', (e) => {
            setCenter(e.latLng.lat(), e.latLng.lng(), 'ตำแหน่งที่เลือก');
        });

        // Initial display (no center filter yet)
        applyFilters();
    }

    function setCenter(lat, lng, labelText) {
        currentCenter = { lat, lng };

        // Center marker
        if (!centerMarker) {
            centerMarker = new google.maps.Marker({
                position: currentCenter,
                map: map,
                draggable: true,
                title: labelText || 'ตำแหน่งที่เลือก',
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    fillColor: '#f97316',
                    fillOpacity: 1,
                    strokeColor: '#ffffff',
                    strokeWeight: 2,
                    scale: 8
                }
            });

            centerMarker.addListener('dragend', (e) => {
                setCenter(e.latLng.lat(), e.latLng.lng(), 'ตำแหน่งที่ลาก');
            });
        } else {
            centerMarker.setPosition(currentCenter);
        }

        // Radius circle
        const radiusMeters = RADIUS_KM * 1000;
        if (!radiusCircle) {
            radiusCircle = new google.maps.Circle({
                strokeColor: '#38bdf8',
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: '#38bdf8',
                fillOpacity: 0.15,
                map: map,
                center: currentCenter,
                radius: radiusMeters
            });
        } else {
            radiusCircle.setCenter(currentCenter);
            radiusCircle.setRadius(radiusMeters);
        }

        map.panTo(currentCenter);
        map.setZoom(14);

        const radiusInfo = document.getElementById('radiusInfo');
        if (radiusInfo && labelText) {
            radiusInfo.querySelector('span').textContent = `กำลังแสดงรัศมี ${RADIUS_KM} กม. รอบ ${labelText}`;
        }

        applyFilters();
    }

    function useMyLocation() {
        if (!navigator.geolocation) {
            alert('เบราว์เซอร์ของคุณไม่รองรับการระบุตำแหน่ง');
            return;
        }
        navigator.geolocation.getCurrentPosition((pos) => {
            setCenter(pos.coords.latitude, pos.coords.longitude, 'ตำแหน่งของคุณ');
        }, (err) => {
            alert('ไม่สามารถระบุตำแหน่งของคุณได้: ' + err.message);
        });
    }

    function calculateDistanceKm(lat1, lng1, lat2, lng2) {
        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLng = (lng2 - lng1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLng / 2) * Math.sin(dLng / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    function applyFilters() {
        const keyword = (document.getElementById('keyword').value || '').toLowerCase();
        const rentalType = document.getElementById('rentalType').value;
        const priceSlider = document.getElementById('priceRange');
        const sliderVal = priceSlider ? parseFloat(priceSlider.value) || 0 : 0;
        const minPrice = 0;
        const maxPrice = sliderVal > 0 ? sliderVal : Infinity;

        const amenityChecked = Array.from(document.querySelectorAll('.amenity-filter:checked'));
        const selectedAmenities = [];
        let requirePetFriendly = false;
        amenityChecked.forEach(cb => {
            if (cb.value === '__petFriendly') {
                requirePetFriendly = true;
            } else {
                selectedAmenities.push(cb.value);
            }
        });

        let filtered = APARTMENTS.filter(apt => {
            if (keyword) {
                const haystack = `${apt.name || ''} ${apt.district || ''} ${apt.province || ''} ${apt.description || ''}`.toLowerCase();
                if (!haystack.includes(keyword)) return false;
            }

            if (rentalType && (!apt.rental_type || apt.rental_type.indexOf(rentalType) === -1)) {
                return false;
            }

            if (requirePetFriendly && !apt.pet_friendly) return false;

            if (selectedAmenities.length > 0) {
                const aptAmenities = Array.isArray(apt.amenities) ? apt.amenities : [];
                const hasAll = selectedAmenities.every(a => aptAmenities.includes(a));
                if (!hasAll) return false;
            }

            // Price check (use monthly if selected or available, otherwise daily)
            let price = null;
            if (rentalType === 'daily') {
                price = apt.price_daily || null;
            } else if (rentalType === 'monthly') {
                price = apt.price_monthly || null;
            } else {
                price = apt.price_monthly || apt.price_daily || null;
            }
            if (price !== null) {
                if (price < minPrice || price > maxPrice) return false;
            }

            // Distance filter if center is set
            if (currentCenter && apt.lat && apt.lng) {
                const d = calculateDistanceKm(currentCenter.lat, currentCenter.lng, apt.lat, apt.lng);
                if (d > RADIUS_KM) return false;
            }

            return true;
        });

        // Sort by rating/views (approx popularity)
        filtered.sort((a, b) => {
            const vrA = (b.rating || 0) - (a.rating || 0);
            if (vrA !== 0) return vrA > 0 ? 1 : -1;
            return (b.views || 0) - (a.views || 0);
        });

        currentFiltered = filtered;
        updateMarkers(currentFiltered);
        renderNearbyList(currentFiltered);
    }

    function resetFilters() {
        document.getElementById('keyword').value = '';
        document.getElementById('rentalType').value = '';
        const priceSlider = document.getElementById('priceRange');
        if (priceSlider) {
            priceSlider.value = 0;
            updatePriceRangeLabel();
        }
        document.querySelectorAll('.amenity-filter').forEach(cb => {
            cb.checked = false;
        });
        applyFilters();
    }

    function updateStationMarkers() {
        stationMarkers.forEach(marker => {
            marker.setVisible(mapMode === 'transit');
        });
    }

    function updateMarkers(filtered) {
        const allowedIds = new Set(filtered.map(a => a.id));
        apartmentMarkers.forEach(({ marker, apt }) => {
            const inFilter = allowedIds.size === 0 || allowedIds.has(apt.id);
            const visible = (mapMode === 'google') && inFilter;
            marker.setVisible(visible);
        });
    }

    function renderNearbyList(list) {
        const container = document.getElementById('nearbyList');
        const noResults = document.getElementById('noResults');
        const summary = document.getElementById('resultSummary');
        container.innerHTML = '';

        if (!list.length) {
            noResults.classList.remove('d-none');
            summary.textContent = 'ไม่พบห้องพักที่ตรงกับเงื่อนไข';
            return;
        }
        noResults.classList.add('d-none');
        summary.textContent = `พบ ${list.length} ห้อง จากข้อมูลทั้งหมด ${APARTMENTS.length} ห้อง`;

        list.forEach(apt => {
            const col = document.createElement('div');
            col.className = 'col-12';

            const monthly = apt.price_monthly ? `฿${Number(apt.price_monthly).toLocaleString()}/เดือน` : '';
            const daily = apt.price_daily ? `฿${Number(apt.price_daily).toLocaleString()}/คืน` : '';
            const priceText = monthly || daily || '';

            const typeLabel = apt.type === 'condo' ? 'คอนโด' : 'อพาร์ตเม้นท์';

            col.innerHTML = `
                <div class="card room-card h-100">
                    <div class="position-relative">
                        <img src="${apt.thumbnail}" alt="${apt.name}" onerror="this.src='assets/images/room-placeholder.jpg'">
                        <span class="badge badge-type position-absolute top-0 start-0 m-2">${typeLabel}</span>
                        <span class="badge badge-price position-absolute top-0 end-0 m-2">${priceText}</span>
                    </div>
                    <div class="card-body">
                        <h5 class="apt-list-card-title mb-1">${apt.name}</h5>
                        <p class="apt-location-text mb-2">
                            <i class="bi bi-geo-alt-fill"></i>
                            ${(apt.district || '') + ', ' + (apt.province || '')}
                        </p>
                        <p class="apt-desc-text text-truncate mb-2">${apt.description || ''}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="bi bi-star-fill text-warning"></i>
                                ${(apt.rating || 0).toFixed(1)}
                            </small>
                            <a href="mock-room-detail.php?id=${apt.id}" class="btn btn-sm btn-primary">
                                ดูรายละเอียด
                            </a>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(col);
        });
    }

    function updatePriceRangeLabel() {
        const slider = document.getElementById('priceRange');
        const label = document.getElementById('priceRangeLabel');
        if (!slider || !label) return;
        const val = parseFloat(slider.value) || 0;
        if (val <= 0) {
            label.textContent = 'ทุกช่วงราคา';
        } else {
            label.textContent = `ไม่เกิน ฿${val.toLocaleString()}`;
        }
    }

    // Tab switching between Google Map and BTS/MRT views
    document.addEventListener('DOMContentLoaded', function () {
        const mapTabBtn = document.querySelector('[data-map-mode="google"]');
        const transitTabBtn = document.querySelector('[data-map-mode="transit"]');

        function handleModeChange(mode) {
            mapMode = mode;
            if (map) {
                google.maps.event.trigger(map, 'resize');
            }
            updateStationMarkers();
            updateMarkers(currentFiltered);
        }

        if (mapTabBtn) {
            mapTabBtn.addEventListener('shown.bs.tab', function () {
                handleModeChange('google');
            });
        }

        if (transitTabBtn) {
            transitTabBtn.addEventListener('shown.bs.tab', function () {
                handleModeChange('transit');
            });
        }
    });
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>&callback=initMap" async defer></script>
</body>
</html>
