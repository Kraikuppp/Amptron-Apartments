# Render Deployment - Quick Commands

## 🚀 Quick Deploy Commands

```bash
# 1. Initialize Git (ถ้ายังไม่ได้ทำ)
git init

# 2. Add all files
git add .

# 3. Commit
git commit -m "Ready for Render deployment"

# 4. Add remote (แทน YOUR_USERNAME/YOUR_REPO ด้วยของคุณ)
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO.git

# 5. Push to GitHub
git branch -M main
git push -u origin main
```

## 🔄 Update Commands (หลังแก้ไขโค้ด)

```bash
git add .
git commit -m "Update: your message here"
git push
```

## 📝 Render Settings

**Build Command:**
```bash
chmod +x build.sh && ./build.sh
```

**Start Command:**
```bash
php -S 0.0.0.0:$PORT -t .
```

**Environment Variables:**
- `PHP_TIMEZONE` = `Asia/Bangkok`
- `APP_ENV` = `production`

## 🌐 Your URLs

- **Local (Development):** `http://localhost/billing/`
- **Production (Render):** `https://[your-service-name].onrender.com`

⚠️ **Important:** After deploying to Render, use the Render URL, NOT localhost!
