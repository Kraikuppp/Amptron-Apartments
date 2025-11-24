<?php
require_once "config/config.php";
require_once "includes/auth.php";
require_once "includes/mock_apartments.php";

$allApartments = mock_get_all_apartments();

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

        .search-panel {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 12px 40px rgba(15, 23, 42, 0.15);
            border: 1px solid #e2e8f0;
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

        /* Hide number input arrows */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type=number] {
            -moz-appearance: textfield;
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

        .results-header small {
            color: var(--medium-gray);
        }

        .bg-section-light {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }
    </style>
</head>
<body>
<?php include "includes/header.php"; ?>

<section class="py-4 mt-4">
    <div class="container">
        <div class="mb-4">
            <h1 class="section-title mb-1">ค้นหาห้องพักทั้งหมด</h1>
            <p class="text-muted mb-0">แสดงรายการห้องพักทุกประเภทจากข้อมูลตัวอย่าง พร้อมตัวกรองการค้นหา</p>
        </div>

        <!-- TOP HORIZONTAL SEARCH BAR (same layout as room.php) -->
        <div class="search-panel mb-4">
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <label class="search-label">ค้นหาชื่ออพาร์ตเม้น / ทำเล</label>
                    <div class="input-with-icon">
                        <i class="bi bi-search text-primary"></i>
                        <input type="text" id="keyword" placeholder="ชื่อ, เขต, จังหวัด" onkeyup="applyFilters()">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="search-label">ประเภท</label>
                    <div class="input-with-icon">
                        <i class="bi bi-calendar2-week text-primary"></i>
                        <select id="rentalType" onchange="applyFilters()">
                            <option value="">ทั้งหมด</option>
                            <option value="monthly">เดือน</option>
                            <option value="daily">วัน</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="search-label">ช่วงราคา (บาท) <small id="priceRangeLabel" class="text-muted ms-1">ทุกช่วงราคา</small></label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="range" id="priceRange" class="form-range" min="100" max="5000000" step="10000" value="100" oninput="updatePriceFromSlider()" onchange="applyFilters()" style="flex: 1;">
                        <div class="input-with-icon" style="padding: 6px 10px; width: 120px;">
                            <span style="font-size: 0.85rem; color: var(--medium-gray);">฿</span>
                            <input type="number" id="priceInput" class="border-0 outline-0" style="width: 100%; font-size: 0.85rem;" min="100" max="5000000" step="10000" value="5000000" placeholder="ไม่จำกัด" oninput="updatePriceFromInput()" onchange="applyFilters()">
                        </div>
                    </div>
                </div>

                <div class="col-md-1">
                    <label class="search-label">&nbsp;</label>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle w-100" type="button" id="advancedFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="padding: 10px 8px;" title="ตัวกรอง">
                            <i class="bi bi-sliders"></i>
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
                </div>

                <div class="col-md-2">
                    <label class="search-label d-block">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary" onclick="resetFilters()" title="รีเซ็ต" style="padding: 10px 14px;">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                        <button type="button" class="btn-search-main flex-grow-1" onclick="applyFilters()" style="padding: 10px 20px;">
                            <i class="bi bi-search"></i>
                            ค้นหา
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- SUMMARY -->
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h2 class="h5 mb-0" style="font-family: var(--font-thai), var(--font-english);">สรุปผลการค้นหา</h2>
            <small id="resultSummary" class="text-muted">พบ <?php echo count($allApartments); ?> ห้อง จากข้อมูลทั้งหมด</small>
        </div>

        <!-- SECTION: MONTHLY ROOMS -->
        <section class="py-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="section-title mb-0" style="font-size: 1.4rem;">ห้องพักรายเดือน</h3>
            </div>
            <div class="row g-3" id="monthlyList"></div>
            <div id="monthlyNoResults" class="alert alert-info mt-3 d-none">
                ไม่พบห้องพักรายเดือนตามเงื่อนไขที่เลือก
            </div>
        </section>

        <!-- SECTION: DAILY ROOMS -->
        <section class="py-4 bg-section-light mt-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="section-title mb-0" style="font-size: 1.4rem;">ห้องพักรายวัน</h3>
            </div>
            <div class="row g-3" id="dailyList"></div>
            <div id="dailyNoResults" class="alert alert-info mt-3 d-none">
                ไม่พบห้องพักรายวันตามเงื่อนไขที่เลือก
            </div>
        </section>
    </div>
</section>

<?php include "includes/footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const APARTMENTS = <?php echo json_encode($allApartments, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

    let currentFiltered = [...APARTMENTS];

    function applyFilters() {
        const keyword = (document.getElementById('keyword').value || '').toLowerCase();
        const rentalType = document.getElementById('rentalType').value;
        const priceSlider = document.getElementById('priceRange');
        const sliderVal = priceSlider ? parseFloat(priceSlider.value) || 100 : 100;
        const minPrice = 0;
        const maxPrice = sliderVal > 100 ? sliderVal : Infinity;

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

            // ราคา
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

            return true;
        });

        // เรียงตาม rating แล้ว views
        filtered.sort((a, b) => {
            const vrA = (b.rating || 0) - (a.rating || 0);
            if (vrA !== 0) return vrA > 0 ? 1 : -1;
            return (b.views || 0) - (a.views || 0);
        });

        currentFiltered = filtered;
        renderLists(currentFiltered);
    }

    function resetFilters() {
        document.getElementById('keyword').value = '';
        document.getElementById('rentalType').value = '';
        const priceSlider = document.getElementById('priceRange');
        const priceInput = document.getElementById('priceInput');
        if (priceSlider) {
            priceSlider.value = 100;
        }
        if (priceInput) {
            priceInput.value = 5000000;
        }
        updatePriceFromSlider();
        document.querySelectorAll('.amenity-filter').forEach(cb => {
            cb.checked = false;
        });
        applyFilters();
    }

    function renderLists(list) {
        const monthlyContainer = document.getElementById('monthlyList');
        const dailyContainer = document.getElementById('dailyList');
        const monthlyNo = document.getElementById('monthlyNoResults');
        const dailyNo = document.getElementById('dailyNoResults');
        const summary = document.getElementById('resultSummary');

        monthlyContainer.innerHTML = '';
        dailyContainer.innerHTML = '';

        if (summary) {
            summary.textContent = `พบ ${list.length} ห้อง จากข้อมูลทั้งหมด ${APARTMENTS.length} ห้อง`;
        }

        const monthly = list.filter(apt => {
            return (apt.rental_type && apt.rental_type.indexOf('monthly') !== -1) || apt.price_monthly;
        });
        const daily = list.filter(apt => {
            return (apt.rental_type && apt.rental_type.indexOf('daily') !== -1) || apt.price_daily;
        });

        if (!monthly.length) {
            monthlyNo.classList.remove('d-none');
        } else {
            monthlyNo.classList.add('d-none');
            monthly.forEach(apt => {
                const col = document.createElement('div');
                col.className = 'col-lg-3 col-md-4 col-sm-6';

                const priceText = apt.price_monthly ? `฿${Number(apt.price_monthly).toLocaleString()}/เดือน` : '';
                const typeLabel = apt.type === 'condo' ? 'คอนโด' : 'อพาร์ตเม้นท์';

                // Build amenities display
                let amenitiesHTML = '';
                if (apt.max_occupancy || (apt.amenities && apt.amenities.length > 0)) {
                    amenitiesHTML = '<div class="mb-2"><small class="text-muted d-flex flex-wrap gap-2">';
                    
                    // Add max occupancy
                    if (apt.max_occupancy) {
                        amenitiesHTML += `<span><i class="bi bi-people-fill" style="color: var(--primary-color);"></i> อยู่ได้ ${apt.max_occupancy} คน</span>`;
                    }
                    
                    // Add other amenities (max 2)
                    if (apt.amenities && apt.amenities.length > 0) {
                        const amenityIcons = {
                            'WiFi': 'bi-wifi',
                            'ที่จอดรถ': 'bi-car-front-fill',
                            'ฟิตเนส': 'bi-heart-pulse-fill',
                            'สระว่ายน้ำ': 'bi-water',
                            'วิวแม่น้ำ': 'bi-water',
                            'ครัว': 'bi-cup-hot-fill',
                            'แอร์': 'bi-snow',
                            'เครื่องปรับอากาศ': 'bi-snow'
                        };
                        const displayAmenities = apt.amenities.slice(0, 2);
                        displayAmenities.forEach(amenity => {
                            const icon = amenityIcons[amenity] || 'bi-check-circle-fill';
                            amenitiesHTML += `<span><i class="${icon}" style="color: var(--primary-color);"></i> ${amenity}</span>`;
                        });
                    }
                    
                    amenitiesHTML += '</small></div>';
                }

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
                            ${amenitiesHTML}
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
                monthlyContainer.appendChild(col);
            });
        }

        if (!daily.length) {
            dailyNo.classList.remove('d-none');
        } else {
            dailyNo.classList.add('d-none');
            daily.forEach(apt => {
                const col = document.createElement('div');
                col.className = 'col-lg-3 col-md-4 col-sm-6';

                const priceText = apt.price_daily ? `฿${Number(apt.price_daily).toLocaleString()}/คืน` : '';
                const typeLabel = apt.type === 'condo' ? 'คอนโด' : 'อพาร์ตเม้นท์';

                // Build amenities display
                let amenitiesHTML = '';
                if (apt.max_occupancy || (apt.amenities && apt.amenities.length > 0)) {
                    amenitiesHTML = '<div class="mb-2"><small class="text-muted d-flex flex-wrap gap-2">';
                    
                    // Add max occupancy
                    if (apt.max_occupancy) {
                        amenitiesHTML += `<span><i class="bi bi-people-fill" style="color: var(--primary-color);"></i> อยู่ได้ ${apt.max_occupancy} คน</span>`;
                    }
                    
                    // Add other amenities (max 2)
                    if (apt.amenities && apt.amenities.length > 0) {
                        const amenityIcons = {
                            'WiFi': 'bi-wifi',
                            'ที่จอดรถ': 'bi-car-front-fill',
                            'ฟิตเนส': 'bi-heart-pulse-fill',
                            'สระว่ายน้ำ': 'bi-water',
                            'วิวแม่น้ำ': 'bi-water',
                            'ครัว': 'bi-cup-hot-fill',
                            'แอร์': 'bi-snow',
                            'เครื่องปรับอากาศ': 'bi-snow'
                        };
                        const displayAmenities = apt.amenities.slice(0, 2);
                        displayAmenities.forEach(amenity => {
                            const icon = amenityIcons[amenity] || 'bi-check-circle-fill';
                            amenitiesHTML += `<span><i class="${icon}" style="color: var(--primary-color);"></i> ${amenity}</span>`;
                        });
                    }
                    
                    amenitiesHTML += '</small></div>';
                }

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
                            ${amenitiesHTML}
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
                dailyContainer.appendChild(col);
            });
        }
    }

    function updatePriceFromSlider() {
        const slider = document.getElementById('priceRange');
        const input = document.getElementById('priceInput');
        const label = document.getElementById('priceRangeLabel');
        
        if (!slider || !input || !label) return;
        
        const val = parseFloat(slider.value) || 100;
        input.value = val;
        
        if (val <= 100) {
            label.textContent = 'ทุกช่วงราคา';
        } else {
            label.textContent = `ไม่เกิน ฿${val.toLocaleString()}`;
        }
    }

    function updatePriceFromInput() {
        const slider = document.getElementById('priceRange');
        const input = document.getElementById('priceInput');
        const label = document.getElementById('priceRangeLabel');
        
        if (!slider || !input || !label) return;
        
        let val = parseFloat(input.value) || 100;
        
        // จำกัดค่าให้อยู่ในช่วงที่กำหนด
        if (val < 100) val = 100;
        if (val > 5000000) val = 5000000;
        
        input.value = val;
        slider.value = val;
        
        if (val <= 100) {
            label.textContent = 'ทุกช่วงราคา';
        } else {
            label.textContent = `ไม่เกิน ฿${val.toLocaleString()}`;
        }
    }

    function updatePriceRangeLabel() {
        // Fallback function for compatibility
        updatePriceFromSlider();
    }

    document.addEventListener('DOMContentLoaded', function () {
        updatePriceFromSlider();
        renderLists(currentFiltered);
    });
</script>
</body>
</html>
