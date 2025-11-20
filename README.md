# 🏢 Billing Rental System - ระบบจัดการอพาร์ตเมนต์และหอพัก

ระบบจัดการอพาร์ตเมนต์และหอพักแบบครบวงจร พร้อมระบบบิล ค่าไฟ และบริการเสริม

[![Deploy to Render](https://render.com/images/deploy-to-render-button.svg)](https://render.com/deploy)

## ✨ Features

### 👥 สำหรับผู้เช่า
- 🔍 **ค้นหาห้องพัก** - ค้นหาและกรองห้องพักตามความต้องการ
- 📊 **ดูข้อมูลห้อง** - ดูรายละเอียดห้อง ราคา สิ่งอำนวยความสะดวก
- 💰 **ตรวจสอบบิล** - ดูค่าเช่า ค่าไฟ และค่าใช้จ่ายอื่นๆ
- 🛠️ **แจ้งซ่อม** - แจ้งปัญหาและขอบริการเสริม
- 📰 **ข่าวสาร** - รับข่าวสารและโปรโมชั่น

### 🏢 สำหรับเจ้าของ (Business)
- 📈 **Dashboard** - ภาพรวมรายได้ ห้องว่าง สัญญาใกล้หมดอายุ
- ⚡ **จัดการค่าไฟ** - บันทึกและคำนวณค่าไฟรายห้อง
- 🏠 **จัดการห้องพัก** - เพิ่ม แก้ไข ลบห้องพัก
- 🎁 **โปรโมชั่น** - สร้างและจัดการโปรโมชั่น
- 📢 **ข่าวสาร** - ประกาศข่าวสารให้ผู้เช่า

### 👨‍💼 สำหรับแอดมิน
- 👥 **จัดการผู้ใช้** - เพิ่ม แก้ไข ลบผู้ใช้
- 🔐 **จัดการสิทธิ์** - กำหนดสิทธิ์การเข้าถึง
- 📊 **รายงาน** - ดูรายงานสรุปต่างๆ

## 🚀 Quick Start

### ใช้งานทันที (Demo)
เว็บไซต์ demo: `https://billing-rental-system.onrender.com`

**Login ทดสอบ:**
- ผู้ใช้ทั่วไป: `amptr` / `amptr`
- Business: `business` / `business123`
- Admin: `admin` / `admin123`

### Deploy เองบน Render (ฟรี!)

**ใช้เวลาแค่ 5 นาที:**

1. **Fork repository นี้** (คลิก Fork ด้านบน)

2. **Deploy บน Render:**
   - ไป https://dashboard.render.com
   - คลิก **New +** → **Web Service**
   - เลือก repository ที่ fork มา
   - คลิก **Create Web Service**
   - รอ 3-5 นาที เสร็จแล้ว!

**คู่มือละเอียด:** อ่าน [`QUICK_DEPLOY.md`](QUICK_DEPLOY.md) หรือ [`RENDER_DEPLOY_GUIDE.md`](RENDER_DEPLOY_GUIDE.md)

## 💻 ติดตั้งบนเครื่อง (Local Development)

### ความต้องการ
- PHP 7.4 หรือสูงกว่า
- Apache/Nginx
- Git

### ขั้นตอน

```bash
# 1. Clone repository
git clone https://github.com/YOUR_USERNAME/billing-rental-system.git
cd billing-rental-system

# 2. เปิดด้วย XAMPP/WAMP
# คัดลอกโฟลเดอร์ไปที่ c:\xampp\htdocs\billing

# 3. เปิดเบราว์เซอร์
# ไปที่ http://localhost/billing
```

**ไม่ต้องตั้งค่าฐานข้อมูล!** โปรเจคนี้ใช้ Mock Data

## 📁 โครงสร้างโปรเจค

```
billing/
├── index.php              # หน้าแรก
├── room-search.php        # ค้นหาห้องพัก
├── room-detail.php        # รายละเอียดห้อง
├── my-room.php           # ห้องของฉัน
├── news.php              # ข่าวสาร
├── login.php             # เข้าสู่ระบบ
├── business/             # ส่วนของเจ้าของ
│   ├── dashboard.php     # Dashboard
│   ├── properties.php    # จัดการห้องพัก
│   ├── energy.php        # จัดการค่าไฟ
│   ├── promotions.php    # โปรโมชั่น
│   └── ...
├── admin/                # ส่วนของแอดมิน
│   ├── dashboard.php
│   ├── users.php
│   └── ...
├── includes/             # ไฟล์ที่ใช้ร่วมกัน
│   ├── header.php
│   ├── footer.php
│   ├── mock_apartments.php  # Mock Data
│   └── ...
├── Dockerfile            # สำหรับ Docker deployment
├── render.yaml           # Render configuration
└── README.md             # ไฟล์นี้
```

## 🎨 เทคโนโลยีที่ใช้

- **Backend:** PHP 8.2
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Styling:** Custom CSS with Glassmorphism
- **Icons:** Font Awesome
- **Fonts:** IBM Plex Sans Thai
- **Deployment:** Docker, Render.com
- **Data:** Mock Data (ไม่ใช้ฐานข้อมูล)

## 📸 Screenshots

### หน้าแรก
![Homepage](docs/screenshots/homepage.png)

### ค้นหาห้องพัก
![Room Search](docs/screenshots/room-search.png)

### Business Dashboard
![Dashboard](docs/screenshots/dashboard.png)

## 🔐 ข้อมูล Login

### ผู้ใช้ทดสอบ

| Role | Username | Password | คำอธิบาย |
|------|----------|----------|----------|
| User | `amptr` | `amptr` | ผู้ใช้ทั่วไป (มีห้องพัก) |
| Business | `business` | `business123` | เจ้าของอพาร์ตเมนต์ |
| Admin | `admin` | `admin123` | ผู้ดูแลระบบ |

## 📚 เอกสารเพิ่มเติม

- [📖 คู่มือ Deploy แบบเร็ว (5 นาที)](QUICK_DEPLOY.md)
- [📘 คู่มือ Deploy ฉบับสมบูรณ์](RENDER_DEPLOY_GUIDE.md)
- [🔧 คู่มือการใช้งาน Business](BUSINESS_USER_SETUP.md)
- [⚡ คู่มือจัดการค่าไฟ](ENERGY_MANAGEMENT.md)
- [🐛 Log การแก้ไข Bug](BUGFIX_LOG.md)

## 🌟 Features Roadmap

- [ ] เพิ่มระบบแจ้งเตือนผ่าน Email
- [ ] เพิ่มระบบชำระเงินออนไลน์
- [ ] เพิ่ม Mobile App
- [ ] เพิ่มระบบ Analytics
- [ ] เพิ่มการ Export รายงาน PDF

## 🤝 Contributing

ยินดีรับ Pull Requests! สำหรับการเปลี่ยนแปลงใหญ่ กรุณาเปิด Issue ก่อน

1. Fork โปรเจค
2. สร้าง Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit การเปลี่ยนแปลง (`git commit -m 'Add some AmazingFeature'`)
4. Push ไปยัง Branch (`git push origin feature/AmazingFeature`)
5. เปิด Pull Request

## 📝 License

โปรเจคนี้เป็น Open Source ภายใต้ MIT License

## 👨‍💻 Author

สร้างโดย **Amptron Team**

## 🆘 Support

- 📧 Email: support@example.com
- 💬 GitHub Issues: [Create an issue](https://github.com/YOUR_USERNAME/billing-rental-system/issues)
- 📖 Documentation: [Wiki](https://github.com/YOUR_USERNAME/billing-rental-system/wiki)

## ⭐ Star History

ถ้าโปรเจคนี้มีประโยชน์ อย่าลืมกด Star ⭐ ด้วยนะครับ!

---

**Made with ❤️ in Thailand**
