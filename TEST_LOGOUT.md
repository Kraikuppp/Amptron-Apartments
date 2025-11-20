# 🧪 Logout Testing Guide

## 📋 Overview
คู่มือทดสอบระบบ Logout เพื่อให้แน่ใจว่าทำงานได้ถูกต้องจากทุกหน้า

---

## ✅ Test Cases

### Test #1: Logout จากหน้าแรก (Root Level)
**URL:** `http://localhost/billing/index.php`

**ขั้นตอน:**
1. Login ด้วย `business/business`
2. คลิกที่ user menu (มุมขวาบน)
3. คลิก "Logout"

**ผลลัพธ์ที่คาดหวัง:**
- ✅ Redirect ไปที่ `index.php`
- ✅ แสดงปุ่ม "Login" และ "Sign Up"
- ✅ ไม่มี user menu
- ✅ ไม่มี error 404

---

### Test #2: Logout จาก Business Dashboard
**URL:** `http://localhost/billing/business/dashboard.php`

**ขั้นตอน:**
1. Login ด้วย `business/business`
2. ไปที่ Dashboard
3. คลิกที่ user menu
4. คลิก "Logout"

**ผลลัพธ์ที่คาดหวัง:**
- ✅ Redirect ไปที่ `../index.php`
- ✅ Session ถูกล้างสำเร็จ
- ✅ กลับไปหน้าแรก
- ✅ ไม่มี error 404

---

### Test #3: Logout จาก Energy Management
**URL:** `http://localhost/billing/business/energy.php`

**ขั้นตอน:**
1. Login ด้วย `business/business`
2. ไปที่ Energy Management
3. คลิกลิงก์ "ออกจากระบบ" ในเมนูซ้าย
4. หรือคลิก logout จาก header

**ผลลัพธ์ที่คาดหวัง:**
- ✅ Redirect สำเร็จ
- ✅ ไม่สามารถกลับไปหน้า Energy ได้
- ✅ Session หมดอายุ

---

### Test #4: Logout จาก Admin Panel
**URL:** `http://localhost/billing/admin/index.php`

**ขั้นตอน:**
1. Login ด้วย `admin/admin`
2. ไปที่ Admin Panel
3. คลิก Logout

**ผลลัพธ์ที่คาดหวัง:**
- ✅ Redirect ไปหน้าแรก
- ✅ ไม่สามารถเข้า admin อีก
- ✅ ต้อง login ใหม่

---

### Test #5: Double Logout
**ขั้นตอน:**
1. Login
2. Logout สำเร็จ
3. พยายาม access `logout.php` อีกครั้ง

**ผลลัพธ์ที่คาดหวัง:**
- ✅ ไม่ error
- ✅ Redirect ไปหน้าแรกตามปกติ
- ✅ ไม่มี warning

---

### Test #6: Direct Access to Logout
**ขั้นตอน:**
1. ไม่ได้ login
2. เข้า `http://localhost/billing/logout.php` โดยตรง

**ผลลัพธ์ที่คาดหวัง:**
- ✅ Redirect ไปหน้าแรก
- ✅ ไม่มี error
- ✅ แสดงหน้าปกติ

---

### Test #7: Logout with Cookie
**ขั้นตอน:**
1. Login และเลือก "Remember me" (ถ้ามี)
2. Logout
3. ตรวจสอบ cookies

**ผลลัพธ์ที่คาดหวัง:**
- ✅ Session cookie ถูกลบ
- ✅ Remember me cookie ถูกลบ
- ✅ ต้อง login ใหม่

---

### Test #8: Logout from Mobile Menu
**ขั้นตอน:**
1. Login
2. ทดสอบบนมือถือหรือ resize browser
3. เปิด mobile menu
4. คลิก Logout

**ผลลัพธ์ที่คาดหวัง:**
- ✅ ทำงานได้เหมือนบน desktop
- ✅ Mobile menu ปิดตัวเอง
- ✅ Redirect สำเร็จ

---

## 🔍 Debug Checklist

### ถ้า Logout ไม่ทำงาน:

#### 1. เช็ค Path
```php
// ดูว่า path ถูกต้องหรือไม่
echo "Logout Path: " . $logoutPath;
```

#### 2. เช็ค Session
```php
// เช็คว่า session ยังอยู่หรือไม่
var_dump($_SESSION);
```

#### 3. เช็ค Redirect
```php
// ดู redirect URL
echo "Redirecting to: " . $redirect_url;
```

