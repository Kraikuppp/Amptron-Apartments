<?php
// ตรวจสอบว่าเป็น Admin หรือไม่
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

$current_page = basename($_SERVER["PHP_SELF"]);
?>

<style>
:root {
    --primary-blue: #3b82f6;
    --secondary-blue: #2563eb;
    --medium-gray: #64748b;
    --dark-gray: #1e293b;
    --font-thai: 'IBM Plex Sans Thai', sans-serif;
}

.dashboard-sidebar {
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
    padding: 25px;
    border: 1px solid #f1f5f9;
    position: sticky;
    top: 90px;
    z-index: 10;
    align-self: flex-start;
}

.dashboard-sidebar .sidebar-header {
    text-align: center;
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f1f5f9;
}

.dashboard-sidebar .admin-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 15px;
    box-shadow: 0 10px 20px rgba(59, 130, 246, 0.2);
}

.dashboard-sidebar .admin-avatar i {
    font-size: 2.5rem;
    color: white;
}

.dashboard-sidebar h5 {
    font-weight: 700;
    color: var(--dark-gray);
    margin-bottom: 5px;
}

.dashboard-sidebar .admin-role {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 14px;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 600;
    background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
    color: white;
    margin-top: 10px;
}

.dashboard-sidebar .nav {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.dashboard-sidebar .nav-link {
    color: var(--medium-gray);
    padding: 12px 15px;
    border-radius: 12px;
    margin-bottom: 0;
    transition: all 0.3s ease;
    font-weight: 500;
    display: flex;
    align-items: center;
    text-decoration: none;
}

.dashboard-sidebar .nav-link:hover {
    background: #eff6ff;
    color: var(--primary-blue);
}

.dashboard-sidebar .nav-link.active {
    background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
    color: white !important;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.dashboard-sidebar .nav-link i {
    margin-right: 12px;
    font-size: 1.2rem;
}

.dashboard-sidebar .nav-link .badge {
    margin-left: auto;
    font-size: 0.7rem;
    padding: 3px 8px;
}

.sidebar-footer {
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid #f1f5f9;
}

.logout-btn {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    border: none;
    color: white;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 14px;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
}

.logout-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
}

.logout-btn i {
    margin-right: 8px;
}

@media (max-width: 991px) {
    .dashboard-sidebar {
        position: relative;
        top: 0;
        margin-bottom: 30px;
    }
}
</style>

<div class="dashboard-sidebar">
    <div class="sidebar-header">
        <div class="admin-avatar">
            <i class="bi bi-shield-check"></i>
        </div>
        <h5><?php echo htmlspecialchars(
            $_SESSION["username"] ?? "Admin",
        ); ?></h5>
        <div class="admin-role">
            <i class="bi bi-patch-check-fill"></i>
            ผู้ดูแลระบบ
        </div>
    </div>

    <nav class="nav flex-column">
        <a class="nav-link <?php echo $current_page == "index.php"
            ? "active"
            : ""; ?>" href="index.php">
            <i class="bi bi-speedometer2"></i> แดชบอร์ด
        </a>

        <a class="nav-link <?php echo $current_page == "rooms.php"
            ? "active"
            : ""; ?>" href="rooms.php">
            <i class="bi bi-door-open"></i> ห้องเช่าทั้งหมด
        </a>

        <a class="nav-link <?php echo $current_page == "room-categories.php"
            ? "active"
            : ""; ?>" href="room-categories.php">
            <i class="bi bi-tags"></i> หมวดหมู่ห้อง
        </a>

        <a class="nav-link <?php echo $current_page == "poi-stations.php"
            ? "active"
            : ""; ?>" href="poi-stations.php">
            <i class="bi bi-train-front"></i> สถานีรถไฟฟ้า
        </a>

        <a class="nav-link <?php echo $current_page == "poi-landmarks.php"
            ? "active"
            : ""; ?>" href="poi-landmarks.php">
            <i class="bi bi-geo-alt"></i> จุดสนใจ
        </a>

        <a class="nav-link <?php echo $current_page == "users.php"
            ? "active"
            : ""; ?>" href="users.php">
            <i class="bi bi-people"></i> ผู้ใช้งานทั้งหมด
        </a>

        <a class="nav-link <?php echo $current_page == "users-roles.php"
            ? "active"
            : ""; ?>" href="users-roles.php">
            <i class="bi bi-person-badge"></i> จัดการสิทธิ์
        </a>

        <a class="nav-link <?php echo $current_page == "verification-queue.php"
            ? "active"
            : ""; ?>" href="verification-queue.php">
            <i class="bi bi-clock-history"></i> คิวรออนุมัติ
            <span class="badge bg-danger rounded-pill ms-auto">12</span>
        </a>

        <a class="nav-link <?php echo $current_page ==
        "verification-approved.php"
            ? "active"
            : ""; ?>" href="verification-approved.php">
            <i class="bi bi-check-circle"></i> อนุมัติแล้ว
        </a>

        <a class="nav-link <?php echo $current_page == "reviews.php"
            ? "active"
            : ""; ?>" href="reviews.php">
            <i class="bi bi-star"></i> รีวิวทั้งหมด
        </a>

        <a class="nav-link <?php echo $current_page == "reviews-flagged.php"
            ? "active"
            : ""; ?>" href="reviews-flagged.php">
            <i class="bi bi-flag"></i> รีวิวที่ถูกรายงาน
        </a>

        <a class="nav-link <?php echo $current_page == "analytics-overview.php"
            ? "active"
            : ""; ?>" href="analytics-overview.php">
            <i class="bi bi-graph-up"></i> ภาพรวมสถิติ
        </a>

        <a class="nav-link <?php echo $current_page == "analytics-rooms.php"
            ? "active"
            : ""; ?>" href="analytics-rooms.php">
            <i class="bi bi-bar-chart"></i> สถิติห้องเช่า
        </a>

        <a class="nav-link <?php echo $current_page == "settings-general.php"
            ? "active"
            : ""; ?>" href="settings-general.php">
            <i class="bi bi-gear"></i> ตั้งค่าทั่วไป
        </a>

        <a class="nav-link <?php echo $current_page == "settings-email.php"
            ? "active"
            : ""; ?>" href="settings-email.php">
            <i class="bi bi-envelope"></i> ตั้งค่าอีเมล
        </a>
    </nav>

    <div class="sidebar-footer">
        <button onclick="location.href='../logout.php'" class="logout-btn">
            <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
        </button>
    </div>
</div>
