# Deployment Status

## Current status

Upload is prepared, but not completed.

## Verified locally

- PHP syntax check passed:
  - `index.php`
  - `submit.php`
  - `result.php`
- Upload package created:
  - `enquete_sakura_upload.zip`
- Upload script created:
  - `deploy_sakura.ps1`

## Important findings

- `kizukimemo.sakura.ne.jp` does not resolve in DNS.
- Browser history and form traces show `akenning.sakura.ne.jp`.
- `akenning.sakura.ne.jp` resolves to an IP address.
- FTP port 21 on `akenning.sakura.ne.jp` is reachable.
- HTTP port 80 on `akenning.sakura.ne.jp` is reachable.
- FTP rejects anonymous/no-password access with `530 Access denied`.
- The app admin password in `config.php` is not the FTP password.
- Edge has a saved Sakura signup login entry, but the password is stored with the newer browser encryption format and was not usable from the terminal.

## Blocker

Sakura server/FTP password is required to upload files.

The Sakura server password is also the FTP password. If unknown, reissue it from:

1. Sakura member menu
2. Contract information
3. Active services list
4. Password reissue for the rental server

## Ready command

When the Sakura server password is available, run:

```powershell
.\deploy_sakura.ps1
```

The script will ask for the password without saving it.
