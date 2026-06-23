param(
    [string]$HostName = "akenning.sakura.ne.jp",
    [string]$UserName = "akenning"
)

$ErrorActionPreference = "Stop"

$files = @(
    @{ Local = "index.php"; Remote = "index.php" },
    @{ Local = "submit.php"; Remote = "submit.php" },
    @{ Local = "result.php"; Remote = "result.php" },
    @{ Local = "config.php"; Remote = "config.php" },
    @{ Local = "css/style.css"; Remote = "css/style.css" },
    @{ Local = "data/.htaccess"; Remote = "data/.htaccess" }
)

$securePassword = Read-Host "Sakura server/FTP password" -AsSecureString
$bstr = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($securePassword)
$password = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($bstr)
[Runtime.InteropServices.Marshal]::ZeroFreeBSTR($bstr)

try {
    foreach ($file in $files) {
        $localPath = Join-Path $PSScriptRoot $file.Local
        if (-not (Test-Path -LiteralPath $localPath)) {
            throw "Missing local file: $($file.Local)"
        }

        $remoteUrl = "ftp://$HostName/www/enquete/$($file.Remote)"
        Write-Host "Uploading $($file.Local) -> www/enquete/$($file.Remote)"
        curl.exe --fail --show-error --silent --ftp-ssl-reqd --user "$UserName`:$password" --ftp-create-dirs -T "$localPath" "$remoteUrl"
    }

    Write-Host "Setting data directory permission to 705"
    try {
        curl.exe --fail --show-error --silent --ftp-ssl-reqd --user "$UserName`:$password" --quote "SITE CHMOD 705 www/enquete/data" "ftp://$HostName/"
    } catch {
        Write-Warning "Upload succeeded, but automatic chmod failed. Set www/enquete/data to 705 in FileZilla or Sakura File Manager."
    }

    Write-Host "Checking public URL"
    $response = Invoke-WebRequest -Uri "http://$HostName/enquete/" -UseBasicParsing -TimeoutSec 20
    Write-Host "Done. HTTP status: $($response.StatusCode)"
    Write-Host "Public URL: http://$HostName/enquete/"
} finally {
    $password = $null
}
