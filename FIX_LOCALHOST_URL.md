# 🔧 แก้ไข: ปัญหา Localhost URL บน Render

## ❌ ปัญหาที่พบ

เมื่อ deploy บน Render แล้ว คลิกลิงก์ "ใกล้ฉัน" หรือ "ค้นหาหอพักเพิ่มเติม" จะเด้งไปที่:
```
http://localhost/billing/room.php?mode=nearby
```

แล้วเกิด error:
```
ERR_CONNECTION_REFUSED
This site can't be reached
```

## 🔍 สาเหตุ

ในไฟล์ `config/config.php` มีการ hardcode URL เป็น:
```php
define('SITE_URL', 'http://localhost/billing');
```

เมื่อ deploy บน Render ระบบยังคงใช้ `localhost` ทำให้ลิงก์ไม่ทำงาน

## ✅ วิธีแก้ไข

เปลี่ยนจาก hardcoded URL เป็น **dynamic URL detection** ที่ตรวจจับ URL อัตโนมัติ

### ก่อนแก้ไข:
```php
define('SITE_URL', 'http://localhost/billing');
```

### หลังแก้ไข:
```php
// Auto-detect SITE_URL (รองรับทั้ง localhost และ production)
if (!defined('SITE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    
    // ตรวจสอบว่าอยู่ใน subdirectory หรือไม่
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $baseDir = str_replace('\\', '/', dirname($scriptName));
    
    // ถ้าอยู่ใน root ให้ใช้ host อย่างเดียว
    if ($baseDir === '/' || $baseDir === '') {
        define('SITE_URL', $protocol . $host);
    } else {
        // ถ้าอยู่ใน subdirectory (เช่น /billing)
        define('SITE_URL', $protocol . $host . $baseDir);
    }
}
```

## 🎯 ผลลัพธ์

### บน Localhost:
- SITE_URL = `http://localhost/billing`
- ลิงก์: `http://localhost/billing/room.php?mode=nearby` ✅

### บน Render:
- SITE_URL = `https://amptron-apartments.onrender.com`
- ลิงก์: `https://amptron-apartments.onrender.com/room.php?mode=nearby` ✅

## 📝 ไฟล์ที่แก้ไข

- ✅ `config/config.php` - เปลี่ยนเป็น dynamic URL detection

## 🚀 การ Deploy

การแก้ไขนี้ถูก push ขึ้น GitHub แล้ว:
```bash
git commit -m "Fix: Replace hardcoded localhost URL with dynamic URL detection"
git push
```

Render จะ **auto-deploy** ภายใน 3-5 นาที

## ⏰ ขั้นตอนต่อไป

1. **รอ Render Deploy เสร็จ** (3-5 นาที)
   - ไปที่ https://dashboard.render.com
   - เลือก service `amptron-apartments`
   - ดูสถานะใน Logs
   - รอจนเห็น "Deploy succeeded"

2. **ทดสอบอีกครั้ง**
   - เข้า https://amptron-apartments.onrender.com
   - คลิก "ใกล้ฉัน" หรือ "ค้นหาหอพักเพิ่มเติม"
   - ตอนนี้ควรทำงานปกติแล้ว! ✅

3. **ทดสอบฟีเจอร์อื่นๆ**
   - Login
   - ค้นหาห้อง
   - ดูรายละเอียดห้อง
   - Business Dashboard

## 🔍 วิธีตรวจสอบว่า Deploy เสร็จ

### ใน Render Dashboard:
1. ไปที่ https://dashboard.render.com
2. เลือก service ของคุณ
3. ดูที่ **Events** หรือ **Logs**
4. จะเห็นข้อความ:
   ```
   ==> Build successful 🎉
   ==> Deploying...
   ==> Deploy succeeded
   ```

### ทดสอบจากเบราว์เซอร์:
1. เปิด https://amptron-apartments.onrender.com
2. กด **Ctrl+Shift+R** (hard refresh) เพื่อล้าง cache
3. คลิก "ใกล้ฉัน"
4. ถ้าทำงานปกติ = แก้ไขสำเร็จ! ✅

## 💡 Tips

### ถ้ายังไม่ทำงาน:
1. **Hard Refresh** เบราว์เซอร์ (Ctrl+Shift+R)
2. **ล้าง Cache** ของเบราว์เซอร์
3. **ลองใน Incognito Mode**
4. **รอ 5-10 นาที** ให้ Render deploy เสร็จสมบูรณ์

### ตรวจสอบ URL ที่ถูกต้อง:
- ✅ `https://amptron-apartments.onrender.com/room.php`
- ❌ `http://localhost/billing/room.php`

## 📚 เอกสารที่เกี่ยวข้อง

- **Render Dashboard:** https://dashboard.render.com
- **GitHub Repository:** https://github.com/Kraikuppp/Amptron-Apartments
- **คู่มือ Deploy:** `RENDER_DEPLOY_GUIDE.md`

---

**แก้ไขเมื่อ:** 2025-11-20 18:35  
**สถานะ:** ✅ Push ขึ้น GitHub แล้ว  
**ขั้นตอนต่อไป:** รอ Render auto-deploy (3-5 นาที)
