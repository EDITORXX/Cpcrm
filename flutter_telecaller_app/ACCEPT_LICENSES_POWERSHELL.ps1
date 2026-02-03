# Android Licenses Acceptance - PowerShell Script
# Run this script in PowerShell

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Android Licenses Acceptance" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Set PATH to include Flutter
$env:PATH += ";C:\flutter\bin"

# Set ANDROID_HOME
$env:ANDROID_HOME = "$env:LOCALAPPDATA\Android\Sdk"

Write-Host "Flutter PATH set" -ForegroundColor Green
Write-Host "ANDROID_HOME set to: $env:ANDROID_HOME" -ForegroundColor Green
Write-Host ""

# Verify Flutter is accessible
$flutterCheck = Get-Command flutter -ErrorAction SilentlyContinue
if ($flutterCheck) {
    Write-Host "[✓] Flutter command found" -ForegroundColor Green
} else {
    Write-Host "[✗] Flutter command not found. Please check Flutter installation." -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host ""
Write-Host "Running: flutter doctor --android-licenses" -ForegroundColor Yellow
Write-Host ""
Write-Host "IMPORTANT: When prompted, type 'y' and press Enter" -ForegroundColor Yellow
Write-Host "for each license (usually 6-7 licenses)." -ForegroundColor Yellow
Write-Host ""

# Run license acceptance
flutter doctor --android-licenses

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Cyan
    Write-Host "  Licenses Accepted Successfully!" -ForegroundColor Green
    Write-Host "========================================" -ForegroundColor Cyan
    Write-Host ""
} else {
    Write-Host ""
    Write-Host "[⚠] Some licenses might not be accepted" -ForegroundColor Yellow
    Write-Host "Please check the output above" -ForegroundColor Yellow
    Write-Host ""
}

Read-Host "Press Enter to exit"
