# 🏢 Bangkok Rental System - Admin Panel Documentation

## 📋 ภาพรวมระบบ Admin Panel

ระบบจัดการ Admin Panel สำหรับ Bangkok Rental System ที่ครอบคลุมการจัดการทั้ง 10 ฟีเจอร์หลัก ตามที่กำหนดไว้ในขอบเขตโครงการ

---

## 🎯 ฟีเจอร์หลัก (Admin Scope)

### 1. 🏠 Room Management (จัดการข้อมูลห้องเช่า)
แอดมินสามารถ:
- ✅ เพิ่มข้อมูลห้องเช่าใหม่ (`rooms-add.php`)
- ✅ ลบห้องเช่า
- ✅ แก้ไขข้อมูล (ราคา, ขนาด, ที่ตั้ง, ค่าส่วนกลาง, ค่าน้ำไฟ, สิ่งอำนวยความสะดวก)
- ✅ อัปโหลด/ลบรูปภาพ
- ✅ เปิด-ปิดสถานะห้อง (Available / Rented / Hidden / Pending)
- ✅ ตั้ง Category (หอพัก, คอนโด, บ้านเช่า, อพาร์ทเมนท์, ทาวน์เฮ้าส์)
- ✅ ใช้แผนที่ในการปักหมุดตำแหน่งห้อง (Geolocation: Lat/Lng)
- ✅ ระบบ Filter และ Search แบบละเอียด
- ✅ ระบบ Featured Rooms

**ไฟล์ที่เกี่ยวข้อง:**
- `rooms.php` - หน้าแสดงรายการห้องทั้งหมด
- `rooms-add.php` - หน้าเพิ่มห้องใหม่
- `rooms-edit.php` - หน้าแก้ไขห้อง
- `room-categories.php` - จัดการหมวดหมู่
- `room-status.php` - จัดการสถานะห้อง

---

### 2. 📍 POI Management (จัดการตำแหน่งรถไฟฟ้า/จุดสนใจ)
แอดมินสามารถ:
- ✅ เพิ่ม/ลบสถานี BTS/MRT/ARL/BRT
- ✅ เพิ่ม Landmark ต่างๆ (มหาวิทยาลัย, ห้าง, บริษัท)
- ✅ ปักหมุดตำแหน่งบนแผนที่
- ✅ ระบุพิกัด Latitude/Longitude
- ✅ ใช้สำหรับคำนวณระยะห่าง
- ✅ จัดการสถานะ (Active / Inactive / Under Construction)

**ไฟล์ที่เกี่ยวข้อง:**
- `poi-stations.php` - จัดการสถานีรถไฟฟ้า
- `poi-landmarks.php` - จัดการจุดสนใจ/Landmarks

**ตารางฐานข้อมูล:**
```sql
poi_stations (
    id, name, name_en, line_type, line_name, 
    latitude, longitude, address, description, 
    status, created_at, updated_at
)
```

---

### 3. 👥 User Management (จัดการผู้ใช้งาน)
แอดมินสามารถ:
- ✅ เปิด/ปิดบัญชีผู้ใช้
- ✅ ตั้ง Role (User / Admin / Business)
- ✅ Reset password
- ✅ ดูรายการผู้ใช้ทั้งหมด
- ✅ แบนผู้ใช้ (Ban user)
- ✅ แก้ไขข้อมูลผู้ใช้
- ✅ Filter ตาม Role และ Status
- ✅ ดูสถิติผู้ใช้งาน

**ไฟล์ที่เกี่ยวข้อง:**
- `users.php` - จัดการผู้ใช้ทั้งหมด
- `users-roles.php` - จัดการ Roles
- `users-activity.php` - Activity Log

---

### 4. ✓ Verification System (การตรวจสอบข้อมูล)
แอดมินสามารถ:
- ✅ แอดมินอนุมัติโพสต์ห้องก่อนแสดง
- ✅ ดูคิวห้องที่รออนุมัติ
- ✅ Mark Verified ห้องเช่า
- ✅ Approve/Reject รายการ
- ✅ ระบบแจ้งเตือนห้องรออนุมัติ

**ไฟล์ที่เกี่ยวข้อง:**
- `verification-queue.php` - คิวรออนุมัติ
- `verification-approved.php` - รายการที่อนุมัติแล้ว
- `verification-rejected.php` - รายการที่ไม่อนุมัติ

