@echo off
cd /d D:\admin-crud-git\CRM-Minimal-Carbon
php artisan queue:work --queue=default --tries=3 --sleep=3
