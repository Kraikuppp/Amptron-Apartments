<?php
require_once "config/config.php";

// ตรวจสอบว่ามี admin อยู่หรือยัง ถ้ามีแล้วไม่ให้รันได้
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
$stmt->execute();
$adminCount = $stmt->fetch()['count'];

$message = '';
$error = '';
$users_created = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup'])) {
    try {
        // เริ่ม transaction
        $pdo->beginTransaction();

        // 1. สร้าง Admin User
        $stmt = $pdo->prepare("
            INSERT INTO users (username, password, email, full_name, phone, role, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())
            ON DUPLICATE KEY UPDATE
                password = VALUES(password),
                email = VALUES(email),
                full_name = VALUES(full_name),
                role = VALUES(role),
                status = 'active'
        ");

        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt->execute(['admin', $adminPassword, 'admin@renthub.com', 'Admin User', '02-123-4567', 'admin']);
        $users_created[] = ['username' => 'admin', 'password' => 'admin123', 'role' => 'Admin'];

        // 2. สร้าง Business User
        $businessPassword = password_hash('business123', PASSWORD_DEFAULT);
        $stmt->execute(['business', $businessPassword, 'business@renthub.com', 'ธุรกิจห้องเช่า RentHub', '081-234-5678', 'business']);
        $users_created[] = ['username' => 'business', 'password' => 'business123', 'role' => 'Business Owner'];

        // 3. สร้าง Customer User
        $customerPassword = password_hash('customer123', PASSWORD_DEFAULT);
        $stmt->execute(['customer', $customerPassword, 'customer@renthub.com', 'ลูกค้าทดสอบ', '089-999-8888', 'customer']);
        $users_created[] = ['username' => 'customer', 'password' => 'customer123', 'role' => 'Customer'];

        // 4. ดึง user_id ของ Business
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'business' LIMIT 1");
        $stmt->execute();
        $businessUserId = $stmt->fetch()['id'];

        // 5. สร้าง Business Profile (ถ้ายังไม่มี)
        $stmt = $pdo->prepare("
            INSERT INTO business_profiles (
                user_id, business_name, business_type, description,
                address, district, province, postal_code, line_id,
                verification_status, subscription_plan, subscription_expires_at, credits
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                business_name = VALUES(business_name),
                description = VALUES(description),
                verification_status = 'verified',
                subscription_plan = VALUES(subscription_plan)
        ");

        $stmt->execute([
            $businessUserId,
            'RentHub Properties',
            'property_rental',
            'ผู้ให้บริการห้องเช่าคุณภาพ มีห้องให้เลือกหลากหลายรูปแบบ ทั้งคอนโด อพาร์ทเมนท์ และหอพัก ในทำเลที่ดีรอบกรุงเทพฯ',
            '123/45 ถนนสุขุมวิท แขวงคลองเตย',
            'คลองเตย',
            'Bangkok',
            '10110',
            '@renthub',
            'verified',
            'premium',
            date('Y-m-d H:i:s', strtotime('+1 year')),
            100
        ]);

        // 6. สร้างห้องตัวอย่างสำหรับ Business User
        $properties = [
            [
                'title' => 'คอนโด Lumpini Park Rama 9 - ห้องสวย พร้อมเข้าอยู่',
                'description' => 'คอนโดหรูใจกลางเมือง ติดรถไฟฟ้า MRT พระราม 9 เพียง 300 เมตร วิวสวย เฟอร์นิเจอร์ครบ เหมาะสำหรับคนทำงาน',
                'property_type' => 'condo',
                'status' => 'available',
                'address' => '99 ถนนพระราม 9 แขวงห้วยขวาง',
                'district' => 'ห้วยขวาง',
                'latitude' => 13.756300,
                'longitude' => 100.565700,
                'room_size' => 32.5,
                'price' => 12000,
                'amenities' => 'WiFi,แอร์,เครื่องซักผ้า,ตู้เย็น,เตียง,โต๊ะทำงาน,ตู้เสื้อผ้า,ทีวี,ฟิตเนส,สระว่ายน้ำ,ที่จอดรถ,รักษาความปลอดภัย 24 ชม.',
                'near_transit' => 'MRT พระราม 9',
                'image' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800'
            ],
            [
                'title' => 'อพาร์ทเมนท์สุขุมวิท 77 - ราคาประหยัด',
                'description' => 'อพาร์ทเมนท์สะอาด ปลอดภัย ใกล้ BTS อ่อนนุช ห้างสรรพสินค้า และแหล่งอาหาร สะดวกสบายทุกการใช้ชีวิต',
                'property_type' => 'apartment',
                'status' => 'available',
                'address' => '156/8 ซอยสุขุมวิท 77',
                'district' => 'พระโขนง',
                'latitude' => 13.705300,
                'longitude' => 100.604800,
                'room_size' => 28,
                'price' => 7500,
                'amenities' => 'WiFi,แอร์,ตู้เย็น,เตียง,โต๊ะทำงาน,ตู้เสื้อผ้า,ที่จอดรถ',
                'near_transit' => 'BTS อ่อนนุช',
                'image' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=800'
            ],
            [
                'title' => 'หอพักรัชดา 36 - ใกล้ MRT สุทธิสาร',
                'description' => 'หอพักสะอาด ปลอดภัย มีกล้องวงจรปิด ผู้จัดการดูแลตลอด 24 ชั่วโมง',
                'property_type' => 'dormitory',
                'status' => 'available',
                'address' => '248 ซอยรัชดาภิเษก 36',
                'district' => 'ดินแดง',
                'latitude' => 13.765000,
                'longitude' => 100.559600,
                'room_size' => 22,
                'price' => 5500,
                'amenities' => 'WiFi,แอร์,เตียง,โต๊ะทำงาน,ตู้เสื้อผ้า,เครื่องทำน้ำอุ่น',
                'near_transit' => 'MRT สุทธิสาร',
                'image' => 'https://images.unsplash.com/photo-1554995207-c18c203602cb?w=800'
            ]
        ];

        $stmt = $pdo->prepare("
            INSERT INTO business_properties (
                business_id, title, description, property_type, status,
                address, district, province, latitude, longitude,
                room_size, bedrooms, bathrooms, floor_number,
                price, deposit, water_cost, electricity_cost, common_fee,
                amenities, near_transit, transit_distance, available_from
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'Bangkok', ?, ?, ?, 1, 1, 5, ?, ?, 18, 7, 0, ?, ?, 300, CURDATE())
        ");

        foreach ($properties as $prop) {
            $stmt->execute([
                $businessUserId,
                $prop['title'],
                $prop['description'],
                $prop['property_type'],
                $prop['status'],
                $prop['address'],
                $prop['district'],
                $prop['latitude'],
                $prop['longitude'],
                $prop['room_size'],
                $prop['price'],
                $prop['price'] * 2,
                $prop['amenities'],
                $prop['near_transit']
            ]);

            // เพิ่มรูปภาพ
            $propertyId = $pdo->lastInsertId();
            $imgStmt = $pdo->prepare("
                INSERT INTO property_images (property_id, image_path, image_type, sort_order)
                VALUES (?, ?, 'main', 1)
            ");
            $imgStmt->execute([$propertyId, $prop['image']]);
        }

        // Commit transaction
        $pdo->commit();

        $message = 'สร้าง Users และข้อมูลทดสอบสำเร็จ!';

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Users - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --sky-blue: #0EA5E9;
            --light-blue: #E0F2FE;
            --dark-blue: #0C4A6E;
        }

        body {
            background: linear-gradient(135deg, #E0F2FE 0%, #F0F9FF 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .setup-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(14, 165, 233, 0.2);
            max-width: 800px;
            width: 100%;
            padding: 40px;
        }

        .logo {
            font-size: 3rem;
            color: var(--sky-blue);
            text-align: center;
            margin-bottom: 20px;
        }

        .user-card {
            background: linear-gradient(135deg, var(--light-blue) 0%, #F0F9FF 100%);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            border: 2px solid var(--sky-blue);
        }

        .user-card h5 {
            color: var(--dark-blue);
            font-weight: 600;
        }

        .credential {
            background: white;
            padding: 10px 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            margin: 5px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--sky-blue) 0%, var(--dark-blue) 100%);
            border: none;
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #0284C7 0%, #075985 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(14, 165, 233, 0.4);
        }

        .badge-role {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
        }

        .role-admin {
            background: #FEE2E2;
            color: #991B1B;
        }

        .role-business {
            background: #DBEAFE;
            color: #1E40AF;
        }

        .role-customer {
            background: #D1FAE5;
            color: #065F46;
        }

        .success-icon {
            font-size: 4rem;
            color: #10B981;
            text-align: center;
            animation: checkmark 0.5s ease-in-out;
        }

        @keyframes checkmark {
            0% {
                transform: scale(0);
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
            }
        }
    </style>
</head>
<body>
    <div class="setup-card">
        <div class="logo">
            <i class="bi bi-house-heart-fill"></i>
        </div>

        <h2 class="text-center mb-4" style="color: var(--dark-blue); font-weight: 700;">
            Setup Test Users
        </h2>

        <?php if ($message): ?>
            <div class="alert alert-success">
                <div class="success-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <h4 class="text-center mt-3"><?php echo $message; ?></h4>
            </div>

            <div class="mt-4">
                <h5 class="mb-3" style="color: var(--dark-blue);">
                    <i class="bi bi-person-badge"></i> ข้อมูลการเข้าสู่ระบบ
                </h5>

                <?php foreach ($users_created as $user): ?>
                <div class="user-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i class="bi bi-person-circle"></i> <?php echo $user['role']; ?>
                        </h5>
                        <span class="badge-role role-<?php echo strtolower(explode(' ', $user['role'])[0]); ?>">
                            <?php echo $user['role']; ?>
                        </span>
                    </div>

                    <div class="credential">
                        <span><strong>Username:</strong> <?php echo $user['username']; ?></span>
                        <button class="btn btn-sm btn-outline-primary" onclick="copyToClipboard('<?php echo $user['username']; ?>')">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>

                    <div class="credential">
                        <span><strong>Password:</strong> <?php echo $user['password']; ?></span>
                        <button class="btn btn-sm btn-outline-primary" onclick="copyToClipboard('<?php echo $user['password']; ?>')">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-4">
                <a href="login.php" class="btn btn-primary">
                    <i class="bi bi-box-arrow-in-right"></i> ไปหน้า Login
                </a>
                <a href="index.php" class="btn btn-outline-secondary ms-2">
                    <i class="bi bi-house"></i> กลับหน้าแรก
                </a>
            </div>

        <?php elseif ($error): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <?php echo $error; ?>
            </div>
            <div class="text-center">
                <button type="button" class="btn btn-primary" onclick="location.reload()">
                    <i class="bi bi-arrow-clockwise"></i> ลองอีกครั้ง
                </button>
            </div>

        <?php else: ?>
            <div class="text-center mb-4">
                <p class="lead">สร้าง Users ทดสอบสำหรับระบบ</p>
            </div>

            <div class="alert alert-info">
                <h6><i class="bi bi-info-circle"></i> ระบบจะสร้าง Users ดังนี้:</h6>
                <ul class="mb-0">
                    <li><strong>Admin User</strong> - สำหรับผู้ดูแลระบบ (จัดการทั้งหมด)</li>
                    <li><strong>Business User</strong> - สำหรับผู้ประกอบการ (จัดการห้องเช่า)</li>
                    <li><strong>Customer User</strong> - สำหรับลูกค้าทั่วไป</li>
                </ul>
            </div>

            <div class="user-card">
                <h5><i class="bi bi-shield-check"></i> Admin User</h5>
                <div class="credential">
                    <span>Username: <code>admin</code></span>
                    <span>Password: <code>admin123</code></span>
                </div>
                <small class="text-muted">สามารถเข้าถึงหน้า Admin Panel และจัดการทุกอย่างในระบบ</small>
            </div>

            <div class="user-card">
                <h5><i class="bi bi-building"></i> Business User</h5>
                <div class="credential">
                    <span>Username: <code>business</code></span>
                    <span>Password: <code>business123</code></span>
                </div>
                <small class="text-muted">สามารถเข้า Business Dashboard และจัดการห้องเช่าของตัวเอง พร้อมห้องตัวอย่าง 3 ห้อง</small>
            </div>

            <div class="user-card">
                <h5><i class="bi bi-person"></i> Customer User</h5>
                <div class="credential">
                    <span>Username: <code>customer</code></span>
                    <span>Password: <code>customer123</code></span>
                </div>
                <small class="text-muted">ผู้ใช้ทั่วไป สามารถค้นหาห้อง บันทึก Wishlist และติดต่อผู้ประกอบการ</small>
            </div>

            <?php if ($adminCount > 0): ?>
            <div class="alert alert-warning mt-3">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>หมายเหตุ:</strong> พบว่ามี Admin อยู่ในระบบแล้ว การรันจะเป็นการอัปเดตข้อมูล Users เหล่านี้
            </div>
            <?php endif; ?>

            <form method="POST" class="mt-4">
                <div class="text-center">
                    <button type="submit" name="setup" class="btn btn-primary btn-lg">
                        <i class="bi bi-rocket-takeoff"></i> สร้าง Users ทดสอบ
                    </button>
                </div>
            </form>
        <?php endif; ?>

        <div class="text-center mt-4">
            <small class="text-muted">
                <i class="bi bi-shield-lock"></i>
                ข้อมูลทั้งหมดจะถูกเก็บไว้ในฐานข้อมูล และเข้ารหัสอย่างปลอดภัย
            </small>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                // Show toast notification
                const toast = document.createElement('div');
                toast.className = 'position-fixed top-0 end-0 p-3';
                toast.style.zIndex = '9999';
                toast.innerHTML = `
                    <div class="toast show" role="alert">
                        <div class="toast-header bg-success text-white">
                            <i class="bi bi-check-circle me-2"></i>
                            <strong class="me-auto">คัดลอกแล้ว!</strong>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                        </div>
                        <div class="toast-body">
                            คัดลอก "${text}" ไปยังคลิปบอร์ดแล้ว
                        </div>
                    </div>
                `;
                document.body.appendChild(toast);

                setTimeout(() => {
                    toast.remove();
                }, 3000);
            });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