#### 4. Browser Console
- เปิด Developer Tools
- ดู Network tab
- ตรวจสอบ redirect chain

#### 5. PHP Error Log
```bash
# Windows
C:\xampp\apache\logs\error.log

# ดู error ล่าสุด
tail -f error.log
```

---

## 📊 Test Results Template

```
Date: ___________
Tester: ___________

Test #1: [ ] PASS [ ] FAIL
Test #2: [ ] PASS [ ] FAIL
Test #3: [ ] PASS [ ] FAIL
Test #4: [ ] PASS [ ] FAIL
Test #5: [ ] PASS [ ] FAIL
Test #6: [ ] PASS [ ] FAIL
Test #7: [ ] PASS [ ] FAIL
Test #8: [ ] PASS [ ] FAIL

Notes:
_________________________________
_________________________________
_________________________________
```

---

## 🐛 Common Issues & Solutions

### Issue #1: 404 Not Found
**Cause:** Path ไม่ถูกต้อง
**Solution:** ใช้ dynamic path calculation
```php
$toRoot = str_repeat("../", $depth);
$logoutPath = $toRoot . "logout.php";
```

### Issue #2: Session Still Active
**Cause:** Session ไม่ถูกลบ
**Solution:** เช็ค logout.php
```php
session_unset();
session_destroy();
$_SESSION = [];
```

### Issue #3: Redirect Loop
**Cause:** Redirect ไปที่ตัวเอง
**Solution:** ใช้ absolute path หรือ relative ที่ถูกต้อง

### Issue #4: Can Still Access Protected Pages
**Cause:** Session cookie ยังอยู่
**Solution:** Clear cookies
```php
setcookie(session_name(), '', time() - 42000);
```

---

## 💡 Best Practices

### 1. Always Test From All Levels
- Root level
- 1 level deep (business/)
- 2 levels deep (admin/users/)

### 2. Test Different Browsers
- Chrome
- Firefox
- Safari
- Edge

### 3. Test Different Devices
- Desktop
- Tablet
- Mobile

### 4. Clear Cache Between Tests
```bash
Ctrl + Shift + Delete (Windows)
Cmd + Shift + Delete (Mac)
```

---

## 🎯 Success Criteria

Logout ถือว่าสำเร็จเมื่อ:

- ✅ ทำงานจากทุกหน้า
- ✅ ไม่มี 404 error
- ✅ Session ถูกล้างสมบูรณ์
- ✅ Redirect ถูกต้อง
- ✅ ไม่สามารถ back ไปหน้าเดิมได้
- ✅ ต้อง login ใหม่
- ✅ Cookies ถูกลบ
- ✅ ทำงานบนทุก browser
- ✅ ทำงานบนทุก device

---

## 📝 Test Log Example

```
=== Logout Test - 2024-12-XX ===

Time: 14:30
Browser: Chrome 120
OS: Windows 11

Test #1: ✅ PASS
- Logout from index.php works
- Redirected successfully
- Session cleared

Test #2: ✅ PASS
- Logout from business/dashboard.php works
- Path calculation correct: ../logout.php
- No 404 error

Test #3: ✅ PASS
- Logout from energy.php works
- Sidebar logout link works
- Header logout works

Test #4: ✅ PASS
- Admin logout works
- Cannot access admin after logout
- Must re-login

Test #5: ✅ PASS
- Double logout handled gracefully
- No error displayed

Test #6: ✅ PASS
- Direct access to logout.php works
- Redirects to home

Test #7: ✅ PASS
- Cookies cleared
- Session destroyed

Test #8: ✅ PASS
- Mobile logout works
- Responsive design maintained

Overall Result: ✅ ALL TESTS PASSED

Notes:
- Logout system working perfectly
- Path calculation logic correct
- Session management proper
- Ready for production
```

---

## 🚀 Automation Script (Optional)

```php
<?php
// test_logout.php
// Simple automated logout test

session_start();
$_SESSION['test_user'] = true;

echo "Before logout:\n";
var_dump($_SESSION);

// Simulate logout
$_SESSION = [];
session_unset();
session_destroy();

echo "\nAfter logout:\n";
var_dump($_SESSION);

echo "\nTest: " . (empty($_SESSION) ? "✅ PASS" : "❌ FAIL");
?>
```

---

**Status:** ✅ Ready for Testing
**Last Updated:** 2024
**Version:** 1.0