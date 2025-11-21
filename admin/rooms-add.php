<?php
session_start();
require_once '../config/config.php';
$pdo = getDB();

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// ดึงรายการ Business Profiles
try {
    $business_stmt = $pdo->query("SELECT bp.id, bp.business_name, u.username, u.email
                                    FROM business_profiles bp
                                    LEFT JOIN users u ON bp.user_id = u.id
                                    ORDER BY bp.business_name");
    $businesses = $business_stmt->fetchAll();
} catch (PDOException $e) {
    $businesses = [];
}

// ประมวลผลฟอร์ม
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $business_id = $_POST['business_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $deposit = $_POST['deposit'] ?? 0;
        $room_type = $_POST['room_type'];
        $area = $_POST['area'] ?? null;
        $bedrooms = $_POST['bedrooms'] ?? 0;
        $bathrooms = $_POST['bathrooms'] ?? 1;
        $floor = $_POST['floor'] ?? null;
        $address = $_POST['address'];
        $province = $_POST['province'] ?? 'กรุงเทพมหานคร';
        $district = $_POST['district'];
        $subdistrict = $_POST['subdistrict'] ?? '';
        $postal_code = $_POST['postal_code'] ?? '';
        $latitude = $_POST['latitude'] ?? null;
        $longitude = $_POST['longitude'] ?? null;
        $status = $_POST['status'] ?? 'available';
        $featured = isset($_POST['featured']) ? 1 : 0;

        // Facilities
        $facilities = [];
        if (isset($_POST['facilities'])) {
            $facilities = $_POST['facilities'];
        }
        $facilities_json = json_encode($facilities);

        // Insert room
        $sql = "INSERT INTO rooms (business_id, title, description, price, deposit, room_type,
                area, bedrooms, bathrooms, floor, address, province, district, subdistrict,
                postal_code, latitude, longitude, facilities, status, featured, created_at)
                VALUES (:business_id, :title, :description, :price, :deposit, :room_type,
                :area, :bedrooms, :bathrooms, :floor, :address, :province, :district, :subdistrict,
                :postal_code, :latitude, :longitude, :facilities, :status, :featured, NOW())";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':business_id' => $business_id,
            ':title' => $title,
            ':description' => $description,
            ':price' => $price,
            ':deposit' => $deposit,
            ':room_type' => $room_type,
            ':area' => $area,
            ':bedrooms' => $bedrooms,
            ':bathrooms' => $bathrooms,
            ':floor' => $floor,
            ':address' => $address,
            ':province' => $province,
            ':district' => $district,
            ':subdistrict' => $subdistrict,
            ':postal_code' => $postal_code,
            ':latitude' => $latitude,
            ':longitude' => $longitude,
            ':facilities' => $facilities_json,
            ':status' => $status,
            ':featured' => $featured
        ]);

        $room_id = $pdo->lastInsertId();

        // Handle image upload (if any)
        // TODO: Implement image upload logic

        $_SESSION['success'] = 'เพิ่มห้องเช่าเรียบร้อยแล้ว!';
        header('Location: rooms.php');
        exit();

    } catch (PDOException $e) {
        $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มห้องเช่า - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }

        .dashboard-container {
            display: flex;
        }

        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 30px;
        }

        .page-header {
            background: white;
            padding: 25px 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .page-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 10px;
        }

        .breadcrumb {
            background: none;
            padding: 0;
            margin: 0;
            font-size: 14px;
        }

        .breadcrumb-item a {
            color: #667eea;
            text-decoration: none;
        }

        .form-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }

        .form-card h3 {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            font-size: 14px;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 8px;
            display: block;
        }

        .form-group label .required {
            color: #f56565;
            margin-left: 3px;
        }

        .form-control, .form-select, textarea {
            border: 2px solid #e2e8f0;
            padding: 12px 15px;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            width: 100%;
        }

        .form-control:focus, .form-select:focus, textarea:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 12px;
            margin-top: 10px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .checkbox-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .checkbox-item label {
            margin: 0;
            cursor: pointer;
            font-weight: 500;
        }

        #map {
            height: 400px;
            border-radius: 8px;
            margin-top: 15px;
            border: 2px solid #e2e8f0;
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 35px;
            border-radius: 8px;
            border: none;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-cancel {
            background: #e2e8f0;
            color: #4a5568;
            padding: 14px 35px;
            border-radius: 8px;
            border: none;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-left: 15px;
        }

        .btn-cancel:hover {
            background: #cbd5e0;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .alert-danger {
            background: #fed7d7;
            color: #c53030;
            border: 1px solid #fc8181;
        }

        .form-hint {
            font-size: 12px;
            color: #718096;
            margin-top: 5px;
        }

        .switch-container {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #cbd5e0;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #667eea;
        }

        input:checked + .slider:before {
            transform: translateX(24px);
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1><i class="fas fa-plus-circle"></i> เพิ่มห้องเช่าใหม่</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="rooms.php">ห้องเช่า</a></li>
                        <li class="breadcrumb-item active">เพิ่มห้องใหม่</li>
                    </ol>
                </nav>
            </div>

            <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <!-- ข้อมูลพื้นฐาน -->
                <div class="form-card">
                    <h3><i class="fas fa-info-circle"></i> ข้อมูลพื้นฐาน</h3>

                    <div class="form-group">
                        <label>ผู้ประกอบการ <span class="required">*</span></label>
                        <select name="business_id" class="form-select" required>
                            <option value="">-- เลือกผู้ประกอบการ --</option>
                            <?php foreach ($businesses as $business): ?>
                            <option value="<?php echo $business['id']; ?>">
                                <?php echo htmlspecialchars($business['business_name'] . ' (' . $business['username'] . ')'); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>ชื่อห้องเช่า <span class="required">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="เช่น: คอนโดหรู ใจกลางเมือง วิวสวย" required>
                    </div>

                    <div class="form-group">
                        <label>คำอธิบาย <span class="required">*</span></label>
                        <textarea name="description" class="form-control" placeholder="อธิบายรายละเอียดห้องเช่า สิ่งอำนวยความสะดวก และข้อมูลเพิ่มเติม..." required></textarea>
                        <div class="form-hint">แนะนำให้ใส่รายละเอียดให้ครบถ้วนเพื่อดึงดูดผู้เช่า</div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>ราคาเช่า/เดือน (บาท) <span class="required">*</span></label>
                            <input type="number" name="price" class="form-control" placeholder="0" min="0" step="0.01" required>
                        </div>

                        <div class="form-group">
                            <label>เงินประกัน (บาท)</label>
                            <input type="number" name="deposit" class="form-control" placeholder="0" min="0" step="0.01">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>ประเภทห้อง <span class="required">*</span></label>
                            <select name="room_type" class="form-select" required>
                                <option value="">-- เลือกประเภท --</option>
                                <option value="หอพัก">หอพัก</option>
                                <option value="คอนโด">คอนโด</option>
                                <option value="อพาร์ทเมนท์">อพาร์ทเมนท์</option>
                                <option value="บ้านเช่า">บ้านเช่า</option>
                                <option value="ทาวน์เฮ้าส์">ทาวน์เฮ้าส์</option>
                                <option value="อื่นๆ">อื่นๆ</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>ขนาดห้อง (ตร.ม.)</label>
                            <input type="number" name="area" class="form-control" placeholder="0" min="0" step="0.01">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>จำนวนห้องนอน</label>
                            <input type="number" name="bedrooms" class="form-control" placeholder="0" min="0" value="1">
                        </div>

                        <div class="form-group">
                            <label>จำนวนห้องน้ำ</label>
                            <input type="number" name="bathrooms" class="form-control" placeholder="1" min="1" value="1">
                        </div>

                        <div class="form-group">
                            <label>ชั้น</label>
                            <input type="number" name="floor" class="form-control" placeholder="เช่น: 5" min="0">
                        </div>
                    </div>
                </div>

                <!-- สิ่งอำนวยความสะดวก -->
                <div class="form-card">
                    <h3><i class="fas fa-check-circle"></i> สิ่งอำนวยความสะดวก</h3>
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <input type="checkbox" name="facilities[]" value="wifi" id="wifi">
                            <label for="wifi">📶 WiFi</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" name="facilities[]" value="air" id="air">
                            <label for="air">❄️ เครื่องปรับอากาศ</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" name="facilities[]" value="furniture" id="furniture">
                            <label for="furniture">🛋️ เฟอร์นิเจอร์</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" name="facilities[]" value="parking" id="parking">
                            <label for="parking">🚗 ที่จอดรถ</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" name="facilities[]" value="elevator" id="elevator">
                            <label for="elevator">🛗 ลิฟต์</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" name="facilities[]" value="security" id="security">
                            <label for="security">🔒 รักษาความปลอดภัย</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" name="facilities[]" value="washing_machine" id="washing_machine">
                            <label for="washing_machine">🧺 เครื่องซักผ้า</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" name="facilities[]" value="fridge" id="fridge">
                            <label for="fridge">🧊 ตู้เย็น</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" name="facilities[]" value="tv" id="tv">
                            <label for="tv">📺 ทีวี</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" name="facilities[]" value="water_heater" id="water_heater">
                            <label for="water_heater">🚿 เครื่องทำน้ำอ่อน</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" name="facilities[]" value="kitchen" id="kitchen">
                            <label for="kitchen">🍳 ครัว</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" name="facilities[]" value="balcony" id="balcony">
                            <label for="balcony">🌆 ระเบียง</label>
                        </div>
                    </div>
                </div>

                <!-- ที่อยู่ -->
                <div class="form-card">
                    <h3><i class="fas fa-map-marker-alt"></i> ที่อยู่และตำแหน่ง</h3>

                    <div class="form-group">
                        <label>ที่อยู่ <span class="required">*</span></label>
                        <textarea name="address" id="address" class="form-control" placeholder="บ้านเลขที่, ซอย, ถนน" required></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>จังหวัด <span class="required">*</span></label>
                            <input type="text" name="province" class="form-control" value="กรุงเทพมหานคร" required>
                        </div>

                        <div class="form-group">
                            <label>เขต <span class="required">*</span></label>
                            <select name="district" class="form-select" required>
                                <option value="">-- เลือกเขต --</option>
                                <option value="ห้วยขวาง">ห้วยขวาง</option>
                                <option value="ดินแดง">ดินแดง</option>
                                <option value="บางกะปิ">บางกะปิ</option>
                                <option value="สาทร">สาทร</option>
                                <option value="ปทุมวัน">ปทุมวัน</option>
                                <option value="วัฒนา">วัฒนา</option>
                                <option value="คลองเตย">คลองเตย</option>
                                <option value="บางซื่อ">บางซื่อ</option>
                                <option value="จตุจักร">จตุจักร</option>
                                <option value="ดุสิต">ดุสิต</option>
                                <option value="ราชเทวี">ราชเทวี</option>
                                <option value="ยานนาวา">ยานนาวา</option>
                                <option value="บางรัก">บางรัก</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>แขวง/ตำบล</label>
                            <input type="text" name="subdistrict" class="form-control" placeholder="เช่น: สามเสนใน">
                        </div>

                        <div class="form-group">
                            <label>รหัสไปรษณีย์</label>
                            <input type="text" name="postal_code" class="form-control" placeholder="เช่น: 10400">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>ปักหมุดตำแหน่งบนแผนที่</label>
                        <div class="form-hint">คลิกบนแผนที่เพื่อเลือกตำแหน่งที่ตั้งของห้องเช่า</div>
                        <div id="map"></div>
                        <input type="hidden" name="latitude" id="latitude">
                        <input type="hidden" name="longitude" id="longitude">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Latitude</label>
                            <input type="text" id="lat_display" class="form-control" readonly placeholder="คลิกบนแผนที่">
                        </div>
                        <div class="form-group">
                            <label>Longitude</label>
                            <input type="text" id="lng_display" class="form-control" readonly placeholder="คลิกบนแผนที่">
                        </div>
                    </div>
                </div>

                <!-- สถานะและการตั้งค่า -->
                <div class="form-card">
                    <h3><i class="fas fa-cog"></i> สถานะและการตั้งค่า</h3>

                    <div class="form-group">
                        <label>สถานะห้อง</label>
                        <select name="status" class="form-select">
                            <option value="available">พร้อมให้เช่า</option>
                            <option value="rented">เช่าแล้ว</option>
                            <option value="pending">รออนุมัติ</option>
                            <option value="approved">อนุมัติแล้ว</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>ห้องแนะนำ (Featured)</label>
                        <div class="switch-container">
                            <label class="switch">
                                <input type="checkbox" name="featured" id="featured">
                                <span class="slider"></span>
                            </label>
                            <span>เปิดใช้งานเพื่อแสดงห้องนี้ในหน้าหลัก</span>
                        </div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="form-card">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> บันทึกห้องเช่า
                    </button>
                    <button type="button" class="btn-cancel" onclick="location.href='rooms.php'">
                        <i class="fas fa-times"></i> ยกเลิก
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>&language=th"></script>
    <script>
        let map;
        let marker;

        function initMap() {
            // Default: Bangkok center
            const bangkok = { lat: 13.7563, lng: 100.5018 };

            map = new google.maps.Map(document.getElementById('map'), {
                center: bangkok,
                zoom: 12,
                mapTypeControl: true,
                streetViewControl: true,
                fullscreenControl: true
            });

            // Click to add marker
            map.addListener('click', function(event) {
                placeMarker(event.latLng);
            });

            // Try to get user's location
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    map.setCenter(userLocation);
                });
            }
        }

        function placeMarker(location) {
            if (marker) {
                marker.setPosition(location);
            } else {
                marker = new google.maps.Marker({
                    position: location,
                    map: map,
                    draggable: true,
                    animation: google.maps.Animation.DROP
                });

                marker.addListener('dragend', function(event) {
                    updateLatLng(event.latLng.lat(), event.latLng.lng());
                });
            }

            updateLatLng(location.lat(), location.lng());
        }

        function updateLatLng(lat, lng) {
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
            document.getElementById('lat_display').value = lat.toFixed(6);
            document.getElementById('lng_display').value = lng.toFixed(6);
        }

        // Initialize map when page loads
        window.onload = initMap;
    </script>
</body>
</html>