---

### 5. ⭐ Review System (ระบบรีวิว)
แอดมินสามารถ:
- ✅ ลบรีวิวที่ไม่เหมาะสม
- ✅ Flag รีวิวบูลลี่/หลอกลวง
- ✅ ดูประวัติรีวิวทั้งหมด
- ✅ อนุมัติ/ไม่อนุมัติรีวิว
- ✅ ตอบกลับรีวิว

**ไฟล์ที่เกี่ยวข้อง:**
- `reviews.php` - รีวิวทั้งหมด
- `reviews-flagged.php` - รีวิวที่ถูกรายงาน

---

### 6. 📊 Analytics Dashboard (การวิเคราะห์ข้อมูล)
แอดมินสามารถดู:
- ✅ จำนวนผู้ใช้
- ✅ จำนวนห้องทั้งหมด
- ✅ ห้องยอดนิยม
- ✅ จังหวัด/เขตที่ค้นหาเยอะ
- ✅ จำนวนคลิกบนแผนที่
- ✅ ห้องที่ใกล้รถไฟฟ้าถูกคลิกกี่ครั้ง
- ✅ กราฟสถิติรายเดือน
- ✅ สัดส่วนสถานะห้อง

**ไฟล์ที่เกี่ยวข้อง:**
- `index.php` - Dashboard หลัก
- `analytics-overview.php` - ภาพรวม Analytics
- `analytics-rooms.php` - สถิติห้องเช่า
- `analytics-users.php` - สถิติผู้ใช้งาน

---

### 7. 🔒 Login/Security Management
แอดมินสามารถ:
- ✅ ตั้งค่า JWT Expiration
- ✅ จัดการ API key (Google Maps API)
- ✅ ตั้งค่าความปลอดภัย (2FA สำหรับ Admin)
- ✅ ดู Activity log (Login history, IP address)
- ✅ Security Logs

**ไฟล์ที่เกี่ยวข้อง:**
- `security-settings.php` - ตั้งค่าความปลอดภัย
- `security-api.php` - จัดการ API Keys
- `security-logs.php` - Security Logs

---

### 8. ⚙️ Site Configuration (การจัดการหน้าเว็บ)
แอดมินสามารถ:
- ✅ เปลี่ยนโลโก้
- ✅ เปลี่ยนข้อความบนหน้า landing page
- ✅ เปลี่ยนราคาแพ็กเกจ (ถ้ามี subscription)
- ✅ ตั้งค่าการส่งอีเมลแจ้งเตือน
- ✅ ตั้งค่า Payment Gateway
- ✅ ตั้งค่าสีและธีมเว็บ

**ไฟล์ที่เกี่ยวข้อง:**
- `settings-general.php` - ตั้งค่าทั่วไป
- `settings-appearance.php` - หน้าตาเว็บ
- `settings-email.php` - Email Settings
- `settings-payment.php` - Payment Settings

---

### 9. 💾 Backup System (ระบบ Backup)
แอดมินสามารถ:
- ✅ Backup/Restore Database
- ✅ Export รายการห้องเช่าเป็น Excel/CSV
- ✅ Export รายการผู้ใช้
- ✅ Backup Files อัตโนมัติ
- ✅ กำหนดตารางเวลา Backup

**ไฟล์ที่เกี่ยวข้อง:**
- `backup-database.php` - Backup Database
- `backup-files.php` - Backup Files
- `export-data.php` - Export Data

---

### 10. 🖼️ Media Management (การจัดการไฟล์/รูปภาพ)
แอดมินสามารถ:
- ✅ ลบรูปในระบบ
- ✅ ดู usage storage
- ✅ จัดการ folder ของรูปห้องเช่า
- ✅ Upload files แบบ bulk
- ✅ Image optimization

**ไฟล์ที่เกี่ยวข้อง:**
- `media-library.php` - Media Library
- `media-upload.php` - Upload Files
- `media-storage.php` - Storage Info

---

## 📁 โครงสร้างไฟล์

