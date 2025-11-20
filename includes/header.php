<?php
$currentPage = basename($_SERVER["PHP_SELF"]);
// Ensure config is loaded so SITE_URL / SITE_NAME are defined.
// Some entry points include config first; others (like index.php) may not — load it if missing.
if (!defined('SITE_URL')) {
    $configPath = __DIR__ . '/../config/config.php';
    if (file_exists($configPath)) {
        require_once $configPath;
    } else {
        // Fallback: derive a reasonable SITE_URL from server vars to avoid fatal errors.
        $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        define('SITE_URL', rtrim($proto . '://' . $host, '/'));
        if (!defined('SITE_NAME')) {
            define('SITE_NAME', 'My Site');
        }
    }
}

// สร้าง relative path สำหรับใช้ใน JavaScript fetch (ป้องกัน Mixed Content)
// ใช้ relative path แทน absolute URL เพื่อให้ browser ใช้ protocol เดียวกับหน้าปัจจุบัน
$scriptName = $_SERVER['SCRIPT_NAME'];
$baseDir = str_replace('\\', '/', dirname($scriptName));

// ถ้าอยู่ใน root ให้ใช้ / ถ้าอยู่ใน subdirectory ให้ใช้ /subdirectory
$relativeBase = ($baseDir === '/' || $baseDir === '') ? '' : $baseDir;

$logoutPath = $relativeBase . "/logout.php";
$indexPath = $relativeBase . "/index.php";
$loginPath = $relativeBase . "/login.php";
$registerPath = $relativeBase . "/register.php";
$roomsPath = $relativeBase . "/room-search.php";
$searchPath = $relativeBase . "/search.php";
$wishlistPath = $relativeBase . "/wishlist.php";
$nearbyPath = $relativeBase . "/room.php?mode=nearby";
$energyPath = $relativeBase . "/energy.php";
$profilePath = $relativeBase . "/profile.php";
$businessDashboardPath = $relativeBase . "/business/dashboard.php";
$adminPath = $relativeBase . "/admin/index.php";
$logoPath = $relativeBase . "/img/amptron-apartments.png";

// Determine Home Link
$homeLink = $indexPath;
if (isLoggedIn()) {
    if (isAdmin()) {
        $homeLink = $adminPath;
    } elseif (isBusiness()) {
        $homeLink = $businessDashboardPath;
    } else {
        $homeLink = $baseUrl . "/my-room.php";
    }
}

// ภาษาปัจจุบันจาก Session (ใช้ร่วมกับ i18n ใน config.php)
 $currentLang = isset($_SESSION["lang"]) ? $_SESSION["lang"] : "en";
