$ErrorActionPreference = "Stop"

function Rename-TenantToBedrijf {
    param([string]$Path)
    
    # Get all items matching *Tenant* or *tenant* recursively
    # Exclude vendor, node_modules etc.
    $items = Get-ChildItem -Path $Path -Recurse | Where-Object { $_.FullName -notmatch '\\(vendor|node_modules|\.git|storage|bootstrap)\\?' -and $_.Name -match 'Tenant|tenant' } | Sort-Object -Property @{Expression={$_.FullName.Length}; Descending=$true}
    
    foreach ($item in $items) {
        $newName = $item.Name -creplace 'Tenant', 'Bedrijf'
        $newName = $newName -creplace 'tenant', 'bedrijf'
        $newName = $newName -creplace 'Tenants', 'Bedrijven'
        $newName = $newName -creplace 'tenants', 'bedrijven'
        
        if ($newName -ne $item.Name) {
            Write-Host "Renaming $($item.FullName) to $newName"
            Rename-Item -Path $item.FullName -NewName $newName
        }
    }
}

Rename-TenantToBedrijf -Path "c:\wamp64\www\facturatieprogramma\app"
Rename-TenantToBedrijf -Path "c:\wamp64\www\facturatieprogramma\database"
Rename-TenantToBedrijf -Path "c:\wamp64\www\facturatieprogramma\resources"
Rename-TenantToBedrijf -Path "c:\wamp64\www\facturatieprogramma\routes"