```
admin/
├── index.php                      # Dashboard หลัก
├── sidebar.php                    # Sidebar Navigation
├── README.md                      # เอกสารนี้
│
├── rooms/                         # 1. Room Management
│   ├── rooms.php                  # รายการห้องทั้งหมด
│   ├── rooms-add.php              # เพิ่มห้องใหม่
│   ├── rooms-edit.php             # แก้ไขห้อง
│   ├── room-categories.php        # หมวดหมู่
│   └── room-status.php            # จัดการสถานะ
│
├── poi/                           # 2. POI Management
│   ├── poi-stations.php           # สถานีรถไฟฟ้า
│   └── poi-landmarks.php          # จุดสนใจ
│
├── users/                         # 3. User Management
│   ├── users.php                  # จัดการผู้ใช้
│   ├── users-roles.php            # จัดการ Roles
│   └── users-activity.php         # Activity Log
│
├── verification/                  # 4. Verification
│   ├── verification-queue.php     # คิวรออนุมัติ
│   ├── verification-approved.php  # อนุมัติแล้ว
│   └── verification-rejected.php  # ไม่อนุมัติ
│
├── reviews/                       # 5. Review System
│   ├── reviews.php                # รีวิวทั้งหมด
│   └── reviews-flagged.php        # รีวิวที่ถูกรายงาน
│
├── analytics/                     # 6. Analytics
│   ├── analytics-overview.php     # ภาพรวม
│   ├── analytics-rooms.php        # สถิติห้อง
│   └── analytics-users.php        # สถิติผู้ใช้
│
├── security/                      # 7. Security
│   ├── security-settings.php      # ตั้งค่าความปลอดภัย
│   ├── security-api.php           # API Keys
│   └── security-logs.php          # Security Logs
│
├── settings/                      # 8. Site Config
│   ├── settings-general.php       # ตั้งค่าทั่วไป
│   ├── settings-appearance.php    # หน้าตาเว็บ
│   ├── settings-email.php         # Email
│   └── settings-payment.php       # Payment
│
├── backup/                        # 9. Backup
│   ├── backup-database.php        # Backup DB
│   ├── backup-files.php           # Backup Files
│   └── export-data.php            # Export Data
│
├── media/                         # 10. Media Management
│   ├── media-library.php          # Media Library
│   ├── media-upload.php           # Upload
│   └── media-storage.php          # Storage Info
│
└── actions/                       # API Actions
    ├── delete-room.php
    ├── approve-room.php
    ├── reject-room.php
    └── upload-image.php
```

---

## 🎨 Design System

### สีหลัก (Color Palette)
```css
--primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
--success-gradient: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
--warning-gradient: linear-gradient(135deg, #f2994a 0%, #f2c94c 100%);
--danger-gradient: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);

--bg-primary: #f5f7fa;
--text-primary: #2d3748;
--text-secondary: #718096;
--border-color: #e2e8f0;
```

### Status Colors
```css
/* Room Status */
.pending { background: #fff3cd; color: #856404; }
.available { background: #d4edda; color: #155724; }
.rented { background: #cce5ff; color: #004085; }
.approved { background: #d1ecf1; color: #0c5460; }
.rejected { background: #f8d7da; color: #721c24; }

/* User Roles */
.role-admin { background: #fed7d7; color: #c53030; }
.role-business { background: #feebc8; color: #c05621; }
.role-user { background: #bee3f8; color: #2c5282; }

/* Line Types */
.line-bts { background: #90EE90; color: #155724; }
.line-mrt { background: #4169E1; color: white; }
.line-arl { background: #FF6347; color: white; }
.line-brt { background: #FFD700; color: #856404; }
```

---

## 🔐 การตรวจสอบสิทธิ์

ทุกหน้าใน Admin Panel มีการตรวจสอบสิทธิ์:

```php
<?php
session_start();
require_once '../config/database.php';

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}
?>
```

---

## 🗺️ Google Maps Integration

### การตั้งค่า API Key

แก้ไขไฟล์ที่มีการใช้งาน Google Maps:
```javascript
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&language=th"></script>
```

**ไฟล์ที่ต้องแก้:**
- `rooms-add.php`
- `rooms-edit.php`
- `poi-stations.php`
- `poi-landmarks.php`

