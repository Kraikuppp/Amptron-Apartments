<?php
$currentPage = basename($_SERVER['PHP_SELF']);
// Ensure $businessProfile is available, if not, try to fetch or use defaults
if (!isset($businessProfile)) {
    $businessProfile = [
        "business_name" => $_SESSION["business_name"] ?? "ธุรกิจของคุณ",
        "verification_status" => "pending"
    ];
}
?>
<div class="dashboard-sidebar">
    <div class="text-center mb-4">
        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px; box-shadow: 0 10px 20px rgba(0,0,0,0.05);">
            <i class="bi bi-building fs-1 text-primary"></i>
        </div>
        <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($businessProfile["business_name"] ?? "ธุรกิจของคุณ"); ?></h5>
        <div class="mt-2">
            <span class="verification-badge <?php echo isset($businessProfile["verification_status"]) && $businessProfile["verification_status"] === "verified" ? "verified" : "pending"; ?>">
                <i class="bi bi-<?php echo isset($businessProfile["verification_status"]) && $businessProfile["verification_status"] === "verified" ? "patch-check-fill" : "clock"; ?>"></i>
                <?php echo isset($businessProfile["verification_status"]) && $businessProfile["verification_status"] === "verified" ? "ยืนยันแล้ว" : "รอการยืนยัน"; ?>
            </span>
        </div>
    </div>

    <hr class="opacity-10 my-4">

    <nav class="nav flex-column gap-2">
        <a class="nav-link <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
            <i class="bi bi-speedometer2 me-3 fs-5"></i> Dashboard
        </a>
        <a class="nav-link <?php echo $currentPage == 'properties.php' || $currentPage == 'add-property.php' || $currentPage == 'edit-property.php' ? 'active' : ''; ?>" href="properties.php">
            <i class="bi bi-building me-3 fs-5"></i> จัดการห้องพัก
        </a>
        <a class="nav-link <?php echo $currentPage == 'energy.php' ? 'active' : ''; ?>" href="energy.php">
            <i class="bi bi-lightning-charge me-3 fs-5"></i> จัดการไฟฟ้า
        </a>
        <a class="nav-link <?php echo $currentPage == 'service-requests.php' ? 'active' : ''; ?>" href="service-requests.php">
            <i class="bi bi-tools me-3 fs-5"></i> คำขอรับบริการ
            <?php if (isset($serviceRequests) && count($serviceRequests) > 0): ?>
            <span class="badge bg-danger rounded-pill ms-auto"><?php echo count($serviceRequests); ?></span>
            <?php endif; ?>
        </a>
        <a class="nav-link <?php echo $currentPage == 'promotions.php' ? 'active' : ''; ?>" href="promotions.php">
            <i class="bi bi-tag me-3 fs-5"></i> จัดการโปรโมชัน
        </a>
        <a class="nav-link <?php echo $currentPage == 'news.php' ? 'active' : ''; ?>" href="news.php">
            <i class="bi bi-newspaper me-3 fs-5"></i> ข่าวสาร/ประกาศ
        </a>
        <a class="nav-link <?php echo $currentPage == 'analytics.php' ? 'active' : ''; ?>" href="analytics.php">
            <i class="bi bi-graph-up me-3 fs-5"></i> รายงานสรุป
        </a>
        <a class="nav-link <?php echo $currentPage == 'profile.php' ? 'active' : ''; ?>" href="profile.php">
            <i class="bi bi-person-circle me-3 fs-5"></i> ข้อมูลธุรกิจ
        </a>
        <a class="nav-link text-danger mt-3" href="<?php echo SITE_URL; ?>/logout.php">
            <i class="bi bi-box-arrow-right me-3 fs-5"></i> ออกจากระบบ
        </a>
    </nav>
</div>
