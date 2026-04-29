$ErrorActionPreference = 'Stop'

# ========================================
# FlexiWhite Theme Deploy Script
# ========================================
$THEME_NAME = "flexiwhite"
$TARGET_DEPLOY = "D:\Data\PolyCMS\Themes\$THEME_NAME"

Write-Host "=== Deploy $THEME_NAME Theme ===" -ForegroundColor Cyan

# Read version from theme.json
$themeJson = Get-Content (Join-Path $PSScriptRoot "theme.json") -Raw | ConvertFrom-Json
$version = $themeJson.version
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
$TempBasePath = Join-Path $ENV:TEMP "${THEME_NAME}_deploy"
if (Test-Path $TempBasePath) { Remove-Item -Recurse -Force $TempBasePath }
New-Item -ItemType Directory -Force -Path $TempBasePath | Out-Null

# Theme must be inside a folder with its name for PolyCMS
$TempThemeDir = Join-Path $TempBasePath $THEME_NAME
New-Item -ItemType Directory -Force -Path $TempThemeDir | Out-Null

Write-Host "Copying theme files..."

# ========================================
# COPY WITH EXCLUSIONS
# ========================================
$RobocopyArgs = @(
    $SourceDir,
    $TempThemeDir,
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
# CREATE ZIP (using .NET ZipFile for cross-platform forward-slash paths)
# ========================================
$ZipFileName = "${THEME_NAME}.zip"
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
Write-Host "$THEME_NAME Deploy completed!" -ForegroundColor Green
Write-Host "  Version:  v$version"
Write-Host "  Package:  $ZipFileName ($([math]::Round($zipSize, 1)) KB)"
Write-Host "  Output:   $TARGET_DEPLOY"
Write-Host ""
Write-Host "  Install: Upload zip in Admin > Appearance > Themes > Install Theme"
