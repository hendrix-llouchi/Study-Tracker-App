# PowerShell script to test the health endpoint

Write-Host "Testing Laravel API Health Endpoint..." -ForegroundColor Cyan
Write-Host ""

# Test 1: Check if server is running
Write-Host "Test 1: Checking if server is accessible..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000" -Method GET -TimeoutSec 5 -ErrorAction Stop
    Write-Host "✓ Server is running" -ForegroundColor Green
} catch {
    Write-Host "✗ Server is NOT running or not accessible" -ForegroundColor Red
    Write-Host "  Error: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please start the Laravel server:" -ForegroundColor Yellow
    Write-Host "  cd backend\study_tracker_app" -ForegroundColor White
    Write-Host "  php artisan serve" -ForegroundColor White
    exit 1
}

Write-Host ""

# Test 2: Test API health endpoint
Write-Host "Test 2: Testing API health endpoint..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/v1/health" -Method GET -TimeoutSec 5 -ErrorAction Stop
    Write-Host "✓ Health endpoint is accessible" -ForegroundColor Green
    Write-Host "  Status Code: $($response.StatusCode)" -ForegroundColor Gray
    Write-Host "  Response:" -ForegroundColor Gray
    $response.Content | ConvertFrom-Json | ConvertTo-Json -Depth 10
} catch {
    Write-Host "✗ Health endpoint returned an error" -ForegroundColor Red
    Write-Host "  Status Code: $($_.Exception.Response.StatusCode.value__)" -ForegroundColor Red
    Write-Host "  Error: $($_.Exception.Message)" -ForegroundColor Red
    
    if ($_.Exception.Response.StatusCode.value__ -eq 404) {
        Write-Host ""
        Write-Host "Possible solutions:" -ForegroundColor Yellow
        Write-Host "  1. Make sure Laravel server is running: php artisan serve" -ForegroundColor White
        Write-Host "  2. Clear route cache: php artisan route:clear" -ForegroundColor White
        Write-Host "  3. Restart the Laravel server" -ForegroundColor White
    }
}

Write-Host ""

# Test 3: Test web health endpoint (for comparison)
Write-Host "Test 3: Testing web health endpoint (for comparison)..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/health" -Method GET -TimeoutSec 5 -ErrorAction Stop
    Write-Host "✓ Web health endpoint is accessible" -ForegroundColor Green
    Write-Host "  Status Code: $($response.StatusCode)" -ForegroundColor Gray
} catch {
    Write-Host "✗ Web health endpoint error: $($_.Exception.Message)" -ForegroundColor Red
}

