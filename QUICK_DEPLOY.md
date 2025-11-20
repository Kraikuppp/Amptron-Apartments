# 🎯 Quick Start: Deploy บน Render ใน 5 นาที

## ขั้นตอนที่ 1: Push โค้ดขึ้น GitHub (2 นาที)

```powershell
# เปิด PowerShell แล้วรันคำสั่งนี้
cd c:\xampp\htdocs\billing

# ตรวจสอบสถานะ
git status

# เพิ่มไฟล์ทั้งหมด
git add .

# Commit
git commit -m "Ready for Render deployment"

# Push (แทน YOUR_USERNAME ด้วยชื่อ GitHub ของคุณ)
git remote set-url origin https://github.com/YOUR_USERNAME/billing-rental-system.git
git push -u origin main
```

**ถ้ายังไม่มี Repository:**
1. ไป https://github.com/new
2. ตั้งชื่อ: `billing-rental-system`
3. เลือก **Public**
4. คลิก **Create repository**
5. รันคำสั่งด้านบน

---

## ขั้นตอนที่ 2: สร้าง Service บน Render (3 นาที)

### 2.1 เข้า Render
1. ไป https://dashboard.render.com
2. **Sign in with GitHub**

### 2.2 สร้าง Web Service
1. คลิก **New +** → **Web Service**
2. ค้นหา `billing-rental-system`
3. คลิก **Connect**

### 2.3 ตั้งค่า
กรอกเฉพาะ 3 ฟิลด์นี้:

| ฟิลด์ | ค่า |
|------|-----|
| **Name** | `billing-rental-system` |
| **Region** | `Singapore` |
| **Plan** | `Free` |

**ที่เหลือไม่ต้องกรอก** (Render จะอ่านจาก `render.yaml` อัตโนมัติ)

### 2.4 Deploy
1. คลิก **Create Web Service**
2. รอ 3-5 นาที
3. เมื่อเห็น **Live** (สีเขียว) = เสร็จแล้ว!

---

## ✅ เข้าใช้งาน

URL ของคุณ: `https://billing-rental-system.onrender.com`

**ทดสอบ Login:**
- Username: `amptr`
- Password: `amptr`

**Business Dashboard:**
- Username: `business`
- Password: `business123`

---

## ⚠️ ข้อควรรู้

### Cold Start (ปัญหาที่พบบ่อย)
- Service จะ sleep หลัง 15 นาทีไม่มีคนใช้
- ครั้งแรกที่เข้าหลัง sleep จะช้า 30-60 วินาที

### แก้ไข: ใช้ UptimeRobot
1. ไป https://uptimerobot.com (ฟรี)
2. เพิ่ม Monitor → URL: `https://your-app.onrender.com`
3. Interval: `5 minutes`
4. Service จะไม่ sleep อีก!

---

## 🔄 อัพเดทโค้ด

```powershell
# แก้ไขไฟล์
# จากนั้น:
git add .
git commit -m "Update something"
git push

# Render จะ deploy อัตโนมัติ!
```

---

## 🆘 แก้ปัญหา

### Build Failed?
- ดู Logs ใน Render Dashboard
- ตรวจสอบว่า `Dockerfile` และ `render.yaml` อยู่ใน GitHub

### เข้าเว็บไม่ได้?
- ใช้ URL จาก Render: `https://your-app.onrender.com`
- **อย่า**ใช้ `localhost`

### ช้า?
- ครั้งแรกหลัง sleep จะช้า (cold start)
- ใช้ UptimeRobot แก้ปัญหา

---

## 📚 อ่านเพิ่มเติม

คู่มือฉบับเต็ม: `RENDER_DEPLOY_GUIDE.md`

---

**เสร็จแล้ว!** 🎉
