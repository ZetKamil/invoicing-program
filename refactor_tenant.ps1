$ErrorActionPreference = "Stop"

$directories = @(
    "app",
    "database\migrations",
    "database\factories",
    "database\seeders",
    "resources\views",
    "routes",
    "config"
)

# Ordered replacements (longest/most specific first to avoid partial matches)
$replacements = @(
    @{ Old = "tenant_id"; New = "bedrijf_id" },
    @{ Old = "tenant_slug"; New = "bedrijf_slug" },
    @{ Old = "Tenants"; New = "Bedrijven" },
    @{ Old = "tenants"; New = "bedrijven" },
    @{ Old = "Tenant"; New = "Bedrijf" },
    @{ Old = "tenant"; New = "bedrijf" },
    @{ Old = "TENANT"; New = "BEDRIJF" }
)

foreach ($dir in $directories) {
    if (Test-Path $dir) {
        $files = Get-ChildItem -Path $dir -Recurse -File -Include *.php, *.blade.php
        foreach ($file in $files) {
            $content = Get-Content $file.FullName -Raw
            $originalContent = $content
            $changed = $false

            foreach ($r in $replacements) {
                # Case-sensitive replace
                if ($content -cmatch [regex]::Escape($r.Old)) {
                    $content = $content -creplace [regex]::Escape($r.Old), $r.New
                    $changed = $true
                }
            }

            if ($changed) {
                Write-Host "Updating: $($file.FullName)"
                [System.IO.File]::WriteAllText($file.FullName, $content, (New-Object System.Text.UTF8Encoding($false)))
            }
        }
    }
}

Write-Host "Done content replacement."
