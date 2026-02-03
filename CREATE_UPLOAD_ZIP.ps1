# File server ke liye deployment zip banata hai (project root se run karein)
Set-Location $PSScriptRoot
if (-not (Test-Path "_archive\deployment\create_deployment_zip.php")) {
    Write-Host "Error: create_deployment_zip.php not found. Run from project root."
    exit 1
}
php _archive\deployment\create_deployment_zip.php
Write-Host "`nZip location: $PSScriptRoot\_archive\deployment\"
Get-ChildItem _archive\deployment\crm_deployment_*.zip | Sort-Object LastWriteTime -Descending | Select-Object -First 1