?>
 <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@100;200;300;400;500;600;700&family=League+Spartan:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Modern Minimal Header -->
    <nav class="modern-navbar">
    <div class="container">
        <div class="navbar-content">
            <!-- Logo Section -->
            <a class="brand" href="<?php echo $homeLink; ?>">
                <img src="<?php echo $logoPath; ?>" alt="<?php echo SITE_NAME; ?>" class="brand-logo">
            </a>

            <!-- Desktop Navigation -->
            <div class="nav-links desktop-nav">
                <?php if (isLoggedIn()): ?>
                    <?php if (isBusiness() && !isAdmin()): ?>
                    <a class="nav-link <?php echo $currentPage === "dashboard.php" ? "active" : ""; ?>"
                       href="<?php echo $businessDashboardPath; ?>"
                       data-tooltip="ธุรกิจของฉัน">
                        <i class="bi bi-briefcase"></i>
                        <span>ธุรกิจของฉัน</span>
                    </a>
                    <?php elseif (!isAdmin()): ?>
                    <a class="nav-link <?php echo $currentPage === "my-room.php" ? "active" : ""; ?>"
                       href="<?php echo $baseUrl . "/my-room.php"; ?>"
                       data-tooltip="ห้องของฉัน">
                        <i class="bi bi-house-door"></i>
                        <span>ห้องของฉัน</span>
                    </a>
                    <a class="nav-link <?php echo $currentPage === "news.php" ? "active" : ""; ?>"
                       href="<?php echo $baseUrl . "/news.php"; ?>"
                       data-tooltip="ข่าวสาร">
                        <i class="bi bi-newspaper"></i>
                        <span>ข่าวสาร</span>
                    </a>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (!isLoggedIn()): ?>
                <form class="nav-search" action="<?php echo $indexPath; ?>" method="get">
                    <div class="nav-search-inner">
                        <i class="bi bi-search"></i>
                        <input type="text"
                               name="keyword"
                               class="nav-search-input"
                               placeholder="ค้นหาห้องพัก คอนโด..."
                               value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
                    </div>
                </form>
                <a class="nav-link"
                   href="<?php echo $nearbyPath; ?>"
                   data-tooltip="ใกล้ฉัน">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 1.1rem; height: 1.1rem;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                    </svg>
                    <span>ใกล้ฉัน</span>
                </a>
                <a class="nav-link"
                   href="<?php echo $roomsPath; ?>"
                   data-tooltip="ค้นหาห้องพัก">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 1.1rem; height: 1.1rem;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                    </svg>
                    <span>ค้นหาห้องพัก</span>
                </a>
                <?php endif; ?>
            </div>

            <!-- Right Actions -->
            <div class="nav-actions">
                <!-- Language Toggle -->
                <button class="icon-btn lang-toggle-btn" id="langToggleBtn" type="button" data-tooltip="Language" data-current-lang="<?php echo htmlspecialchars($currentLang); ?>" aria-label="<?php echo $currentLang === 'th' ? 'ภาษา (TH)' : 'Language (EN)'; ?>">
                    <span id="langFlag" class="lang-flag <?php echo $currentLang === 'th' ? 'flag-th' : 'flag-en'; ?>"></span>
                </button>

                <?php if (isLoggedIn()): ?>

                    <!-- Notification Bell -->
                    <div class="dropdown">
                        <button class="icon-btn position-relative me-2" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false" data-tooltip="การแจ้งเตือน">
                            <i class="bi bi-bell"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light p-1" style="font-size: 0.6rem; transform: translate(-25%, 25%) !important;">
                                3
                                <span class="visually-hidden">unread messages</span>
                            </span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end p-0 border-0 shadow-lg" style="width: 320px; max-height: 400px; overflow-y: auto; border-radius: 16px;">
                            <li class="p-3 border-bottom bg-light d-flex justify-content-between align-items-center rounded-top-4">
                                <strong class="text-dark" style="font-family: var(--font-thai);">การแจ้งเตือน</strong>
                                <span class="badge bg-primary rounded-pill">3 ใหม่</span>
                            </li>
                            <li>
                                <a class="dropdown-item p-3 border-bottom" href="#">
                                    <div class="d-flex gap-3">
                                        <div class="flex-shrink-0 text-warning bg-warning bg-opacity-10 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="bi bi-receipt-cutoff fs-5"></i>
                                        </div>
                                        <div style="white-space: normal;">
                                            <p class="mb-1 small fw-bold text-dark" style="font-family: var(--font-thai);">บิลค่าเช่าเดือน พ.ย. มาแล้ว</p>
                                            <p class="mb-1 small text-muted" style="font-family: var(--font-thai);">ยอดชำระ 7,350 บาท ครบกำหนด 5 พ.ย.</p>
                                            <small class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-clock me-1"></i>2 ชม. ที่แล้ว</small>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item p-3 border-bottom" href="#">
                                    <div class="d-flex gap-3">
                                        <div class="flex-shrink-0 text-success bg-success bg-opacity-10 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="bi bi-check-circle fs-5"></i>
                                        </div>
                                        <div style="white-space: normal;">
                                            <p class="mb-1 small fw-bold text-dark" style="font-family: var(--font-thai);">แจ้งซ่อมแอร์เสร็จสิ้น</p>
                                            <p class="mb-1 small text-muted" style="font-family: var(--font-thai);">ช่างได้ดำเนินการล้างแอร์เรียบร้อยแล้ว</p>
                                            <small class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-clock me-1"></i>1 วันที่แล้ว</small>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item p-3" href="#">
                                    <div class="d-flex gap-3">
                                        <div class="flex-shrink-0 text-primary bg-primary bg-opacity-10 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="bi bi-box-seam fs-5"></i>
                                        </div>
                                        <div style="white-space: normal;">
                                            <p class="mb-1 small fw-bold text-dark" style="font-family: var(--font-thai);">อนุมัติคำขอเช่าตู้เย็น</p>
                                            <p class="mb-1 small text-muted" style="font-family: var(--font-thai);">เจ้าหน้าที่จะนำตู้เย็นไปส่งที่ห้องพรุ่งนี้</p>
                                            <small class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-clock me-1"></i>2 วันที่แล้ว</small>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li class="p-2 text-center bg-light rounded-bottom-4">
                                <a href="#" class="text-decoration-none small text-primary fw-bold" style="font-family: var(--font-thai);">ดูทั้งหมด</a>
                            </li>
                        </ul>
                    </div>

                    <!-- User Menu -->
                    <div class="dropdown">
                        <button class="user-btn" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="avatar">
                                <i class="bi bi-person-circle"></i>
                            </div>
                            <span class="user-name"><?php echo htmlspecialchars(
                                $_SESSION["full_name"],
                            ); ?></span>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <div class="dropdown-header">
                                    <strong><?php echo htmlspecialchars(
                                        $_SESSION["full_name"],
                                    ); ?></strong>
                                    <small class="text-muted"><?php echo htmlspecialchars(
                                        $_SESSION["email"] ?? "",
                                    ); ?></small>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="<?php echo $profilePath; ?>">
                                    <i class="bi bi-person"></i> <?php echo t('nav.profile', [], 'Profile'); ?>
                                </a>
                            </li>

                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?php echo $logoutPath; ?>">
                                    <i class="bi bi-box-arrow-right"></i> <?php echo t('nav.logout', [], 'Logout'); ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Login/Register Buttons (prominent login) -->
                    <a href="#"
                       class="btn-primary-minimal"
                       data-bs-toggle="modal"
                       data-bs-target="#loginModal"
                       data-tooltip="เข้าสู่ระบบ"
                       aria-label="เข้าสู่ระบบ">
                        <i class="bi bi-box-arrow-in-right"></i>
                        <span>เข้าสู่ระบบ</span>
                    </a>
                <?php endif; ?>

                <!-- Mobile Menu Toggle -->
                <button class="mobile-toggle" id="mobileMenuToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div class="mobile-nav" id="mobileNav">
            <?php if (!isLoggedIn()): ?>
            <form class="mobile-search" action="<?php echo $indexPath; ?>" method="get">
                <input type="text"
                       name="keyword"
                       class="mobile-search-input"
                       placeholder="ค้นหาห้องพัก..."
                       value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
                <button type="submit" class="mobile-search-btn">
                    <i class="bi bi-search"></i>
                </button>
            </form>
            <a class="mobile-nav-link" href="<?php echo $nearbyPath; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 1.25rem; height: 1.25rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                </svg>
                <span>ใกล้ฉัน</span>
            </a>
            <a class="mobile-nav-link" href="<?php echo $roomsPath; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 1.25rem; height: 1.25rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                </svg>
                <span>ค้นหาห้องพัก</span>
            </a>
            <?php endif; ?>
            <?php if (isLoggedIn()): ?>
                <?php if (isBusiness() && !isAdmin()): ?>
                <a class="mobile-nav-link" href="<?php echo $businessDashboardPath; ?>">
                    <i class="bi bi-briefcase"></i>
                    <span>ธุรกิจของฉัน</span>
                </a>
                <?php elseif (!isAdmin()): ?>
                <a class="mobile-nav-link" href="<?php echo $baseUrl . "/my-room.php"; ?>">
                    <i class="bi bi-house-door"></i>
                    <span>ห้องของฉัน</span>
                </a>
                <a class="mobile-nav-link" href="<?php echo $baseUrl . "/news.php"; ?>">
                    <i class="bi bi-newspaper"></i>
                    <span>ข่าวสาร</span>
                </a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (!isLoggedIn()): ?>
            <div class="mobile-auth">
                <a href="#"
                   class="mobile-btn-primary"
                   data-bs-toggle="modal"
                   data-bs-target="#loginModal"
                   data-tooltip="เข้าสู่ระบบ"
                   aria-label="เข้าสู่ระบบ">
                   เข้าสู่ระบบ
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<?php if (!empty($topAds)): ?>
    <div id="topAdCarousel" class="carousel slide top-banner-carousel" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php foreach ($topAds as $i => $ad): ?>
            <div class="carousel-item <?php echo $i === 0 ? 'active' : ''; ?>">
                <a href="<?php echo htmlspecialchars($ad['link'] ?? '#'); ?>" target="_blank" style="display:block; position:relative;">
                    <img src="<?php echo htmlspecialchars($ad['image']); ?>" alt="<?php echo htmlspecialchars($ad['title']); ?>" class="top-banner-img">
                    <div class="top-banner-overlay" aria-hidden="true"></div>
                    <div class="carousel-caption d-none d-md-block top-banner-caption">
                        <h5><?php echo htmlspecialchars($ad['title']); ?></h5>
                        <?php if (!empty($ad['desc'])): ?>
                            <div class="top-banner-desc"><?php echo htmlspecialchars($ad['desc']); ?></div>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#topAdCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#topAdCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
