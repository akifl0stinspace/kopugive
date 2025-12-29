<?php
/**
 * General Configuration
 * KopuGive - MRSM Kota Putra Donation System
 */

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 0); // Set to 1 for development, 0 for production
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Timezone
date_default_timezone_set('Asia/Kuala_Lumpur');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// Application Settings
define('APP_NAME', 'KopuGive');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/kopugive');
define('APP_TIMEZONE', 'Asia/Kuala_Lumpur');

// File Upload Settings
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf']);

// Payment Gateway Settings (Stripe)
define('PAYMENT_MODE', 'test'); // test or live
define('STRIPE_PUBLISHABLE_KEY', 'letak sini'); // Get from Stripe Dashboard
define('STRIPE_SECRET_KEY', 'letak sini'); // Get from Stripe Dashboard
define('STRIPE_WEBHOOK_SECRET', 'whsec_YOUR_WEBHOOK_SECRET_HERE'); // Get from Stripe Dashboard (optional for now)
define('STRIPE_CURRENCY', 'myr'); // Malaysian Ringgit

// Security Settings
define('PASSWORD_MIN_LENGTH', 8);
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// Pagination
define('ITEMS_PER_PAGE', 10);

// Currency
define('CURRENCY', 'MYR');
define('CURRENCY_SYMBOL', 'RM');

// Include database config
require_once __DIR__ . '/database.php';
