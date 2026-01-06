# Laravel Queue Worker Startup Script
# Run this script to start the queue worker for Import/Export jobs

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Laravel Queue Worker - CRM System" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if we're in the right directory
if (!(Test-Path ".\artisan")) {
    Write-Host "ERROR: artisan file not found!" -ForegroundColor Red
    Write-Host "Please run this script from the Laravel project root directory." -ForegroundColor Yellow
    Write-Host "Current directory: $PWD" -ForegroundColor Yellow
    pause
    exit 1
}

# Check for pending jobs
Write-Host "Checking for pending jobs..." -ForegroundColor Yellow
$pendingJobs = php artisan tinker --execute="echo DB::table('jobs')->count();" 2>$null
if ($pendingJobs -match '\d+') {
    $count = $Matches[0]
    Write-Host "Found $count pending jobs in queue" -ForegroundColor Green
}

Write-Host ""
Write-Host "Starting Queue Worker..." -ForegroundColor Green
Write-Host "Press Ctrl+C to stop the worker" -ForegroundColor Yellow
Write-Host ""

# Start the queue worker
try {
    php artisan queue:work --queue=default --tries=3 --timeout=300 --sleep=3 --max-jobs=1000
}
catch {
    Write-Host ""
    Write-Host "Worker stopped." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Queue Worker has been stopped." -ForegroundColor Cyan
pause
