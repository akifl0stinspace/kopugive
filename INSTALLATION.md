# KopuGive Installation Guide

## System Requirements

- **PHP**: 8.0 or higher
- **MySQL**: 8.0 or higher
- **Web Server**: Apache (via XAMPP) or Nginx
- **Composer**: (Optional, for dependencies)

## Installation Steps

### 1. Install XAMPP

1. Download XAMPP from https://www.apachefriends.org/
2. Install XAMPP with Apache and MySQL components
3. Start Apache and MySQL services from XAMPP Control Panel

### 2. Clone/Extract Project

```bash
# Extract or clone the project to XAMPP htdocs folder
C:\xampp\htdocs\kopugive\
```

### 3. Create Database

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click "New" to create a new database
3. Database name: `kopugive`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"

### 4. Import Database Schema

1. In phpMyAdmin, select the `kopugive` database
2. Click on "SQL" tab
3. Open `database/schema.sql` file
4. Copy and paste the contents
5. Click "Go" to execute

### 5. Import Sample Data (Optional)

1. Still in phpMyAdmin SQL tab
2. Open `database/seed.sql` file
3. Copy and paste the contents
4. Click "Go" to execute

### 6. Configure Database Connection

1. Open `config/database.php`
2. Update database credentials if needed:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'kopugive');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default XAMPP password is empty
```

### 7. Create Required Directories

Create these directories and ensure they have write permissions:

```
kopugive/
├── uploads/
│   ├── campaigns/
│   └── receipts/
└── logs/
```

### 8. Set Permissions (Windows)

Right-click on `uploads` and `logs` folders → Properties → Security → Edit → Add write permissions for Users.

For Linux/Mac:
```bash
chmod -R 755 uploads/
chmod -R 755 logs/
```

### 9. Access the Application

Open your web browser and navigate to:
- Homepage: http://localhost/kopugive/
- Admin Login: http://localhost/kopugive/auth/login.php
- Register: http://localhost/kopugive/auth/register.php

### 10. Default Login Credentials

**Admin Account:**
- Email: admin@mrsmkp.edu.my
- Password: admin123

**Demo Donor Account:**
- Email: ahmad@example.com
- Password: admin123

## Post-Installation Configuration

### Update Application Settings

Edit `config/config.php`:

```php
// Update these based on your environment
define('APP_URL', 'http://localhost/kopugive');
define('APP_NAME', 'KopuGive');
```

### Email Configuration (Optional)

If you want to enable email notifications, update SMTP settings in `config/config.php`:

```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
```

### Enable Error Logging

For development, enable error display in `config/config.php`:

```php
ini_set('display_errors', 1); // Show errors on screen
```

For production:

```php
ini_set('display_errors', 0); // Hide errors from users
```

## Troubleshooting

### Database Connection Error

- Verify MySQL service is running in XAMPP
- Check database credentials in `config/database.php`
- Ensure database `kopugive` exists

### File Upload Issues

- Check `uploads/` directory exists and has write permissions
- Verify `MAX_FILE_SIZE` in `config/config.php`
- Check PHP upload settings in `php.ini`:
  ```ini
  upload_max_filesize = 5M
  post_max_size = 5M
  ```

### White Screen / No Output

- Check Apache error log: `C:\xampp\apache\logs\error.log`
- Check PHP error log: `logs/php_errors.log`
- Enable error display temporarily in `config/config.php`

### Permission Denied Errors

- Ensure web server has write access to `uploads/` and `logs/`
- On Windows, check folder Security properties
- On Linux/Mac: `sudo chmod -R 755 uploads/ logs/`

## Security Recommendations

### For Production Deployment

1. **Change Default Passwords**
   - Update admin password immediately
   - Use strong passwords (min 12 characters)

2. **Update Configuration**
   - Set `display_errors = 0`
   - Generate new session encryption keys
   - Enable HTTPS/SSL

3. **Database Security**
   - Create dedicated MySQL user (not root)
   - Use strong database password
   - Grant only necessary permissions

4. **File Permissions**
   - Set strict file permissions (644 for files, 755 for directories)
   - Prevent directory listing
   - Protect sensitive files

5. **Backup Strategy**
   - Regular database backups
   - Backup uploaded files
   - Store backups securely off-site

## Support

For issues or questions:
- Check the documentation
- Review error logs
- Contact MUAFAKAT committee

## License

This project is developed for MRSM Kota Putra internal use.
© 2025 MRSM Kota Putra. All rights reserved.

