# 🎉 Stripe Payment Integration Guide
## KopuGive - Professional Payment Gateway

---

## 📋 Table of Contents
1. [Overview](#overview)
2. [Quick Setup (15 minutes)](#quick-setup)
3. [Stripe Account Setup](#stripe-account-setup)
4. [Installation Steps](#installation-steps)
5. [Configuration](#configuration)
6. [Testing](#testing)
7. [Going Live](#going-live)
8. [Troubleshooting](#troubleshooting)

---

## 🎯 Overview

KopuGive now integrates with **Stripe** - a world-class payment processor that supports:
- ✅ **FPX** (Malaysian Online Banking)
- ✅ **Credit/Debit Cards** (Visa, Mastercard, etc.)
- ✅ **GrabPay** (E-Wallet)
- ✅ **Automatic payment verification**
- ✅ **Secure PCI-compliant processing**
- ✅ **Real-time webhook notifications**

**No more manual receipt verification for online payments!**

---

## ⚡ Quick Setup (15 minutes)

### Step 1: Install Stripe PHP SDK

Open terminal/command prompt in your project folder:

```bash
cd C:\xampp\htdocs\kopugive
composer install
```

If you don't have Composer installed:
1. Download from: https://getcomposer.org/download/
2. Install Composer
3. Run the command above

### Step 2: Create Stripe Account

1. Go to: https://dashboard.stripe.com/register
2. Sign up with your email
3. Complete the registration (you can skip business details for testing)
4. You'll be in **Test Mode** by default (perfect for development!)

### Step 3: Get Your API Keys

1. In Stripe Dashboard, go to: **Developers → API Keys**
2. You'll see:
   - **Publishable key** (starts with `pk_test_`)
   - **Secret key** (starts with `sk_test_`) - Click "Reveal test key"

### Step 4: Update Configuration

Edit `config/config.php` and replace these lines:

```php
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_YOUR_KEY_HERE');
define('STRIPE_SECRET_KEY', 'sk_test_YOUR_KEY_HERE');
```

**Example:**
```php
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_51Abc123xyz...');
define('STRIPE_SECRET_KEY', 'sk_test_51Abc123xyz...');
```

### Step 5: Run Database Migration

Run this SQL in phpMyAdmin:

```sql
USE kopugive;
SOURCE database/migrations/004_add_stripe_fields.sql;
```

Or manually execute:

```sql
ALTER TABLE donations 
ADD COLUMN stripe_payment_intent_id VARCHAR(255) NULL AFTER transaction_id,
ADD COLUMN stripe_checkout_session_id VARCHAR(255) NULL AFTER stripe_payment_intent_id,
ADD COLUMN payment_status VARCHAR(50) DEFAULT 'pending' AFTER status,
ADD INDEX idx_stripe_payment_intent (stripe_payment_intent_id),
ADD INDEX idx_stripe_checkout_session (stripe_checkout_session_id);

UPDATE donations SET payment_status = 'pending' WHERE payment_status IS NULL;
```

### Step 6: Test It!

1. Go to your KopuGive site
2. Select a campaign
3. Make a donation
4. Choose "Online Banking" or "Credit/Debit Card"
5. You'll be redirected to Stripe Checkout
6. Use test card: `4242 4242 4242 4242`
   - Expiry: Any future date (e.g., 12/25)
   - CVC: Any 3 digits (e.g., 123)
   - ZIP: Any 5 digits (e.g., 12345)

**Done! Your payment should be processed automatically!** ✅

---

## 🔐 Stripe Account Setup (Detailed)

### Creating Your Stripe Account

1. **Visit Stripe**: https://stripe.com
2. **Click "Start now"** or "Sign up"
3. **Enter your details**:
   - Email address
   - Full name
   - Password
   - Country: **Malaysia**

4. **Verify your email**
5. **Complete business profile** (can skip for testing):
   - Business name: MRSM Kota Putra / KopuGive
   - Business type: Non-profit
   - Industry: Education

### Understanding Test Mode vs Live Mode

**Test Mode** (Default):
- Use test API keys (start with `pk_test_` and `sk_test_`)
- No real money is processed
- Use test card numbers
- Perfect for development

**Live Mode** (Production):
- Use live API keys (start with `pk_live_` and `sk_live_`)
- Real money is processed
- Requires business verification
- Only activate when ready to accept real donations

---

## 💻 Installation Steps

### Prerequisites

- ✅ PHP 7.4 or higher
- ✅ MySQL database
- ✅ Composer (PHP package manager)
- ✅ XAMPP/WAMP running

### 1. Install Composer (if not installed)

**Windows:**
1. Download: https://getcomposer.org/Composer-Setup.exe
2. Run installer
3. Follow the wizard
4. Restart your terminal/command prompt

**Verify installation:**
```bash
composer --version
```

### 2. Install Stripe PHP SDK

```bash
cd C:\xampp\htdocs\kopugive
composer install
```

This will:
- Create `vendor/` folder
- Install Stripe PHP library
- Set up autoloading

### 3. Verify Installation

Check that these files exist:
- ✅ `vendor/autoload.php`
- ✅ `vendor/stripe/stripe-php/`
- ✅ `composer.json`
- ✅ `composer.lock`

---

## ⚙️ Configuration

### 1. Basic Configuration

Edit `config/config.php`:

```php
// Payment Gateway Settings (Stripe)
define('PAYMENT_MODE', 'test'); // test or live
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_YOUR_PUBLISHABLE_KEY_HERE');
define('STRIPE_SECRET_KEY', 'sk_test_YOUR_SECRET_KEY_HERE');
define('STRIPE_WEBHOOK_SECRET', 'whsec_YOUR_WEBHOOK_SECRET_HERE');
define('STRIPE_CURRENCY', 'myr'); // Malaysian Ringgit
```

### 2. Get Your API Keys

**From Stripe Dashboard:**

1. Go to: https://dashboard.stripe.com/test/apikeys
2. Copy **Publishable key**: `pk_test_...`
3. Click "Reveal test key" and copy **Secret key**: `sk_test_...`

### 3. Setup Webhooks (Optional but Recommended)

Webhooks ensure payments are verified even if users close their browser.

**For Local Development:**

1. Install Stripe CLI: https://stripe.com/docs/stripe-cli
2. Login: `stripe login`
3. Forward webhooks:
   ```bash
   stripe listen --forward-to http://localhost/kopugive/payment/stripe_webhook.php
   ```
4. Copy the webhook secret (starts with `whsec_`)
5. Add to `config/config.php`

**For Production:**

1. In Stripe Dashboard, go to: **Developers → Webhooks**
2. Click **"Add endpoint"**
3. Enter URL: `https://yourdomain.com/kopugive/payment/stripe_webhook.php`
4. Select events to listen for:
   - `checkout.session.completed`
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`
   - `charge.refunded`
5. Copy the **Signing secret** (starts with `whsec_`)
6. Add to `config/config.php`

### 4. Database Migration

Run the migration to add Stripe fields:

**Option A: phpMyAdmin**
1. Open phpMyAdmin
2. Select `kopugive` database
3. Click "SQL" tab
4. Paste contents of `database/migrations/004_add_stripe_fields.sql`
5. Click "Go"

**Option B: MySQL Command Line**
```bash
mysql -u root -p kopugive < database/migrations/004_add_stripe_fields.sql
```

---

## 🧪 Testing

### Test Card Numbers

Stripe provides test cards for different scenarios:

| Card Number | Scenario |
|-------------|----------|
| `4242 4242 4242 4242` | ✅ Successful payment |
| `4000 0025 0000 3155` | ✅ Requires authentication (3D Secure) |
| `4000 0000 0000 9995` | ❌ Declined - insufficient funds |
| `4000 0000 0000 0002` | ❌ Declined - generic decline |

**For all test cards:**
- Expiry: Any future date (e.g., 12/25)
- CVC: Any 3 digits (e.g., 123)
- ZIP: Any 5 digits (e.g., 12345)

### Testing FPX (Malaysian Online Banking)

1. Select "Online Banking (FPX)" as payment method
2. In Stripe Checkout, choose any test bank
3. Click "Authorize test payment"
4. Payment will be processed automatically

### Testing GrabPay

1. Select "E-Wallet (GrabPay)" as payment method
2. In Stripe Checkout, click "Authorize test payment"
3. Payment will be processed automatically

### Test Flow

1. **Create a donation:**
   - Go to any campaign
   - Click "Donate Now"
   - Enter amount (e.g., RM 50.00)
   - Select payment method: "Online Banking" or "Credit/Debit Card"
   - Click "Donate Now"

2. **You'll be redirected to Stripe Checkout:**
   - Professional, secure payment page
   - Stripe branding (can be customized)
   - Multiple payment options

3. **Complete payment:**
   - Use test card: `4242 4242 4242 4242`
   - Enter any future expiry date
   - Enter any CVC
   - Click "Pay"

4. **Verify success:**
   - You'll be redirected back to KopuGive
   - Donation status: **Verified** (automatic!)
   - Campaign total updated immediately
   - Check "My Donations" page

### What Gets Updated Automatically

✅ Donation status → `verified`
✅ Payment status → `paid`
✅ Transaction ID → Generated
✅ Campaign total → Increased
✅ Stripe Payment Intent ID → Saved
✅ Activity log → Created

---

## 🚀 Going Live

### Before Going Live Checklist

- [ ] Test all payment methods thoroughly
- [ ] Verify webhook is working
- [ ] Complete Stripe business verification
- [ ] Add bank account for payouts
- [ ] Update terms of service
- [ ] Test refund process
- [ ] Set up email notifications

### Activating Live Mode

1. **Complete Stripe Verification:**
   - In Stripe Dashboard, click "Activate account"
   - Provide business details
   - Submit required documents
   - Wait for approval (usually 1-2 business days)

2. **Get Live API Keys:**
   - Go to: **Developers → API Keys**
   - Switch to **Live mode** (toggle in top-right)
   - Copy your live keys (`pk_live_...` and `sk_live_...`)

3. **Update Configuration:**

Edit `config/config.php`:

```php
define('PAYMENT_MODE', 'live'); // Changed from 'test'
define('STRIPE_PUBLISHABLE_KEY', 'pk_live_YOUR_LIVE_KEY');
define('STRIPE_SECRET_KEY', 'sk_live_YOUR_LIVE_KEY');
define('STRIPE_WEBHOOK_SECRET', 'whsec_YOUR_LIVE_WEBHOOK_SECRET');
```

4. **Setup Live Webhook:**
   - In Stripe Dashboard (Live mode)
   - Go to: **Developers → Webhooks**
   - Add endpoint: `https://yourdomain.com/kopugive/payment/stripe_webhook.php`
   - Select same events as test mode
   - Copy signing secret

5. **Test with Real Card:**
   - Make a small test donation (RM 1.00)
   - Use your real card
   - Verify everything works
   - Refund the test payment

### Important Security Notes

⚠️ **Never commit API keys to Git/GitHub**
⚠️ **Use environment variables for production**
⚠️ **Enable HTTPS (SSL certificate) for live mode**
⚠️ **Keep Stripe PHP SDK updated**
⚠️ **Monitor Stripe Dashboard regularly**

---

## 🐛 Troubleshooting

### Issue: "Composer not found"

**Solution:**
1. Install Composer: https://getcomposer.org/download/
2. Restart terminal
3. Verify: `composer --version`

### Issue: "Stripe API key not found"

**Solution:**
1. Check `config/config.php` has correct keys
2. Keys should start with `pk_test_` and `sk_test_`
3. No spaces or quotes issues
4. Keys are from correct mode (test vs live)

### Issue: "Class 'Stripe\Stripe' not found"

**Solution:**
1. Run: `composer install`
2. Check `vendor/` folder exists
3. Verify `require_once '../vendor/autoload.php';` in payment files

### Issue: Payment succeeds but donation not verified

**Solution:**
1. Check webhook is configured
2. Verify webhook secret in config
3. Check webhook logs in Stripe Dashboard
4. Test webhook endpoint: `payment/stripe_webhook.php`
5. Check PHP error logs: `logs/php_errors.log`

### Issue: "Invalid API key"

**Solution:**
1. Verify you copied the full key
2. Check you're using correct mode (test vs live)
3. Regenerate keys in Stripe Dashboard if needed

### Issue: FPX not showing up

**Solution:**
1. FPX only shows for Malaysian Ringgit (MYR)
2. Check `STRIPE_CURRENCY` is set to `'myr'`
3. Stripe account must be in Malaysia region

### Issue: Webhook not receiving events

**Local Development:**
1. Use Stripe CLI: `stripe listen --forward-to http://localhost/kopugive/payment/stripe_webhook.php`
2. Check firewall settings

**Production:**
1. Verify webhook URL is publicly accessible
2. Check webhook signing secret is correct
3. Review webhook logs in Stripe Dashboard
4. Ensure endpoint returns 200 status

---

## 📊 Payment Flow Diagram

```
User selects campaign
        ↓
Fills donation form
        ↓
Clicks "Donate Now"
        ↓
Donation record created (status: pending)
        ↓
Redirected to Stripe Checkout
        ↓
User completes payment
        ↓
Stripe processes payment
        ↓
User redirected back to KopuGive
        ↓
Payment verified (status: verified)
        ↓
Campaign total updated
        ↓
Confirmation shown to user
```

---

## 💰 Stripe Fees

**Malaysia Pricing:**
- Domestic cards: **2.0% + RM 0.50** per transaction
- International cards: **3.4% + RM 0.50** per transaction
- FPX: **2.0% + RM 0.50** per transaction
- GrabPay: **2.8% + RM 0.50** per transaction

**Example:**
- Donation: RM 100.00
- Stripe fee: RM 2.50 (2.0% + RM 0.50)
- You receive: RM 97.50

**No monthly fees, no setup fees, no hidden costs!**

---

## 🎓 Additional Resources

- **Stripe Documentation**: https://stripe.com/docs
- **Stripe Dashboard**: https://dashboard.stripe.com
- **Test Cards**: https://stripe.com/docs/testing
- **Stripe Status**: https://status.stripe.com
- **Support**: https://support.stripe.com

---

## 🔒 Security Best Practices

1. **Never expose Secret Key**: Keep `sk_test_` and `sk_live_` keys private
2. **Use HTTPS**: Required for live mode
3. **Validate webhooks**: Always verify webhook signatures
4. **Keep SDK updated**: Run `composer update` regularly
5. **Monitor transactions**: Check Stripe Dashboard daily
6. **Enable 2FA**: Secure your Stripe account
7. **Set up alerts**: Get notified of failed payments

---

## ✅ Success Checklist

After setup, verify:

- [ ] Composer installed
- [ ] Stripe PHP SDK installed (`vendor/` folder exists)
- [ ] API keys configured in `config/config.php`
- [ ] Database migration completed
- [ ] Test donation successful
- [ ] Payment auto-verified
- [ ] Campaign total updated
- [ ] Webhook configured (optional but recommended)
- [ ] Error logging working

---

## 🎉 You're All Set!

Your KopuGive platform now has professional payment processing!

**What's different:**
- ✅ No more manual receipt verification for online payments
- ✅ Instant donation confirmation
- ✅ Secure, PCI-compliant processing
- ✅ Support for Malaysian payment methods (FPX, GrabPay)
- ✅ Professional checkout experience
- ✅ Automatic fraud detection

**Need help?** Check the troubleshooting section or contact Stripe support.

---

**Happy fundraising! 🎊**

