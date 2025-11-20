# 🚀 Quick Start Guide - RentHub Platform

## เริ่มต้นใช้งานภายใน 5 นาที!

### ขั้นตอนที่ 1: เปิดเว็บไซต์

เปิดเบราว์เซอร์และไปที่:
```
http://localhost/billing/
```

---

### ขั้นตอนที่ 2: สร้าง Users ทดสอบ

เปิดหน้าสร้าง Users อัตโนมัติ:
```
http://localhost/billing/setup-users.php
```

คลิกปุ่ม **"สร้าง Users ทดสอบ"**

ระบบจะสร้าง Users ทั้งหมด 3 บัญชี:

---

## 👤 บัญชีผู้ใช้ทดสอบ

### 1. 🛡️ Admin (ผู้ดูแลระบบ)
```
Username: admin
Password: admin123
URL: http://localhost/billing/admin/
```

**สามารถทำได้:**
- ✅ จัดการผู้ใช้ทั้งหมด
- ✅ อนุมัติ/ปฏิเสธห้องเช่า
- ✅ ยืนยันตัวตนผู้ประกอบการ
- ✅ ดูสถิติระบบทั้งหมด
- ✅ จัดการหมวดหมู่และการตั้งค่า

---

### 2. 🏢 Business (ผู้ประกอบการ)
```
Username: business
Password: business123
URL: http://localhost/billing/business/dashboard.php
```

**สามารถทำได้:**
- ✅ เพิ่ม/แก้ไข/ลบห้องเช่าของตัวเอง
- ✅ อัปโหลดรูปภาพห้อง
- ✅ ปักหมุดตำแหน่งบนแผนที่
- ✅ สร้างโปรโมชัน/ส่วนลด
- ✅ ดูสถิติห้องของตัวเอง
- ✅ รับ-ส่งข้อความกับผู้สนใจ
- ✅ จัดการโปรไฟล์ธุรกิจ

**ห้องตัวอย่าง:** ระบบสร้างห้องเช่าตัวอย่าง 3 ห้องให้อัตโนมัติ

---

### 3. 👨‍💼 Customer (ลูกค้า)
```
Username: customer
Password: customer123
URL: http://localhost/billing/rooms.php
```

**สามารถทำได้:**
- ✅ ค้นหาห้องเช่า
- ✅ ดูรายละเอียดห้อง
- ✅ บันทึกห้องโปรด (Wishlist)
- ✅ ติดต่อผู้ประกอบการ
- ✅ เขียนรีวิว

---

## 📱 ทดสอบฟีเจอร์หลัก

### สำหรับผู้ประกอบการ (Business User)

#### 1. เข้าสู่ระบบ
1. ไปที่ http://localhost/billing/login.php
2. ใช้ `business` / `business123`
3. คลิก Login

#### 2. ดู Dashboard
- คุณจะเห็น Dashboard แสดง:
  - จำนวนห้องทั้งหมด
  - ห้องว่าง/ให้เช่าแล้ว
  - ยอดเข้าชม
  - กราฟสถิติ 7 วัน

#### 3. จัดการห้องเช่า
**ดูห้องทั้งหมด:**
```
http://localhost/billing/business/properties.php
```

**เพิ่มห้องใหม่:**
```
http://localhost/billing/business/add-property.php
```

**แก้ไขห้อง:**
- คลิกที่ห้องที่ต้องการ
- แก้ไขข้อมูล
- ปักหมุดบนแผนที่ได้
- อัปโหลดรูปภาพได้

#### 4. สร้างโปรโมชัน
```
http://localhost/billing/business/promotions.php
```
- เลือกห้อง
- กำหนดส่วนลด (% หรือ จำนวนเงิน)
- ตั้งวันเริ่ม-สิ้นสุด

#### 5. ดูสถิติ
```
http://localhost/billing/business/analytics.php
```
- กราฟยอดเข้าชม
- ห้องที่ได้รับความสนใจมากที่สุด
- รายงานรายวัน/รายเดือน

---

### สำหรับลูกค้า (Customer)

#### 1. ค้นหาห้อง
```
http://localhost/billing/rooms.php
```

**ฟีเจอร์ค้นหา:**
- 🔍 คำค้นหา
- 📍 เลือกเขต
- 💰 กำหนดช่วงราคา
- 🏠 ประเภทห้อง
- ⭐ สิ่งอำนวยความสะดวก

**แผนที่:**
- แสดงห้องทั้งหมดบนแผนที่
- คลิก marker เพื่อดูข้อมูล
- ปุ่ม "ห้องใกล้ฉัน" หาห้องในรัศมี 5 กม.

#### 2. ดูรายละเอียดห้อง
- คลิกที่การ์ดห้อง
- ดูรูปภาพ Gallery
- ดูแผนที่ตำแหน่ง
- บันทึกห้องโปรด
- ติดต่อผู้ประกอบการ

---

## 🎯 Quick Tasks

### Task 1: เพิ่มห้องใหม่ (Business)
1. Login เป็น `business`
2. ไปที่ Dashboard → เพิ่มห้องใหม่
3. กรอกข้อมูล:
   - ชื่อห้อง
   - ราคา
   - ที่อยู่