<?php endif; ?>

<style>
/* Modern Minimal Navbar Styles */
* {
    font-family: 'IBM Plex Sans Thai', 'League Spartan', sans-serif;
}

h1, h2, h3, h4, h5, h6,
.brand,
.brand-text {
    font-family: 'League Spartan', sans-serif;
    font-weight: 700;
    letter-spacing: -0.02em;
}

.modern-navbar {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    padding: 12px 0;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    transition: all 0.3s ease;
}

.modern-navbar.scrolled {
    padding: 8px 0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.navbar-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
}

/* Brand/Logo */
.brand {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    color: #1e293b;
    font-weight: 700;
    font-size: 1.25rem;
    transition: all 0.3s ease;
}

.brand:hover {
    color: #3b82f6;
}

.brand-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
}

.brand-text {
    font-size: 1.25rem;
    background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.brand-logo {
    height: 70px;
    width: auto;
    display: block;
}

/* Desktop Navigation */
.desktop-nav {
    display: flex;
    align-items: center;
    gap: 4px;
    flex: 1;
    margin-left: 32px;
}

.nav-search {
    flex: 0 0 400px;
}

.nav-search-inner {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 999px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
}

.nav-search-inner i {
    color: #94a3b8;
    font-size: 1rem;
}

.nav-search-input {
    flex: 1;
    border: none;
    background: transparent;
    outline: none;
    font-size: 0.9rem;
    color: #0f172a;
}

.nav-search-input::placeholder {
    color: #94a3b8;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 8px;
    text-decoration: none;
    color: #64748b;
    font-weight: 500;
    font-size: 0.95rem;
    transition: all 0.2s ease;
    position: relative;
}

.nav-link:hover {
    color: #3b82f6;
    background: rgba(59, 130, 246, 0.05);
}

.nav-link.active {
    color: #3b82f6;
    background: rgba(59, 130, 246, 0.1);
}

.nav-link i {
    font-size: 1.1rem;
}

/* Right Actions */
.nav-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Icon Button */
.icon-btn {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    border: none;
    background: transparent;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 1.25rem;
    text-decoration: none;
}

.icon-btn:hover {
    background: rgba(59, 130, 246, 0.08);
    color: #3b82f6;
}

.lang-flag {
    width: 24px;
    height: 16px;
    border-radius: 3px;
    display: inline-block;
    background-size: cover;
    background-position: center;
}

.lang-flag.flag-th {
    background-image: url('https://flagcdn.com/w40/th.png');
}

.lang-flag.flag-en {
    background-image: url('https://flagcdn.com/w40/gb.png');
}

/* User Button */
.user-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 6px 12px 6px 6px;
    border-radius: 50px;
    border: 1px solid #e2e8f0;
    background: white;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #1e293b;
}

