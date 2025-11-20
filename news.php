<?php
require_once "config/config.php";
require_once "includes/auth.php";

// Ensure user is logged in
if (!isLoggedIn()) {
    redirect("login.php");
}

// Mock Data for News
$newsData = [
    [
        'id' => 1,
        'title' => 'แจ้งกำหนดการล้างแทงค์น้ำประจำปี',
        'date' => '2024-11-15',
        'content' => 'ทางหอพักจะดำเนินการล้างแทงค์น้ำในวันที่ 25 พฤศจิกายน 2567 เวลา 10.00 - 15.00 น. อาจทำให้น้ำไหลอ่อนหรือไม่ไหลในช่วงเวลาดังกล่าว ขออภัยในความไม่สะดวก',
        'type' => 'maintenance', 
        'image' => 'https://plus.unsplash.com/premium_photo-1664299312933-f81ee9e02126?q=80&w=1171&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'
    ],
    [
        'id' => 2,
        'title' => 'กิจกรรม Big Cleaning Day',
        'date' => '2024-11-10',
        'content' => 'ขอเชิญชวนผู้พักอาศัยทุกท่านร่วมกิจกรรม Big Cleaning Day ทำความสะอาดพื้นที่ส่วนกลาง ในวันอาทิตย์ที่ 1 ธันวาคม 2567 เวลา 09.00 น. เป็นต้นไป มีของว่างและเครื่องดื่มบริการ',
        'type' => 'activity',
        'image' => 'https://plus.unsplash.com/premium_photo-1661662917928-b1a42a08d094?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'
    ],
    [
        'id' => 3,
        'title' => 'ปรับปรุงระบบอินเทอร์เน็ต',
        'date' => '2024-11-05',
        'content' => 'แจ้งปรับปรุงระบบอินเทอร์เน็ตเพื่อเพิ่มความเร็วและความเสถียร ในคืนวันที่ 20 พฤศจิกายน 2567 เวลา 02.00 - 04.00 น. อินเทอร์เน็ตจะไม่สามารถใช้งานได้ชั่วคราว',
        'type' => 'maintenance',
        'image' => 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'
    ],
    [
        'id' => 4,
        'title' => 'ระเบียบการใช้ห้องฟิตเนสใหม่',
        'date' => '2024-11-01',
        'content' => 'แจ้งปรับเปลี่ยนเวลาเปิด-ปิดห้องฟิตเนส เป็นเวลา 06.00 - 22.00 น. และขอความร่วมมือผู้ใช้บริการทุกท่านสวมรองเท้ากีฬาและนำผ้าขนหนูส่วนตัวมาด้วยทุกครั้ง',
        'type' => 'announcement',
        'image' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'
    ]
];

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข่าวสารและประกาศ - <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/img/amptron-apartments.png?v=2">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@100;200;300;400;500;600;700&family=League+Spartan:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        /* Page Specific Styles */
        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-thai) !important;
        }

        .page-header-section {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            padding: 120px 0 80px;
            color: white;
            position: relative;
            overflow: hidden;
            margin-bottom: -60px;
            border-radius: 0 0 50px 50px;
        }
        
        .page-header-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: radial-gradient(rgba(255, 255, 255, 0.1) 1px, transparent 1px);
            background-size: 30px 30px;
            opacity: 0.5;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            overflow: hidden;
            height: 100%;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 60px rgba(37, 99, 235, 0.15);
        }

        .news-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .news-content {
            padding: 24px;
        }

        .news-date {
            color: var(--medium-gray);
            font-size: 0.9rem;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .news-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark-gray);
            margin-bottom: 12px;
            line-height: 1.4;
        }

        .news-desc {
            color: var(--medium-gray);
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .news-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(4px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .badge-maintenance {
            color: #ea580c;
            border: 1px solid #ffedd5;
        }

        .badge-activity {
            color: #16a34a;
            border: 1px solid #dcfce7;
        }

        .badge-announcement {
            color: #2563eb;
            border: 1px solid #dbeafe;
        }

        .read-more-btn {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: gap 0.3s ease;
        }

        .read-more-btn:hover {
            gap: 10px;
        }
    </style>
</head>
<body>

    <?php include "includes/header.php"; ?>

    <!-- Page Header -->
    <div class="page-header-section">
        <div class="page-header-pattern"></div>
        <div class="container position-relative z-2 text-center">
            <h1 class="display-4 fw-bold mb-3">ข่าวสารและประกาศ</h1>
            <p class="lead opacity-90 mb-0">ติดตามข่าวสาร กิจกรรม และประกาศสำคัญจากทางหอพัก</p>
        </div>
    </div>

    <div class="container pb-5" style="margin-top: -20px;">
        <div class="row g-4">
            <?php foreach ($newsData as $news): ?>
            <div class="col-md-6 col-lg-4">
                <div class="glass-card position-relative">
                    <span class="news-badge badge-<?php echo $news['type']; ?>">
                        <?php 
                        switch($news['type']) {
                            case 'maintenance': echo '<i class="bi bi-tools me-1"></i> แจ้งซ่อม/บำรุง'; break;
                            case 'activity': echo '<i class="bi bi-calendar-event me-1"></i> กิจกรรม'; break;
                            default: echo '<i class="bi bi-megaphone me-1"></i> ประกาศ';
                        }
                        ?>
                    </span>
                    <img src="<?php echo $news['image']; ?>" alt="<?php echo $news['title']; ?>" class="news-image">
                    <div class="news-content">
                        <div class="news-date">
                            <i class="bi bi-calendar3"></i>
                            <?php echo date('d/m/Y', strtotime($news['date'])); ?>
                        </div>
                        <h3 class="news-title"><?php echo $news['title']; ?></h3>
                        <p class="news-desc"><?php echo $news['content']; ?></p>
                        <a href="#" class="read-more-btn" 
                           data-bs-toggle="modal" 
                           data-bs-target="#newsModal"
                           data-title="<?php echo htmlspecialchars($news['title']); ?>"
                           data-date="<?php echo date('d/m/Y', strtotime($news['date'])); ?>"
                           data-content="<?php echo htmlspecialchars($news['content']); ?>"
                           data-image="<?php echo $news['image']; ?>"
                           data-type="<?php echo $news['type']; ?>">
                            อ่านเพิ่มเติม <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php include "includes/footer.php"; ?>

    <!-- News Detail Modal -->
    <div class="modal fade" id="newsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header border-0 position-relative p-0" style="height: 300px;">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 z-3 bg-dark p-2 rounded-circle opacity-75" data-bs-dismiss="modal" aria-label="Close"></button>
                    <img src="" id="modalNewsImage" class="w-100 h-100 object-fit-cover" alt="News Image">
                    <div class="position-absolute bottom-0 start-0 w-100 p-4" style="background: linear-gradient(to top, rgba(0,0,0,0.9), transparent);">
                        <span id="modalNewsBadge" class="badge mb-2 px-3 py-2 rounded-pill"></span>
                        <h3 id="modalNewsTitle" class="text-white mb-1 fw-bold" style="text-shadow: 0 2px 4px rgba(0,0,0,0.5);"></h3>
                        <div class="text-white-50">
                            <i class="bi bi-calendar3 me-2"></i><span id="modalNewsDate"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-body p-4 p-lg-5">
                    <p id="modalNewsContent" class="text-secondary mb-0" style="line-height: 1.8; font-size: 1.1rem; white-space: pre-line;"></p>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const newsModal = document.getElementById('newsModal');
            if (newsModal) {
                newsModal.addEventListener('show.bs.modal', function(event) {
                    // Button that triggered the modal
                    const button = event.relatedTarget;
                    
                    // Extract info from data-* attributes
                    const title = button.getAttribute('data-title');
                    const date = button.getAttribute('data-date');
                    const content = button.getAttribute('data-content');
                    const image = button.getAttribute('data-image');
                    const type = button.getAttribute('data-type');
                    
                    // Update the modal's content.
                    const modalTitle = newsModal.querySelector('#modalNewsTitle');
                    const modalDate = newsModal.querySelector('#modalNewsDate');
                    const modalContent = newsModal.querySelector('#modalNewsContent');
                    const modalImage = newsModal.querySelector('#modalNewsImage');
                    const modalBadge = newsModal.querySelector('#modalNewsBadge');
                    
                    modalTitle.textContent = title;
                    modalDate.textContent = date;
                    modalContent.textContent = content;
                    modalImage.src = image;
                    
                    // Set badge style and text based on type
                    let badgeClass = '';
                    let badgeText = '';
                    let badgeIcon = '';
                    
                    switch(type) {
                        case 'maintenance':
                            badgeClass = 'bg-warning text-dark';
                            badgeText = 'แจ้งซ่อม/บำรุง';
                            badgeIcon = '<i class="bi bi-tools me-1"></i>';
                            break;
                        case 'activity':
                            badgeClass = 'bg-success';
                            badgeText = 'กิจกรรม';
                            badgeIcon = '<i class="bi bi-calendar-event me-1"></i>';
                            break;
                        default: // announcement
                            badgeClass = 'bg-primary';
                            badgeText = 'ประกาศ';
                            badgeIcon = '<i class="bi bi-megaphone me-1"></i>';
                    }
                    
                    modalBadge.className = 'badge mb-2 px-3 py-2 rounded-pill ' + badgeClass;
                    modalBadge.innerHTML = badgeIcon + ' ' + badgeText;
                });
            }
        });
    </script>
</body>
</html>
