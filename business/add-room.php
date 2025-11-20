<?php
require_once '../config/config.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || !isBusiness()) {
    redirect('../login.php');
}

$db = getDB();
$businessProfile = getBusinessProfile($_SESSION['user_id']);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $deposit = $_POST['deposit'] ?? 0;
    $room_type = $_POST['room_type'] ?? '';
    $area = $_POST['area'] ?? 0;
    $bedrooms = $_POST['bedrooms'] ?? 0;
    $bathrooms = $_POST['bathrooms'] ?? 1;
    $floor = $_POST['floor'] ?? null;
    $address = $_POST['address'] ?? '';
    $province = $_POST['province'] ?? '';
    $district = $_POST['district'] ?? '';
    $subdistrict = $_POST['subdistrict'] ?? '';
    $postal_code = $_POST['postal_code'] ?? '';
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;
    $facilities = $_POST['facilities'] ?? '';
    
    if (empty($title) || empty($description) || empty($price) || empty($address)) {
        $error = 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน';
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO rooms (business_id, title, description, price, deposit, room_type, area, bedrooms, bathrooms, floor, address, province, district, subdistrict, postal_code, latitude, longitude, facilities, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$businessProfile['id'], $title, $description, $price, $deposit, $room_type, $area, $bedrooms, $bathrooms, $floor, $address, $province, $district, $subdistrict, $postal_code, $latitude, $longitude, $facilities]);
            
            $roomId = $db->lastInsertId();
            
            // อัปโหลดรูปภาพ
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                foreach ($_FILES['images']['name'] as $key => $name) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $file = [
                            'name' => $name,
                            'type' => $_FILES['images']['type'][$key],
                            'tmp_name' => $_FILES['images']['tmp_name'][$key],
                            'error' => $_FILES['images']['error'][$key],
                            'size' => $_FILES['images']['size'][$key]
                        ];
                        
                        $imagePath = uploadFile($file, 'rooms');
                        if ($imagePath) {
                            $isPrimary = $key === 0 ? 1 : 0;
                            $stmt = $db->prepare("INSERT INTO room_images (room_id, image_path, is_primary) VALUES (?, ?, ?)");
                            $stmt->execute([$roomId, $imagePath, $isPrimary]);
                        }
                    }
                }
            }
            
            logActivity($_SESSION['user_id'], 'create', 'rooms', $roomId, 'เพิ่มห้องเช่า: ' . $title);
            $success = 'เพิ่มห้องเช่าสำเร็จ! รอการอนุมัติจากผู้ดูแลระบบ';
            
        } catch (PDOException $e) {
            $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มห้องเช่า - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        #map {
            height: 300px;
            width: 100%;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container my-4">
        <div class="row">
            <div class="col-md-3">
                <?php include 'sidebar.php'; ?>
            </div>
            
            <div class="col-md-9">
                <h2 class="mb-4">เพิ่มห้องเช่า</h2>
                
                <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="card mb-3">
                        <div class="card-header">ข้อมูลพื้นฐาน</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">ชื่อห้อง <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">รายละเอียด <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control" rows="5" required></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">ราคาเช่า (บาท/เดือน) <span class="text-danger">*</span></label>
                                    <input type="number" name="price" class="form-control" step="0.01" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">เงินมัดจำ (บาท)</label>
                                    <input type="number" name="deposit" class="form-control" step="0.01" value="0">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">ประเภทห้อง</label>
                                    <select name="room_type" class="form-select">
                                        <option value="">เลือกประเภท</option>
                                        <option value="ห้องพัก">ห้องพัก</option>
                                        <option value="คอนโด">คอนโด</option>
                                        <option value="อพาร์ตเมนต์">อพาร์ตเมนต์</option>
                                        <option value="บ้าน">บ้าน</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">พื้นที่ (ตร.ม.)</label>
                                    <input type="number" name="area" class="form-control" step="0.01">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">ห้องนอน</label>
                                    <input type="number" name="bedrooms" class="form-control" value="0">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">ห้องน้ำ</label>
                                    <input type="number" name="bathrooms" class="form-control" value="1">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">ชั้น</label>
                                    <input type="number" name="floor" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-3">
                        <div class="card-header">ที่อยู่</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">ที่อยู่ <span class="text-danger">*</span></label>
                                <textarea name="address" class="form-control" rows="2" required></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">จังหวัด</label>
                                    <input type="text" name="province" class="form-control">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">อำเภอ/เขต</label>
                                    <input type="text" name="district" class="form-control">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">ตำบล/แขวง</label>
                                    <input type="text" name="subdistrict" class="form-control">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">รหัสไปรษณีย์</label>
                                    <input type="text" name="postal_code" class="form-control">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">ละติจูด</label>
                                    <input type="text" name="latitude" id="latitude" class="form-control" readonly>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">ลองจิจูด</label>
                                    <input type="text" name="longitude" id="longitude" class="form-control" readonly>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">เลือกตำแหน่งบนแผนที่</label>
                                <div id="map"></div>
                                <small class="text-muted">คลิกบนแผนที่เพื่อกำหนดตำแหน่ง</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-3">
                        <div class="card-header">สิ่งอำนวยความสะดวก</div>
                        <div class="card-body">
                            <textarea name="facilities" class="form-control" rows="3" placeholder="เช่น แอร์, WiFi, ที่จอดรถ, ฯลฯ"></textarea>
                        </div>
                    </div>
                    
                    <div class="card mb-3">
                        <div class="card-header">รูปภาพ</div>
                        <div class="card-body">
                            <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                            <small class="text-muted">สามารถอัปโหลดได้หลายไฟล์ รูปแรกจะเป็นรูปหลัก</small>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> บันทึก
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary">ยกเลิก</a>
                </form>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>&callback=initMap" async defer></script>
    <script>
        let map;
        let marker;
        
        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                center: { lat: 13.7563, lng: 100.5018 },
                zoom: 12
            });
            
            map.addListener('click', (e) => {
                const lat = e.latLng.lat();
                const lng = e.latLng.lng();
                
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
                
                if (marker) {
                    marker.setMap(null);
                }
                
                marker = new google.maps.Marker({
                    position: { lat, lng },
                    map: map
                });
            });
        }
    </script>
</body>
</html>