.user-btn:hover {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}

.user-name {
    font-weight: 500;
    font-size: 0.9rem;
    max-width: 120px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Minimal Buttons */
.btn-minimal {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 8px;
    text-decoration: none;
    color: #64748b;
    font-weight: 500;
    font-size: 0.95rem;
    transition: all 0.2s ease;
    border: none;
    background: transparent;
}

.btn-minimal:hover {
    color: #3b82f6;
    background: rgba(59, 130, 246, 0.05);
}

.btn-primary-minimal {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    color: white;
    font-weight: 600;
    font-size: 0.95rem;
    background: #3b82f6;
    border: none;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.25);
}

.btn-primary-minimal:hover {
    background: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.35);
}

/* Dropdown Menu Styling */
.dropdown-menu {
    border: 1px solid rgba(0, 0, 0, 0.05);
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    border-radius: 12px;
    padding: 8px;
    min-width: 220px;
    margin-top: 8px;
}

.dropdown-item {
    border-radius: 8px;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.9rem;
    transition: all 0.2s ease;
}

.dropdown-item:hover {
    background: rgba(59, 130, 246, 0.08);
    color: #3b82f6;
}

.dropdown-item i {
    width: 20px;
    text-align: center;
}

.dropdown-header {
    padding: 10px 14px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

/* Mobile Toggle */
.mobile-toggle {
    display: none;
    flex-direction: column;
    gap: 4px;
    width: 40px;
    height: 40px;
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 8px;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.mobile-toggle:hover {
    background: rgba(59, 130, 246, 0.08);
}

.mobile-toggle span {
    width: 100%;
    height: 2px;
    background: #64748b;
    border-radius: 2px;
    transition: all 0.3s ease;
}

.mobile-toggle.active span:nth-child(1) {
    transform: rotate(45deg) translate(5px, 5px);
}

.mobile-toggle.active span:nth-child(2) {
    opacity: 0;
}

.mobile-toggle.active span:nth-child(3) {
    transform: rotate(-45deg) translate(7px, -7px);
}

/* Mobile Navigation */
.mobile-nav {
    display: none;
    flex-direction: column;
    gap: 4px;
    padding: 16px 0;
    border-top: 1px solid rgba(0, 0, 0, 0.05);
    margin-top: 16px;
}

.mobile-nav.active {
    display: flex;
}

.mobile-nav-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    border-radius: 8px;
    text-decoration: none;
    color: #64748b;
    font-weight: 500;
    transition: all 0.2s ease;
}

.mobile-nav-link:hover,
.mobile-nav-link.active {
    background: rgba(59, 130, 246, 0.08);
    color: #3b82f6;
}

.mobile-search {
    display: flex;
    gap: 8px;
    margin-bottom: 12px;
}

.mobile-search-input {
    flex: 1;
    padding: 10px 12px;
    border-radius: 999px;
    border: 1px solid #e2e8f0;
    font-size: 0.9rem;
}

.mobile-search-btn {
    width: 44px;
    height: 44px;
    border-radius: 999px;
    border: none;
    background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.mobile-search-btn i {
    font-size: 1.1rem;
}

.mobile-auth {
    display: flex;
    gap: 12px;
    margin-top: 8px;
}

.mobile-btn-minimal,
.mobile-btn-primary {
    flex: 1;
    padding: 12px;
    border-radius: 8px;
    text-align: center;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.2s ease;
}

.mobile-btn-minimal {
    background: rgba(59, 130, 246, 0.08);
    color: #3b82f6;
}

.mobile-btn-primary {
    background: #3b82f6;
    color: white;
}

.mobile-btn-primary:hover {
    background: #2563eb;
}

/* Responsive */
@media (max-width: 992px) {
    .desktop-nav {
        display: none;
    }

    .nav-search {
        display: none;
    }

    .user-name {
        display: none;
    }

    .btn-minimal span,
    .btn-primary-minimal span {
        display: none;
    }

    .mobile-toggle {
        display: flex;
    }
}

@media (max-width: 576px) {
    .brand-text {
        display: none;
    }

    .navbar-content {
        gap: 12px;
    }

    .nav-actions {
        gap: 4px;
    }

    .icon-btn {
        width: 36px;
        height: 36px;
        font-size: 1.1rem;
    }
}

/* Smooth Scroll Padding */
body {
    /* navbar height (70px) padding to avoid content under fixed navbar */
    padding-top: 70px;
}

/* Top banner styles (displayed below header in normal flow) */
.top-banner-carousel {
    position: relative;
    width: 600px;
    max-width: 95vw;
    height: 340px;
    margin: 48px auto 24px auto; /* increased top spacing so banner sits further below the navbar */
    overflow: hidden;
    background: #000;
    box-shadow: 0 8px 34px rgba(0,0,0,0.10);
    border-bottom: 1px solid rgba(0,0,0,0.06);
    border-radius: 18px;
    display: block;
}
.top-banner-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center center;
    display: block;
    border-radius: 18px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.10);
    transition: transform 0.45s ease;
}
.top-banner-container .carousel-item.active .top-banner-img {
    transform: scale(1.01);
}
.top-banner-overlay {
    position: absolute;
    inset: 0;
    pointer-events: none;
    border-radius: 18px;
    background: linear-gradient(to bottom, rgba(0,0,0,0) 30%, rgba(0,0,0,0.45) 75%);
}
.top-banner-caption {
    position: absolute;
    left: 32px;
    bottom: 32px;
    right: 32px;
    text-align: left;
    padding: 0;
    z-index: 2;
}
.top-banner-caption h5 {
    font-size: 22px;
    margin: 0 0 8px 0;
    font-weight: 700;
    color: #fff;
    text-shadow: 0 6px 18px rgba(0,0,0,0.6);
}
.top-banner-desc {
    font-size: 16px;
    color: #f3f3f3;
    font-weight: 400;
    text-shadow: 0 2px 8px rgba(0,0,0,0.4);
    margin-top: 2px;
}
@media (max-width: 768px) {
    .top-banner-carousel {
        height: 200px;
        width: 98vw;
        max-width: 98vw;
    }
    .top-banner-caption h5 {
        font-size: 18px;
    }
    .top-banner-desc {
        font-size: 14px;
    }
}
@media (max-width: 576px) {
    .top-banner-carousel {
        height: 120px;
    }
    .top-banner-caption h5 {
        font-size: 15px;
    }
    .top-banner-desc {
        font-size: 12px;
    }
}