4. ปักหมุดบนแผนที่
5. อัปโหลดรูปภาพ
6. บันทึก

### Task 2: สร้างโปรโมชัน (Business)
1. Login เป็น `business`
2. ไปที่ โปรโมชัน
3. คลิก "สร้างโปรโมชันใหม่"
4. เลือกห้อง
5. กำหนดส่วนลด 20%
6. ตั้งวันที่
7. บันทึก

### Task 3: ค้นหาและดูห้อง (Customer)
1. Login เป็น `customer` (หรือไม่ Login ก็ได้)
2. ไปที่หน้า ค้นหาห้อง
3. ใช้ filter:
   - เลือกเขต "ห้วยขวาง"
   - ราคา 5000-15000
4. ดูห้องบนแผนที่
5. คลิกดูรายละเอียด

### Task 4: บันทึกห้องโปรด (Customer)
1. Login เป็น `customer`
2. เปิดห้องที่ชอบ
3. คลิก ❤️ บันทึกห้องโปรด
4. ดูได้ที่ Wishlist

---

## 📂 โครงสร้างไฟล์สำคัญ

```
billing/
├── setup-users.php          ← สร้าง Users ทดสอบ
├── login.php                ← หน้า Login
├── rooms.php                ← ค้นหาห้องเช่า (พร้อมแผนที่)
├── room-detail.php          ← รายละเอียดห้อง
│
├── business/                ← โฟลเดอร์ Business User
│   ├── dashboard.php        ← Dashboard หลัก
│   ├── properties.php       ← จัดการห้องทั้งหมด
│   ├── add-property.php     ← เพิ่มห้องใหม่
│   ├── edit-property.php    ← แก้ไขห้อง
│   ├── analytics.php        ← สถิติและรายงาน
│   ├── promotions.php       ← จัดการโปรโมชัน
│   ├── messages.php         ← ข้อความ/แชท
│   ├── profile.php          ← โปรไฟล์ธุรกิจ
│   └── subscription.php     ← แพ็กเกจสมาชิก
│
└── admin/                   ← โฟลเดอร์ Admin
    ├── index.php            ← Admin Dashboard
    ├── users.php            ← จัดการผู้ใช้
    └── properties.php       ← อนุมัติห้อง
```

---

## 🔥 ฟีเจอร์เด่น

### 🗺️ แผนที่อัจฉริยะ
- แสดงห้องทั้งหมดบน Google Maps
- คลิก marker ดูข้อมูล
- Filter แบบ Real-time
- ปุ่ม "ห้องใกล้ฉัน"

### 🎨 UI/UX สวยงาม
- โทนสีฟ้า Sky Blue
- Gradient Effects
- Smooth Animations
- Responsive Design

### 📊 Analytics & Reports
- Dashboard สวยงาม
- กราฟแบบ Real-time
- Export PDF/Excel
- สถิติรายวัน/รายเดือน

### 💬 ระบบข้อความ
- Chat แบบ Real-time
- แจ้งเตือนข้อความใหม่
- Conversation History

### 🏷️ โปรโมชัน
- สร้างโปรโมชันได้ง่าย
- กำหนดส่วนลด % หรือ ฿
- ตั้งวันเริ่ม-สิ้นสุด

---

## 🆘 แก้ปัญหา

### ปัญหา: Login ไม่ได้

**วิธีแก้:**
1. ตรวจสอบว่ารัน `setup-users.php` แล้ว
2. ลอง Login อีกครั้ง
3. เช็ค Username/Password ให้ถูกต้อง

### ปัญหา: ไม่มีห้องแสดงบนแผนที่

**วิธีแก้:**
1. Login เป็น `business`
2. ไปที่ Dashboard
3. เช็คว่ามีห้องหรือยัง
4. ถ้าไม่มี ให้รัน `setup-users.php` ใหม่

### ปัญหา: แผนที่ไม่แสดง

**วิธีแก้:**
1. ตรวจสอบ Google Maps API Key
2. เช็คว่ามี Internet
3. ลองรีเฟรชหน้าเว็บ

### ปัญหา: Permission Denied

**วิธีแก้:**
1. ตรวจสอบว่า Login ด้วย User ที่ถูกต้อง
2. Business User เข้าได้เฉพาะ `/business/*`
3. Admin User เข้าได้เฉพาะ `/admin/*`

---

## 📞 ช่องทางติดต่อ

- 📧 Email: support@renthub.com
- 📱 Line: @renthub
- 📘 Facebook: RentHub Thailand
- ☎️ Tel: 086-341-2503

---

## 🎓 เอกสารเพิ่มเติม

- 📖 [BUSINESS_USER_SETUP.md](BUSINESS_USER_SETUP.md) - คู่มือผู้ประกอบการแบบละเอียด
- 📖 [INSTALL.md](INSTALL.md) - คู่มือการติดตั้งระบบ
- 📖 [database.sql](database.sql) - โครงสร้างฐานข้อมูล

---

## ✨ เริ่มต้นเลย!

```
👉 http://localhost/billing/setup-users.php
```

สร้าง Users และเริ่มทดสอบระบบได้ทันที! 🚀

---

**Happy Testing! 🎉**

Copyright © 2024 RentHub Thailand. All rights reserved.