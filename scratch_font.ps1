$appPath = 'c:\laragon\www\spk-penagihan\resources\views\layouts\app.blade.php'
$appContent = Get-Content $appPath -Raw
$appContent = [Regex]::Replace($appContent, 'font-size:\s*(\d+)px', { param($m) "font-size: " + ([int]$m.Groups[1].Value + 2) + "px" })
Set-Content $appPath $appContent

$dpPath = 'c:\laragon\www\spk-penagihan\resources\views\staff\datapelanggan\datapelanggan.blade.php'
$dpContent = Get-Content $dpPath -Raw
$dpContent = $dpContent -replace '1\.5rem', '1.75rem'
$dpContent = $dpContent -replace '1\.25rem', '1.5rem'
$dpContent = $dpContent -replace '1\.125rem', '1.25rem'
$dpContent = $dpContent -replace '(?<!\.)1rem', '1.125rem'
$dpContent = $dpContent -replace '0\.8125rem', '0.9375rem'
$dpContent = $dpContent -replace '0\.75rem', '0.875rem'
$dpContent = $dpContent -replace '0\.7rem', '0.8rem'
$dpContent = $dpContent -replace '0\.6rem', '0.7rem'
$dpContent = $dpContent -replace '\.875rem', '1rem'
$dpContent = $dpContent -replace '\.8125rem', '.9375rem'
$dpContent = $dpContent -replace '\.75rem', '.875rem'
Set-Content $dpPath $dpContent
