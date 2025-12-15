# ⚡ Stripe Quick Start - 5 Minutes Setup

## Get Stripe working in 5 minutes!

### Step 1: Install Stripe (2 minutes)

Open Command Prompt/Terminal:

```bash
cd C:\xampp\htdocs\kopugive
composer install
```

### Step 2: Get Stripe Keys (2 minutes)

1. Go to: https://dashboard.stripe.com/register
2. Sign up (use your email)
3. Go to: **Developers → API Keys**
4. Copy both keys:
   - Publishable key: `pk_test_...`
   - Secret key: `sk_test_...` (click "Reveal")

### Step 3: Update Config (30 seconds)

Edit `config/config.php`, find these lines and paste your keys:

```php
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_YOUR_KEY_HERE');
define('STRIPE_SECRET_KEY', 'sk_test_YOUR_KEY_HERE');
```

### Step 4: Update Database (30 seconds)

Open phpMyAdmin, select `kopugive` database, run this SQL:

```sql
ALTER TABLE donations 
ADD COLUMN stripe_payment_intent_id VARCHAR(255) NULL AFTER transaction_id,
ADD COLUMN stripe_checkout_session_id VARCHAR(255) NULL AFTER stripe_payment_intent_id,
ADD COLUMN payment_status VARCHAR(50) DEFAULT 'pending' AFTER status;

UPDATE donations SET payment_status = 'pending' WHERE payment_status IS NULL;
```

### Step 5: Test! (1 minute)

1. Go to any campaign on your site
2. Make a donation
3. Use test card: **4242 4242 4242 4242**
4. Expiry: **12/25**, CVC: **123**
5. Click Pay

**Done! Your donation is auto-verified!** ✅

---

## Test Cards

| Card | Result |
|------|--------|
| 4242 4242 4242 4242 | ✅ Success |
| 4000 0000 0000 9995 | ❌ Declined |

All cards: Expiry: 12/25, CVC: 123

---

## Troubleshooting

**"Composer not found"**
→ Install from: https://getcomposer.org/download/

**"Class Stripe not found"**
→ Run: `composer install`

**"Invalid API key"**
→ Check you copied the full key from Stripe Dashboard

---

## Need More Help?

See full guide: `STRIPE_INTEGRATION_GUIDE.md`

---

**That's it! You now have professional payment processing! 🎉**

