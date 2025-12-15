# 📦 Install Composer First - Then Continue Stripe Setup

## You're here because Composer is not installed yet!

**Don't worry - it's quick and easy!** ⚡

---

## 🎯 What is Composer?

Composer is a **dependency manager for PHP**. It's like npm for Node.js or pip for Python.

We need it to install the **Stripe PHP SDK** (the library that handles payments).

---

## 🚀 Quick Install (5 Minutes)

### **Step 1: Download Composer**

**Direct Download Link:**
👉 https://getcomposer.org/Composer-Setup.exe

Or visit: https://getcomposer.org/download/

---

### **Step 2: Run the Installer**

1. Double-click `Composer-Setup.exe`
2. Click **"Next"**
3. It will find your PHP (from XAMPP automatically)
4. Click **"Install"**
5. Click **"Finish"**

**That's it!** ✅

---

### **Step 3: Verify Installation**

1. Open a **NEW** Command Prompt (important!)
   - Press `Win + R`
   - Type: `cmd`
   - Press Enter

2. Type:
   ```bash
   composer --version
   ```

3. You should see:
   ```
   Composer version 2.x.x
   ```

✅ **Success!** Composer is installed!

---

### **Step 4: Install Stripe SDK**

Now install the Stripe library:

```bash
cd C:\xampp\htdocs\kopugive
composer install
```

You'll see:
```
Loading composer repositories with package information
Installing dependencies from lock file
...
```

Wait ~30 seconds... Done! ✅

---

## 🎉 Now Continue Stripe Setup!

Go back to: **`STRIPE_SETUP_NOW.txt`** (Step 3)

Or visit: http://localhost/kopugive/install_stripe.php

---

## 🐛 Troubleshooting

### **Issue: "composer: command not found"**

**Solution:**
1. Close Command Prompt completely
2. Open a **NEW** Command Prompt
3. Try again: `composer --version`

(Composer needs a fresh terminal to be recognized)

---

### **Issue: "PHP not found during installation"**

**Solution:**
1. Make sure XAMPP is installed
2. During Composer setup, manually browse to:
   - `C:\xampp\php\php.exe`
3. Continue installation

---

### **Issue: Installer won't run**

**Solution: Manual Installation**

1. Download: https://getcomposer.org/composer.phar
2. Save to: `C:\xampp\htdocs\kopugive\`
3. Use it directly:
   ```bash
   cd C:\xampp\htdocs\kopugive
   php composer.phar install
   ```

---

## 📹 Video Tutorial (Optional)

If you prefer video instructions:
- Search YouTube: "Install Composer on Windows"
- Official guide: https://getcomposer.org/doc/00-intro.md#installation-windows

---

## ✅ Quick Checklist

- [ ] Downloaded Composer-Setup.exe
- [ ] Ran the installer
- [ ] Closed and reopened Command Prompt
- [ ] Verified: `composer --version` works
- [ ] Ran: `composer install` in kopugive folder
- [ ] Saw "vendor/" folder created
- [ ] Ready to continue Stripe setup!

---

## 🎯 What Happens When You Run `composer install`?

1. Composer reads `composer.json` (already created for you)
2. Downloads Stripe PHP SDK from the internet
3. Creates a `vendor/` folder
4. Installs all dependencies
5. Creates `vendor/autoload.php` (loads Stripe automatically)

**Time:** ~30 seconds  
**Internet:** Required (downloads ~2MB)  
**Result:** Stripe SDK ready to use! ✅

---

## 🚀 After Installation

Your folder will look like this:

```
kopugive/
├── vendor/           ← NEW! (Created by Composer)
│   ├── stripe/
│   │   └── stripe-php/
│   └── autoload.php
├── composer.json     ← Already exists
├── composer.lock     ← NEW! (Created by Composer)
└── ... other files
```

---

## 💡 Pro Tips

1. **Always use a NEW terminal** after installing Composer
2. **Run composer install** from the project root (kopugive folder)
3. **Don't delete vendor/** folder - Stripe needs it!
4. **Internet required** for first install (downloads libraries)

---

## 🎓 Alternative: Use PHP Directly

If Composer installation fails completely, you can use it without installing:

1. Download: https://getcomposer.org/composer.phar
2. Save to your project: `C:\xampp\htdocs\kopugive\`
3. Use it:
   ```bash
   cd C:\xampp\htdocs\kopugive
   php composer.phar install
   ```

This works exactly the same way!

---

## 🎉 Ready to Continue?

Once `composer install` completes successfully:

1. ✅ You'll see a `vendor/` folder
2. ✅ Stripe PHP SDK is installed
3. ✅ Continue with Stripe setup!

**Next step:** Go to `STRIPE_SETUP_NOW.txt` (Step 3)

Or visit: http://localhost/kopugive/install_stripe.php

---

## 📞 Still Stuck?

**Common issue:** Command Prompt not recognizing composer

**Solution:**
1. Close ALL Command Prompt windows
2. Open a brand new one
3. Try: `composer --version`

**If still not working:**
- Use manual method (php composer.phar)
- Or search: "Install Composer Windows XAMPP"

---

## 🎊 You're Almost There!

Composer installation is the **only prerequisite** for Stripe integration.

Once it's installed, the rest is super easy!

**Time remaining:** ~5 minutes after Composer is installed

**Let's go!** 🚀

---

**Need help?** Check: https://getcomposer.org/doc/00-intro.md

**Ready to continue?** Go to: `STRIPE_SETUP_NOW.txt`

---

*This is a one-time setup. You won't need to install Composer again!*