/* Animations */
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.dropdown-menu {
    animation: slideDown 0.2s ease;
}
</style>

<script>
// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileToggle = document.getElementById('mobileMenuToggle');
    const mobileNav = document.getElementById('mobileNav');

    if (mobileToggle) {
        mobileToggle.addEventListener('click', function() {
            this.classList.toggle('active');
            mobileNav.classList.toggle('active');
        });
    }

    // Navbar scroll effect
    let lastScroll = 0;
    const navbar = document.querySelector('.modern-navbar');

    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset;

        if (currentScroll > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }

        lastScroll = currentScroll;
    });

    // Language toggle button
    const langToggleBtn = document.getElementById('langToggleBtn');
    const langFlag = document.getElementById('langFlag');

    if (langToggleBtn && langFlag) {
        const initialLang = langToggleBtn.dataset.currentLang || 'en';

        function updateLangFlag(lang) {
            if (!langFlag) return;
            langFlag.classList.toggle('flag-th', lang === 'th');
            langFlag.classList.toggle('flag-en', lang !== 'th');
        }

        // Initial flag based on current PHP language
        updateLangFlag(initialLang);

        langToggleBtn.addEventListener('click', function() {
            const currentLang = langToggleBtn.dataset.currentLang || initialLang || 'en';
            const nextLang = currentLang === 'en' ? 'th' : 'en';

            // Update data attribute for consistency
            langToggleBtn.dataset.currentLang = nextLang;

            // Reload page with ?lang= parameter so PHP i18n can handle translations
            const url = new URL(window.location.href);
            url.searchParams.set('lang', nextLang);
            window.location.href = url.toString();
        });
    }
});
</script>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden;">
            <div class="modal-body p-0">
                <div class="login-card" style="box-shadow: none; border: none; margin: 0;">


                    <!-- Body -->
                    <div class="login-body p-4">
                        <div id="loginAlert" class="alert alert-danger alert-custom d-none">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <span id="loginAlertMessage"></span>
                        </div>

                        <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close" style="z-index: 10;"></button>

                        <!-- Login Form -->
                        <form id="loginForm" method="POST" action="<?php echo $loginPath; ?>">
                            <div class="mb-3">
                                <label for="modal_username" class="form-label">Username หรือ Email</label>
                                <div class="input-group-custom">
                                    <i class="bi bi-person-fill"></i>
                                    <input type="text" class="form-control" id="modal_username" name="username" placeholder="กรอก username หรือ email" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="modal_password" class="form-label">Password</label>
                                <div class="input-group-custom">
                                    <i class="bi bi-lock-fill"></i>
                                    <input type="password" class="form-control" id="modal_password" name="password" placeholder="กรอกรหัสผ่าน" required>
                                </div>
                            </div>

                            <div class="mb-4 d-flex justify-content-between align-items-center">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="modal_remember">
                                    <label class="form-check-label" for="modal_remember">จดจำฉันไว้</label>
                                </div>
                                <a href="#" class="text-link" style="font-size: 0.9rem;">ลืมรหัสผ่าน?</a>
                            </div>

                            <button type="submit" class="btn-login">
                                <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                <span>เข้าสู่ระบบ</span>
                            </button>
                        </form>

                        <div class="text-center mt-4">
                            <span style="color: var(--medium-gray);">ยังไม่มีบัญชี? </span>
                            <span style="color: var(--medium-gray);">ยังไม่มีบัญชี? </span>
                            <a href="#" class="text-link" data-bs-toggle="modal" data-bs-target="#registerModal">สมัครสมาชิก</a>
                        </div>

                        <div class="divider">
                            <span>หรือเข้าสู่ระบบด่วน</span>
                        </div>

                        <!-- Quick Login Buttons -->
                        <div class="quick-login-section">
                            <div class="d-grid gap-2">
                                <button type="button" class="btn-quick-login admin" onclick="quickLoginModal('admin', 'admin')">
                                    <i class="bi bi-shield-check"></i>
                                    <span>Login as Admin</span>
                                </button>
                                <button type="button" class="btn-quick-login business" onclick="quickLoginModal('business', 'business')">
                                    <i class="bi bi-shop"></i>
                                    <span>Login as Business</span>
                                </button>
                                <button type="button" class="btn-quick-login user" onclick="quickLoginModal('user', 'user')">
                                    <i class="bi bi-person"></i>
                                    <span>Login as User</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Modal Specific Styles */
