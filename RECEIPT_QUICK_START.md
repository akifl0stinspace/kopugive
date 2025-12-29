# 🚀 Receipt Generation - Quick Start Guide

## ⚡ 5-Minute Setup

### Step 1: Install Libraries (Already Done! ✓)

```bash
composer update
```

Libraries installed:
- ✅ TCPDF (PDF generation)
- ✅ PHPMailer (Email sending)

---

### Step 2: Configure Email (REQUIRED)

Open `config/config.php` and update:

```php
// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');      // ← Change this
define('SMTP_PASSWORD', 'your-app-password-here');    // ← Change this
define('SMTP_FROM_EMAIL', 'noreply@kopugive.com');
define('SMTP_FROM_NAME', 'KopuGive MRSM Kota Putra');
```

#### Gmail App Password Setup:
1. Go to https://myaccount.google.com/security
2. Enable "2-Step Verification"
3. Go to "App passwords"
4. Create password for "Mail"
5. Copy the 16-character password
6. Paste into `SMTP_PASSWORD`

---

### Step 3: Test It!

Visit: `http://localhost/kopugive/test_receipt_generation.php`

You should see:
- ✓ Receipt Generation: PASS
- ✓ Email Notification: PASS
- ✓ Complete Process: PASS

---

### Step 4: Make a Test Donation

1. Go to any campaign
2. Select "Online Payment (Stripe)"
3. Use test card: `4242 4242 4242 4242`
4. Complete payment

**What happens:**
- ✅ Payment processed
- ✅ Receipt PDF generated automatically
- ✅ Email sent to donor with receipt
- ✅ Receipt available for download

---

## 🎯 What You Get

### Automatic Receipts
- PDF generated after every Stripe payment
- Professional maroon & gold design
- All donation details included

### Email Notifications
- Sent automatically to donor
- Receipt PDF attached
- Professional HTML template

### Easy Access
- Download button in "My Donations"
- Download button in admin panel
- Receipts saved permanently

---

## 📧 Email Not Working?

### Quick Fixes:

**Gmail Users:**
- Use App Password (not regular password)
- Enable 2-Factor Authentication first
- Check "Less secure apps" is OFF

**Other Providers:**
- Outlook: `smtp-mail.outlook.com` (Port 587)
- Yahoo: `smtp.mail.yahoo.com` (Port 587)

**Still Not Working?**
- Check firewall (Port 587 must be open)
- Verify credentials are correct
- Check error logs: `logs/php_errors.log`

---

## ✅ That's It!

Your receipt system is now live! 🎉

**Every Stripe payment will automatically:**
1. Generate a professional PDF receipt
2. Email it to the donor
3. Save it for future download

---

## 📚 Need More Info?

See `RECEIPT_GENERATION_COMPLETE.md` for:
- Detailed documentation
- Customization options
- Troubleshooting guide
- Advanced features

