# PowerShell Script สำหรับ Deploy โปรเจคขึ้น GitHub
# สำหรับ Render Deployment

Write-Host "🚀 Billing Rental System - GitHub Deployment Script" -ForegroundColor Cyan
Write-Host "=================================================" -ForegroundColor Cyan
Write-Host ""

# ตรวจสอบว่าอยู่ในโฟลเดอร์ที่ถูกต้อง
$currentPath = Get-Location
Write-Host "📁 Current directory: $currentPath" -ForegroundColor Yellow

# ตรวจสอบว่ามี Git หรือไม่
try {
    $gitVersion = git --version
    Write-Host "✅ Git found: $gitVersion" -ForegroundColor Green
} catch {
    Write-Host "❌ Git not found! Please install Git first." -ForegroundColor Red
    Write-Host "   Download from: https://git-scm.com" -ForegroundColor Yellow
    exit 1
}

Write-Host ""
Write-Host "📋 Checking required files..." -ForegroundColor Yellow

# ตรวจสอบไฟล์ที่จำเป็น
$requiredFiles = @(
    "Dockerfile",
    "render.yaml",
    ".dockerignore",
    "index.php",
    "README.md"
)

$allFilesExist = $true
foreach ($file in $requiredFiles) {
    if (Test-Path $file) {
        Write-Host "   ✅ $file" -ForegroundColor Green
    } else {
        Write-Host "   ❌ $file NOT FOUND!" -ForegroundColor Red
        $allFilesExist = $false
    }
}

if (-not $allFilesExist) {
    Write-Host ""
    Write-Host "❌ Some required files are missing!" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "✅ All required files found!" -ForegroundColor Green
Write-Host ""

# ถามชื่อ GitHub username
Write-Host "📝 GitHub Configuration" -ForegroundColor Cyan
Write-Host "========================" -ForegroundColor Cyan
$username = Read-Host "Enter your GitHub username"

if ([string]::IsNullOrWhiteSpace($username)) {
    Write-Host "❌ Username cannot be empty!" -ForegroundColor Red
    exit 1
}

$repoName = "billing-rental-system"
$repoUrl = "https://github.com/$username/$repoName.git"

Write-Host ""
Write-Host "Repository URL: $repoUrl" -ForegroundColor Yellow
Write-Host ""

# ถามยืนยัน
$confirm = Read-Host "Do you want to continue? (y/n)"
if ($confirm -ne "y" -and $confirm -ne "Y") {
    Write-Host "❌ Cancelled by user" -ForegroundColor Red
    exit 0
}

Write-Host ""
Write-Host "🔄 Starting Git operations..." -ForegroundColor Cyan
Write-Host ""

# ตรวจสอบว่ามี Git repository อยู่แล้วหรือไม่
if (Test-Path ".git") {
    Write-Host "✅ Git repository already initialized" -ForegroundColor Green
} else {
    Write-Host "📦 Initializing Git repository..." -ForegroundColor Yellow
    git init
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Git initialized successfully" -ForegroundColor Green
    } else {
        Write-Host "❌ Failed to initialize Git" -ForegroundColor Red
        exit 1
    }
}

Write-Host ""
Write-Host "📝 Adding files to Git..." -ForegroundColor Yellow
git add .

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Files added successfully" -ForegroundColor Green
} else {
    Write-Host "❌ Failed to add files" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "💾 Committing changes..." -ForegroundColor Yellow
git commit -m "Add Render deployment files with Docker support"

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Changes committed successfully" -ForegroundColor Green
} else {
    Write-Host "⚠️  Commit failed (maybe no changes to commit)" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "🔗 Setting up remote repository..." -ForegroundColor Yellow

# ตรวจสอบว่ามี remote origin อยู่แล้วหรือไม่
$remoteExists = git remote | Select-String -Pattern "origin"

if ($remoteExists) {
    Write-Host "   Updating existing remote..." -ForegroundColor Yellow
    git remote set-url origin $repoUrl
} else {
    Write-Host "   Adding new remote..." -ForegroundColor Yellow
    git remote add origin $repoUrl
}

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Remote configured successfully" -ForegroundColor Green
} else {
    Write-Host "❌ Failed to configure remote" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "🌿 Setting branch to main..." -ForegroundColor Yellow
git branch -M main

Write-Host ""
Write-Host "🚀 Pushing to GitHub..." -ForegroundColor Yellow
Write-Host "   (You may need to enter your GitHub credentials)" -ForegroundColor Cyan
Write-Host ""

git push -u origin main

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "✅ Successfully pushed to GitHub!" -ForegroundColor Green
    Write-Host ""
    Write-Host "=================================================" -ForegroundColor Cyan
    Write-Host "🎉 Deployment to GitHub Complete!" -ForegroundColor Green
    Write-Host "=================================================" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "📍 Your repository:" -ForegroundColor Yellow
    Write-Host "   https://github.com/$username/$repoName" -ForegroundColor White
    Write-Host ""
    Write-Host "🚀 Next Steps:" -ForegroundColor Cyan
    Write-Host "   1. Go to https://dashboard.render.com" -ForegroundColor White
    Write-Host "   2. Click 'New +' → 'Web Service'" -ForegroundColor White
    Write-Host "   3. Connect your GitHub repository" -ForegroundColor White
    Write-Host "   4. Click 'Create Web Service'" -ForegroundColor White
    Write-Host "   5. Wait 3-5 minutes for deployment" -ForegroundColor White
    Write-Host ""
    Write-Host "📚 For detailed instructions, read:" -ForegroundColor Cyan
    Write-Host "   - QUICK_DEPLOY.md (Quick guide)" -ForegroundColor White
    Write-Host "   - RENDER_DEPLOY_GUIDE.md (Full guide)" -ForegroundColor White
    Write-Host ""
} else {
    Write-Host ""
    Write-Host "❌ Failed to push to GitHub!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Possible reasons:" -ForegroundColor Yellow
    Write-Host "   1. Repository doesn't exist on GitHub" -ForegroundColor White
    Write-Host "      → Create it at: https://github.com/new" -ForegroundColor Cyan
    Write-Host "   2. Authentication failed" -ForegroundColor White
    Write-Host "      → Check your GitHub credentials" -ForegroundColor Cyan
    Write-Host "   3. No internet connection" -ForegroundColor White
    Write-Host "      → Check your network" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "📚 For help, read RENDER_DEPLOY_GUIDE.md" -ForegroundColor Yellow
    Write-Host ""
    exit 1
}

Write-Host "Press any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
