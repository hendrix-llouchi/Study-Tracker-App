# Enable fileinfo Extension in XAMPP
# This script enables the php_fileinfo extension in XAMPP's php.ini file

Write-Host "Enabling fileinfo extension in XAMPP..." -ForegroundColor Yellow

# Common XAMPP PHP paths
$phpIniPaths = @(
    "C:\xampp\php\php.ini",
    "C:\xampp\php82\php.ini",
    "C:\xampp\php83\php.ini"
)

$phpIniPath = $null

# Find php.ini file
foreach ($path in $phpIniPaths) {
    if (Test-Path $path) {
        $phpIniPath = $path
        Write-Host "Found php.ini at: $path" -ForegroundColor Green
        break
    }
}

if (-not $phpIniPath) {
    Write-Host "`nCould not find php.ini in common XAMPP locations." -ForegroundColor Red
    Write-Host "Please provide the path to your php.ini file:" -ForegroundColor Yellow
    $customPath = Read-Host "Enter php.ini path (e.g., C:\xampp\php\php.ini)"
    
    if ($customPath -and (Test-Path $customPath)) {
        $phpIniPath = $customPath
    } else {
        Write-Host "Invalid path or file not found!" -ForegroundColor Red
        exit 1
    }
}

# Read php.ini content
Write-Host "`nReading php.ini..." -ForegroundColor Yellow
$content = Get-Content $phpIniPath -Raw

# Check if fileinfo is already enabled
if ($content -match '^\s*extension\s*=\s*fileinfo\s*$' -and $content -notmatch '^\s*;\s*extension\s*=\s*fileinfo') {
    Write-Host "`n✓ fileinfo extension is already enabled!" -ForegroundColor Green
    exit 0
}

# Check if fileinfo line exists (commented or uncommented)
if ($content -match ';?\s*extension\s*=\s*fileinfo') {
    Write-Host "Found fileinfo extension line, enabling it..." -ForegroundColor Yellow
    
    # Replace commented extension=fileinfo with uncommented version
    $newContent = $content -replace ';\s*extension\s*=\s*fileinfo', 'extension=fileinfo'
    
    # Create backup
    $backupPath = "$phpIniPath.backup.$(Get-Date -Format 'yyyyMMdd_HHmmss')"
    Copy-Item $phpIniPath $backupPath
    Write-Host "Created backup: $backupPath" -ForegroundColor Cyan
    
    # Write updated content
    Set-Content -Path $phpIniPath -Value $newContent -NoNewline
    Write-Host "`n✓ Successfully enabled fileinfo extension!" -ForegroundColor Green
} else {
    Write-Host "`nfileinfo extension line not found in php.ini" -ForegroundColor Yellow
    Write-Host "Adding extension=fileinfo to php.ini..." -ForegroundColor Yellow
    
    # Find a good place to add it (after other extensions)
    if ($content -match '(?s)(.*?)(;.*?extension.*?)(.*)') {
        # Try to add after other extension lines
        $extensionPattern = '(?m)^(;?\s*extension\s*=.*?)$'
        if ($content -match $extensionPattern) {
            $newContent = $content -replace '(?m)^(;?\s*extension\s*=.*?)$', "`$1`nextension=fileinfo"
        } else {
            # Add at the end of the file
            $newContent = $content + "`nextension=fileinfo`n"
        }
    } else {
        # Add at the end
        $newContent = $content + "`nextension=fileinfo`n"
    }
    
    # Create backup
    $backupPath = "$phpIniPath.backup.$(Get-Date -Format 'yyyyMMdd_HHmmss')"
    Copy-Item $phpIniPath $backupPath
    Write-Host "Created backup: $backupPath" -ForegroundColor Cyan
    
    # Write updated content
    Set-Content -Path $phpIniPath -Value $newContent -NoNewline
    Write-Host "`n✓ Successfully added fileinfo extension!" -ForegroundColor Green
}

# Verify the change
Write-Host "`nVerifying change..." -ForegroundColor Yellow
$verifyContent = Get-Content $phpIniPath -Raw
if ($verifyContent -match '^\s*extension\s*=\s*fileinfo\s*$' -or $verifyContent -match '(?m)^extension\s*=\s*fileinfo\s*$') {
    Write-Host "✓ Verification successful!" -ForegroundColor Green
} else {
    Write-Host "⚠ Warning: Could not verify the change. Please check manually." -ForegroundColor Yellow
}

Write-Host "`n" -NoNewline
Write-Host "IMPORTANT: " -ForegroundColor Red -NoNewline
Write-Host "You must restart Apache in XAMPP Control Panel for changes to take effect!" -ForegroundColor Yellow
Write-Host "`nSteps to restart Apache:" -ForegroundColor Cyan
Write-Host "  1. Open XAMPP Control Panel" -ForegroundColor White
Write-Host "  2. Click 'Stop' button next to Apache" -ForegroundColor White
Write-Host "  3. Wait a few seconds" -ForegroundColor White
Write-Host "  4. Click 'Start' button to restart Apache" -ForegroundColor White
Write-Host "`nAfter restarting, you can verify the extension is enabled by running:" -ForegroundColor Cyan
Write-Host "  cd C:\xampp\php" -ForegroundColor White
Write-Host "  php -m | Select-String fileinfo" -ForegroundColor White
