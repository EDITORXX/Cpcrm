# Flutter PATH Setup Script - System Variables
# This script requires Administrator rights

# Check if running as Administrator
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)

if (-not $isAdmin) {
    Write-Host "[✗] Admin rights required!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please run this script as Administrator:" -ForegroundColor Yellow
    Write-Host "1. Right-click on this file" -ForegroundColor Yellow
    Write-Host "2. Select 'Run with PowerShell'" -ForegroundColor Yellow
    Write-Host "3. Or run PowerShell as Administrator and execute this script" -ForegroundColor Yellow
    Write-Host ""
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Flutter PATH Setup - System Variables" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if Flutter exists
$flutterPath = "C:\flutter\bin\flutter.bat"
if (Test-Path $flutterPath) {
    Write-Host "[✓] Flutter found at C:\flutter\bin" -ForegroundColor Green
} else {
    Write-Host "[✗] Flutter not found at C:\flutter\bin" -ForegroundColor Red
    Write-Host "Please install Flutter first at C:\flutter" -ForegroundColor Yellow
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host ""
Write-Host "Adding Flutter to System PATH..." -ForegroundColor Yellow
Write-Host ""

# Get current System PATH
$currentPath = [Environment]::GetEnvironmentVariable('Path', 'Machine')

# Check if Flutter already exists
if ($currentPath -like '*C:\flutter\bin*') {
    Write-Host "[!] Flutter PATH already exists in System variables" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Current PATH already contains: C:\flutter\bin" -ForegroundColor Green
} else {
    # Add Flutter to PATH
    $newPath = $currentPath + ';C:\flutter\bin'
    [Environment]::SetEnvironmentVariable('Path', $newPath, 'Machine')
    
    Write-Host "[✓] Flutter PATH added to System variables successfully!" -ForegroundColor Green
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  ✓ Setup Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Flutter has been added to System PATH." -ForegroundColor Green
Write-Host ""
Write-Host "IMPORTANT: Please restart your terminal/IDE" -ForegroundColor Yellow
Write-Host "for changes to take effect." -ForegroundColor Yellow
Write-Host ""
Write-Host "After restart, run: flutter --version" -ForegroundColor Cyan
Write-Host ""

Read-Host "Press Enter to exit"
