# 📚 Render Deployment - Complete Guide

## 📁 ไฟล์คู่มือทั้งหมด

1. **RENDER_DEPLOYMENT.md** - คู่มือการ deploy ครั้งแรก
2. **UPDATE_GUIDE.md** - คู่มือการอัปเดตหลัง deploy แล้ว ⭐
3. **DEPLOY_COMMANDS.md** - คำสั่งสำคัญแบบย่อ

---

## 🎯 เลือกคู่มือตามสถานการณ์

### ถ้ายัง**ไม่เคย** deploy บน Render
👉 อ่าน **RENDER_DEPLOYMENT.md**

### ถ้า deploy ไปแล้ว และต้องการ**อัปเดต**
👉 อ่าน **UPDATE_GUIDE.md** ⭐ (ไฟล์นี้!)

### ต้องการดูคำสั่งแบบย่อ
👉 อ่าน **DEPLOY_COMMANDS.md**

---

## ⚡ Quick Update (สำหรับคนที่รีบ)

```bash
# ขั้นตอนการอัปเดตแบบเร็ว
cd c:\xampp\htdocs\billing
git add .
git commit -m "Update: คำอธิบาย"
git push

# Render จะ auto-deploy ให้อัตโนมัติภายใน 2-5 นาที
```

---

## 📖 สารบัญคู่มือ

### RENDER_DEPLOYMENT.md
- วิธีการ deploy ครั้งแรก
- การตั้งค่า Render
- การเชื่อมต่อ GitHub
- Environment Variables
- ข้อควรรู้เกี่ยวกับ Free Plan

### UPDATE_GUIDE.md ⭐
- วิธีการอัปเดตโค้ด
- คำสั่ง Git ที่ใช้บ่อย
- การตรวจสอบสถานะ Deploy
- การแก้ปัญหาที่พบบ่อย
- Best Practices
- Tips & Tricks

### DEPLOY_COMMANDS.md
- คำสั่ง Git พื้นฐาน
- Render Settings
- URL References

---

## 🔗 Links สำคัญ

- **Render Dashboard:** https://dashboard.render.com/
- **Render Docs:** https://render.com/docs
- **GitHub Repository:** https://github.com/Kraikuppp/Amptron-Apartment
- **Your Live Site:** https://[your-service-name].onrender.com

---

## 🆘 ติดปัญหา?

1. ดู **UPDATE_GUIDE.md** ส่วน "การแก้ปัญหา"
2. ตรวจสอบ Logs ใน Render Dashboard
3. ดู Render Community: https://community.render.com/

---

## 📞 Support

- Render Status: https://status.render.com/
- Render Docs: https://render.com/docs
- Community Forum: https://community.render.com/

---

**สร้างเมื่อ:** 2025-11-20  
**อัปเดตล่าสุด:** 2025-11-20  
**Version:** 1.0
