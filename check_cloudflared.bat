@echo off
echo ========================================
echo Finding cloudflared.exe...
echo ========================================
echo.

if exist "C:\cloudflared\cloudflared.exe" (
    echo [FOUND] C:\cloudflared\cloudflared.exe
) else (
    echo [NOT FOUND] C:\cloudflared\cloudflared.exe
)

if exist "C:\cloudflared\cloudflared.exe.exe" (
    echo [FOUND] C:\cloudflared\cloudflared.exe.exe
) else (
    echo [NOT FOUND] C:\cloudflared\cloudflared.exe.exe
)

echo.
echo Please check File Explorer and verify the exact file name.
echo.
pause
