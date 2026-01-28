@echo off
echo Starting Laravel Scheduler in background...
start /min "" "%~dp0run_scheduler.bat"
echo Scheduler started in background window.
echo To stop it, close the minimized window or use Task Manager.
pause
