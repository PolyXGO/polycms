$ErrorActionPreference = 'Stop'

# ========================================
# CookieConsent Module Deploy Script
# ========================================
$MODULE_NAME = "CookieConsent"
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
# BUILD STANDALONE FRONTEND (if vite config exists)
# ========================================
$ViteConfig = Join-Path $PSScriptRoot "vite.config.module.js"
if (Test-Path $ViteConfig) {
    Write-Host "Building micro-frontend bundle..." -ForegroundColor Yellow
    Set-Location $PSScriptRoot
    & npx vite build --config vite.config.module.js
    if ($LASTEXITCODE -ne 0) {
        Write-Error "Vite build failed for $MODULE_NAME"
        exit 1
    }
}

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
           "debug_*.php", "test_*.php", "scratch-*.php", "vite.config.module.js",
    "/NJH", "/NJS", "/NDL", "/NC", "/NS"
)
& robocopy $RobocopyArgs | Out-Null

# ========================================
# CREATE ZIP (using .NET ZipFile for cross-platform forward-slash paths)
# ========================================
$ZipFileName = "${MODULE_NAME}.zip"
$ZipPath = Join-Path $TARGET_DEPLOY $ZipFileName

if (Test-Path $ZipPath) { Remove-Item $ZipPath -Force }

Write-Host "Creating $ZipFileName..."

Add-Type -AssemblyName System.IO.Compression.FileSystem
[System.IO.Compression.ZipFile]::CreateFromDirectory(
    $TempBasePath,
    $ZipPath,
    [System.IO.Compression.CompressionLevel]::Optimal,
    $false  # includeBaseDirectory = false (content only)
)

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
