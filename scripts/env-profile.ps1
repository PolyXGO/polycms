param(
    [ValidateSet('apply-perf', 'restore-local', 'status')]
    [string]$Action = 'status'
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $PSScriptRoot
$envPath = Join-Path $projectRoot '.env'
$backupPath = Join-Path $projectRoot '.env.local.backup'
$statePath = Join-Path $projectRoot '.env.profile.state'

if (-not (Test-Path -LiteralPath $envPath)) {
    throw ".env not found at: $envPath"
}

function Get-EnvMap {
    param([string]$Path)
    $map = @{}
    foreach ($line in Get-Content -LiteralPath $Path) {
        if ($line -match '^\s*([A-Z0-9_]+)=(.*)$') {
            $map[$matches[1]] = $matches[2]
        }
    }
    return $map
}

function Set-EnvValue {
    param(
        [string]$Path,
        [string]$Key,
        [string]$Value
    )

    $content = [System.IO.File]::ReadAllText($Path)
    $pattern = "(?m)^\s*$Key=.*$"
    $replacement = "$Key=$Value"

    if ([System.Text.RegularExpressions.Regex]::IsMatch($content, $pattern)) {
        $content = [System.Text.RegularExpressions.Regex]::Replace($content, $pattern, $replacement)
    } else {
        $lineBreak = if ($content.Contains("`r`n")) { "`r`n" } else { "`n" }
        if (-not [string]::IsNullOrEmpty($content) -and -not $content.EndsWith("`n")) {
            $content += $lineBreak
        }
        $content += "$replacement$lineBreak"
    }

    [System.IO.File]::WriteAllText($Path, $content, [System.Text.UTF8Encoding]::new($false))
}

function Show-Status {
    $map = Get-EnvMap -Path $envPath
    $keys = @(
        'APP_ENV',
        'APP_DEBUG',
        'LOG_LEVEL',
        'SESSION_DRIVER',
        'QUEUE_CONNECTION',
        'CACHE_STORE'
    )

    Write-Host 'Current .env profile:'
    foreach ($key in $keys) {
        $value = if ($map.ContainsKey($key)) { $map[$key] } else { '<missing>' }
        Write-Host ("  {0}={1}" -f $key, $value)
    }

    Write-Host ("Backup exists: {0}" -f (Test-Path -LiteralPath $backupPath))
    Write-Host ("Profile state exists: {0}" -f (Test-Path -LiteralPath $statePath))
}

function Invoke-Artisan {
    param(
        [string]$Command,
        [switch]$AllowFailure
    )

    Write-Host ("php artisan {0}" -f $Command)
    & php artisan $Command
    $exitCode = $LASTEXITCODE
    if ($exitCode -ne 0 -and -not $AllowFailure) {
        throw "artisan $Command failed with exit code $exitCode"
    }
    return $exitCode
}

switch ($Action) {
    'apply-perf' {
        if (-not (Test-Path -LiteralPath $backupPath)) {
            Copy-Item -LiteralPath $envPath -Destination $backupPath -Force
            Write-Host "Created backup: $backupPath"
        } else {
            Write-Host "Using existing backup: $backupPath"
        }

        Set-EnvValue -Path $envPath -Key 'APP_ENV' -Value 'local'
        Set-EnvValue -Path $envPath -Key 'APP_DEBUG' -Value 'false'
        Set-EnvValue -Path $envPath -Key 'LOG_LEVEL' -Value 'warning'
        Set-EnvValue -Path $envPath -Key 'SESSION_DRIVER' -Value 'file'
        Set-EnvValue -Path $envPath -Key 'QUEUE_CONNECTION' -Value 'sync'
        Set-EnvValue -Path $envPath -Key 'CACHE_STORE' -Value 'file'

        Set-Content -LiteralPath $statePath -Value @(
            "profile=perf"
            "applied_at=$([DateTime]::UtcNow.ToString('o'))"
        ) -Encoding UTF8

        Push-Location $projectRoot
        try {
            Invoke-Artisan -Command 'config:cache' | Out-Host
            Invoke-Artisan -Command 'event:cache' -AllowFailure | Out-Host

            $viewExit = Invoke-Artisan -Command 'view:cache' -AllowFailure
            if ($viewExit -ne 0) {
                Write-Host 'view:cache failed (some module view paths are invalid). Continuing without precompiled views.'
                Invoke-Artisan -Command 'view:clear' -AllowFailure | Out-Host
            }

            $routeExit = Invoke-Artisan -Command 'route:cache' -AllowFailure
            if ($routeExit -ne 0) {
                Write-Host 'route:cache failed (likely duplicate route names). Continuing without route cache.'
                Invoke-Artisan -Command 'route:clear' -AllowFailure | Out-Host
            }
        } finally {
            Pop-Location
        }

        Write-Host 'Applied Local Perf profile.'
        Show-Status
        break
    }

    'restore-local' {
        if (-not (Test-Path -LiteralPath $backupPath)) {
            throw "Backup not found: $backupPath"
        }

        Copy-Item -LiteralPath $backupPath -Destination $envPath -Force

        Push-Location $projectRoot
        try {
            Invoke-Artisan -Command 'optimize:clear' -AllowFailure | Out-Host
        } finally {
            Pop-Location
        }

        if (Test-Path -LiteralPath $statePath) {
            Remove-Item -LiteralPath $statePath -Force
        }

        Write-Host 'Restored Local profile from backup.'
        Show-Status
        break
    }

    'status' {
        Show-Status
        break
    }
}
