# Restart Apache to Enable fileinfo Extension

The `fileinfo` extension is already enabled in your `php.ini` file, but Apache needs to be restarted for the change to take effect.

## Quick Steps:

1. **Open XAMPP Control Panel**
2. **Stop Apache** (click the "Stop" button)
3. **Start Apache** (click the "Start" button)

## Alternative: Restart via Command Line

If you prefer using the command line, you can restart Apache using:

```powershell
# Stop Apache
net stop Apache2.4

# Start Apache
net start Apache2.4
```

Or if you have XAMPP in your PATH:

```powershell
# Navigate to XAMPP directory
cd C:\xampp

# Stop Apache
.\apache_stop.bat

# Start Apache
.\apache_start.bat
```

## Verify Extension is Loaded

After restarting Apache, you can verify the extension is loaded by:

1. Create a test file `test_fileinfo.php` in your web root:
   ```php
   <?php
   if (extension_loaded('fileinfo')) {
       echo "✓ fileinfo extension is enabled!";
   } else {
       echo "✗ fileinfo extension is NOT enabled";
   }
   phpinfo();
   ?>
   ```

2. Visit `http://localhost/test_fileinfo.php` in your browser

3. Or check via command line:
   ```bash
   php -m | findstr fileinfo
   ```

## Note

The extension is already configured in `C:\xampp\php\php.ini` on line 930:
```
extension=fileinfo
```

After restarting Apache, your bulk upload feature should work without the MIME type error.
