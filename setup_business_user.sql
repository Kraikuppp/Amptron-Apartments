-- ============================================
-- Setup Business User และข้อมูลทดสอบ
-- ============================================

-- 1. เพิ่ม Business User
-- Username: business / Password: business123
INSERT INTO users (username, password, email, full_name, phone, role, status, created_at)
VALUES (
    'business',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: business123
    'business@renthub.com',
    'ธุรกิจห้องเช่า RentHub',
    '081-234-5678',
    'business',
    'active',
    NOW()
) ON DUPLICATE KEY UPDATE
    email = 'business@renthub.com',
    full_name = 'ธุรกิจห้องเช่า RentHub',
    phone = '081-234-5678',
    role = 'business',
    status = 'active';

-- 2. สร้างตาราง business_profiles (ถ้ายังไม่มี)
CREATE TABLE IF NOT EXISTS business_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    business_name VARCHAR(255) NOT NULL,
    business_type VARCHAR(100) DEFAULT 'property_rental',
    description TEXT,
    address TEXT,
    district VARCHAR(100),
    province VARCHAR(100) DEFAULT 'Bangkok',
    postal_code VARCHAR(10),
    tax_id VARCHAR(20),
    logo_url VARCHAR(500),
    website VARCHAR(255),
    line_id VARCHAR(100),
    facebook_url VARCHAR(255),
    business_hours TEXT,
    verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    verification_documents TEXT,
    subscription_plan ENUM('free', 'basic', 'premium', 'enterprise') DEFAULT 'free',
    subscription_expires_at DATETIME,
    credits INT DEFAULT 0,
    total_properties INT DEFAULT 0,
    total_views INT DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_business (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. เพิ่ม Business Profile
SET @business_user_id = (SELECT id FROM users WHERE username = 'business' LIMIT 1);

INSERT INTO business_profiles (
    user_id,
    business_name,
    business_type,
    description,
    address,
    district,
    province,
    postal_code,
    line_id,
    verification_status,
    subscription_plan,
    subscription_expires_at,
    credits
) VALUES (
    @business_user_id,
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
    DATE_ADD(NOW(), INTERVAL 1 YEAR),
    100
) ON DUPLICATE KEY UPDATE
    business_name = 'RentHub Properties',
    description = 'ผู้ให้บริการห้องเช่าคุณภาพ มีห้องให้เลือกหลากหลายรูปแบบ ทั้งคอนโด อพาร์ทเมนท์ และหอพัก ในทำเลที่ดีรอบกรุงเทพฯ',
    verification_status = 'verified',
    subscription_plan = 'premium';

-- 4. สร้างตาราง business_properties (ห้องเช่าของผู้ประกอบการ)
CREATE TABLE IF NOT EXISTS business_properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    property_type ENUM('condo', 'apartment', 'dormitory', 'house', 'office') DEFAULT 'condo',
    status ENUM('available', 'rented', 'maintenance', 'hidden') DEFAULT 'available',

    -- ข้อมูลที่อยู่
    address TEXT,
    district VARCHAR(100),
    province VARCHAR(100) DEFAULT 'Bangkok',
    postal_code VARCHAR(10),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),

    -- ข้อมูลห้อง
    room_size DECIMAL(10, 2),
    bedrooms INT DEFAULT 1,
    bathrooms INT DEFAULT 1,
    floor_number INT,

    -- ราคาและค่าใช้จ่าย
    price DECIMAL(10, 2) NOT NULL,
    deposit DECIMAL(10, 2),
    water_cost DECIMAL(10, 2),
    electricity_cost DECIMAL(10, 2),
    common_fee DECIMAL(10, 2),

    -- สิ่งอำนวยความสะดวก
    amenities TEXT,
    furniture TEXT,

    -- ข้อมูลเพิ่มเติม
    near_transit VARCHAR(255),
    transit_distance INT,
    contract_type VARCHAR(50),
    minimum_stay INT DEFAULT 3,
    pets_allowed BOOLEAN DEFAULT FALSE,

    -- สถิติ
    views INT DEFAULT 0,
    favorites INT DEFAULT 0,
    contacts INT DEFAULT 0,

    -- โปรโมชัน
    is_promoted BOOLEAN DEFAULT FALSE,
    promotion_expires_at DATETIME,
    discount_percentage INT DEFAULT 0,

    -- เวลา
    available_from DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (business_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_district (district),
    INDEX idx_price (price),
    INDEX idx_location (latitude, longitude)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. สร้างตาราง property_images
CREATE TABLE IF NOT EXISTS property_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    image_type ENUM('main', 'gallery', 'floor_plan') DEFAULT 'gallery',
    sort_order INT DEFAULT 0,
    caption VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES business_properties(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. สร้างตาราง property_promotions
CREATE TABLE IF NOT EXISTS property_promotions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    promotion_title VARCHAR(255) NOT NULL,
    promotion_description TEXT,
    discount_type ENUM('percentage', 'fixed_amount') DEFAULT 'percentage',
    discount_value DECIMAL(10, 2) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES business_properties(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. สร้างตาราง property_analytics
CREATE TABLE IF NOT EXISTS property_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    date DATE NOT NULL,
    views INT DEFAULT 0,
    detail_views INT DEFAULT 0,
    favorites INT DEFAULT 0,
    contacts INT DEFAULT 0,
    shares INT DEFAULT 0,
    FOREIGN KEY (property_id) REFERENCES business_properties(id) ON DELETE CASCADE,
    UNIQUE KEY unique_property_date (property_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. สร้างตาราง business_messages
CREATE TABLE IF NOT EXISTS business_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    conversation_id VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES business_properties(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_conversation (conversation_id),
    INDEX idx_receiver (receiver_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. เพิ่มห้องตัวอย่างสำหรับ Business User
INSERT INTO business_properties (
    business_id, title, description, property_type, status,
    address, district, province, latitude, longitude,
    room_size, bedrooms, bathrooms, floor_number,
    price, deposit, water_cost, electricity_cost, common_fee,
    amenities, furniture, near_transit, transit_distance,
    available_from
) VALUES
(
    @business_user_id,
    'คอนโด Lumpini Park Rama 9 - ห้องสวย พร้อมเข้าอยู่',
    'คอนโดหรูใจกลางเมือง ติดรถไฟฟ้า MRT พระราม 9 เพียง 300 เมตร วิวสวย เฟอร์นิเจอร์ครบ เหมาะสำหรับคนทำงาน',
    'condo',
    'available',
    '99 ถนนพระราม 9 แขวงห้วยขวาง',
    'ห้วยขวาง',
    'Bangkok',
    13.756300,
    100.565700,
    32.5,
    1,
    1,
    15,
    12000,
    24000,
    18,
    7,
    1500,
    'WiFi,แอร์,เครื่องซักผ้า,ตู้เย็น,เตียง,โต๊ะทำงาน,ตู้เสื้อผ้า,ทีวี,ไมโครเวฟ,ฟิตเนส,สระว่ายน้ำ,ที่จอดรถ,รักษาความปลอดภัย 24 ชม.',
    'เตียง 6 ฟุต,โต๊ะทำงาน,เก้าอี้,ตู้เสื้อผ้า,โซฟา,ทีวี 32 นิ้ว,แอร์,เครื่องทำน้ำอุ่น',
    'MRT พระราม 9',
    300,
    CURDATE()
),
(
    @business_user_id,
    'อพาร์ทเมนท์สุขุมวิท 77 - ราคาประหยัด',
    'อพาร์ทเมนท์สะอาด ปลอดภัย ใกล้ BTS อ่อนนุช ห้างสรรพสินค้า และแหล่งอาหาร สะดวกสบายทุกการใช้ชีวิต',
    'apartment',
    'available',
    '156/8 ซอยสุขุมวิท 77',
    'พระโขนง',
    'Bangkok',
    13.705300,
    100.604800,
    28,
    1,
    1,
    8,
    7500,
    7500,
    20,
    7,
    0,
    'WiFi,แอร์,ตู้เย็น,เตียง,โต๊ะทำงาน,ตู้เสื้อผ้า,ที่จอดรถ',
    'เตียง,โต๊ะ,เก้าอี้,ตู้เสื้อผ้า',
    'BTS อ่อนนุช',
    600,
    CURDATE()
),
(
    @business_user_id,
    'หอพักรัชดา 36 - ใกล้ MRT สุทธิสาร',
    'หอพักสะอาด ปลอดภัย มีกล้องวงจรปิด ผู้จัดการดูแลตลอด 24 ชั่วโมง ใกล้ MRT สุทธิสาร และ ห้าง เซ็นทรัล พระราม 9',
    'dormitory',
    'available',
    '248 ซอยรัชดาภิเษก 36',
    'ดินแดง',
    'Bangkok',
    13.765000,
    100.559600,
    22,
    1,
    1,
    5,
    5500,
    5500,
    18,
    6,
    0,
    'WiFi,แอร์,เตียง,โต๊ะทำงาน,ตู้เสื้อผ้า,เครื่องทำน้ำอุ่น',
    'เตียง,โต๊ะ,เก้าอี่,ตู้เสื้อผ้า',
    'MRT สุทธิสาร',
    400,
    CURDATE()
),
(
    @business_user_id,
    'คอนโด The Tree Sukhumvit 64 - โครงการใหม่',
    'คอนโดใหม่ ห้องสวย ตกแต่งพร้อมอยู่ ใกล้ BTS ปุณณวิถี เดินทางสะดวก ใกล้ห้างสรรพสินค้า',
    'condo',
    'available',
    '555 ถนนสุขุมวิท 64',
    'พระโขนง',
    'Bangkok',
    13.720400,
    100.593300,
    35,
    1,
    1,
    20,
    15000,
    30000,
    20,
    8,
    2000,
    'WiFi,แอร์,เครื่องซักผ้า,ตู้เย็น,เตียง,โต๊ะทำงาน,ตู้เสื้อผ้า,ทีวี,ฟิตเนส,สระว่ายน้ำ,ที่จอดรถ,รักษาความปลอดภัย 24 ชม.,สวนหย่อม',
    'ครบชุด - เตียง,โซฟา,ทีวี,โต๊ะทำงาน,ตู้เสื้อผ้า,ไมโครเวฟ,เตาไฟฟ้า',
    'BTS ปุณณวิถี',
    500,
    CURDATE()
),
(
    @business_user_id,
    'อพาร์ทเมนท์ลาดพร้าว 71 - เงียบสงบ',
    'อพาร์ทเมนท์บรรยากาศดี ร่มรื่น เงียบสงบ เหมาะสำหรับการพักผ่อน ใกล้ตลาด ร้านอาหาร และ ห้างสรรพสินค้า',
    'apartment',
    'rented',
    '88/99 ซอยลาดพร้าว 71',
    'วังทองหลาง',
    'Bangkok',
    13.783600,
    100.608900,
    30,
    1,
    1,
    6,
    6500,
    6500,
    18,
    7,
    0,
    'WiFi,แอร์,ตู้เย็น,เตียง,โต๊ะทำงาน,ตู้เสื้อผ้า',
    'เตียง,โต๊ะ,เก้าอี้,ตู้เสื้อผ้า,พัดลม',
    'รถเมล์ สาย 136',
    200,
    DATE_ADD(CURDATE(), INTERVAL 3 MONTH)
);

-- 10. เพิ่มรูปภาพตัวอย่าง
SET @prop1_id = (SELECT id FROM business_properties WHERE title LIKE '%Lumpini Park Rama 9%' LIMIT 1);
SET @prop2_id = (SELECT id FROM business_properties WHERE title LIKE '%สุขุมวิท 77%' LIMIT 1);
SET @prop3_id = (SELECT id FROM business_properties WHERE title LIKE '%รัชดา 36%' LIMIT 1);
SET @prop4_id = (SELECT id FROM business_properties WHERE title LIKE '%The Tree%' LIMIT 1);

INSERT INTO property_images (property_id, image_path, image_type, sort_order) VALUES
(@prop1_id, 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800', 'main', 1),
(@prop1_id, 'https://images.unsplash.com/photo-1502672260066-6bc35f0b8013?w=800', 'gallery', 2),
(@prop1_id, 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800', 'gallery', 3),
(@prop2_id, 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=800', 'main', 1),
(@prop2_id, 'https://images.unsplash.com/photo-1515263487990-61b07816b324?w=800', 'gallery', 2),
(@prop3_id, 'https://images.unsplash.com/photo-1554995207-c18c203602cb?w=800', 'main', 1),
(@prop3_id, 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800', 'gallery', 2),
(@prop4_id, 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800', 'main', 1),
(@prop4_id, 'https://images.unsplash.com/photo-1502672260066-6bc35f0b8013?w=800', 'gallery', 2);

-- 11. เพิ่มโปรโมชันตัวอย่าง
INSERT INTO property_promotions (property_id, promotion_title, promotion_description, discount_type, discount_value, start_date, end_date, is_active) VALUES
(@prop1_id, 'ลดพิเศษเดือนแรก 20%', 'ลดค่าเช่าเดือนแรก 20% สำหรับผู้เช่าใหม่', 'percentage', 20, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), TRUE),
(@prop4_id, 'โปรโมชันห้องใหม่ ลด 3,000 บาท', 'เข้าอยู่เดือนนี้ ลดทันที 3,000 บาท', 'fixed_amount', 3000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 15 DAY), TRUE);

-- 12. เพิ่มข้อมูล Analytics ย้อนหลัง (30 วัน)
INSERT INTO property_analytics (property_id, date, views, detail_views, favorites, contacts, shares)
SELECT
    id as property_id,
    DATE_SUB(CURDATE(), INTERVAL n DAY) as date,
    FLOOR(RAND() * 50) + 10 as views,
    FLOOR(RAND() * 20) + 5 as detail_views,
    FLOOR(RAND() * 5) as favorites,
    FLOOR(RAND() * 3) as contacts,
    FLOOR(RAND() * 2) as shares
FROM business_properties
CROSS JOIN (
    SELECT 0 AS n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION
    SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION
    SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION
    SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION
    SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 UNION
    SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29
) numbers
WHERE business_id = @business_user_id
ON DUPLICATE KEY UPDATE views = views;

-- 13. อัปเดตจำนวน properties ใน business_profiles
UPDATE business_profiles
SET total_properties = (
    SELECT COUNT(*) FROM business_properties WHERE business_id = @business_user_id
),
total_views = (
    SELECT COALESCE(SUM(views), 0) FROM business_properties WHERE business_id = @business_user_id
)
WHERE user_id = @business_user_id;

-- 14. แสดงข้อมูลที่สร้าง
SELECT '=== Business User Created ===' as Status;
SELECT id, username, email, full_name, role, status FROM users WHERE username = 'business';

SELECT '=== Business Profile ===' as Status;
SELECT * FROM business_profiles WHERE user_id = @business_user_id;

SELECT '=== Properties Created ===' as Status;
SELECT id, title, property_type, status, price, district FROM business_properties WHERE business_id = @business_user_id;

SELECT 'Setup completed successfully!' as Message;
SELECT 'Login with username: business, password: business123' as Credentials;
