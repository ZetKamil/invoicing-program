$ErrorActionPreference = "Stop"

$directories = @(
    ".",
    "prezentacja",
    "prezentacja A - szczegolowa"
)

$replacements = @(
    # Tenant -> Bedrijf replacements
    @{ Old = "Tenantów"; New = "Bedrijven (Firm Transportowych)" },
    @{ Old = "Tenantami"; New = "Bedrijven" },
    @{ Old = "Tenanta"; New = "Bedrijf" },
    @{ Old = "Tenanci"; New = "Bedrijven" },
    @{ Old = "Tenant"; New = "Bedrijf" },
    @{ Old = "Tenants"; New = "Bedrijven" },
    @{ Old = "tenant"; New = "bedrijf" },
    @{ Old = "tenants"; New = "bedrijven" },
    
    # UI adjustments
    @{ Old = "użytkownikami"; New = "Klanten (Użytkownikami)" },
    @{ Old = "Użytkownicy"; New = "Klanten (Użytkownicy)" }
)

foreach ($dir in $directories) {
    if (Test-Path $dir) {
        $files = Get-ChildItem -Path "$dir\*" -Include *.md
        foreach ($file in $files) {
            # READ AS UTF-8
            $content = [System.IO.File]::ReadAllText($file.FullName, [System.Text.Encoding]::UTF8)
            $changed = $false

            foreach ($r in $replacements) {
                if ($content -cmatch $r.Old) {
                    $content = $content -creplace $r.Old, $r.New
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

# Rename any markdown files that have 'tenant' in the name
foreach ($dir in $directories) {
    if (Test-Path $dir) {
        $files = Get-ChildItem -Path "$dir\*" -Include *tenant*.md
        foreach ($file in $files) {
            $newName = $file.Name -replace "tenant", "bedrijf"
            Rename-Item -Path $file.FullName -NewName $newName
            Write-Host "Renamed: $($file.Name) -> $newName"
        }
    }
}

Write-Host "Done documentation replacement."
