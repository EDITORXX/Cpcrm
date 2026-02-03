# Flutter PATH Setup Script
# This script adds Flutter to User PATH (no admin needed)

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Flutter PATH Setup" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if Flutter exists (multiple possible locations)
$flutterPaths = @(
    "C:\flutter\bin\flutter.bat",
    "C:\flutter\bin\flutter.exe",
    "C:\flutter\bin\flutter"
)

$flutterFound = $false
$flutterPath = ""

foreach ($path in $flutterPaths) {
    if (Test-Path $path) {
        $flutterFound = $true
        $flutterPath = $path
        break
    }
}

if (-not $flutterFound) {
    Write-Host "[✗] Flutter not found at C:\flutter\bin" -ForegroundColor Red
    Write-Host ""
    Write-Host "Checking C:\flutter folder..." -ForegroundColor Yellow
    if (Test-Path "C:\flutter") {
        Write-Host "[i] C:\flutter folder exists" -ForegroundColor Cyan
        $binContents = Get-ChildItem "C:\flutter\bin" -ErrorAction SilentlyContinue
        if ($binContents) {
            Write-Host "[i] Contents of C:\flutter\bin:" -ForegroundColor Cyan
            $binContents | Select-Object Name | Format-Table -AutoSize
        }
    } else {
        Write-Host "[✗] C:\flutter folder does not exist" -ForegroundColor Red
    }
    Write-Host ""
    Write-Host "Please verify Flutter installation at C:\flutter" -ForegroundColor Yellow
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "[✓] Flutter found at: $flutterPath" -ForegroundColor Green

Write-Host ""
Write-Host "Adding Flutter to User PATH..." -ForegroundColor Yellow
Write-Host ""

# Get current User PATH
$currentPath = [Environment]::GetEnvironmentVariable('Path', 'User')

# Check if Flutter already exists
if ($currentPath -like '*C:\flutter\bin*') {
    Write-Host "[!] Flutter PATH already exists in User variables" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Current PATH already contains: C:\flutter\bin" -ForegroundColor Green
} else {
    # Add Flutter to PATH
    $newPath = $currentPath + ';C:\flutter\bin'
    [Environment]::SetEnvironmentVariable('Path', $newPath, 'User')
    
    Write-Host "[✓] Flutter PATH added to User variables successfully!" -ForegroundColor Green
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  ✓ Setup Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Flutter has been added to User PATH." -ForegroundColor Green
Write-Host ""
Write-Host "IMPORTANT: Please restart your terminal/IDE" -ForegroundColor Yellow
Write-Host "for changes to take effect." -ForegroundColor Yellow
Write-Host ""
Write-Host "After restart, run: flutter --version" -ForegroundColor Cyan
Write-Host ""

Read-Host "Press Enter to exit"
