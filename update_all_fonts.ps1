$files = Get-ChildItem -Path "c:\laragon\www\spk-penagihan\resources\views" -Filter "*.blade.php" -Recurse

foreach ($file in $files) {
    if ($file.FullName -match "app.blade.php$" -or $file.FullName -match "datapelanggan.blade.php$") {
        continue
    }

    $content = Get-Content $file.FullName -Raw

    # Boost px sizes by 2px
    $content = [Regex]::Replace($content, 'font-size:\s*(\d+)px', { param($m) "font-size: " + ([int]$m.Groups[1].Value + 2) + "px" })

    # Boost rem sizes by 0.125rem
    $content = [Regex]::Replace($content, 'font-size:\s*([\d\.]+)rem', { param($m) 
        $valStr = $m.Groups[1].Value
        if ($valStr.StartsWith(".")) {
            $valStr = "0" + $valStr
        }
        $val = [double]$valStr
        $newVal = $val + 0.125
        
        $newStr = $newVal.ToString("G", [System.Globalization.CultureInfo]::InvariantCulture)
        
        return "font-size: " + $newStr + "rem"
    })
    
    Set-Content $file.FullName $content
}

Write-Output "Fonts updated successfully"
