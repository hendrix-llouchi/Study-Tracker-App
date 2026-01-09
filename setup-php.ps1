# PHP Setup Script
# This script helps locate and configure PHP 8.2+ for your Laravel project

Write-Host "Searching for PHP installation..." -ForegroundColor Yellow

# Common installation locations
$searchPaths = @(
    "C:\php",
    "C:\php82",
    "C:\php83",
    "C:\Program Files\PHP",
    "$env:USERPROFILE\php",
    "$env:USERPROFILE\Downloads\php*",
    "C:\tools\php"
)

$foundPhp = $null

foreach ($path in $searchPaths) {
    $phpExe = Join-Path $path "php.exe"
    if (Test-Path $phpExe) {
        $version = & $phpExe -v 2>&1 | Select-Object -First 1
        if ($version -match "PHP (\d+)\.(\d+)") {
            $major = [int]$matches[1]
            $minor = [int]$matches[2]
            if ($major -gt 8 -or ($major -eq 8 -and $minor -ge 2)) {
                $foundPhp = $path
                Write-Host "`nFound PHP installation at: $path" -ForegroundColor Green
                Write-Host "Version: $version" -ForegroundColor Green
                break
            }
        }
    }
}

if (-not $foundPhp) {
    Write-Host "`nPHP 8.2+ not found in common locations." -ForegroundColor Red
    Write-Host "`nPlease provide the path where you extracted PHP:" -ForegroundColor Yellow
    Write-Host "For example: C:\php82 or C:\Program Files\PHP" -ForegroundColor Yellow
    $customPath = Read-Host "Enter PHP installation path"
    
    if ($customPath -and (Test-Path (Join-Path $customPath "php.exe"))) {
        $foundPhp = $customPath
        $version = & (Join-Path $foundPhp "php.exe") -v 2>&1 | Select-Object -First 1
        Write-Host "Found PHP at: $foundPhp" -ForegroundColor Green
        Write-Host "Version: $version" -ForegroundColor Green
    } else {
        Write-Host "Invalid path or php.exe not found!" -ForegroundColor Red
        exit 1
    }
}

# Add to PATH for current session
$env:Path = "$foundPhp;$env:Path"

# Add to user PATH permanently
$currentPath = [Environment]::GetEnvironmentVariable("Path", "User")
if ($currentPath -notlike "*$foundPhp*") {
    [Environment]::SetEnvironmentVariable("Path", "$foundPhp;$currentPath", "User")
    Write-Host "`nAdded PHP to PATH permanently!" -ForegroundColor Green
} else {
    Write-Host "`nPHP already in PATH" -ForegroundColor Yellow
}

# Verify PHP version
Write-Host "`nVerifying PHP installation..." -ForegroundColor Yellow
$phpVersion = php -v 2>&1 | Select-Object -First 1
Write-Host $phpVersion -ForegroundColor Cyan

if ($phpVersion -match "PHP (\d+)\.(\d+)") {
    $major = [int]$matches[1]
    $minor = [int]$matches[2]
    if ($major -gt 8 -or ($major -eq 8 -and $minor -ge 2)) {
        Write-Host "`n✓ PHP version is compatible with Laravel 12!" -ForegroundColor Green
        Write-Host "`nYou can now run:" -ForegroundColor Yellow
        Write-Host "  cd backend\study_tracker_app" -ForegroundColor Cyan
        Write-Host "  composer install" -ForegroundColor Cyan
        Write-Host "  php artisan serve" -ForegroundColor Cyan
    } else {
        Write-Host "`n✗ PHP version is too old. Need PHP 8.2+ but found $major.$minor" -ForegroundColor Red
    }
}

Write-Host "`nNote: You may need to restart your terminal for PATH changes to take full effect." -ForegroundColor Yellow
