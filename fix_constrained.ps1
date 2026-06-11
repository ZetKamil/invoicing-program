$files = Get-ChildItem -Path "c:\wamp64\www\facturatieprogramma\database\migrations" -Filter "*.php"
foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    if ($content -match "bedrijf_id'\)->constrained\(\)") {
        $content = $content -replace "bedrijf_id'\)->constrained\(\)", "bedrijf_id')->constrained('bedrijven')"
        [System.IO.File]::WriteAllText($file.FullName, $content, (New-Object System.Text.UTF8Encoding($false)))
        Write-Host "Updated $($file.Name)"
    }
}
