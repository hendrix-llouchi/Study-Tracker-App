# PHP fileinfo Extension Setup Guide

## Problem
The bulk upload feature requires the `php_fileinfo` extension to detect MIME types of uploaded files. If this extension is not enabled, you'll see the error:

```
Unable to guess the MIME type as no guessers are available (have you enabled the php_fileinfo extension?).
```

## Solution: Enable php_fileinfo Extension in XAMPP

### Step 1: Locate php.ini File

1. Open XAMPP Control Panel
2. Click **Config** button next to Apache
3. Select **PHP (php.ini)** from the dropdown menu
4. This will open the `php.ini` file in your default text editor

Alternatively, you can find it at:
- `C:\xampp\php\php.ini`

### Step 2: Enable the Extension

1. In the `php.ini` file, search for `extension=fileinfo` (use Ctrl+F)
2. You'll likely find a line that looks like:
   ```ini
   ;extension=fileinfo
   ```
3. Remove the semicolon (`;`) at the beginning to uncomment it:
   ```ini
   extension=fileinfo
   ```

### Step 3: Save and Restart

1. Save the `php.ini` file
2. Restart Apache in XAMPP Control Panel (click **Stop**, then **Start**)

### Step 4: Verify Installation

You can verify that the extension is enabled by:

1. Creating a PHP file (e.g., `test_fileinfo.php`) in your project root:
   ```php
   <?php
   if (extension_loaded('fileinfo')) {
       echo "fileinfo extension is enabled!";
   } else {
       echo "fileinfo extension is NOT enabled";
   }
   ?>
   ```

2. Or check via command line:
   ```bash
   php -m | findstr fileinfo
   ```

3. Or check the phpinfo() output:
   ```php
   <?php phpinfo(); ?>
   ```
   Look for "fileinfo" in the list of loaded extensions.

## Alternative: Check via Laravel

You can also check if the extension is available by running:
```bash
php artisan tinker
```
Then in tinker:
```php
extension_loaded('fileinfo')
```

## Note

The code has been updated to handle the case where fileinfo is not available gracefully, but it's still recommended to enable the extension for proper MIME type validation and security.