#loginModal .modal-content {
    background: transparent;
}

#loginModal .login-card {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(20px);
}

#loginModal .login-header {
    background: linear-gradient(135deg, #0ea5e9 0%, #3b82f6 100%);
    padding: 30px 24px;
}

#loginModal .btn-close-white {
    filter: brightness(0) invert(1);
    opacity: 0.8;
}

#loginModal .btn-close-white:hover {
    opacity: 1;
}

/* Reuse styles from login.php */
.form-label {
    font-weight: 600;
    color: var(--dark-gray);
    margin-bottom: 8px;
    font-size: 0.9rem;
}

.form-control {
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px 16px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    outline: none;
}

.input-group-custom {
    position: relative;
}

.input-group-custom i {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #64748b;
    font-size: 1.1rem;
    z-index: 5;
}

.input-group-custom .form-control {
    padding-left: 45px;
}

.btn-login {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #0ea5e9 0%, #3b82f6 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(14, 165, 233, 0.4);
}

.divider {
    display: flex;
    align-items: center;
    text-align: center;
    margin: 20px 0;
}

.divider::before,
.divider::after {
    content: '';
    flex: 1;
    border-bottom: 1px solid #e2e8f0;
}

.divider span {
    padding: 0 16px;
    color: #64748b;
    font-size: 0.85rem;
    font-weight: 500;
}

