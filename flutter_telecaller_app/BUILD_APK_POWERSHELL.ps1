# Flutter APK Build - PowerShell Script
# Run this script in PowerShell

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Flutter APK Build" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Kill stuck Java processes
Write-Host "Step 0: Killing stuck Java processes..." -ForegroundColor Yellow
taskkill /F /IM java.exe /T 2>$null
Start-Sleep -Seconds 2
Write-Host ""

# Set PATH
$env:PATH += ";C:\flutter\bin"
$env:ANDROID_HOME = "$env:LOCALAPPDATA\Android\Sdk"

Write-Host "Flutter PATH set" -ForegroundColor Green
Write-Host "ANDROID_HOME set to: $env:ANDROID_HOME" -ForegroundColor Green
Write-Host ""

# Navigate to app directory
$scriptPath = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $scriptPath

Write-Host "Current directory: $(Get-Location)" -ForegroundColor Cyan
Write-Host ""

# Clear Gradle cache
Write-Host "Step 1: Clearing Gradle cache..." -ForegroundColor Yellow
if (Test-Path "$env:USERPROFILE\.gradle\caches") {
    Remove-Item -Path "$env:USERPROFILE\.gradle\caches" -Recurse -Force -ErrorAction SilentlyContinue
    Write-Host "[✓] Gradle cache cleared" -ForegroundColor Green
} else {
    Write-Host "[i] Gradle cache already clean" -ForegroundColor Cyan
}
Write-Host ""

# Flutter clean
Write-Host "Step 2: Flutter clean..." -ForegroundColor Yellow
flutter clean
Write-Host ""

# Get dependencies
Write-Host "Step 3: Getting dependencies..." -ForegroundColor Yellow
flutter pub get
Write-Host ""

# Build APK
Write-Host "Step 4: Building APK..." -ForegroundColor Yellow
Write-Host ""
Write-Host "IMPORTANT: This will take 3-5 minutes" -ForegroundColor Yellow
Write-Host "Please wait, do NOT close this window" -ForegroundColor Yellow
Write-Host ""

flutter build apk --release

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Cyan
    Write-Host "  ✓ APK Built Successfully!" -ForegroundColor Green
    Write-Host "========================================" -ForegroundColor Cyan
    Write-Host ""
    
    $apkPath = Join-Path (Get-Location) "build\app\outputs\flutter-apk\app-release.apk"
    if (Test-Path $apkPath) {
        Write-Host "APK Location:" -ForegroundColor Green
        Write-Host $apkPath -ForegroundColor Cyan
        Write-Host ""
        Write-Host "Opening folder..." -ForegroundColor Yellow
        Start-Process "explorer.exe" -ArgumentList (Split-Path $apkPath)
    }
} else {
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Red
    Write-Host "  ✗ Build Failed" -ForegroundColor Red
    Write-Host "========================================" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please check error messages above" -ForegroundColor Yellow
    Write-Host ""
}

Write-Host ""
Read-Host "Press Enter to exit"
