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

// Handle search-box filters using the same mock data
$searchResults = [];
$hasSearch = false;
if ($_GET) {
    $keyword       = trim($_GET['keyword'] ?? '');
    $rentalType    = $_GET['rental_type'] ?? '';
    $priceRange    = $_GET['price_range'] ?? '';
    $petFriendly   = isset($_GET['pet_friendly']) && $_GET['pet_friendly'] === '1';
    $province      = $_GET['province'] ?? '';

    $hasSearch = $keyword !== '' || $rentalType !== '' || $priceRange !== '' || $petFriendly || $province !== '';

    if ($hasSearch) {
        $all = mock_get_all_apartments();

        $minPrice = null;
        $maxPrice = null;
        if ($priceRange && strpos($priceRange, '-') !== false) {
            [$minStr, $maxStr] = explode('-', $priceRange, 2);
            $minPrice = is_numeric($minStr) ? (int) $minStr : null;
            $maxPrice = is_numeric($maxStr) ? (int) $maxStr : null;
        }

        $searchResults = array_values(array_filter($all, function ($apt) use (
            $keyword,
            $rentalType,
            $province,
            $petFriendly,
            $minPrice,
            $maxPrice
        ) {
            // rental_type filter
            if ($rentalType && !in_array($rentalType, $apt['rental_type'], true)) {
                return false;
            }

            // province filter
            if ($province && ($apt['province'] ?? '') !== $province) {
                return false;
            }

            // pet friendly filter
            if ($petFriendly && empty($apt['pet_friendly'])) {
                return false;
            }

            // keyword filter (name, district, province, description)
            if ($keyword !== '') {
                $haystack = ($apt['name'] ?? '') . ' ' . ($apt['district'] ?? '') . ' ' . ($apt['province'] ?? '') . ' ' . ($apt['description'] ?? '');
                $haystackLower = function_exists('mb_strtolower') ? mb_strtolower($haystack, 'UTF-8') : strtolower($haystack);
                $needleLower   = function_exists('mb_strtolower') ? mb_strtolower($keyword, 'UTF-8') : strtolower($keyword);

                if (strpos($haystackLower, $needleLower) === false) {
                    return false;
                }
            }

            // price range filter
            if ($minPrice !== null || $maxPrice !== null) {
                $priceField = $rentalType === 'daily' ? 'price_daily' : 'price_monthly';
                $price = $apt[$priceField] ?? null;
                if ($price === null) {
                    return false;
                }
                if ($minPrice !== null && $price < $minPrice) {
                    return false;
                }
                if ($maxPrice !== null && $price > $maxPrice) {
                    return false;
                }
            }

            return true;
        }));

        if (!empty($searchResults)) {
            usort($searchResults, function ($a, $b) {
                return ($b['rating'] <=> $a['rating']) ?: ($b['views'] <=> $a['views']);
            });
        }
    }
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

        /* Search Form Styles */
        .search-tabs {
            display: flex;
            gap: 8px;
            padding: 16px 16px 0 16px;
            background: white;
        }

        .search-tab {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border: none;
            background: transparent;
            color: var(--medium-gray);
            font-weight: 600;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-tab:hover {
            color: var(--primary-color);
        }

        .search-tab.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }

        .search-form-main {
            padding: 24px;
        }

        .search-input-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 12px;
            align-items: end;
        }

        .search-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .search-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--dark-gray);
        }

        .input-with-icon {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            background: white;
            transition: all 0.3s ease;
        }

        .input-with-icon:focus-within {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .input-with-icon i {
            font-size: 1.1rem;
        }

        .search-input {
            flex: 1;
            border: none;
            outline: none;
            font-size: 0.95rem;
            color: var(--dark-gray);
        }

        .search-input::placeholder {
            color: var(--medium-gray);
        }

        .btn-search-main {
            padding: 12px 32px;
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            white-space: nowrap;
        }

        .btn-search-main:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4);
        }

        .btn-comparison {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            font-weight: 600;
            padding: 10px 24px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-comparison:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-comparison.active {
            background: var(--primary-color);
            color: white;
        }

        .btn-advanced-filters {
            background: transparent;
            border: 2px solid var(--medium-gray);
            color: var(--medium-gray);
            font-weight: 600;
            padding: 10px 24px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-advanced-filters:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            background: rgba(59, 130, 246, 0.05);
        }

        .btn-advanced-filters.active {
            border-color: var(--primary-color);
            color: var(--primary-color);
            background: rgba(59, 130, 246, 0.1);
        }

        .facility-checkbox-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            background: white;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .facility-checkbox-item:hover {
            border-color: var(--primary-color);
            background: rgba(59, 130, 246, 0.02);
        }

        .facility-checkbox-item input[type="checkbox"]:checked + label,
        .facility-checkbox-item:has(input[type="checkbox"]:checked) {
            border-color: var(--primary-color);
            background: rgba(59, 130, 246, 0.08);
        }

        .facility-checkbox-item .form-check-input {
            width: 20px;
            height: 20px;
            border: 2px solid #cbd5e0;
            cursor: pointer;
            margin: 0;
        }

        .facility-checkbox-item .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .facility-checkbox-item .form-check-label {
            cursor: pointer;
            margin: 0;
            font-weight: 500;
            color: var(--dark-gray);
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
        }

        .facility-checkbox-item .form-check-label i {
            font-size: 1.1rem;
        }

        .advanced-filters-section {
            padding: 20px;
            background: var(--light-gray);
            border-radius: 12px;
            margin-top: 16px;
        }

        /* Inline filters under main search */
        .search-filters-inline {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 8px;
        }

        .search-filters-inline .filter-item {
            flex: 1 1 200px;
            min-width: 0;
        }

        .search-filters-inline .search-input,
        .search-filters-inline .form-select {
            width: 100%;
        }

        .pet-filter-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 999px;
            border: 2px solid #e2e8f0;
            background: white;
            cursor: pointer;
            transition: all 0.25s ease;
            font-size: 0.9rem;
            color: var(--medium-gray);
            user-select: none;
        }

        .pet-filter-chip i {
            color: var(--primary-color);
        }

        .pet-filter-input {
            display: none;
        }

        .pet-filter-input:checked + .pet-filter-chip {
            border-color: var(--primary-color);
            background: rgba(59, 130, 246, 0.06);
            color: var(--primary-color);
        }

        /* Guest Modal Styles */
        .guest-counter {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .guest-counter:last-child {
            border-bottom: none;
        }

        .counter-controls {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .btn-counter {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 2px solid #e2e8f0;
            background: white;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-counter:hover {
            border-color: var(--primary-color);
            background: var(--primary-color);
            color: white;
        }

        .counter-value {
            font-weight: 600;
            font-size: 1.1rem;
            min-width: 30px;
            text-align: center;
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

        /* Hero Banner Section */
        .hero-banner-section {
            position: relative;
            margin-bottom: 120px;
        }

        .hero-banner-carousel {
            position: relative;
            height: 500px;
            overflow: hidden;
        }

        .hero-banner-img {
            height: 500px;
            width: 100%;
            object-fit: cover;
            object-position: center;
            filter: brightness(0.75);
            transition: transform 0.5s ease;
        }

.carousel-item.active .hero-banner-img {
            animation: none;
        }

        .carousel-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                to bottom,
                rgba(0, 0, 0, 0.3) 0%,
                rgba(0, 0, 0, 0.4) 50%,
                rgba(0, 0, 0, 0.5) 100%
            );
            pointer-events: none;
        }

        /* Carousel Indicators */
        .carousel-indicators {
            bottom: 140px;
            margin-bottom: 0;
            z-index: 15;
        }

        .carousel-indicators [data-bs-target] {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.5);
            border: 2px solid rgba(255, 255, 255, 0.8);
            margin: 0 6px;
            transition: all 0.3s ease;
        }

        .carousel-indicators .active {
            width: 30px;
            border-radius: 5px;
            background-color: white;
        }

        /* Modern Carousel Controls */
        .carousel-control-prev,
        .carousel-control-next {
            width: 60px;
            height: 60px;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 15;
        }

        .hero-banner-carousel:hover .carousel-control-prev,
        .hero-banner-carousel:hover .carousel-control-next {
            opacity: 1;
        }

        .carousel-control-prev {
            left: 30px;
        }

        .carousel-control-next {
            right: 30px;
        }

        .carousel-control-prev:hover {
            transform: translateY(-50%) translateX(-5px);
        }

        .carousel-control-next:hover {
            transform: translateY(-50%) translateX(5px);
        }

        /* Custom Modern Arrow Icons */
        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            position: relative;
        }

        .carousel-control-prev-icon::before,
        .carousel-control-next-icon::before {
            content: '';
            width: 14px;
            height: 14px;
            border-left: 3px solid white;
            border-bottom: 3px solid white;
            position: absolute;
            transition: all 0.3s ease;
        }

        .carousel-control-prev-icon::before {
            transform: rotate(45deg);
            left: 24px;
        }

        .carousel-control-next-icon::before {
            transform: rotate(-135deg);
            right: 24px;
        }

        .carousel-control-prev:hover .carousel-control-prev-icon,
        .carousel-control-next:hover .carousel-control-next-icon {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            transform: scale(1.1);
        }

        .carousel-control-prev:hover .carousel-control-prev-icon::before,
        .carousel-control-next:hover .carousel-control-next-icon::before {
            border-color: #ffffff;
            filter: drop-shadow(0 0 8px rgba(255, 255, 255, 0.5));
        }

        /* Remove default Bootstrap backgrounds */
        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background-image: none !important;
        }

        /* Search Box Overlay */
        .search-box-overlay {
            position: absolute;
            bottom: -80px;
            left: 0;
            right: 0;
            z-index: 20;
        }

        .search-box-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .search-box-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .hero-banner-carousel {
                height: 450px;
            }
            
            .hero-banner-img {
                height: 450px;
            }
        }

        @media (max-width: 992px) {
            .hero-banner-carousel {
                height: 400px;
            }
            
            .hero-banner-img {
                height: 400px;
            }
            
            .search-box-overlay {
                bottom: -100px;
            }
            
            .hero-banner-section {
                margin-bottom: 140px;
            }
        }

        @media (max-width: 768px) {
            .hero-banner-carousel {
                height: 350px;
            }
            
            .hero-banner-img {
                height: 350px;
            }
            
            .carousel-indicators {
                bottom: 120px;
            }
            
            .search-box-overlay {
                bottom: -120px;
            }
            
            .hero-banner-section {
                margin-bottom: 160px;
            }
            
            .carousel-control-prev,
            .carousel-control-next {
                width: 50px;
                height: 50px;
            }
            
            .carousel-control-prev {
                left: 20px;
            }
            
            .carousel-control-next {
                right: 20px;
            }
            
            .carousel-control-prev-icon,
            .carousel-control-next-icon {
                width: 50px;
                height: 50px;
            }
            
            .carousel-control-prev-icon::before,
            .carousel-control-next-icon::before {
                width: 12px;
                height: 12px;
                border-width: 2.5px;
            }
            
            .carousel-control-prev-icon::before {
                left: 20px;
            }
            
            .carousel-control-next-icon::before {
                right: 20px;
            }
        }

        @media (max-width: 576px) {
            .hero-banner-carousel {
                height: 280px;
            }
            
            .hero-banner-img {
                height: 280px;
            }
            
            .carousel-indicators {
                bottom: 100px;
            }
            
            .carousel-indicators [data-bs-target] {
                width: 8px;
                height: 8px;
                margin: 0 4px;
            }
            
            .carousel-indicators .active {
                width: 20px;
            }
            
            .carousel-control-prev,
            .carousel-control-next {
                width: 44px;
                height: 44px;
            }
            
            .carousel-control-prev {
                left: 10px;
            }
            
            .carousel-control-next {
                right: 10px;
            }
            
            .carousel-control-prev-icon,
            .carousel-control-next-icon {
                width: 44px;
                height: 44px;
                border-width: 1.5px;
            }
            
            .carousel-control-prev-icon::before,
            .carousel-control-next-icon::before {
                width: 10px;
                height: 10px;
                border-width: 2px;
            }
            
            .carousel-control-prev-icon::before {
                left: 18px;
            }
            
            .carousel-control-next-icon::before {
                right: 18px;
            }
            
            /* Always show controls on mobile for better UX */
            .carousel-control-prev,
            .carousel-control-next {
                opacity: 0.7;
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

    <!-- Hero Background Carousel with Search Box -->
    <section class="hero-banner-section">
        <div class="container-fluid p-0">
            <!-- Background Image Carousel -->
            <div id="heroBannerCarousel" class="carousel slide hero-banner-carousel" data-bs-ride="carousel" data-bs-interval="4000">
                <!-- Carousel Indicators -->
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#heroBannerCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                    <button type="button" data-bs-target="#heroBannerCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                    <button type="button" data-bs-target="#heroBannerCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
                    <button type="button" data-bs-target="#heroBannerCarousel" data-bs-slide-to="3" aria-label="Slide 4"></button>
                    <button type="button" data-bs-target="#heroBannerCarousel" data-bs-slide-to="4" aria-label="Slide 5"></button>
                </div>
                
                <!-- Carousel Slides -->
                <div class="carousel-inner">
                    <!-- Slide 1: Modern Hotel Room -->
                    <div class="carousel-item active">
                        <img src="https://images.unsplash.com/photo-1566665797739-1674de7a421a?w=1920&h=600&fit=crop&q=80" 
                             class="d-block w-100 hero-banner-img" 
                             alt="Modern Hotel Room">
                        <div class="carousel-overlay"></div>
                    </div>
                    
                    <!-- Slide 2: Luxury Bedroom -->
                    <div class="carousel-item">
                        <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=1920&h=600&fit=crop&q=80" 
                             class="d-block w-100 hero-banner-img" 
                             alt="Luxury Bedroom">
                        <div class="carousel-overlay"></div>
                    </div>
                    
                    <!-- Slide 3: Cozy Apartment -->
                    <div class="carousel-item">
                        <img src="https://media.istockphoto.com/id/2180053464/photo/living-room-luxury-house-in-modern-style-white-sofa-with-pool-and-sea-view-3d-rendering.webp?a=1&b=1&s=612x612&w=0&k=20&c=6fN2N6Wf-B4SdCdc9gw9t6C9em12iazuP4z2mLVncX8=" 
                             class="d-block w-100 hero-banner-img" 
                             alt="Cozy Apartment">
                        <div class="carousel-overlay"></div>
                    </div>
                    
                    <!-- Slide 4: Modern Living Space -->
                    <div class="carousel-item">
                        <img src="https://images.unsplash.com/photo-1574362848149-11496d93a7c7?q=80&w=1084&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" 
                             class="d-block w-100 hero-banner-img" 
                             alt="Modern Living Space">
                        <div class="carousel-overlay"></div>
                    </div>
                    
                    <!-- Slide 5: Elegant Hotel Suite -->
                    <div class="carousel-item">
                        <img src="https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=1920&h=600&fit=crop&q=80" 
                             class="d-block w-100 hero-banner-img" 
                             alt="Elegant Hotel Suite">
                        <div class="carousel-overlay"></div>
                    </div>
                </div>

                <!-- Carousel Controls -->
                <button class="carousel-control-prev" type="button" data-bs-target="#heroBannerCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#heroBannerCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>

        <!-- Overlapping Search Box -->
        <div class="search-box-overlay">
            <div class="container">
                <div class="search-box-wrapper">
                    <div class="search-box-card">
                        <div class="search-tabs">
                            <button type="button" class="search-tab active" data-tab="monthly" data-rental-type="monthly">
                                <i class="bi bi-building"></i>
                                <span>อพาร์ตเม้นรายเดือน</span>
                            </button>
                            <button type="button" class="search-tab" data-tab="daily" data-rental-type="daily">
                                <i class="bi bi-sun"></i>
                                <span>อพาร์ตเม้นรายวัน</span>
                            </button>
                        </div>

                        <form action="index.php" method="get" class="search-form-main">
                            <input type="hidden" name="rental_type" id="rental_type_input" value="monthly">
                            <!-- Location Input -->
                            <div class="search-input-row">
                                <div class="search-field search-field-location">
                                    <label class="search-label">ชื่อเมือง สถานที่ หรืออพาร์ตเมนต์</label>
                                    <div class="input-with-icon">
                                        <i class="bi bi-geo-alt-fill text-primary"></i>
                                        <input type="text" 
                                               name="keyword" 
                                               class="search-input" 
                                               placeholder="เมือง, อพาร์ตเมนต์, วิลล่า หรือสถานที่"
                                               autocomplete="off">
                                    </div>
                                </div>

                                <!-- Search Button -->
                                <div class="search-field search-field-button">
                                    <button type="submit" class="btn-search-main">
                                        <i class="bi bi-search me-2"></i>
                                        ค้นหา
                                    </button>
                                </div>
                            </div>

                            <!-- Inline Filters: Price range + Pet friendly -->
                            <div class="search-filters-inline mt-2">
                                <div class="filter-item">
                                    <label class="search-label">ช่วงราคา</label>
                                    <select name="price_range" class="form-select">
                                        <option value="">ทุกช่วงราคา</option>
                                        <option value="0-5000">ไม่เกิน 5,000 บาท/เดือน</option>
                                        <option value="5000-8000">5,000 - 8,000 บาท/เดือน</option>
                                        <option value="8000-12000">8,000 - 12,000 บาท/เดือน</option>
                                        <option value="12000-20000">12,000 - 20,000 บาท/เดือน</option>
                                        <option value="20000-999999">มากกว่า 20,000 บาท/เดือน</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Advanced Filters Button -->
                            <div class="text-center mt-3">
                                <button type="button" class="btn-comparison" onclick="toggleComparisonMode()">
                                    <i class="bi bi-arrow-left-right"></i>
                                    เปรียบเทียบห้อง
                                    <span id="comparisonCount" class="badge bg-primary rounded-pill ms-2" style="display: none;">0</span>
                                </button>
                                
                                <button type="button" class="btn-advanced-filters ms-2" onclick="toggleAdvancedFilters()">
                                    <i class="bi bi-sliders"></i>
                                    ตัวกรองเพิ่มเติม
                                    <i class="bi bi-chevron-down ms-1" id="advancedFilterIcon"></i>
                                </button>
                            </div>
                                </button>
                            </div>

                            <!-- Advanced Filters Section -->
                            <div id="advancedFiltersSection" class="advanced-filters-section" style="display: none;">
                                <div class="row g-3">
                                    <div class="col-md-3 col-sm-6">
                                        <div class="facility-checkbox-item">
                                            <input type="checkbox" class="form-check-input" id="filter_wifi" name="facilities[]" value="wifi">
                                            <label class="form-check-label" for="filter_wifi">
                                                <i class="bi bi-wifi text-primary"></i> WiFi
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <div class="facility-checkbox-item">
                                            <input type="checkbox" class="form-check-input" id="filter_air" name="facilities[]" value="air_conditioner">
                                            <label class="form-check-label" for="filter_air">
                                                <i class="bi bi-snow text-info"></i> เครื่องปรับอากาศ
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <div class="facility-checkbox-item">
                                            <input type="checkbox" class="form-check-input" id="filter_parking" name="facilities[]" value="parking">
                                            <label class="form-check-label" for="filter_parking">
                                                <i class="bi bi-car-front text-success"></i> ที่จอดรถ
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <div class="facility-checkbox-item">
                                            <input type="checkbox" class="form-check-input" id="filter_pool" name="facilities[]" value="pool">
                                            <label class="form-check-label" for="filter_pool">
                                                <i class="bi bi-water text-primary"></i> สระว่ายน้ำ
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <div class="facility-checkbox-item">
                                            <input type="checkbox" class="form-check-input" id="filter_gym" name="facilities[]" value="gym">
                                            <label class="form-check-label" for="filter_gym">
                                                <i class="bi bi-heart-pulse text-danger"></i> ฟิตเนส
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <div class="facility-checkbox-item">
                                            <input type="checkbox" class="form-check-input" id="filter_elevator" name="facilities[]" value="elevator">
                                            <label class="form-check-label" for="filter_elevator">
                                                <i class="bi bi-arrow-up-square text-secondary"></i> ลิฟต์
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <div class="facility-checkbox-item">
                                            <input type="checkbox" class="form-check-input" id="filter_security" name="facilities[]" value="security">
                                            <label class="form-check-label" for="filter_security">
                                                <i class="bi bi-shield-check text-warning"></i> รปภ. 24 ชม.
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <div class="facility-checkbox-item">
                                            <input type="checkbox" class="form-check-input" id="filter_cctv" name="facilities[]" value="cctv">
                                            <label class="form-check-label" for="filter_cctv">
                                                <i class="bi bi-camera-video text-dark"></i> กล้องวงจรปิด
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <div class="facility-checkbox-item">
                                            <input type="checkbox" class="form-check-input" id="filter_laundry" name="facilities[]" value="laundry">
                                            <label class="form-check-label" for="filter_laundry">
                                                <i class="bi bi-moisture text-info"></i> เครื่องซักผ้า
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <div class="facility-checkbox-item">
                                            <input type="checkbox" class="form-check-input" id="filter_kitchen" name="facilities[]" value="kitchen">
                                            <label class="form-check-label" for="filter_kitchen">
                                                <i class="bi bi-egg-fried text-warning"></i> ครัว
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <div class="facility-checkbox-item">
                                            <input type="checkbox" class="form-check-input" id="filter_furniture" name="facilities[]" value="furniture">
                                            <label class="form-check-label" for="filter_furniture">
                                                <i class="bi bi-house-door text-success"></i> เฟอร์นิเจอร์
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <div class="facility-checkbox-item">
                                            <input type="checkbox" class="form-check-input" id="filter_balcony" name="facilities[]" value="balcony">
                                            <label class="form-check-label" for="filter_balcony">
                                                <i class="bi bi-building text-primary"></i> ระเบียง
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-end mt-3">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearFilters()">
                                        <i class="bi bi-x-circle me-1"></i>ล้างตัวกรอง
                                    </button>
                                    <button type="submit" class="btn btn-sm btn-primary ms-2">
                                        <i class="bi bi-search me-1"></i>ค้นหา
                                    </button>
                                </div>
                            </div>

                            <!-- Comparison Mode Message -->
                            <div id="comparisonModeMessage" class="alert alert-info mt-3" style="display: none;">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>โหมดเปรียบเทียบ:</strong> เลือกห้องที่ต้องการเปรียบเทียบ (สูงสุด 3 ห้อง)
                                <button type="button" class="btn btn-sm btn-primary ms-3" onclick="showComparison()">
                                    <i class="bi bi-eye me-1"></i>ดูการเปรียบเทียบ
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="clearComparison()">
                                    <i class="bi bi-x-circle me-1"></i>ล้าง
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Guests Modal -->
    <div class="modal fade" id="guestsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">เลือกจำนวนผู้เข้าพักและห้อง</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Adults -->
                    <div class="guest-counter mb-3">
                        <div class="guest-label">
                            <strong>ผู้ใหญ่</strong>
                            <small class="text-muted">อายุ 18 ปีขึ้นไป</small>
                        </div>
                        <div class="counter-controls">
                            <button type="button" class="btn-counter" onclick="changeCount('adults', -1)">
                                <i class="bi bi-dash"></i>
                            </button>
                            <span id="adultsCount" class="counter-value">2</span>
                            <button type="button" class="btn-counter" onclick="changeCount('adults', 1)">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Children -->
                    <div class="guest-counter mb-3">
                        <div class="guest-label">
                            <strong>เด็ก</strong>
                            <small class="text-muted">อายุ 0-17 ปี</small>
                        </div>
                        <div class="counter-controls">
                            <button type="button" class="btn-counter" onclick="changeCount('children', -1)">
                                <i class="bi bi-dash"></i>
                            </button>
                            <span id="childrenCount" class="counter-value">0</span>
                            <button type="button" class="btn-counter" onclick="changeCount('children', 1)">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Rooms -->
                    <div class="guest-counter">
                        <div class="guest-label">
                            <strong>ห้อง</strong>
                        </div>
                        <div class="counter-controls">
                            <button type="button" class="btn-counter" onclick="changeCount('rooms', -1)">
                                <i class="bi bi-dash"></i>
                            </button>
                            <span id="roomsCount" class="counter-value">1</span>
                            <button type="button" class="btn-counter" onclick="changeCount('rooms', 1)">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary w-100" onclick="updateGuestsDisplay()" data-bs-dismiss="modal">
                        ยืนยัน
                    </button>
                </div>
            </div>
        </div>
    </div>

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

    <?php if ($hasSearch): ?>
    <!-- SEARCH RESULTS SECTION -->
    <section class="py-5">
        <div class="container position-relative">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="section-title mb-0">ผลการค้นหาอพาร์ตเม้นในกรุงเทพฯ</h2>
                <span class="text-muted">พบ <?php echo count($searchResults); ?> ห้อง</span>
            </div>

            <?php if (empty($searchResults)): ?>
                <div class="alert alert-info">ไม่พบห้องที่ตรงกับเงื่อนไขการค้นหา</div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($searchResults as $apt): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="card room-card h-100" data-apt-id="<?php echo (int) $apt['id']; ?>">
                            <div class="position-relative">
                                <img src="<?php echo htmlspecialchars($apt['thumbnail']); ?>"
                                     class="card-img-top"
                                     alt="<?php echo htmlspecialchars($apt['name']); ?>"
                                     style="height: 200px; object-fit: cover;"
                                     onerror="this.onerror=null; this.src='https://via.placeholder.com/400x300?text=No+Image'">
                                <span class="badge position-absolute top-0 end-0 m-2" style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);">
                                    <?php if (!empty($apt['price_monthly'])): ?>
                                        ฿<?php echo number_format($apt['price_monthly']); ?>/เดือน
                                    <?php elseif (!empty($apt['price_daily'])): ?>
                                        ฿<?php echo number_format($apt['price_daily']); ?>/คืน
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title fw-bold" style="color: var(--dark-gray);">
                                    <?php echo htmlspecialchars($apt['name']); ?>
                                </h5>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-geo-alt-fill" style="color: var(--medium-gray);"></i>
                                    <?php echo htmlspecialchars(($apt['district'] ?? '') . ', ' . ($apt['province'] ?? '')); ?>
                                </p>
                                <p class="card-text text-truncate" style="color: var(--medium-gray);">
                                    <?php echo htmlspecialchars($apt['description']); ?>
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
                                            'กล้องวงจรปิด' => 'bi-camera-video-fill',
                                            'รูมเซอร์วิส' => 'bi-bell-fill',
                                            'ห้องประชุม' => 'bi-briefcase-fill',
                                            'สวนส่วนกลาง' => 'bi-tree-fill',
                                            'Co-working space' => 'bi-laptop',
                                            'คาเฟ่' => 'bi-cup-straw',
                                            'เลี้ยงสัตว์ได้' => 'bi-heart-fill',
                                        ];
                                        $displayAmenities = array_slice($apt['amenities'], 0, 4);
                                        foreach ($displayAmenities as $amenity):
                                            $icon = $amenityIcons[$amenity] ?? 'bi-check-circle-fill';
                                        ?>
                                            <span><i class="<?php echo $icon; ?>" style="color: var(--primary-blue);"></i> <?php echo htmlspecialchars($amenity); ?></span>
                                        <?php endforeach; ?>
                                    </small>
                                </div>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small class="text-muted">
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <?php echo number_format($apt['rating'], 1); ?> ·
                                        <i class="bi bi-eye ms-1"></i> <?php echo (int) $apt['views']; ?>
                                    </small>
                                    <a href="mock-room-detail.php?id=<?php echo (int) $apt['id']; ?>" class="btn btn-sm btn-primary">
                                        ดูรายละเอียด
                                    </a>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input compare-checkbox" type="checkbox" value="<?php echo (int) $apt['id']; ?>" id="compare_<?php echo (int) $apt['id']; ?>">
                                    <label class="form-check-label small" for="compare_<?php echo (int) $apt['id']; ?>">
                                        เปรียบเทียบห้องนี้
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <!-- Compare Bar -->
            <div id="compareBar" class="card shadow-lg border-0 px-3 py-2 d-none" style="position:fixed; left:50%; transform:translateX(-50%); bottom:20px; z-index:1040; max-width:800px;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center flex-wrap gap-2" id="compareSelectedList">
                        <!-- filled by JS -->
                    </div>
                    <div class="d-flex align-items-center gap-2 ms-auto">
                        <small class="text-muted" id="compareCountLabel"></small>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="clearCompareBtn">
                            ล้างการเลือก
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" id="openCompareModalBtn">
                            <i class="bi bi-columns-gap"></i> เปรียบเทียบ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Category Filter Buttons Section -->
    <section class="category-filter-section">
        <div class="category-filter-container">
            <!-- Right Scroll Arrow Only -->
            <button class="category-scroll-arrow right" onclick="scrollCategories('right')" aria-label="Scroll right">
                <i class="bi bi-chevron-right"></i>
            </button>
            
            <div class="category-filter-scroll-wrapper" id="categoryScrollWrapper">
                <div class="category-filter-wrapper" id="categoryFilterWrapper">
                    <button class="category-filter-btn" data-category="near_school" onclick="filterByCategory('near_school')">
                        <i class="bi bi-book-fill" style="color: #f59e0b;"></i>
                        ที่พักใกล้โรงเรียน
                    </button>
                    <button class="category-filter-btn" data-category="near_transit" onclick="filterByCategory('near_transit')">
                        <i class="bi bi-train-front-fill" style="color: #10b981;"></i>
                        ที่พักติด BTS/MRT
                    </button>
                    <button class="category-filter-btn" data-category="luxury" onclick="filterByCategory('luxury')">
                        <i class="bi bi-gem" style="color: #8b5cf6;"></i>
                        คอนโดหรู
                    </button>
                    <button class="category-filter-btn" data-category="pet_friendly" onclick="filterByCategory('pet_friendly')">
                        <i class="bi bi-heart-fill" style="color: #ec4899;"></i>
                        เลี้ยงสัตว์ได้
                    </button>
                    <button class="category-filter-btn" data-category="near_university" onclick="filterByCategory('near_university')">
                        <i class="bi bi-mortarboard-fill" style="color: #3b82f6;"></i>
                        ใกล้มหาวิทยาลัย
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

    <!-- SECTION 1: Popular apartments & condos -->
    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="section-title mb-0">อพาร์ตเม้น &amp; คอนโด ยอดฮิตในกรุงเทพฯ</h2>
                <a href="room-search.php" class="btn btn-outline-primary btn-sm">
                    ดูห้องพักทั้งหมด <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="row g-4">
                <?php foreach ($popularApts as $apt): ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card room-card h-100">
                        <div class="position-relative">
                            <img src="<?php echo htmlspecialchars($apt['thumbnail']); ?>"
                                 class="card-img-top"
                                 alt="<?php echo htmlspecialchars($apt['name']); ?>"
                                 style="height: 200px; object-fit: cover;"
                                 onerror="this.onerror=null; this.src='https://via.placeholder.com/400x300?text=No+Image'">
                            <?php if (!empty($apt['is_popular'])): ?>
                            <span class="badge position-absolute top-0 start-0 m-2" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">ยอดฮิต</span>
                            <?php endif; ?>
                            <span class="badge position-absolute top-0 end-0 m-2" style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);">
                                <?php if (!empty($apt['price_monthly'])): ?>
                                    ฿<?php echo number_format($apt['price_monthly']); ?>/เดือน
                                <?php elseif (!empty($apt['price_daily'])): ?>
                                    ฿<?php echo number_format($apt['price_daily']); ?>/คืน
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold" style="color: var(--dark-gray);">
                                <?php echo htmlspecialchars($apt['name']); ?>
                            </h5>
                            <p class="text-muted mb-2">
                                <i class="bi bi-geo-alt-fill" style="color: var(--medium-gray);"></i>
                                <?php echo htmlspecialchars($apt['district'] . ', ' . $apt['province']); ?>
                            </p>
                            <p class="card-text text-truncate" style="color: var(--medium-gray);">
                                <?php echo htmlspecialchars($apt['description']); ?>
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
                                        'กล้องวงจรปิด' => 'bi-camera-video-fill',
                                        'รูมเซอร์วิส' => 'bi-bell-fill',
                                        'ห้องประชุม' => 'bi-briefcase-fill',
                                        'สวนส่วนกลาง' => 'bi-tree-fill',
                                        'Co-working space' => 'bi-laptop',
                                        'คาเฟ่' => 'bi-cup-straw',
                                        'เลี้ยงสัตว์ได้' => 'bi-heart-fill',
                                    ];
                                    $displayAmenities = array_slice($apt['amenities'], 0, 4);
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
                                    <?php echo number_format($apt['rating'], 1); ?> ·
                                    <i class="bi bi-eye ms-1"></i> <?php echo (int) $apt['views']; ?>
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

    <!-- SECTION 2: Recommended monthly apartments -->
    <section class="py-5 bg-section-light">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="section-title mb-0">อพาร์ตเม้นรายเดือน แนะนำ</h2>
                <a href="room-search.php?rental_type=monthly" class="btn btn-outline-primary btn-sm">
                    ดูห้องพักรายเดือนทั้งหมด <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="row g-4">
                <?php foreach ($monthlyApts as $apt): ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card room-card h-100">
                        <div class="position-relative">
                            <img src="<?php echo htmlspecialchars($apt['thumbnail']); ?>"
                                 class="card-img-top"
                                 alt="<?php echo htmlspecialchars($apt['name']); ?>"
                                 style="height: 200px; object-fit: cover;"
                                 onerror="this.onerror=null; this.src='https://via.placeholder.com/400x300?text=No+Image'">
                            <span class="badge position-absolute top-0 end-0 m-2" style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);">
                                ฿<?php echo number_format($apt['price_monthly']); ?>/เดือน
                            </span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold" style="color: var(--dark-gray);">
                                <?php echo htmlspecialchars($apt['name']); ?>
                            </h5>
                            <p class="text-muted mb-2">
                                <i class="bi bi-geo-alt-fill" style="color: var(--medium-gray);"></i>
                                <?php echo htmlspecialchars($apt['district'] . ', ' . $apt['province']); ?>
                            </p>
                            <p class="card-text text-truncate" style="color: var(--medium-gray);">
                                <?php echo htmlspecialchars($apt['description']); ?>
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
                                        'กล้องวงจรปิด' => 'bi-camera-video-fill',
                                        'รูมเซอร์วิส' => 'bi-bell-fill',
                                        'ห้องประชุม' => 'bi-briefcase-fill',
                                        'สวนส่วนกลาง' => 'bi-tree-fill',
                                        'Co-working space' => 'bi-laptop',
                                        'คาเฟ่' => 'bi-cup-straw',
                                        'เลี้ยงสัตว์ได้' => 'bi-heart-fill',
                                    ];
                                    $displayAmenities = array_slice($apt['amenities'], 0, 4);
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

    <!-- SECTION 3: Recommended daily apartments -->
    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="section-title mb-0">อพาร์ตเม้นรายวัน แนะนำ</h2>
                <a href="room-search.php?rental_type=daily" class="btn btn-outline-primary btn-sm">
                    ดูห้องพักรายวันทั้งหมด <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="row g-4">
                <?php foreach ($dailyApts as $apt): ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card room-card h-100">
                        <div class="position-relative">
                            <img src="<?php echo htmlspecialchars($apt['thumbnail']); ?>"
                                 class="card-img-top"
                                 alt="<?php echo htmlspecialchars($apt['name']); ?>"
                                 style="height: 200px; object-fit: cover;"
                                 onerror="this.onerror=null; this.src='https://via.placeholder.com/400x300?text=No+Image'">
                            <span class="badge position-absolute top-0 end-0 m-2" style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);">
                                ฿<?php echo number_format($apt['price_daily']); ?>/คืน
                            </span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold" style="color: var(--dark-gray);">
                                <?php echo htmlspecialchars($apt['name']); ?>
                            </h5>
                            <p class="text-muted mb-2">
                                <i class="bi bi-geo-alt-fill" style="color: var(--medium-gray);"></i>
                                <?php echo htmlspecialchars($apt['district'] . ', ' . $apt['province']); ?>
                            </p>
                            <p class="card-text text-truncate" style="color: var(--medium-gray);">
                                <?php echo htmlspecialchars($apt['description']); ?>
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
                                        'กล้องวงจรปิด' => 'bi-camera-video-fill',
                                        'รูมเซอร์วิส' => 'bi-bell-fill',
                                        'ห้องประชุม' => 'bi-briefcase-fill',
                                        'สวนส่วนกลาง' => 'bi-tree-fill',
                                        'Co-working space' => 'bi-laptop',
                                        'คาเฟ่' => 'bi-cup-straw',
                                        'เลี้ยงสัตว์ได้' => 'bi-heart-fill',
                                    ];
                                    $displayAmenities = array_slice($apt['amenities'], 0, 4);
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

            // Initialize hero search tabs (monthly / daily)
            initializeSearchTabs();

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

        // Initialize search tabs (monthly / daily)
        function initializeSearchTabs() {
            const tabs = document.querySelectorAll('.search-tab');
            const rentalTypeInput = document.getElementById('rental_type_input');
            const priceSelect = document.querySelector('select[name="price_range"]');

            if (!tabs.length || !rentalTypeInput) return;

            // Function to update price options based on type
            function updatePriceOptions(type) {
                if (!priceSelect) return;
                
                // Store current selection if possible, though ranges differ so it might not match
                const currentValue = priceSelect.value;
                priceSelect.innerHTML = '<option value="">ทุกช่วงราคา</option>';
                
                if (type === 'daily') {
                    const dailyOptions = [
                        { val: '0-500', text: 'ไม่เกิน 500 บาท/คืน' },
                        { val: '500-1000', text: '500 - 1,000 บาท/คืน' },
                        { val: '1000-2000', text: '1,000 - 2,000 บาท/คืน' },
                        { val: '2000-999999', text: 'มากกว่า 2,000 บาท/คืน' }
                    ];
                    dailyOptions.forEach(opt => {
                        const option = document.createElement('option');
                        option.value = opt.val;
                        option.textContent = opt.text;
                        priceSelect.appendChild(option);
                    });
                } else {
                    const monthlyOptions = [
                        { val: '0-5000', text: 'ไม่เกิน 5,000 บาท/เดือน' },
                        { val: '5000-8000', text: '5,000 - 8,000 บาท/เดือน' },
                        { val: '8000-12000', text: '8,000 - 12,000 บาท/เดือน' },
                        { val: '12000-20000', text: '12,000 - 20,000 บาท/เดือน' },
                        { val: '20000-999999', text: 'มากกว่า 20,000 บาท/เดือน' }
                    ];
                    monthlyOptions.forEach(opt => {
                        const option = document.createElement('option');
                        option.value = opt.val;
                        option.textContent = opt.text;
                        // Try to preserve selection if it matches a range
                        if (opt.val === currentValue) option.selected = true;
                        priceSelect.appendChild(option);
                    });
                }
            }

            tabs.forEach(tab => {
                tab.addEventListener('click', function (e) {
                    e.preventDefault();
                    
                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');

                    const type = this.dataset.rentalType || 'monthly';
                    rentalTypeInput.value = type;
                    
                    updatePriceOptions(type);
                });
            });
            
            // Initialize state based on current input value (from PHP or default)
            if (rentalTypeInput.value) {
                const activeTab = document.querySelector(`.search-tab[data-rental-type="${rentalTypeInput.value}"]`);
                if (activeTab) {
                    tabs.forEach(t => t.classList.remove('active'));
                    activeTab.classList.add('active');
                    // Only update options if we are not on the default (monthly) or if we want to ensure consistency
                    // But since PHP renders monthly options by default, we only need to update if it's daily
                    if (rentalTypeInput.value === 'daily') {
                        updatePriceOptions('daily');
                    }
                }
            }
        }

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