.btn-quick-login {
    width: 100%;
    padding: 10px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    background: white;
    color: #1e293b;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-quick-login:hover {
    border-color: #3b82f6;
    background: rgba(59, 130, 246, 0.05);
    color: #3b82f6;
    transform: translateY(-2px);
}

.text-link {
    color: #3b82f6;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.text-link:hover {
    color: #8b5cf6;
    text-decoration: underline;
}

.alert-custom {
    border-radius: 12px;
    border: none;
    padding: 12px 16px;
}

.alert-info {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    color: #1e40af;
    border-left: 4px solid #3b82f6;
}
</style>

<script>
function quickLoginModal(username, password) {
    document.getElementById('modal_username').value = username;
    document.getElementById('modal_password').value = password;
    // Submit via the form submit handler
    document.getElementById('loginForm').dispatchEvent(new Event('submit'));
}

document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const loginAlert = document.getElementById('loginAlert');
    const loginAlertMessage = document.getElementById('loginAlertMessage');
    const submitBtn = loginForm.querySelector('button[type="submit"]');
    const spinner = submitBtn.querySelector('.spinner-border');
    const btnIcon = submitBtn.querySelector('.bi-box-arrow-in-right');
    const btnText = submitBtn.querySelector('span:last-child');

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Reset alert
        loginAlert.classList.add('d-none');
        
        // Loading state
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        btnIcon.classList.add('d-none');
        btnText.textContent = 'กำลังเข้าสู่ระบบ...';

        const formData = new FormData(loginForm);
        formData.append('ajax_login', '1'); // Flag for server to know it's AJAX

        fetch('<?php echo $loginPath; ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Success - Redirect
                window.location.href = data.redirect_url || 'index.php';
            } else {
                // Error
                loginAlertMessage.textContent = data.message || 'เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง';
                loginAlert.classList.remove('d-none');
                
                // Reset button
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
                btnIcon.classList.remove('d-none');
                btnText.textContent = 'เข้าสู่ระบบ';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            loginAlertMessage.textContent = 'เกิดข้อผิดพลาดในการเชื่อมต่อ';
            loginAlert.classList.remove('d-none');
            
            // Reset button
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
            btnIcon.classList.remove('d-none');
            btnText.textContent = 'เข้าสู่ระบบ';
        });
    });

    // Register Form Handling
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        const registerAlert = document.getElementById('registerAlert');
        const registerAlertMessage = document.getElementById('registerAlertMessage');
        const registerSuccessAlert = document.getElementById('registerSuccessAlert');
        const registerSuccessMessage = document.getElementById('registerSuccessMessage');
        const regSubmitBtn = registerForm.querySelector('button[type="submit"]');
        const regSpinner = regSubmitBtn.querySelector('.spinner-border');
        const regBtnText = regSubmitBtn.querySelector('span:last-child');

        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Reset alerts
            registerAlert.classList.add('d-none');
            registerSuccessAlert.classList.add('d-none');
            
            // Validate password match
            const password = document.getElementById('reg_password').value;
            const confirmPassword = document.getElementById('reg_confirm_password').value;
            
            if (password !== confirmPassword) {
                registerAlertMessage.textContent = 'รหัสผ่านไม่ตรงกัน';
                registerAlert.classList.remove('d-none');
                return;
            }

            // Loading state
            regSubmitBtn.disabled = true;
            regSpinner.classList.remove('d-none');
            regBtnText.textContent = 'กำลังสมัครสมาชิก...';

            const formData = new FormData(registerForm);
            formData.append('ajax_register', '1');

            fetch('<?php echo $registerPath; ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Success
                    registerSuccessMessage.textContent = data.message || 'สมัครสมาชิกสำเร็จ! กำลังเข้าสู่ระบบ...';
                    registerSuccessAlert.classList.remove('d-none');
                    
                    // Redirect after short delay
                    setTimeout(() => {
                        window.location.href = data.redirect_url || 'index.php';
                    }, 1500);
                } else {
                    // Error
                    registerAlertMessage.textContent = data.message || 'เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง';
                    registerAlert.classList.remove('d-none');
                    
                    // Reset button
                    regSubmitBtn.disabled = false;
                    regSpinner.classList.add('d-none');
                    regBtnText.textContent = 'สมัครสมาชิก';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                registerAlertMessage.textContent = 'เกิดข้อผิดพลาดในการเชื่อมต่อ';
                registerAlert.classList.remove('d-none');
                
                // Reset button
                regSubmitBtn.disabled = false;
                regSpinner.classList.add('d-none');
                regBtnText.textContent = 'สมัครสมาชิก';
            });
        });
    }
});
</script>