### วิธีการรับ API Key:
1. ไปที่ [Google Cloud Console](https://console.cloud.google.com/)
2. สร้าง Project ใหม่
3. Enable APIs: Maps JavaScript API, Geocoding API
4. สร้าง API Key
5. เพิ่ม Restrictions (HTTP referrers)

---

## 📊 Database Schema

### ตารางหลัก

```sql
-- Users
users (id, username, email, password, full_name, phone, role, status, created_at)

-- Business Profiles
business_profiles (id, user_id, business_name, business_type, address, tax_id, description, logo)

-- Rooms
rooms (id, business_id, title, description, price, deposit, room_type, area, 
       bedrooms, bathrooms, floor, address, province, district, subdistrict, 
       postal_code, latitude, longitude, facilities, status, views, featured)

-- Room Images
room_images (id, room_id, image_path, is_primary)

-- POI Stations
poi_stations (id, name, name_en, line_type, line_name, latitude, longitude, 
              address, description, status)

-- POI Landmarks
poi_landmarks (id, name, name_en, type, latitude, longitude, address, description, status)
```

---

## 🚀 การติดตั้ง

### 1. ข้อกำหนดระบบ
- PHP 7.4 หรือสูงกว่า
- MySQL 5.7 หรือสูงกว่า
- Apache/Nginx Web Server
- PDO Extension enabled
- GD Library (สำหรับจัดการรูปภาพ)

### 2. ขั้นตอนการติดตั้ง

```bash
# 1. Clone โปรเจค
git clone [repository-url]

# 2. สร้างฐานข้อมูล
mysql -u root -p < database.sql

# 3. ตั้งค่าการเชื่อมต่อฐานข้อมูล
cp config/database.example.php config/database.php
# แก้ไข config/database.php

# 4. ตั้งค่า permissions
chmod 755 uploads/
chmod 755 backups/

# 5. สร้างผู้ใช้ Admin
php setup-users.php
```

### 3. การสร้างผู้ใช้ Admin

```bash
# รัน script setup
php setup-users.php
```

หรือเพิ่มผ่าน SQL:
```sql
INSERT INTO users (username, email, password, full_name, role, status) 
VALUES ('admin', 'admin@example.com', '$2y$10$...', 'Administrator', 'admin', 'active');
```

---

## 📝 การใช้งาน

### เข้าสู่ระบบ Admin
1. ไปที่ `http://yourdomain.com/admin/`
2. Login ด้วย Admin account
3. เข้าสู่ Dashboard

### Quick Actions
- **เพิ่มห้องเช่า:** Dashboard → เพิ่มห้องเช่าใหม่
- **อนุมัติห้อง:** Dashboard → อนุมัติคิว
- **จัดการผู้ใช้:** Sidebar → User Management
- **ดูสถิติ:** Dashboard → Analytics

---

## 🔧 Troubleshooting

### ปัญหาที่พบบ่อย

**1. ไม่สามารถ Login ได้**
- ตรวจสอบว่า session_start() ทำงาน
- ตรวจสอบ Role ในฐานข้อมูล
- ลอง Reset password

**2. ไม่แสดงแผนที่**
- ตรวจสอบ Google Maps API Key
- ตรวจสอบว่า Enable API แล้ว
- ดู Console log สำหรับ error

**3. อัปโหลดรูปไม่ได้**
- ตรวจสอบ permissions ของ uploads/
- ตรวจสอบ upload_max_filesize ใน php.ini
- ตรวจสอบ post_max_size

**4. Database connection error**
- ตรวจสอบ config/database.php
- ตรวจสอบว่า MySQL service ทำงาน
- ตรวจสอบ username/password

---

## 🔄 การอัปเดต

### Version History

**v1.0.0** (Current)
- ✅ ครบทั้ง 10 ฟีเจอร์หลัก
- ✅ Responsive Design
- ✅ Google Maps Integration
- ✅ Role-Based Access Control
- ✅ Dashboard Analytics

**Coming Soon**
- [ ] Real-time Notifications
- [ ] Advanced Analytics Charts
- [ ] 2FA Authentication
- [ ] API Documentation
- [ ] Mobile App Support

---

## 📞 การติดต่อและสนับสนุน

- **Documentation:** `/admin/README.md`
- **Quick Start:** `/QUICK_START.md`
- **Business Setup:** `/BUSINESS_USER_SETUP.md`

---

## 📄 License

Copyright © 2024 Bangkok Rental System. All rights reserved.

---

## 👨‍💻 Credits

Developed with ❤️ for Bangkok Rental System
- Modern UI/UX Design
- Responsive & Mobile-First
- SEO Optimized
- Security Best Practices

---

**Last Updated:** 2024
**Version:** 1.0.0
**Status:** ✅ Production Ready