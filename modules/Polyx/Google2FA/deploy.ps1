$ErrorActionPreference = 'Stop'

# ========================================
# Google2FA Module Deploy Script
# ========================================
$MODULE_NAME = "Google2FA"
$MODULE_VENDOR = "Polyx"
$TARGET_DEPLOY = "D:\Data\PolyCMS\Modules\$MODULE_NAME"

Write-Host "=== Deploy $MODULE_NAME Module ===" -ForegroundColor Cyan

# Read version from module.json
$moduleJson = Get-Content (Join-Path $PSScriptRoot "module.json") -Raw | ConvertFrom-Json
$version = $moduleJson.version
Write-Host "Version: $version"

# Create target dir if not exists
if (!(Test-Path -Path $TARGET_DEPLOY)) {
    New-Item -ItemType Directory -Force -Path $TARGET_DEPLOY | Out-Null
}

$CurrentDir = $PSScriptRoot
$SourceDir = $PSScriptRoot

# ========================================
# PREPARE TEMP DIRECTORY
# ========================================
$TempBasePath = Join-Path $ENV:TEMP "${MODULE_NAME}_deploy"
if (Test-Path $TempBasePath) { Remove-Item -Recurse -Force $TempBasePath }
New-Item -ItemType Directory -Force -Path $TempBasePath | Out-Null

# Module must be inside vendor/module folder structure for PolyCMS
$TempModuleDir = Join-Path $TempBasePath "$MODULE_VENDOR\$MODULE_NAME"
New-Item -ItemType Directory -Force -Path $TempModuleDir | Out-Null

Write-Host "Copying module files..."

# ========================================
# COPY WITH EXCLUSIONS
# ========================================
$RobocopyArgs = @(
    $SourceDir,
    $TempModuleDir,
    "/E",
    "/XD", "node_modules", ".git", "heraspec", ".agents", ".ai", "docs", "_private_note",
    "/XF", "package.json", "package-lock.json", "composer.json", "composer.lock",
           "AGENTS.heraspec.md", ".cursorrules", ".DS_Store",
           "*.sh", "*.ps1", "*.bak", "*.tmp",
           ".gitignore", ".gitattributes", ".editorconfig",
           "debug_*.php", "test_*.php", "scratch-*.php",
    "/NJH", "/NJS", "/NDL", "/NC", "/NS"
)
& robocopy $RobocopyArgs | Out-Null

# ========================================
# CREATE ZIP
# ========================================
$ZipFileName = "${MODULE_NAME}.zip"
$ZipPath = Join-Path $TARGET_DEPLOY $ZipFileName

if (Test-Path $ZipPath) { Remove-Item $ZipPath -Force }

Write-Host "Creating $ZipFileName..."
Compress-Archive -Path (Join-Path $TempBasePath "*") -DestinationPath $ZipPath -Force

# ========================================
# CLEANUP
# ========================================
Remove-Item -Recurse -Force $TempBasePath

# ========================================
# SUMMARY
# ========================================
$zipSize = (Get-Item $ZipPath).Length / 1KB
Write-Host ""
Write-Host "$MODULE_NAME Deploy completed!" -ForegroundColor Green
Write-Host "  Version:  v$version"
Write-Host "  Package:  $ZipFileName ($([math]::Round($zipSize, 1)) KB)"
Write-Host "  Output:   $TARGET_DEPLOY"
Write-Host ""
Write-Host "  Install: Upload zip in Admin > Modules > Install Module"
