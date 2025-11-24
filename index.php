<?php
require_once "config/config.php";
require_once "includes/auth.php";
require_once "includes/mock_apartments.php";

// Redirect logged-in users to My Room page (unless they are admin/business or searching)
if (isLoggedIn() && !isBusiness() && empty($_GET)) {
    redirect("my-room.php");
}

// Load featured data (real DB if available, otherwise fallback arrays)
if (function_exists('getActiveAdvertisements')) {
    $topAds = getActiveAdvertisements('banner') ?: [];
} else {
    $topAds = [];
}

// Mock apartment sections
$popularApts       = mock_get_popular_apartments(5);
$monthlyApts       = mock_get_monthly_apartments(5);
$dailyApts         = mock_get_daily_apartments(5);


?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/img/amptron-apartments.png?v=2">
    <link rel="apple-touch-icon" href="<?php echo SITE_URL; ?>/img/amptron-apartments.png?v=2">
    
    <!-- Google Fonts -->
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
            
            /* Fonts */
            --font-english: 'League Spartan', sans-serif;
            --font-thai: 'IBM Plex Sans Thai', sans-serif;
        }

        * {
            font-family: var(--font-english);
        }

        /* Apply Thai font to elements containing Thai text */
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

        /* Keep English font for headings */
        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-english);
            font-weight: 700;
            letter-spacing: -0.02em;
        }



        /* Card Styles */
        .room-card, .product-card {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            background: white;
        }

        .room-card:hover, .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
        }

        .stats-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .testimonial-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .testimonial-card:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .testimonial-text {
            color: var(--medium-gray);
            line-height: 1.6;
            font-style: italic;
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

        /* Responsive Design */
        @media (max-width: 992px) {
            .search-input-row {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .btn-search-main {
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .section-title {
                font-size: 1.5rem;
            }
        }



        /* Category Filter Buttons */
        .category-filter-section {
            padding: 30px 0;
            position: relative;
            background: linear-gradient(to bottom, #f8fafc 0%, #ffffff 100%);
        }

        .category-filter-container {
            position: relative;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 60px 0 20px; /* More padding on right for arrow */
        }

        .category-filter-scroll-wrapper {
            position: relative;
            overflow: hidden;
        }

        .category-filter-wrapper {
            display: flex;
            gap: 12px;
            overflow-x: auto;
            scroll-behavior: smooth;
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE and Edge */
            padding: 10px 0;
            position: relative;
        }

        .category-filter-wrapper::-webkit-scrollbar {
            display: none; /* Chrome, Safari, Opera */
        }

        /* Gradient fade effects on edges */
        .category-filter-scroll-wrapper::before,
        .category-filter-scroll-wrapper::after {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            width: 40px;
            pointer-events: none;
            z-index: 2;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .category-filter-scroll-wrapper::before {
            left: 0;
            background: linear-gradient(to right, rgba(248, 250, 252, 1) 0%, rgba(248, 250, 252, 0) 100%);
        }

        .category-filter-scroll-wrapper::after {
            right: 0;
            background: linear-gradient(to left, rgba(248, 250, 252, 1) 0%, rgba(248, 250, 252, 0) 100%);
        }

        .category-filter-scroll-wrapper.show-left-fade::before {
            opacity: 1;
        }

        .category-filter-scroll-wrapper.show-right-fade::after {
            opacity: 1;
        }

        /* Scroll arrows */
        .category-scroll-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 44px;
            height: 44px;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 3;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            opacity: 1; /* Show by default */
            pointer-events: all;
        }

        .category-scroll-arrow.hidden {
            opacity: 0;
            pointer-events: none;
        }

        .category-scroll-arrow.visible {
            opacity: 1;
            pointer-events: all;
        }

        .category-scroll-arrow:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
            transform: translateY(-50%) scale(1.1);
        }

        .category-scroll-arrow:hover i {
            color: white;
        }

        .category-scroll-arrow i {
            font-size: 1.2rem;
            color: var(--primary-color);
            transition: color 0.3s ease;
        }

        .category-scroll-arrow.left {
            left: 10px;
        }

        .category-scroll-arrow.right {
            right: 10px;
        }

        .category-filter-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 28px;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 50px;
            color: var(--dark-gray);
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            flex-shrink: 0;
        }

        .category-filter-btn i {
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }

        .category-filter-btn:hover {
            border-color: var(--primary-color);
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.08) 0%, rgba(139, 92, 246, 0.08) 100%);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.2);
        }

        .category-filter-btn:hover i {
            transform: scale(1.15) rotate(5deg);
        }

        .category-filter-btn.active {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            border-color: #2563eb;
            color: white;
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.35);
            transform: translateY(-3px);
        }

        .category-filter-btn.active i {
            color: white;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        /* Responsive category filters */
        @media (max-width: 768px) {
            .category-filter-container {
                padding: 0 50px 0 15px;
            }

            .category-filter-btn {
                padding: 12px 22px;
                font-size: 0.9rem;
            }

            .category-scroll-arrow {
                width: 38px;
                height: 38px;
            }

            .category-scroll-arrow i {
                font-size: 1rem;
            }
        }

        @media (max-width: 576px) {
            .category-filter-container {
                padding: 0 45px 0 10px;
            }

            .category-filter-btn {
                padding: 10px 18px;
                font-size: 0.85rem;
            }

            .category-scroll-arrow {
                width: 34px;
                height: 34px;
            }

            .category-scroll-arrow.right {
                right: 5px;
            }
        }
    </style>
</head>
<body>
    <?php include "includes/header.php"; ?>



    <script>
    // Category Filter System - Define early so onclick handlers can use it
    let currentCategory = 'all';

    function filterByCategory(category) {
        currentCategory = category;
        
        // Update button states
        document.querySelectorAll('.category-filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.closest('.category-filter-btn').classList.add('active');
        
        // Get all room cards
        const roomCards = document.querySelectorAll('.room-card');
        const allCards = Array.from(roomCards);
        
        // Category mapping based on apartment characteristics
        const categoryFilters = {
            'all': () => true,
            'near_school': (card) => {
                const name = card.querySelector('.card-title')?.textContent.toLowerCase() || '';
                const desc = card.querySelector('.card-text')?.textContent.toLowerCase() || '';
                return name.includes('student') || name.includes('ramkhamhaeng') || 
                       desc.includes('นักศึกษา') || desc.includes('โรงเรียน') || desc.includes('มหาวิทยาลัย');
            },
            'near_transit': (card) => {
                const name = card.querySelector('.card-title')?.textContent.toLowerCase() || '';
                const desc = card.querySelector('.card-text')?.textContent.toLowerCase() || '';
                return name.includes('bts') || name.includes('mrt') || name.includes('transit') ||
                       desc.includes('bts') || desc.includes('mrt') || desc.includes('รถไฟฟ้า');
            },
            'luxury': (card) => {
                const priceText = card.querySelector('.badge.position-absolute.top-0.end-0')?.textContent || '';
                const price = parseInt(priceText.replace(/[^0-9]/g, '')) || 0;
                const name = card.querySelector('.card-title')?.textContent.toLowerCase() || '';
                return price >= 18000 || name.includes('luxury') || name.includes('executive') || 
                       name.includes('suite') || name.includes('boutique');
            },
            'pet_friendly': (card) => {
                const amenities = card.querySelector('.text-muted.d-flex.flex-wrap')?.textContent || '';
                return amenities.includes('เลี้ยงสัตว์ได้') || amenities.includes('pet');
            },
            'near_university': (card) => {
                const name = card.querySelector('.card-title')?.textContent.toLowerCase() || '';
                const desc = card.querySelector('.card-text')?.textContent.toLowerCase() || '';
                return name.includes('student') || name.includes('ramkhamhaeng') || 
                       desc.includes('มหาวิทยาลัย') || desc.includes('university') || desc.includes('นักศึกษา');
            },
            'near_hospital': (card) => {
                const desc = card.querySelector('.card-text')?.textContent.toLowerCase() || '';
                const location = card.querySelector('.text-muted.mb-2')?.textContent.toLowerCase() || '';
                return desc.includes('โรงพยาบาล') || desc.includes('hospital') || 
                       location.includes('สาทร') || location.includes('สีลม');
            },
            'near_mall': (card) => {
                const amenities = card.querySelector('.text-muted.d-flex.flex-wrap')?.textContent || '';
                const desc = card.querySelector('.card-text')?.textContent.toLowerCase() || '';
                return amenities.includes('ใกล้ห้างสรรพสินค้า') || desc.includes('ห้าง') || 
                       desc.includes('mall') || desc.includes('shopping');
            },
            'near_park': (card) => {
                const amenities = card.querySelector('.text-muted.d-flex.flex-wrap')?.textContent || '';
                const name = card.querySelector('.card-title')?.textContent.toLowerCase() || '';
                return amenities.includes('สวนส่วนกลาง') || name.includes('park') || 
                       name.includes('garden') || name.includes('green');
            }
        };
        
        const filterFn = categoryFilters[category] || categoryFilters['all'];
        
        // First, try to find matching rooms
        const matchingCards = allCards.filter(card => filterFn(card));
        let cardsToShow = matchingCards;
        
        // If no matches found, randomly select 3-5 rooms to simulate results
        if (matchingCards.length === 0 && category !== 'all') {
            const randomCount = Math.floor(Math.random() * 3) + 3; // 3-5 rooms
            const shuffled = allCards.sort(() => 0.5 - Math.random());
            cardsToShow = shuffled.slice(0, randomCount);
        }
        
        let visibleCount = 0;
        
        // Hide all cards first
        allCards.forEach(card => {
            card.closest('.col-lg-3, .col-md-4, .col-sm-6').style.display = 'none';
        });
        
        // Show selected cards with animation
        cardsToShow.forEach(card => {
            card.closest('.col-lg-3, .col-md-4, .col-sm-6').style.display = '';
            card.style.animation = 'fadeIn 0.3s ease-in';
            visibleCount++;
        });
        
        // Scroll to results
        const popularSection = document.querySelector('.py-5');
        if (popularSection) {
            popularSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        
        // Show category info message
        showCategoryMessage(visibleCount, category, matchingCards.length === 0);
    }

    function showCategoryMessage(count, category, isRandom) {
        // Remove existing message
        const existingMsg = document.querySelector('.filter-result-message');
        if (existingMsg) {
            existingMsg.remove();
        }
        
        const categoryNames = {
            'near_school': 'ที่พักใกล้โรงเรียน',
            'near_transit': 'ที่พักติด BTS/MRT',
            'luxury': 'คอนโดหรู',
            'pet_friendly': 'เลี้ยงสัตว์ได้',
            'near_university': 'ใกล้มหาวิทยาลัย',
            'near_hospital': 'ใกล้โรงพยาบาล',
            'near_mall': 'ใกล้ห้างสรรพสินค้า',
            'near_park': 'ใกล้สวนสาธารณะ'
        };
        
        if (category !== 'all') {
            const message = document.createElement('div');
            message.className = `alert ${isRandom ? 'alert-warning' : 'alert-success'} filter-result-message mt-3`;
            
            if (isRandom) {
                message.innerHTML = `
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>แสดงห้องพักแนะนำ:</strong> 
                    พบ ${count} ห้องพักที่อาจเหมาะกับหมวด "${categoryNames[category]}" 
                    (ข้อมูลจำลอง)
                `;
            } else {
                message.innerHTML = `
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>พบ ${count} ห้องพัก</strong> ในหมวด "${categoryNames[category]}"
                `;
            }
            
            const popularSection = document.querySelector('.py-5 .container');
            if (popularSection) {
                popularSection.insertBefore(message, popularSection.firstChild.nextSibling);
            }
        }
    }

    function scrollCategories(direction) {
        const wrapper = document.getElementById('categoryFilterWrapper');
        if (!wrapper) return;
        
        const scrollAmount = 300;
        wrapper.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    }

    // Advanced Filters Toggle
    function toggleAdvancedFilters() {
        const section = document.getElementById('advancedFiltersSection');
        const btn = document.querySelector('.btn-advanced-filters');
        const icon = document.getElementById('advancedFilterIcon');
        
        if (section && section.style.display === 'none') {
            section.style.display = 'block';
            if (btn) btn.classList.add('active');
            if (icon) {
                icon.classList.remove('bi-chevron-down');
                icon.classList.add('bi-chevron-up');
            }
        } else if (section) {
            section.style.display = 'none';
            if (btn) btn.classList.remove('active');
            if (icon) {
                icon.classList.remove('bi-chevron-up');
                icon.classList.add('bi-chevron-down');
            }
        }
    }

    // Clear all filters
    function clearFilters() {
        document.querySelectorAll('#advancedFiltersSection input[type="checkbox"]').forEach(checkbox => {
            checkbox.checked = false;
        });
    }

    // Comparison functions
    let selectedRoomsForComparison = [];

    function showComparison() {
        // Get selected checkboxes
        const selectedCheckboxes = document.querySelectorAll('.compare-checkbox:checked');
        
        if (selectedCheckboxes.length === 0) {
            alert('กรุณาเลือกห้องที่ต้องการเปรียบเทียบอย่างน้อย 1 ห้อง');
            return;
        }
        
        if (selectedCheckboxes.length > 3) {
            alert('สามารถเปรียบเทียบได้สูงสุด 3 ห้องเท่านั้น');
            return;
        }
        
        // Show comparison modal or redirect to comparison page
        alert(`กำลังเปรียบเทียบ ${selectedCheckboxes.length} ห้อง`);
        // TODO: Implement actual comparison display
    }

    function clearComparison() {
        // Clear all comparison checkboxes
        document.querySelectorAll('.compare-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        
        // Hide comparison message if exists
        const comparisonMsg = document.getElementById('comparisonModeMessage');
        if (comparisonMsg) {
            comparisonMsg.style.display = 'none';
        }
        
        selectedRoomsForComparison = [];
    }

    // Guest Counter Functions
    let guestCounts = {
        adults: 2,
        children: 0,
        rooms: 1
    };

    function changeCount(type, delta) {
        const min = type === 'rooms' ? 1 : 0;
        const max = type === 'rooms' ? 10 : 20;
        
        guestCounts[type] = Math.max(min, Math.min(max, guestCounts[type] + delta));
        
        document.getElementById(type + 'Count').textContent = guestCounts[type];
        
        // Update hidden inputs
        const hiddenInput = document.querySelector(`input[name="${type}"]`);
        if (hiddenInput) {
            hiddenInput.value = guestCounts[type];
        }
    }

    function updateGuestsDisplay() {
        const displayText = `${guestCounts.adults} ผู้ใหญ่, ${guestCounts.children} เด็ก, ${guestCounts.rooms} ห้อง`;
        const displayInput = document.querySelector('.search-field-guests .search-input');
        if (displayInput) {
            displayInput.value = displayText;
        }
    }

    // Toggle Advanced Search
    function toggleAdvancedSearch() {
        const advancedFilters = document.getElementById('advancedSearchFilters');
        const icon = document.getElementById('advancedFilterIcon');
        
        if (advancedFilters.style.display === 'none') {
            advancedFilters.style.display = 'block';
            icon.classList.remove('bi-chevron-down');
            icon.classList.add('bi-chevron-up');
        } else {
            advancedFilters.style.display = 'none';
            icon.classList.remove('bi-chevron-up');
            icon.classList.add('bi-chevron-down');
        }
    }

    // Auto-calculate checkout date
    document.addEventListener('DOMContentLoaded', function() {
        const checkinInput = document.querySelector('input[name="checkin"]');
        const checkoutInput = document.querySelector('input[name="checkout"]');
        const durationSelect = document.querySelector('select[name="duration"]');
        
        function updateCheckout() {
            if (checkinInput && checkoutInput && durationSelect) {
                const checkin = new Date(checkinInput.value);
                const duration = parseInt(durationSelect.value);
                
                if (!isNaN(checkin.getTime()) && !isNaN(duration)) {
                    const checkout = new Date(checkin);
                    checkout.setDate(checkout.getDate() + duration);
                    checkoutInput.value = checkout.toISOString().split('T')[0];
                }
            }
        }
        
        if (checkinInput) {
            checkinInput.addEventListener('change', updateCheckout);
        }
        
        if (durationSelect) {
            durationSelect.addEventListener('change', updateCheckout);
        }
        
        // Initial calculation
        updateCheckout();
    });
    </script>

    <!-- Hero Carousel - Featured Rooms -->
    <section class="hero-rooms-carousel">
        <div id="heroRoomsCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
            <!-- Indicators -->
            <div class="carousel-indicators">
                <?php for ($i = 0; $i < min(5, count($popularApts)); $i++): ?>
                <button type="button" data-bs-target="#heroRoomsCarousel" data-bs-slide-to="<?php echo $i; ?>" 
                        <?php echo $i === 0 ? 'class="active" aria-current="true"' : ''; ?> 
                        aria-label="Slide <?php echo $i + 1; ?>"></button>
                <?php endfor; ?>
            </div>

            <!-- Slides -->
            <div class="carousel-inner">
                <?php 
                $featuredRooms = array_slice($popularApts, 0, 5);
                foreach ($featuredRooms as $index => $room): 
                ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                    <div class="hero-room-slide">
                        <!-- Background Image -->
                        <div class="hero-room-bg" style="background-image: url('<?php echo htmlspecialchars($room['thumbnail']); ?>');"></div>
                        <div class="hero-room-overlay"></div>
                        
                        <!-- Content -->
                        <div class="container">
                            <div class="row align-items-center" style="min-height: 500px;">
                                <div class="col-lg-6">
                                    <div class="hero-room-content">
                                        <span class="hero-room-badge">
                                            <i class="bi bi-star-fill"></i>
                                            <?php echo number_format($room['rating'], 1); ?> · แนะนำ
                                        </span>
                                        <h1 class="hero-room-title">
                                            <?php echo htmlspecialchars($room['name']); ?>
                                        </h1>
                                        <p class="hero-room-location">
                                            <i class="bi bi-geo-alt-fill"></i>
                                            <?php echo htmlspecialchars(($room['district'] ?? '') . ', ' . ($room['province'] ?? '')); ?>
                                        </p>
                                        <p class="hero-room-description">
                                            <?php echo htmlspecialchars($room['description']); ?>
                                        </p>
                                        
                                        <!-- Amenities -->
                                        <?php if (!empty($room['amenities'])): ?>
                                        <div class="hero-room-amenities">
                                            <?php 
                                            $amenityIcons = [
                                                'WiFi' => 'bi-wifi',
                                                'ที่จอดรถ' => 'bi-car-front-fill',
                                                'ฟิตเนส' => 'bi-heart-pulse-fill',
                                                'สระว่ายน้ำ' => 'bi-water',
                                                'แอร์' => 'bi-snow',
                                                'เครื่องปรับอากาศ' => 'bi-snow',
                                            ];
                                            $displayAmenities = array_slice($room['amenities'], 0, 4);
                                            foreach ($displayAmenities as $amenity):
                                                $icon = $amenityIcons[$amenity] ?? 'bi-check-circle-fill';
                                            ?>
                                            <span class="amenity-item">
                                                <i class="<?php echo $icon; ?>"></i>
                                                <?php echo htmlspecialchars($amenity); ?>
                                            </span>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <!-- Price & CTA -->
                                        <div class="hero-room-footer">
                                            <div class="hero-room-price">
                                                <?php if (!empty($room['price_monthly'])): ?>
                                                    <span class="price-amount">฿<?php echo number_format($room['price_monthly']); ?></span>
                                                    <span class="price-period">/เดือน</span>
                                                <?php elseif (!empty($room['price_daily'])): ?>
                                                    <span class="price-amount">฿<?php echo number_format($room['price_daily']); ?></span>
                                                    <span class="price-period">/คืน</span>
                                                <?php endif; ?>
                                            </div>
                                            <a href="mock-room-detail.php?id=<?php echo (int) $room['id']; ?>" class="btn-hero-view">
                                                <i class="bi bi-eye me-2"></i>
                                                ดูรายละเอียด
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Controls -->
            <button class="carousel-control-prev" type="button" data-bs-target="#heroRoomsCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroRoomsCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </section>

    <style>
        /* Hero Rooms Carousel Styles */
        .hero-rooms-carousel {
            position: relative;
            margin-bottom: 60px;
        }

        .hero-room-slide {
            position: relative;
            min-height: 500px;
            display: flex;
            align-items: center;
        }

        .hero-room-bg {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            filter: brightness(0.6);
        }

        .hero-room-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(
                to right,
                rgba(0, 0, 0, 0.8) 0%,
                rgba(0, 0, 0, 0.6) 50%,
                rgba(0, 0, 0, 0.3) 100%
            );
        }

        .hero-room-content {
            position: relative;
            z-index: 10;
            color: white;
            padding: 40px 0;
        }

        .hero-room-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .hero-room-badge i {
            color: #fbbf24;
        }

        .hero-room-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 15px;
            line-height: 1.2;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
        }

        .hero-room-location {
            font-size: 1.1rem;
            margin-bottom: 20px;
            opacity: 0.9;
        }

        .hero-room-location i {
            color: #3b82f6;
        }

        .hero-room-description {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 25px;
            opacity: 0.95;
            max-width: 600px;
        }

        .hero-room-amenities {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 30px;
        }

        .amenity-item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            padding: 10px 16px;
            border-radius: 25px;
            font-size: 0.95rem;
            font-weight: 500;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .amenity-item i {
            font-size: 1.1rem;
            color: #60a5fa;
        }

        .hero-room-footer {
            display: flex;
            align-items: center;
            gap: 25px;
            margin-top: 35px;
        }

        .hero-room-price {
            display: flex;
            align-items: baseline;
            gap: 5px;
        }

        .price-amount {
            font-size: 2.5rem;
            font-weight: 700;
            color: #60a5fa;
            text-shadow: 0 2px 10px rgba(96, 165, 250, 0.5);
        }

        .price-period {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .btn-hero-view {
            display: inline-flex;
            align-items: center;
            padding: 14px 32px;
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1.05rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
        }

        .btn-hero-view:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.6);
            color: white;
        }

        /* Carousel Controls */
        .hero-rooms-carousel .carousel-control-prev,
        .hero-rooms-carousel .carousel-control-next {
            width: 60px;
            height: 60px;
            top: 50%;
            transform: translateY(-50%);
            opacity: 1;
            z-index: 15;
        }

        .hero-rooms-carousel .carousel-control-prev {
            left: 30px;
        }

        .hero-rooms-carousel .carousel-control-next {
            right: 30px;
        }

        .hero-rooms-carousel .carousel-control-prev:hover,
        .hero-rooms-carousel .carousel-control-next:hover {
            opacity: 1;
        }

        .hero-rooms-carousel .carousel-control-prev-icon,
        .hero-rooms-carousel .carousel-control-next-icon {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.4);
            background-image: none;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            transition: all 0.3s ease;
        }

        /* Custom Arrow Icons */
        .hero-rooms-carousel .carousel-control-prev-icon::before,
        .hero-rooms-carousel .carousel-control-next-icon::before {
            content: '';
            width: 16px;
            height: 16px;
            border-left: 3px solid white;
            border-bottom: 3px solid white;
            position: absolute;
            transition: all 0.3s ease;
        }

        .hero-rooms-carousel .carousel-control-prev-icon::before {
            transform: rotate(45deg);
            left: 24px;
        }

        .hero-rooms-carousel .carousel-control-next-icon::before {
            transform: rotate(-135deg);
            right: 24px;
        }

        .hero-rooms-carousel .carousel-control-prev:hover .carousel-control-prev-icon,
        .hero-rooms-carousel .carousel-control-next:hover .carousel-control-next-icon {
            background: rgba(255, 255, 255, 0.35);
            border-color: rgba(255, 255, 255, 0.6);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            transform: scale(1.1);
        }

        .hero-rooms-carousel .carousel-control-prev:hover .carousel-control-prev-icon::before,
        .hero-rooms-carousel .carousel-control-next:hover .carousel-control-next-icon::before {
            border-color: #ffffff;
            filter: drop-shadow(0 0 8px rgba(255, 255, 255, 0.8));
        }

        .hero-rooms-carousel .carousel-indicators {
            bottom: 30px;
        }

        .hero-rooms-carousel .carousel-indicators [data-bs-target] {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.5);
            border: 2px solid rgba(255, 255, 255, 0.8);
            margin: 0 6px;
        }

        .hero-rooms-carousel .carousel-indicators .active {
            width: 40px;
            border-radius: 6px;
            background-color: white;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .hero-room-title {
                font-size: 2.2rem;
            }

            .hero-room-description {
                font-size: 1rem;
            }

            .price-amount {
                font-size: 2rem;
            }
        }

        @media (max-width: 768px) {
            .hero-room-slide {
                min-height: 400px;
            }

            .hero-room-title {
                font-size: 1.8rem;
            }

            .hero-room-footer {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .btn-hero-view {
                width: 100%;
                justify-content: center;
            }

            /* Mobile carousel controls */
            .hero-rooms-carousel .carousel-control-prev,
            .hero-rooms-carousel .carousel-control-next {
                width: 50px;
                height: 50px;
            }

            .hero-rooms-carousel .carousel-control-prev {
                left: 15px;
            }

            .hero-rooms-carousel .carousel-control-next {
                right: 15px;
            }

            .hero-rooms-carousel .carousel-control-prev-icon,
            .hero-rooms-carousel .carousel-control-next-icon {
                width: 50px;
                height: 50px;
            }

            .hero-rooms-carousel .carousel-control-prev-icon::before,
            .hero-rooms-carousel .carousel-control-next-icon::before {
                width: 12px;
                height: 12px;
                border-width: 2.5px;
            }

            .hero-rooms-carousel .carousel-control-prev-icon::before {
                left: 20px;
            }

            .hero-rooms-carousel .carousel-control-next-icon::before {
                right: 20px;
            }
        }
    </style>

    <!-- Rental Type Tabs Section -->
    <section class="rental-tabs-section">
        <div class="container">
            <div class="rental-tabs-wrapper">
                <button class="rental-tab active" data-rental-type="monthly" onclick="switchRentalTab('monthly')">
                    <i class="bi bi-calendar-month"></i>
                    <span>รายเดือน</span>
                </button>
                <button class="rental-tab" data-rental-type="daily" onclick="switchRentalTab('daily')">
                    <i class="bi bi-calendar-day"></i>
                    <span>รายวัน</span>
                </button>
            </div>
        </div>
    </section>

    <!-- Category Filter Buttons Section -->
    <section class="category-filter-section">
        <div class="category-filter-container">
            <!-- Right Scroll Arrow Only -->
            <button class="category-scroll-arrow right" onclick="scrollCategories('right')" aria-label="Scroll right">
                <i class="bi bi-chevron-right"></i>
            </button>
            
            <div class="category-filter-scroll-wrapper" id="categoryScrollWrapper">
                <div class="category-filter-wrapper" id="categoryFilterWrapper">
                    <button class="category-filter-btn" data-category="near_transit" onclick="filterByCategory('near_transit')">
                        <i class="bi bi-train-front-fill" style="color: #10b981;"></i>
                        ที่พักติด BTS/MRT
                    </button>
                    <button class="category-filter-btn" data-category="near_university" onclick="filterByCategory('near_university')">
                        <i class="bi bi-mortarboard-fill" style="color: #3b82f6;"></i>
                        ใกล้มหาวิทยาลัย
                    </button>
                    <button class="category-filter-btn" data-category="near_school" onclick="filterByCategory('near_school')">
                        <i class="bi bi-book-fill" style="color: #f59e0b;"></i>
                        ที่พักใกล้โรงเรียน
                    </button>
                    <button class="category-filter-btn" data-category="luxury" onclick="filterByCategory('luxury')">
                        <i class="bi bi-gem" style="color: #8b5cf6;"></i>
                        คอนโดหรู
                    </button>
                    <button class="category-filter-btn" data-category="pet_friendly" onclick="filterByCategory('pet_friendly')">
                        <i class="bi bi-heart-fill" style="color: #ec4899;"></i>
                        เลี้ยงสัตว์ได้
                    </button>
                    <button class="category-filter-btn" data-category="near_hospital" onclick="filterByCategory('near_hospital')">
                        <i class="bi bi-hospital-fill" style="color: #ef4444;"></i>
                        ใกล้โรงพยาบาล
                    </button>
                    <button class="category-filter-btn" data-category="near_mall" onclick="filterByCategory('near_mall')">
                        <i class="bi bi-shop" style="color: #f97316;"></i>
                        ใกล้ห้างสรรพสินค้า
                    </button>
                    <button class="category-filter-btn" data-category="near_park" onclick="filterByCategory('near_park')">
                        <i class="bi bi-tree-fill" style="color: #22c55e;"></i>
                        ใกล้สวนสาธารณะ
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Tab Content: Monthly Rentals -->
    <div id="monthly-content" class="tab-content-section active">
        <!-- Monthly: Featured Apartments -->
        <section class="py-5">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="section-title mb-0">
                        <i class="bi bi-star-fill text-warning me-2"></i>
                        อพาร์ตเม้นรายเดือนแนะนำ
                    </h2>
                    <a href="room-search.php?rental_type=monthly" class="btn btn-outline-primary btn-sm">
                        ดูห้องทั้งหมด <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="row g-4">
                    <?php 
                    $monthlyFeatured = array_filter($popularApts, function($apt) {
                        return in_array('monthly', $apt['rental_type'] ?? []);
                    });
                    $monthlyFeatured = array_slice($monthlyFeatured, 0, 4);
                    foreach ($monthlyFeatured as $apt): 
                    ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="card room-card h-100">
                            <div class="position-relative">
                                <img src="<?php echo htmlspecialchars($apt['thumbnail']); ?>"
                                     class="card-img-top"
                                     alt="<?php echo htmlspecialchars($apt['name']); ?>"
                                     style="height: 220px; object-fit: cover;">
                                <span class="badge position-absolute top-0 end-0 m-2" style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);">
                                    ฿<?php echo number_format($apt['price_monthly']); ?>/เดือน
                                </span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title fw-bold"><?php echo htmlspecialchars($apt['name']); ?></h5>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-geo-alt-fill"></i>
                                    <?php echo htmlspecialchars(($apt['district'] ?? '') . ', ' . ($apt['province'] ?? '')); ?>
                                </p>
                                <!-- Amenities -->
                                <?php if (!empty($apt['amenities']) || !empty($apt['max_occupancy'])): ?>
                                <div class="mb-2">
                                    <small class="text-muted d-flex flex-wrap gap-2">
                                        <?php if (!empty($apt['max_occupancy'])): ?>
                                            <span><i class="bi bi-people-fill" style="color: var(--primary-blue);"></i> อยู่ได้ <?php echo (int)$apt['max_occupancy']; ?> คน</span>
                                        <?php endif; ?>
                                        <?php 
                                        if (!empty($apt['amenities'])):
                                            $amenityIcons = [
                                                'WiFi' => 'bi-wifi',
                                                'ที่จอดรถ' => 'bi-car-front-fill',
                                                'ฟิตเนส' => 'bi-heart-pulse-fill',
                                                'สระว่ายน้ำ' => 'bi-water',
                                                'วิวแม่น้ำ' => 'bi-water',
                                                'ครัว' => 'bi-cup-hot-fill',
                                                'แอร์' => 'bi-snow',
                                            ];
                                            $displayAmenities = array_slice($apt['amenities'], 0, 2);
                                            foreach ($displayAmenities as $amenity):
                                                $icon = $amenityIcons[$amenity] ?? 'bi-check-circle-fill';
                                            ?>
                                                <span><i class="<?php echo $icon; ?>" style="color: var(--primary-blue);"></i> <?php echo htmlspecialchars($amenity); ?></span>
                                            <?php endforeach; 
                                        endif; ?>
                                    </small>
                                </div>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <small class="text-muted">
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <?php echo number_format($apt['rating'], 1); ?>
                                    </small>
                                    <a href="mock-room-detail.php?id=<?php echo (int) $apt['id']; ?>" class="btn btn-sm btn-primary">
                                        ดูรายละเอียด
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Monthly: Luxury Apartments -->
        <section class="py-5 bg-light">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="section-title mb-0">
                        <i class="bi bi-gem text-purple me-2" style="color: #8b5cf6;"></i>
                        อพาร์ตเม้นรายเดือนหรู
                    </h2>
                    <a href="room-search.php?rental_type=monthly" class="btn btn-outline-primary btn-sm">
                        ดูห้องทั้งหมด <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="row g-4">
                    <?php 
                    $monthlyLuxury = array_filter(mock_get_all_apartments(), function($apt) {
                        $isMonthly = in_array('monthly', $apt['rental_type'] ?? []);
                        $isLuxury = ($apt['price_monthly'] ?? 0) >= 15000;
                        return $isMonthly && $isLuxury;
                    });
                    $monthlyLuxury = array_slice($monthlyLuxury, 0, 4);
                    foreach ($monthlyLuxury as $apt): 
                    ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="card room-card h-100">
                            <div class="position-relative">
                                <img src="<?php echo htmlspecialchars($apt['thumbnail']); ?>"
                                     class="card-img-top"
                                     alt="<?php echo htmlspecialchars($apt['name']); ?>"
                                     style="height: 220px; object-fit: cover;">
                                <span class="badge position-absolute top-0 start-0 m-2" style="background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);">
                                    <i class="bi bi-gem"></i> Luxury
                                </span>
                                <span class="badge position-absolute top-0 end-0 m-2" style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);">
                                    ฿<?php echo number_format($apt['price_monthly']); ?>/เดือน
                                </span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title fw-bold"><?php echo htmlspecialchars($apt['name']); ?></h5>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-geo-alt-fill"></i>
                                    <?php echo htmlspecialchars(($apt['district'] ?? '') . ', ' . ($apt['province'] ?? '')); ?>
                                </p>
                                <!-- Amenities -->
                                <?php if (!empty($apt['amenities'])): ?>
                                <div class="mb-2">
                                    <small class="text-muted d-flex flex-wrap gap-2">
                                        <?php 
                                        $amenityIcons = [
                                            'WiFi' => 'bi-wifi',
                                            'ที่จอดรถ' => 'bi-car-front-fill',
                                            'ฟิตเนส' => 'bi-heart-pulse-fill',
                                            'สระว่ายน้ำ' => 'bi-water',
                                            'วิวแม่น้ำ' => 'bi-water',
                                            'ครัว' => 'bi-cup-hot-fill',
                                            'แอร์' => 'bi-snow',
                                        ];
                                        $displayAmenities = array_slice($apt['amenities'], 0, 3);
                                        foreach ($displayAmenities as $amenity):
                                            $icon = $amenityIcons[$amenity] ?? 'bi-check-circle-fill';
                                        ?>
                                            <span><i class="<?php echo $icon; ?>" style="color: var(--primary-blue);"></i> <?php echo htmlspecialchars($amenity); ?></span>
                                        <?php endforeach; ?>
                                    </small>
                                </div>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <small class="text-muted">
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <?php echo number_format($apt['rating'], 1); ?>
                                    </small>
                                    <a href="mock-room-detail.php?id=<?php echo (int) $apt['id']; ?>" class="btn btn-sm btn-primary">
                                        ดูรายละเอียด
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </div>

    <!-- Tab Content: Daily Rentals -->
    <div id="daily-content" class="tab-content-section" style="display: none;">
        <!-- Daily: Featured Apartments -->
        <section class="py-5">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="section-title mb-0">
                        <i class="bi bi-star-fill text-warning me-2"></i>
                        อพาร์ตเม้นรายวันแนะนำ
                    </h2>
                    <a href="room-search.php?rental_type=daily" class="btn btn-outline-primary btn-sm">
                        ดูห้องทั้งหมด <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="row g-4">
                    <?php 
                    $dailyFeatured = array_filter($dailyApts, function($apt) {
                        return in_array('daily', $apt['rental_type'] ?? []);
                    });
                    $dailyFeatured = array_slice($dailyFeatured, 0, 4);
                    foreach ($dailyFeatured as $apt): 
                    ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="card room-card h-100">
                            <div class="position-relative">
                                <img src="<?php echo htmlspecialchars($apt['thumbnail']); ?>"
                                     class="card-img-top"
                                     alt="<?php echo htmlspecialchars($apt['name']); ?>"
                                     style="height: 220px; object-fit: cover;">
                                <span class="badge position-absolute top-0 end-0 m-2" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">
                                    ฿<?php echo number_format($apt['price_daily']); ?>/คืน
                                </span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title fw-bold"><?php echo htmlspecialchars($apt['name']); ?></h5>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-geo-alt-fill"></i>
                                    <?php echo htmlspecialchars(($apt['district'] ?? '') . ', ' . ($apt['province'] ?? '')); ?>
                                </p>
                                <!-- Amenities -->
                                <?php if (!empty($apt['amenities']) || !empty($apt['max_occupancy'])): ?>
                                <div class="mb-2">
                                    <small class="text-muted d-flex flex-wrap gap-2">
                                        <?php if (!empty($apt['max_occupancy'])): ?>
                                            <span><i class="bi bi-people-fill" style="color: var(--primary-blue);"></i> อยู่ได้ <?php echo (int)$apt['max_occupancy']; ?> คน</span>
                                        <?php endif; ?>
                                        <?php 
                                        if (!empty($apt['amenities'])):
                                            $amenityIcons = [
                                                'WiFi' => 'bi-wifi',
                                                'ที่จอดรถ' => 'bi-car-front-fill',
                                                'ฟิตเนส' => 'bi-heart-pulse-fill',
                                                'สระว่ายน้ำ' => 'bi-water',
                                                'วิวแม่น้ำ' => 'bi-water',
                                                'ครัว' => 'bi-cup-hot-fill',
                                                'แอร์' => 'bi-snow',
                                            ];
                                            $displayAmenities = array_slice($apt['amenities'], 0, 2);
                                            foreach ($displayAmenities as $amenity):
                                                $icon = $amenityIcons[$amenity] ?? 'bi-check-circle-fill';
                                            ?>
                                                <span><i class="<?php echo $icon; ?>" style="color: var(--primary-blue);"></i> <?php echo htmlspecialchars($amenity); ?></span>
                                            <?php endforeach; 
                                        endif; ?>
                                    </small>
                                </div>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <small class="text-muted">
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <?php echo number_format($apt['rating'], 1); ?>
                                    </small>
                                    <a href="mock-room-detail.php?id=<?php echo (int) $apt['id']; ?>" class="btn btn-sm btn-primary">
                                        ดูรายละเอียด
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Daily: Luxury Apartments -->
        <section class="py-5 bg-light">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="section-title mb-0">
                        <i class="bi bi-gem text-purple me-2" style="color: #8b5cf6;"></i>
                        อพาร์ตเม้นรายวันหรู
                    </h2>
                    <a href="room-search.php?rental_type=daily" class="btn btn-outline-primary btn-sm">
                        ดูห้องทั้งหมด <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="row g-4">
                    <?php 
                    $dailyLuxury = array_filter(mock_get_all_apartments(), function($apt) {
                        $isDaily = in_array('daily', $apt['rental_type'] ?? []);
                        $isLuxury = ($apt['price_daily'] ?? 0) >= 1500;
                        return $isDaily && $isLuxury;
                    });
                    $dailyLuxury = array_slice($dailyLuxury, 0, 4);
                    foreach ($dailyLuxury as $apt): 
                    ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="card room-card h-100">
                            <div class="position-relative">
                                <img src="<?php echo htmlspecialchars($apt['thumbnail']); ?>"
                                     class="card-img-top"
                                     alt="<?php echo htmlspecialchars($apt['name']); ?>"
                                     style="height: 220px; object-fit: cover;">
                                <span class="badge position-absolute top-0 start-0 m-2" style="background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);">
                                    <i class="bi bi-gem"></i> Luxury
                                </span>
                                <span class="badge position-absolute top-0 end-0 m-2" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">
                                    ฿<?php echo number_format($apt['price_daily']); ?>/คืน
                                </span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title fw-bold"><?php echo htmlspecialchars($apt['name']); ?></h5>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-geo-alt-fill"></i>
                                    <?php echo htmlspecialchars(($apt['district'] ?? '') . ', ' . ($apt['province'] ?? '')); ?>
                                </p>
                                <!-- Amenities -->
                                <?php if (!empty($apt['amenities']) || !empty($apt['max_occupancy'])): ?>
                                <div class="mb-2">
                                    <small class="text-muted d-flex flex-wrap gap-2">
                                        <?php if (!empty($apt['max_occupancy'])): ?>
                                            <span><i class="bi bi-people-fill" style="color: var(--primary-blue);"></i> อยู่ได้ <?php echo (int)$apt['max_occupancy']; ?> คน</span>
                                        <?php endif; ?>
                                        <?php 
                                        if (!empty($apt['amenities'])):
                                            $amenityIcons = [
                                                'WiFi' => 'bi-wifi',
                                                'ที่จอดรถ' => 'bi-car-front-fill',
                                                'ฟิตเนส' => 'bi-heart-pulse-fill',
                                                'สระว่ายน้ำ' => 'bi-water',
                                                'วิวแม่น้ำ' => 'bi-water',
                                                'ครัว' => 'bi-cup-hot-fill',
                                                'แอร์' => 'bi-snow',
                                            ];
                                            $displayAmenities = array_slice($apt['amenities'], 0, 2);
                                            foreach ($displayAmenities as $amenity):
                                                $icon = $amenityIcons[$amenity] ?? 'bi-check-circle-fill';
                                            ?>
                                                <span><i class="<?php echo $icon; ?>" style="color: var(--primary-blue);"></i> <?php echo htmlspecialchars($amenity); ?></span>
                                            <?php endforeach; 
                                        endif; ?>
                                    </small>
                                </div>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <small class="text-muted">
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <?php echo number_format($apt['rating'], 1); ?>
                                    </small>
                                    <a href="mock-room-detail.php?id=<?php echo (int) $apt['id']; ?>" class="btn btn-sm btn-primary">
                                        ดูรายละเอียด
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </div>

    <style>
        /* Rental Tabs Styles */
        .rental-tabs-section {
            padding: 30px 0;
            background: linear-gradient(to bottom, #ffffff 0%, #f8fafc 100%);
            border-bottom: 1px solid #e2e8f0;
        }

        .rental-tabs-wrapper {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .rental-tab {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 32px;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 50px;
            color: var(--dark-gray);
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            position: relative;
            overflow: hidden;
        }

        .rental-tab::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            transition: left 0.3s ease;
            z-index: 0;
        }

        .rental-tab i,
        .rental-tab span {
            position: relative;
            z-index: 1;
            transition: all 0.3s ease;
        }

        .rental-tab i {
            font-size: 1.3rem;
            color: var(--primary-color);
        }

        .rental-tab:hover {
            border-color: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.25);
        }

        .rental-tab:hover i {
            transform: scale(1.15) rotate(5deg);
        }

        .rental-tab.active {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            border-color: #2563eb;
            color: white;
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.35);
            transform: translateY(-3px);
        }

        .rental-tab.active::before {
            left: 0;
        }

        .rental-tab.active i {
            color: white;
            animation: pulse-icon 2s ease-in-out infinite;
        }

        @keyframes pulse-icon {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .rental-tabs-wrapper {
                gap: 10px;
            }

            .rental-tab {
                padding: 12px 24px;
                font-size: 0.95rem;
            }

            .rental-tab i {
                font-size: 1.2rem;
            }
        }

        @media (max-width: 576px) {
            .rental-tab {
                flex: 1 1 calc(50% - 10px);
                justify-content: center;
                min-width: 140px;
            }
        }
    </style>

    <style>
        /* Tab Content Styles */
        .tab-content-section {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <!-- Old sections removed, now using tab content above -->

    <!-- Statistics Section -->
    <section class="py-5" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">สถิติแพลตฟอร์มของเรา</h2>
                <p class="text-muted">ร่วมเป็นส่วนหนึ่งกับลูกค้าหลายพันรายที่พึงพอใจ</p>
            </div>
            <div class="row g-4 text-center">
                <div class="col-lg-3 col-md-6">
                    <div class="stats-card p-4">
                        <div class="stats-icon mb-3">
                            <i class="bi bi-building-fill" style="font-size: 3rem; color: var(--primary-blue);"></i>
                        </div>
                        <h3 class="fw-bold mb-2" style="color: var(--dark-gray);">1,250+</h3>
                        <p class="text-muted mb-0">ห้องพักที่พร้อมให้เช่า</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stats-card p-4">
                        <div class="stats-icon mb-3">
                            <i class="bi bi-people-fill" style="font-size: 3rem; color: var(--secondary-blue);"></i>
                        </div>
                        <h3 class="fw-bold mb-2" style="color: var(--dark-gray);">5,800+</h3>
                        <p class="text-muted mb-0">ลูกค้าที่พึงพอใจ</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stats-card p-4">
                        <div class="stats-icon mb-3">
                            <i class="bi bi-box-seam-fill" style="font-size: 3rem; color: var(--sky-blue);"></i>
                        </div>
                        <h3 class="fw-bold mb-2" style="color: var(--dark-gray);">850+</h3>
                        <p class="text-muted mb-0">สินค้าคุณภาพ</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stats-card p-4">
                        <div class="stats-icon mb-3">
                            <i class="bi bi-star-fill" style="font-size: 3rem; color: #f59e0b;"></i>
                        </div>
                        <h3 class="fw-bold mb-2" style="color: var(--dark-gray);">4.8/5</h3>
                        <p class="text-muted mb-0">คะแนนเฉลี่ย</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">ลูกค้าของเราพูดว่าอย่างไร</h2>
                <p class="text-muted">ประสบการณ์จริงจากลูกค้าที่พึงพอใจ</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="testimonial-card p-4 h-100">
                        <div class="stars mb-3">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                        </div>
                        <p class="testimonial-text">"เจอห้องที่เหมาะสมในกรุงเทพภายในหนึ่งสัปดาห์! แพลตฟอร์มใช้งานง่าย และเจ้าของห้องตอบสนองรวดเร็วมาก"</p>
                        <div class="testimonial-author mt-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <span class="fw-bold">AS</span>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-0">Apinya S.</h6>
                                    <small class="text-muted">ดิจิทัล โนแมด</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card p-4 h-100">
                        <div class="stars mb-3">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                        </div>
                        <p class="testimonial-text">"สินค้าไฟฟ้าครบครัน จัดส่งรวดเร็ว บริการลูกค้าดีเยี่ยม แนะนำสำหรับธุรกิจ!"</p>
                        <div class="testimonial-author mt-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3">
                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <span class="fw-bold">SK</span>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-0">Somchai K.</h6>
                                    <small class="text-muted">วิศวกรไฟฟ้า</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card p-4 h-100">
                        <div class="stars mb-3">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star text-muted"></i>
                        </div>
                        <p class="testimonial-text">"แพลตฟอร์มช่วยให้ฉันหาผู้เช่าที่มีคุณภาพสำหรับห้องของฉัน กระบวนการตรวจสอบทำให้มั่นใจในการให้เช่า"</p>
                        <div class="testimonial-author mt-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3">
                                    <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <span class="fw-bold">NW</span>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-0">Niran W.</h6>
                                    <small class="text-muted">เจ้าของห้อง</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-5 bg-section-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">คำถามที่พบบ่อย</h2>
                <p class="text-muted">คำตอบสำหรับคำถามที่ถูกถามบ่อย</p>
            </div>
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    <i class="bi bi-question-circle me-2 text-primary"></i>
                                    ฉันจะค้นหาห้องเช่าได้อย่างไร?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    คุณสามารถใช้แบบฟอร์มค้นหาบนหน้าแรกเพื่อกรองห้องตามสถานที่ จังหวัด และราคาสูงสุด หรือคลิกปุ่ม "ดูห้องทั้งหมด" เพื่อเรียกดูห้องที่มีทั้งหมด
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    <i class="bi bi-shield-check me-2 text-primary"></i>
                                    ห้องเช่าได้รับการตรวจสอบหรือไม่?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    ใฌ่ครับ ห้องเช่าทั้งหมดบนแพลตฟอร์มของเราจะผ่านกระบวนการตรวจสอบ เราตรวจสอบเอกสารทรัพย์สินและรูปภาพเพื่อรับรองความถูกต้องก่อนอนุมัติรายการ
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    <i class="bi bi-credit-card me-2 text-primary"></i>
                                    รับชำระเงินแบบไหนบ้าง?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    เรารับวิธีการชำระเงินหลากหลาย รวมถึงโอนเงินผ่านธนาคาร บัตรเครดิต/เดบิต และกระเป๋าเงินดิจิทัล เงื่อนไขการชำระเงินจะตกลงโดยตรงกับเจ้าของห้อง
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    <i class="bi bi-tools me-2 text-primary"></i>
                                    คุณขายอุปกรณ์ไฟฟ้าหรือไม่?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    ใฌ่ครับ! เรามีอุปกรณ์ไฟฟ้าหลากหลาย รวมถึงมิเตอร์ไฟฟ้า หม้อแปลงกระแส CT และอุปกรณ์ตรวจวัดสำหรับทั้งที่อยู่อาศัยและเชิงพาณิชย์ สินค้าทั้งหมดมีการรับประกันและสนับสนุนทางเทคนิค
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                    <i class="bi bi-building me-2 text-primary"></i>
                                    ฉันจะลงประกาศห้องของฉันได้อย่างไร?
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    สมัครเป็นผู้ใช้ธุรกิจและเข้าสู่แดชบอร์ด คุณสามารถเพิ่มรายละเอียดห้อง รูปภาพ และตั้งราคา ทีมงานของเราจะตรวจสอบและอนุมัติรายการของคุณภายใน 24-48 ชั่วโมง
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Compare Modal -->
    <div class="modal fade" id="compareModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">เปรียบเทียบอพาร์ตเม้น / คอนโด</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="compareTable">
                            <thead>
                                <tr>
                                    <th style="width: 18%;">คุณสมบัติ</th>
                                    <th style="width: 27%;" class="text-center">ที่พัก 1</th>
                                    <th style="width: 27%;" class="text-center">ที่พัก 2</th>
                                    <th style="width: 28%;" class="text-center">ที่พัก 3</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><th>ชื่อ</th><td></td><td></td><td></td></tr>
                                <tr><th>ประเภท</th><td></td><td></td><td></td></tr>
                                <tr><th>ทำเล</th><td></td><td></td><td></td></tr>
                                <tr><th>ราคา</th><td></td><td></td><td></td></tr>
                                <tr><th>ประเภทการเช่า</th><td></td><td></td><td></td></tr>
                                <tr><th>เลี้ยงสัตว์ได้</th><td></td><td></td><td></td></tr>
                                <tr><th>คะแนนรีวิว</th><td></td><td></td><td></td></tr>
                                <tr><th>สิ่งอำนวยความสะดวก</th><td></td><td></td><td></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Language Switcher Function
        function changeLanguage(lang) {
            // Store language preference in localStorage
            localStorage.setItem('preferredLanguage', lang);

            // Update UI text immediately
            const langElement = document.getElementById('currentLang');
            if (langElement) {
                langElement.textContent = lang.toUpperCase();
            }

            // Show notification
            alert('Language changed to ' + (lang === 'th' ? 'Thai' : 'English') + '. Full i18n implementation coming soon!');

            // For now, just reload with lang parameter (you can implement full i18n later)
            // window.location.href = window.location.pathname + '?lang=' + lang;
        }

        // Navbar transparency on scroll
        function handleNavbarScroll() {
            const navbar = document.querySelector('.navbar');
            if (navbar) {
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            }
        }

        // Switch between rental type tabs
        function switchRentalTab(type) {
            // Update tab states
            document.querySelectorAll('.rental-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelector(`.rental-tab[data-rental-type="${type}"]`).classList.add('active');
            
            // Hide all tab content
            document.querySelectorAll('.tab-content-section').forEach(content => {
                content.style.display = 'none';
            });
            
            // Show selected tab content with animation
            const selectedContent = document.getElementById(`${type}-content`);
            if (selectedContent) {
                selectedContent.style.display = 'block';
                selectedContent.style.animation = 'fadeIn 0.5s ease-in';
            }
            
            // Scroll to top of content
            window.scrollTo({
                top: document.querySelector('.rental-tabs-section').offsetTop - 100,
                behavior: 'smooth'
            });
        }

        // Load saved language preference on page load
        const COMPARE_APARTMENTS = <?php echo json_encode(mock_get_all_apartments(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

        document.addEventListener('DOMContentLoaded', function() {
            const langElement = document.getElementById('currentLang');
            if (langElement) {
                const savedLang = localStorage.getItem('preferredLanguage') || 'en';
                langElement.textContent = savedLang.toUpperCase();
            }

            // Add scroll event listener for navbar
            window.addEventListener('scroll', handleNavbarScroll);

            // Initial check
            handleNavbarScroll();

            // Initialize animations and interactions
            initializeAnimations();
            initializeCounters();
            initializeCardInteractions();



            initializeCompareFeature();
        });

        // Animation functions
        function initializeAnimations() {
            // Animate elements on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            // Observe elements for animation (ตัด .room-card ออกเพื่อไม่ให้การ์ด 3 section เด้ง/กระพริบ)
            const animateElements = document.querySelectorAll('.stats-card, .testimonial-card');
            animateElements.forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
                observer.observe(el);
            });
        }

        // Counter animation for statistics
        function initializeCounters() {
            const counters = document.querySelectorAll('.stats-card h3');
            const counterObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animateCounter(entry.target);
                        counterObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });

            counters.forEach(counter => {
                counterObserver.observe(counter);
            });
        }

        function animateCounter(element) {
            const text = element.textContent;
            
            // ถ้าข้อความมี "/" (เช่น 4.8/5) ให้ข้ามการ animate
            if (text.includes('/')) {
                return;
            }
            
            const number = parseInt(text.replace(/[^0-9]/g, ''));
            const suffix = text.replace(/[0-9,]/g, '');
            let current = 0;
            const increment = number / 100;
            const duration = 2000;

            const timer = setInterval(() => {
                current += increment;
                if (current >= number) {
                    current = number;
                    clearInterval(timer);
                }

                let displayNumber = Math.floor(current);
                if (displayNumber >= 1000) {
                    displayNumber = (displayNumber / 1000).toFixed(1) + 'K';
                } else {
                    displayNumber = displayNumber.toLocaleString();
                }

                element.textContent = displayNumber + suffix;
            }, duration / 100);
        }

        // Compare feature using mock apartments
        function initializeCompareFeature() {
            const compareCheckboxes = document.querySelectorAll('.compare-checkbox');
            const compareBar = document.getElementById('compareBar');
            const compareSelectedList = document.getElementById('compareSelectedList');
            const compareCountLabel = document.getElementById('compareCountLabel');
            const clearCompareBtn = document.getElementById('clearCompareBtn');
            const openCompareModalBtn = document.getElementById('openCompareModalBtn');
            const compareModalEl = document.getElementById('compareModal');
            const compareModal = compareModalEl ? new bootstrap.Modal(compareModalEl) : null;

            if (!compareCheckboxes.length || !compareBar) {
                return;
            }

            const selectedIds = new Set();

            function updateBar() {
                const ids = Array.from(selectedIds);
                if (!ids.length) {
                    compareBar.classList.add('d-none');
                    compareSelectedList.innerHTML = '';
                    if (compareCountLabel) compareCountLabel.textContent = '';
                    return;
                }

                compareBar.classList.remove('d-none');
                compareSelectedList.innerHTML = '';

                ids.slice(0, 3).forEach(id => {
                    const apt = COMPARE_APARTMENTS.find(a => a.id === Number(id));
                    if (!apt) return;
                    const chip = document.createElement('div');
                    chip.className = 'badge bg-light text-dark border d-flex align-items-center gap-1';
                    chip.innerHTML = `<span>${apt.name}</span>`;
                    compareSelectedList.appendChild(chip);
                });

                if (compareCountLabel) {
                    compareCountLabel.textContent = `เลือกแล้ว ${ids.length} ห้อง (สูงสุด 3 ห้อง)`;
                }
            }

            compareCheckboxes.forEach(cb => {
                cb.addEventListener('change', function () {
                    const id = this.value;
                    if (this.checked) {
                        if (selectedIds.size >= 3) {
                            this.checked = false;
                            alert('สามารถเปรียบเทียบได้สูงสุด 3 ห้องพร้อมกัน');
                            return;
                        }
                        selectedIds.add(id);
                    } else {
                        selectedIds.delete(id);
                    }
                    updateBar();
                });
            });

            if (clearCompareBtn) {
                clearCompareBtn.addEventListener('click', function () {
                    selectedIds.clear();
                    compareCheckboxes.forEach(cb => cb.checked = false);
                    updateBar();
                });
            }

            if (openCompareModalBtn && compareModal) {
                openCompareModalBtn.addEventListener('click', function () {
                    const ids = Array.from(selectedIds).slice(0, 3).map(id => Number(id));
                    if (!ids.length) {
                        alert('กรุณาเลือกห้องอย่างน้อย 2 ห้องเพื่อเปรียบเทียบ');
                        return;
                    }
                    fillCompareTable(ids);
                    compareModal.show();
                });
            }
        }

        function fillCompareTable(ids) {
            const table = document.getElementById('compareTable');
            if (!table) return;

            const apartments = ids.map(id => COMPARE_APARTMENTS.find(a => a.id === Number(id)) || null);

            const rows = table.querySelectorAll('tbody tr');
            rows.forEach((row, rowIndex) => {
                const header = row.querySelector('th').textContent.trim();
                const cells = row.querySelectorAll('td');

                apartments.forEach((apt, idx) => {
                    const cell = cells[idx];
                    if (!cell) return;
                    if (!apt) {
                        cell.textContent = '-';
                        return;
                    }

                    switch (header) {
                        case 'ชื่อ':
                            cell.textContent = apt.name || '';
                            break;
                        case 'ประเภท':
                            cell.textContent = apt.type === 'condo' ? 'คอนโด' : 'อพาร์ตเม้นท์';
                            break;
                        case 'ทำเล':
                            cell.textContent = `${apt.district || ''}, ${apt.province || ''}`;
                            break;
                        case 'ราคา':
                            let priceText = '';
                            if (apt.price_monthly) {
                                priceText = `฿${Number(apt.price_monthly).toLocaleString()}/เดือน`;
                            } else if (apt.price_daily) {
                                priceText = `฿${Number(apt.price_daily).toLocaleString()}/คืน`;
                            }
                            cell.textContent = priceText;
                            break;
                        case 'ประเภทการเช่า':
                            cell.textContent = Array.isArray(apt.rental_type) ? apt.rental_type.join(', ') : '';
                            break;
                        case 'เลี้ยงสัตว์ได้':
                            cell.textContent = apt.pet_friendly ? 'ใช่' : 'ไม่ใช่';
                            break;
                        case 'คะแนนรีวิว':
                            cell.textContent = typeof apt.rating === 'number' ? apt.rating.toFixed(1) : '-';
                            break;
                        case 'สิ่งอำนวยความสะดวก':
                            cell.textContent = Array.isArray(apt.amenities) ? apt.amenities.join(', ') : '';
                            break;
                        default:
                            cell.textContent = '';
                    }
                });
            });
        }

        // Card interaction effects
        function initializeCardInteractions() {
            // Room card hover effects
            const roomCards = document.querySelectorAll('.room-card');
            roomCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px) scale(1.02)';
                });

                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });

            // Add wishlist functionality
            const heartButtons = document.querySelectorAll('.bi-heart');
            heartButtons.forEach(heart => {
                heart.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    this.classList.toggle('bi-heart');
                    this.classList.toggle('bi-heart-fill');
                    this.classList.toggle('text-danger');

                    // Add animation
                    this.style.transform = 'scale(1.3)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 200);
                });
            });
        }

        // Smooth scroll for internal links
        document.addEventListener('click', function(e) {
            if (e.target.matches('a[href^="#"]')) {
                e.preventDefault();
                const target = document.querySelector(e.target.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });

        // Add loading animation for images (ปิดใช้งาน เพื่อลดการกระพริบของรูป)
        function addImageLoadingEffect() {
            // no-op
        }

        // ไม่เรียกใช้ effect นี้แล้วเพื่อลดการกระพริบของรูป
        // addImageLoadingEffect();



        // Modern Search Functions
        // Toggle rental type and update filters
        // NOTE: This is legacy logic for old radio buttons / selects.
        // On pages that don't have those elements (เช่น search-box แบบใหม่บนหน้าแรก)
        // ฟังก์ชันนี้จะ return ทันทีเพื่อไม่ให้เกิด error.
        function updateRentalType() {
            const dailyRadio = document.getElementById('daily-type');
            const monthlyRadio = document.getElementById('monthly-type');
            const rentalTypeInput = document.getElementById('rental_type_input');
            const priceSelect = document.getElementById('price_select');
            const roomTypeSelect = document.getElementById('room_type_select');

            // ถ้าไม่มี radio เก่าเลย หรือไม่มี hidden rental_type ให้หยุดทำงาน
            if (!rentalTypeInput || (!dailyRadio && !monthlyRadio)) {
                return;
            }

            const isDaily = dailyRadio && dailyRadio.checked;
            const isMonthly = monthlyRadio && monthlyRadio.checked;

            if (isDaily) {
                rentalTypeInput.value = 'daily';
                // Show daily prices
                document.querySelectorAll('.daily-price').forEach(opt => opt.style.display = '');
                document.querySelectorAll('.monthly-price').forEach(opt => opt.style.display = 'none');
                // Show daily room types
                document.querySelectorAll('.daily-room').forEach(opt => opt.style.display = '');
                document.querySelectorAll('.monthly-room').forEach(opt => opt.style.display = 'none');
            } else if (isMonthly) {
                rentalTypeInput.value = 'monthly';
                // Show monthly prices
                document.querySelectorAll('.daily-price').forEach(opt => opt.style.display = 'none');
                document.querySelectorAll('.monthly-price').forEach(opt => opt.style.display = '');
                // Show monthly room types
                document.querySelectorAll('.daily-room').forEach(opt => opt.style.display = 'none');
                document.querySelectorAll('.monthly-room').forEach(opt => opt.style.display = '');
            }

            // Reset selections (ถ้ามี select เหล่านี้ในหน้า)
            if (priceSelect) {
                priceSelect.value = '';
            }
            if (roomTypeSelect) {
                roomTypeSelect.value = '';
            }
        }

        // Toggle more filters section
        function toggleMoreFilters() {
            const section = document.getElementById('more-filters-section');
            const icon = document.getElementById('more-filters-icon');

            if (section.style.display === 'none' || !section.style.display) {
                section.style.display = 'block';
                icon.classList.remove('bi-chevron-down');
                icon.classList.add('bi-chevron-up');
            } else {
                section.style.display = 'none';
                icon.classList.remove('bi-chevron-up');
                icon.classList.add('bi-chevron-down');
            }
        }

        // Clear all filters
        function clearFilters() {
            const form = document.querySelector('.modern-search-form');
            // Reset text inputs
            form.querySelector('input[name="keyword"]').value = '';
            // Reset selects
            form.querySelectorAll('select').forEach(select => select.value = '');
            // Reset radio buttons
            form.querySelectorAll('input[type="radio"][name="distance"]').forEach(radio => {
                radio.checked = radio.value === '';
            });
            // Reset checkboxes
            form.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = false;
            });
        }

        // Initialize rental type listeners (legacy)
        document.addEventListener('DOMContentLoaded', function() {
            const dailyRadio = document.getElementById('daily-type');
            const monthlyRadio = document.getElementById('monthly-type');

            if (dailyRadio) {
                dailyRadio.addEventListener('change', updateRentalType);
            }
            if (monthlyRadio) {
                monthlyRadio.addEventListener('change', updateRentalType);
            }

            // เรียกใช้เฉพาะเมื่อมี radio เก่าอย่างน้อย 1 อัน
            if (dailyRadio || monthlyRadio) {
                updateRentalType();
            }
        });

        // Add search form validation

        const searchForm = document.querySelector('.modern-search-form, .search-form');
        if (searchForm) {
            searchForm.addEventListener('submit', function(e) {
                const keyword = this.querySelector('input[name="keyword"]').value.trim();
                const province = this.querySelector('select[name="province"]').value;
                const maxPrice = this.querySelector('input[name="max_price"]').value;

                if (!keyword && !province && !maxPrice) {
                    e.preventDefault();
                    alert('Please enter at least one search criteria');
                    return false;
                }
            });
        }

        // Room Comparison System
        let comparisonMode = false;
        let selectedRooms = [];
        const MAX_COMPARE = 3;

        function toggleComparisonMode() {
            comparisonMode = !comparisonMode;
            const btn = document.querySelector('.btn-comparison');
            const message = document.getElementById('comparisonModeMessage');
            
            if (comparisonMode) {
                btn.classList.add('active');
                message.style.display = 'block';
            } else {
                btn.classList.remove('active');
                message.style.display = 'none';
            }
            
            updateComparisonUI();
        }

        function toggleRoomSelection(roomId, roomData) {
            if (!comparisonMode) {
                toggleComparisonMode();
            }
            
            const index = selectedRooms.findIndex(r => r.id === roomId);
            
            if (index > -1) {
                selectedRooms.splice(index, 1);
            } else {
                if (selectedRooms.length >= MAX_COMPARE) {
                    alert(`คุณสามารถเปรียบเทียบได้สูงสุด ${MAX_COMPARE} ห้องเท่านั้น`);
                    return;
                }
                selectedRooms.push({id: roomId, ...roomData});
            }
            
            updateComparisonUI();
        }

        function updateComparisonUI() {
            const countBadge = document.getElementById('comparisonCount');
            countBadge.textContent = selectedRooms.length;
            countBadge.style.display = selectedRooms.length > 0 ? 'inline-block' : 'none';
            
            // Update card selections
            document.querySelectorAll('.room-card, .product-card').forEach(card => {
                const roomId = card.dataset.roomId;
                const checkbox = card.querySelector('.comparison-checkbox');
                if (checkbox) {
                    checkbox.checked = selectedRooms.some(r => r.id == roomId);
                }
            });
        }

        function showComparison() {
            if (selectedRooms.length < 2) {
                alert('กรุณาเลือกอย่างน้อย 2 ห้องเพื่อเปรียบเทียบ');
                return;
            }
            
            // Create comparison modal
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.id = 'comparisonModal';
            modal.innerHTML = `
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="bi bi-arrow-left-right me-2"></i>เปรียบเทียบห้อง</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table table-bordered comparison-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 200px;">รายการ</th>
                                            ${selectedRooms.map(room => `
                                                <th class="text-center">
                                                    <div class="fw-bold">${room.name}</div>
                                                    <div class="text-primary fs-5 mt-2">฿${room.price?.toLocaleString()}</div>
                                                </th>
                                            `).join('')}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${generateComparisonRows()}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
            
            modal.addEventListener('hidden.bs.modal', () => {
                modal.remove();
            });
        }

        function generateComparisonRows() {
            const features = [
                {label: 'ประเภท', key: 'rental_type', format: v => Array.isArray(v) ? v.join(', ') : v},
                {label: 'เขต', key: 'district'},
                {label: 'จังหวัด', key: 'province'},
                {label: 'คะแนน', key: 'rating', format: v => v ? `⭐ ${v}/5` : '-'},
                {label: 'WiFi', key: 'wifi', isBool: true},
                {label: 'แอร์', key: 'air_conditioner', isBool: true},
                {label: 'ที่จอดรถ', key: 'parking', isBool: true},
                {label: 'เลี้ยงสัตว์ได้', key: 'pet_friendly', isBool: true},
                {label: 'สระว่ายน้ำ', key: 'pool', isBool: true},
                {label: 'ฟิตเนส', key: 'gym', isBool: true},
            ];
            
            return features.map(feature => {
                const values = selectedRooms.map(room => {
                    let value = room[feature.key];
                    
                    if (feature.isBool) {
                        return value ? '✓' : '✗';
                    }
                    
                    if (feature.format) {
                        return feature.format(value) || '-';
                    }
                    
                    return value || '-';
                });
                
                // Check which rooms have this feature (for highlighting)
                const hasFeature = selectedRooms.map(room => {
                    if (feature.isBool) {
                        return room[feature.key] === true;
                    }
                    return room[feature.key] != null && room[feature.key] !== '';
                });
                
                return `
                    <tr>
                        <td class="fw-semibold">${feature.label}</td>
                        ${values.map((val, idx) => `
                            <td class="text-center ${hasFeature[idx] && feature.isBool ? 'table-success fw-bold' : ''}">
                                ${val}
                            </td>
                        `).join('')}
                    </tr>
                `;
            }).join('');
        }

        function clearComparison() {
            selectedRooms = [];
            updateComparisonUI();
        }

        // Advanced Filters Toggle
        function toggleAdvancedFilters() {
            const section = document.getElementById('advancedFiltersSection');
            const btn = document.querySelector('.btn-advanced-filters');
            const icon = document.getElementById('advancedFilterIcon');
            
            if (section.style.display === 'none') {
                section.style.display = 'block';
                btn.classList.add('active');
                icon.classList.remove('bi-chevron-down');
                icon.classList.add('bi-chevron-up');
            } else {
                section.style.display = 'none';
                btn.classList.remove('active');
                icon.classList.remove('bi-chevron-up');
                icon.classList.add('bi-chevron-down');
            }
        }

        // Clear all filters
        function clearFilters() {
            document.querySelectorAll('#advancedFiltersSection input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = false;
            });
        }

        // Add fade-in animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
        `;
        document.head.appendChild(style);

        // Update scroll arrows visibility and fade effects
        function updateCategoryScrollState() {
            const wrapper = document.getElementById('categoryFilterWrapper');
            const scrollWrapper = document.getElementById('categoryScrollWrapper');
            const rightArrow = document.querySelector('.category-scroll-arrow.right');
            
            if (!wrapper || !scrollWrapper || !rightArrow) return;
            
            const scrollLeft = wrapper.scrollLeft;
            const scrollWidth = wrapper.scrollWidth;
            const clientWidth = wrapper.clientWidth;
            const maxScroll = scrollWidth - clientWidth;
            
            // Show/hide right arrow only
            if (scrollLeft < maxScroll - 10) {
                rightArrow.classList.remove('hidden');
                rightArrow.classList.add('visible');
                scrollWrapper.classList.add('show-right-fade');
            } else {
                rightArrow.classList.add('hidden');
                rightArrow.classList.remove('visible');
                scrollWrapper.classList.remove('show-right-fade');
            }
        }

        // Initialize category scroll on page load
        document.addEventListener('DOMContentLoaded', function() {
            const wrapper = document.getElementById('categoryFilterWrapper');
            
            if (wrapper) {
                // Update on scroll
                wrapper.addEventListener('scroll', updateCategoryScrollState);
                
                // Update on window resize
                window.addEventListener('resize', updateCategoryScrollState);
                
                // Initial update
                setTimeout(updateCategoryScrollState, 100);
            }
        });
    </script>
</body>
</html>