<!-- Register Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden;">
            <div class="modal-body p-0">
                <div class="login-card" style="box-shadow: none; border: none; margin: 0;">
                    
                    <!-- Body -->
                    <div class="login-body p-4">
                        <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close" style="z-index: 10;"></button>
                        
                        <h2 class="text-center mb-4">สมัครสมาชิก</h2>

                        <div id="registerAlert" class="alert alert-danger alert-custom d-none">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <span id="registerAlertMessage"></span>
                        </div>
                        
                        <div id="registerSuccessAlert" class="alert alert-success alert-custom d-none">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <span id="registerSuccessMessage"></span>
                        </div>

                        <!-- Register Form -->
                        <form id="registerForm" method="POST" action="<?php echo $registerPath; ?>">
                            <div class="mb-3">
                                <label class="form-label">ประเภทผู้ใช้</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="role" id="reg_role_user" value="user" checked>
                                    <label class="btn btn-outline-primary" for="reg_role_user">ผู้ใช้ทั่วไป</label>
                                    
                                    <input type="radio" class="btn-check" name="role" id="reg_role_business" value="business">
                                    <label class="btn btn-outline-primary" for="reg_role_business">ผู้ประกอบการ</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="reg_username" class="form-label">Username</label>
                                    <div class="input-group-custom">
                                        <i class="bi bi-person-fill"></i>
                                        <input type="text" class="form-control" id="reg_username" name="username" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="reg_email" class="form-label">Email</label>
                                    <div class="input-group-custom">
                                        <i class="bi bi-envelope-fill"></i>
                                        <input type="email" class="form-control" id="reg_email" name="email" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="reg_full_name" class="form-label">ชื่อ-นามสกุล</label>
                                    <div class="input-group-custom">
                                        <i class="bi bi-card-heading"></i>
                                        <input type="text" class="form-control" id="reg_full_name" name="full_name" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="reg_phone" class="form-label">เบอร์โทรศัพท์</label>
                                    <div class="input-group-custom">
                                        <i class="bi bi-telephone-fill"></i>
                                        <input type="tel" class="form-control" id="reg_phone" name="phone">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="reg_password" class="form-label">Password</label>
                                    <div class="input-group-custom">
                                        <i class="bi bi-lock-fill"></i>
                                        <input type="password" class="form-control" id="reg_password" name="password" required minlength="6">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="reg_confirm_password" class="form-label">Confirm Password</label>
                                    <div class="input-group-custom">
                                        <i class="bi bi-lock-fill"></i>
                                        <input type="password" class="form-control" id="reg_confirm_password" name="confirm_password" required minlength="6">
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn-login mt-2">
                                <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                                <i class="bi bi-person-plus-fill me-2"></i>
                                <span>สมัครสมาชิก</span>
                            </button>
                        </form>

                        <div class="text-center mt-4">
                            <span style="color: var(--medium-gray);">มีบัญชีแล้ว? </span>
                            <a href="#" class="text-link" data-bs-toggle="modal" data-bs-target="#loginModal">เข้าสู่ระบบ</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
