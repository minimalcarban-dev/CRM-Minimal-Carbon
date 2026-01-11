@echo off
REM laravel-queue.bat - simple wrapper to run Laravel queue worker on Windows
REM Place this file in the project root (where artisan lives) and run with PM2:
REM pm2 start laravel-queue.bat --name laravel-queue







exit /b %ERRORLEVEL%
n:: exit when the worker stopsphp artisan queue:work --sleep=3 --tries=3 --timeout=90 >> "%~dp0storage\logs\queue.log" 2>&1
n:: run the queue worker and append output to storage/logs/queue.logcd /d %~dp0n:: ensure we are in the project directory