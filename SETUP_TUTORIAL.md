# KopuGive - Complete Setup Tutorial for Beginners

This guide will walk you through **every step** to set up the KopuGive donation system on your computer, even if you're new to web development.

---

## 📥 Part 1: Download and Install XAMPP

### What is XAMPP?
XAMPP is a free software package that includes:
- **Apache** - Web server (runs your PHP website)
- **MySQL** - Database server (stores your data)
- **PHP** - Programming language
- **phpMyAdmin** - Visual tool to manage MySQL databases

### Step 1: Download XAMPP

1. **Open your web browser** (Chrome, Firefox, Edge, etc.)

2. **Go to the XAMPP website:**
   ```
   https://www.apachefriends.org/
   ```

3. **Click the Download button for Windows**
   - The website will automatically detect your operating system
   - Download the latest version (PHP 8.x recommended)
   - File size: ~150-200 MB
   - Save the file (e.g., `xampp-windows-x64-8.2.12-0-VS16-installer.exe`)

4. **Wait for the download to complete**

### Step 2: Install XAMPP

1. **Locate the downloaded file**
   - Usually in your `Downloads` folder
   - Look for a file like `xampp-windows-x64-...installer.exe`

2. **Double-click the installer file**
   - If Windows asks "Do you want to allow this app to make changes?", click **Yes**

3. **Installation Wizard will open:**

   **Screen 1 - Warning about UAC (User Account Control)**
   - Just click **OK**

   **Screen 2 - Welcome Screen**
   - Click **Next**

   **Screen 3 - Select Components**
   - ✅ Make sure these are checked:
     - Apache
     - MySQL
     - PHP
     - phpMyAdmin
   - You can uncheck FileZilla, Mercury, Tomcat, Perl if you don't need them
   - Click **Next**

   **Screen 4 - Installation Folder**
   - Default: `C:\xampp`
   - **Leave it as default** (recommended)
   - Click **Next**

   **Screen 5 - Bitnami**
   - Uncheck "Learn more about Bitnami for XAMPP"
   - Click **Next**

   **Screen 6 - Ready to Install**
   - Click **Next**
   - Installation will begin (takes 2-5 minutes)

   **Screen 7 - Completing Setup**
   - Check "Do you want to start the Control Panel now?"
   - Click **Finish**

---

## 🚀 Part 2: Start XAMPP and Run MySQL

### Step 1: Open XAMPP Control Panel

1. **Find XAMPP Control Panel:**
   - **Method 1:** It should open automatically after installation
   - **Method 2:** Search for "XAMPP Control Panel" in Windows Start Menu
   - **Method 3:** Go to `C:\xampp\xampp-control.exe`

2. **The Control Panel shows several modules:**
   ```
   Apache    [Start] [Admin] [Config] [Logs]
   MySQL     [Start] [Admin] [Config] [Logs]
   FileZilla [Start] [Admin] [Config] [Logs]
   ...
   ```

### Step 2: Start Apache and MySQL

1. **Click the "Start" button next to Apache**
   - It will turn green and show "Running" on port 80, 443
   - If you see an error about port 80/443 already in use:
     - Another program (like Skype) might be using it
     - Click "Config" → "httpd.conf" → Find `Listen 80` → Change to `Listen 8080`
     - Save and restart Apache

2. **Click the "Start" button next to MySQL**
   - It will turn green and show "Running" on port 3306
   - If port 3306 is busy, you may need to stop other MySQL services

3. **Both should now be green/highlighted and say "Running"**

### What Just Happened?
- ✅ Apache is now running - Your computer is now a web server!
- ✅ MySQL is running - Your database server is ready!

---

## 💾 Part 3: Access and Use MySQL Database

### Step 1: Open phpMyAdmin

**phpMyAdmin** is a visual tool to manage your MySQL database (no command line needed!)

1. **Make sure Apache and MySQL are running** (green in XAMPP Control Panel)

2. **Open your web browser**

3. **Type this address in the address bar:**
   ```
   http://localhost/phpmyadmin
   ```
   OR if you changed Apache to port 8080:
   ```
   http://localhost:8080/phpmyadmin
   ```

4. **Press Enter**

5. **You should see the phpMyAdmin interface!**
   - Left sidebar: List of databases
   - Top: Navigation tabs
   - Center: Main workspace

### Step 2: Create the KopuGive Database

1. **In phpMyAdmin, click "New" in the left sidebar**
   - Or click the "Databases" tab at the top

2. **You'll see "Create database"**

3. **Enter the database name:**
   ```
   kopugive
   ```
   (Type exactly: k-o-p-u-g-i-v-e, all lowercase, no spaces)

4. **Choose Collation:**
   - Click the dropdown next to "Collation"
   - Scroll down and select: `utf8mb4_unicode_ci`
   - This allows special characters and emojis

5. **Click the "Create" button**

6. **Success!** You'll see "kopugive" appear in the left sidebar

---

## 📊 Part 4: Import Database Tables (Schema)

Now we need to create the tables inside the database.

### Step 1: Open the SQL Tab

1. **Click on "kopugive" database in the left sidebar**
   - It should be highlighted

2. **Click the "SQL" tab at the top**
   - You'll see a large text box where you can type SQL commands

### Step 2: Import Schema File

**Method 1: Copy-Paste SQL (Easiest)**

1. **Open the file:** `database/schema.sql` in Notepad or VS Code

2. **Select all the text** (Ctrl + A) and **Copy** (Ctrl + C)

3. **Go back to phpMyAdmin SQL tab**

4. **Click inside the big text box**

5. **Paste the SQL code** (Ctrl + V)

6. **Click "Go" button** (bottom right)

7. **Wait for it to execute** (may take 5-10 seconds)

8. **You should see a success message:**
   ```
   X rows affected.
   ```

**Method 2: Import SQL File**

1. **Click the "Import" tab** (instead of SQL)

2. **Click "Choose File" button**

3. **Navigate to your project folder:**
   ```
   C:\xampp\htdocs\kopugive\database\schema.sql
   ```

4. **Select `schema.sql` and click "Open"**

5. **Click "Go" at the bottom**

6. **Wait for import to complete**

### Step 3: Verify Tables Were Created

1. **Click on "kopugive" in the left sidebar**

2. **You should now see all the tables:**
   - activity_logs
   - campaign_updates
   - campaigns
   - donations
   - settings
   - users

3. **Click on any table** (e.g., "users") to see its structure

---

## 🎯 Part 5: Import Sample Data (Optional)

To test the system with sample campaigns and donors:

1. **Click the "SQL" tab** (while kopugive database is selected)

2. **Open the file:** `database/seed.sql`

3. **Copy all the content** (Ctrl + A, then Ctrl + C)

4. **Paste into the SQL tab** in phpMyAdmin

5. **Click "Go"**

6. **Success!** Now you have:
   - 1 Admin account
   - 3 Sample donors
   - 3 Sample campaigns
   - Several sample donations

---

## 🌐 Part 6: Set Up the KopuGive Project Files

### Step 1: Extract/Copy Project Files

1. **Locate your KopuGive project folder**
   - Example: If you downloaded it, it might be in `Downloads/kopugive/`

2. **Copy the entire `kopugive` folder**

3. **Paste it into XAMPP's `htdocs` folder:**
   ```
   C:\xampp\htdocs\
   ```

4. **Final location should be:**
   ```
   C:\xampp\htdocs\kopugive\
   ```

### Step 2: Create Upload Folders

1. **Open File Explorer**

2. **Navigate to:**
   ```
   C:\xampp\htdocs\kopugive\
   ```

3. **Create these folders if they don't exist:**
   - Right-click → New → Folder → Name it `uploads`
   - Inside `uploads`, create two folders:
     - `campaigns`
     - `receipts`
   - Create a folder called `logs`

4. **Your structure should look like:**
   ```
   kopugive/
   ├── admin/
   ├── auth/
   ├── config/
   ├── database/
   ├── donor/
   ├── includes/
   ├── payment/
   ├── uploads/
   │   ├── campaigns/
   │   └── receipts/
   ├── logs/
   └── index.php
   ```

---

## 🎉 Part 7: Access Your Website

### Step 1: Make Sure Services Are Running

1. **Open XAMPP Control Panel**

2. **Verify both Apache and MySQL are green/running**
   - If not, click "Start" for each

### Step 2: Open KopuGive in Browser

1. **Open your web browser**

2. **Type this in the address bar:**
   ```
   http://localhost/kopugive/
   ```

3. **Press Enter**

4. **You should see the KopuGive homepage!** 🎉

### Step 3: Test Login

**Try logging in as Admin:**

1. **Click "Login" in the navigation**

2. **Enter these credentials:**
   - Email: `admin@mrsmkp.edu.my`
   - Password: `admin123`

3. **Click "Sign In"**

4. **You should be redirected to the Admin Dashboard!**

---

## 📱 Common Pages to Visit

Once everything is set up:

### Public Pages
- **Homepage:** `http://localhost/kopugive/`
- **Login:** `http://localhost/kopugive/auth/login.php`
- **Register:** `http://localhost/kopugive/auth/register.php`

### Admin Pages (login as admin first)
- **Dashboard:** `http://localhost/kopugive/admin/dashboard.php`
- **Campaigns:** `http://localhost/kopugive/admin/campaigns.php`
- **Donations:** `http://localhost/kopugive/admin/donations.php`
- **Reports:** `http://localhost/kopugive/admin/reports.php`

### Donor Pages (login as donor)
- **Dashboard:** `http://localhost/kopugive/donor/dashboard.php`
- **Browse Campaigns:** `http://localhost/kopugive/donor/campaigns.php`
- **My Donations:** `http://localhost/kopugive/donor/my_donations.php`

---

## 🔧 How to Use MySQL Database (Basics)

### View Data in phpMyAdmin

1. **Open:** `http://localhost/phpmyadmin`

2. **Click "kopugive" database** (left sidebar)

3. **Click any table** (e.g., "users")

4. **Click "Browse" tab** to see all records

### Add Data Manually

1. **Select a table** (e.g., "campaigns")

2. **Click "Insert" tab**

3. **Fill in the form fields**

4. **Click "Go" to save**

### Edit Data

1. **Click "Browse" tab**

2. **Click the pencil icon** ✏️ next to any row

3. **Edit the values**

4. **Click "Go" to save changes**

### Delete Data

1. **Click "Browse" tab**

2. **Click the X icon** ❌ next to any row

3. **Confirm deletion**

### Run SQL Queries

1. **Click "SQL" tab**

2. **Type your SQL command**, for example:
   ```sql
   SELECT * FROM users WHERE role = 'admin';
   ```

3. **Click "Go"**

4. **See the results below**

### Export Database (Backup)

1. **Click "kopugive" database**

2. **Click "Export" tab**

3. **Leave default settings (Quick, SQL format)**

4. **Click "Go"**

5. **Save the .sql file** - This is your backup!

### Import Database (Restore)

1. **Click "kopugive" database**

2. **Click "Import" tab**

3. **Choose your backup .sql file**

4. **Click "Go"**

---

## ⚠️ Troubleshooting Common Issues

### Issue 1: Apache Won't Start (Port 80 Conflict)

**Problem:** "Port 80 in use by another program"

**Solution:**
1. Click "Config" button next to Apache
2. Click "httpd.conf"
3. Find the line: `Listen 80`
4. Change to: `Listen 8080`
5. Save and close
6. Start Apache
7. Access site using: `http://localhost:8080/kopugive/`

### Issue 2: MySQL Won't Start (Port 3306 Conflict)

**Problem:** "Port 3306 already in use"

**Solution:**
1. Open Windows Task Manager (Ctrl + Shift + Esc)
2. Look for "MySQL" or "mysqld" processes
3. End those processes
4. Try starting MySQL in XAMPP again

OR:

1. Click "Config" next to MySQL
2. Click "my.ini"
3. Find: `port=3306`
4. Change to: `port=3307`
5. Save and restart
6. Update `config/database.php` to use port 3307

### Issue 3: Can't Access phpMyAdmin

**Problem:** Page won't load

**Solutions:**
1. Make sure Apache is running (green)
2. Make sure MySQL is running (green)
3. Try: `http://localhost/phpmyadmin`
4. If Apache is on port 8080: `http://localhost:8080/phpmyadmin`

### Issue 4: "Access Denied" in phpMyAdmin

**Problem:** Can't login to phpMyAdmin

**Solution:**
1. Open `C:\xampp\phpMyAdmin\config.inc.php`
2. Find these lines:
   ```php
   $cfg['Servers'][$i]['user'] = 'root';
   $cfg['Servers'][$i]['password'] = '';
   ```
3. Password should be empty by default
4. Save and refresh phpMyAdmin

### Issue 5: Website Shows "Database Connection Failed"

**Problem:** KopuGive can't connect to database

**Solution:**
1. Make sure MySQL is running in XAMPP
2. Open `config/database.php`
3. Verify these settings:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'kopugive');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```
4. Make sure database "kopugive" exists in phpMyAdmin

### Issue 6: Upload Folder Errors

**Problem:** "Permission denied" or "Failed to upload"

**Solution:**
1. Right-click `uploads` folder
2. Properties → Security
3. Click "Edit"
4. Select "Users"
5. Check "Full Control"
6. Click OK

---

## 📚 Useful MySQL Commands for Beginners

### See all databases:
```sql
SHOW DATABASES;
```

### Select a database:
```sql
USE kopugive;
```

### See all tables:
```sql
SHOW TABLES;
```

### See table structure:
```sql
DESCRIBE users;
```

### Get all records from a table:
```sql
SELECT * FROM users;
```

### Get specific columns:
```sql
SELECT full_name, email FROM users;
```

### Filter results:
```sql
SELECT * FROM campaigns WHERE status = 'active';
```

### Count records:
```sql
SELECT COUNT(*) FROM donations;
```

### Get total donations:
```sql
SELECT SUM(amount) FROM donations WHERE status = 'verified';
```

---

## 🎓 Next Steps

Now that everything is set up:

1. ✅ **Explore the system:**
   - Login as admin
   - Create a new campaign
   - Login as a donor
   - Make a test donation

2. ✅ **Learn the code:**
   - Open files in VS Code
   - Read through `index.php`
   - Explore `includes/functions.php`

3. ✅ **Customize:**
   - Change colors in CSS
   - Add new features
   - Modify database schema

4. ✅ **Read the documentation:**
   - `TECH_STACK.md` - Technology details
   - `INSTALLATION.md` - Installation reference
   - `README.md` - Project overview

---

## 💡 Tips for Learning

### For Database (MySQL):
- Practice writing simple SELECT queries in phpMyAdmin
- Try creating your own test table
- Export and import databases for practice
- Learn about relationships (foreign keys)

### For PHP:
- Echo variables to see their values
- Use `var_dump()` for debugging
- Read PHP documentation: https://www.php.net/
- Learn about PHP data types, functions, and classes

### For Web Development:
- Learn HTML/CSS basics first
- Then JavaScript for interactivity
- Bootstrap documentation: https://getbootstrap.com/
- Practice with small projects

---

## 🆘 Need Help?

If you get stuck:

1. **Check error logs:**
   - `C:\xampp\apache\logs\error.log`
   - `C:\xampp\mysql\data\mysql_error.log`
   - `logs/php_errors.log` (in your project)

2. **Enable error display:**
   - Open `config/config.php`
   - Change `ini_set('display_errors', 0);` to `1`
   - Refresh your page to see errors

3. **Search online:**
   - Copy error message and Google it
   - Stack Overflow has many solutions
   - PHP.net has official documentation

4. **Ask for help:**
   - MUAFAKAT committee
   - Your project supervisor
   - Classmates or friends

---

## ✅ Checklist - Did you complete everything?

- [ ] Downloaded XAMPP
- [ ] Installed XAMPP
- [ ] Started Apache (green)
- [ ] Started MySQL (green)
- [ ] Opened phpMyAdmin
- [ ] Created "kopugive" database
- [ ] Imported schema.sql
- [ ] Imported seed.sql (optional)
- [ ] Copied project to htdocs/kopugive/
- [ ] Created uploads and logs folders
- [ ] Accessed http://localhost/kopugive/
- [ ] Logged in successfully
- [ ] Everything works!

---

**Congratulations!** 🎉 You've successfully set up KopuGive on your local computer!

---

## 📖 Additional Resources

### Video Tutorials (YouTube):
- "How to Install XAMPP"
- "PHP MySQL Tutorial for Beginners"
- "phpMyAdmin Tutorial"

### Websites:
- W3Schools: https://www.w3schools.com/php/
- PHP Manual: https://www.php.net/manual/en/
- MySQL Tutorial: https://www.mysqltutorial.org/

### Practice:
- Try creating a simple "Hello World" PHP page
- Practice SQL queries in phpMyAdmin
- Modify KopuGive colors and text

---

**Remember:** Everyone was a beginner once. Take it step by step, and don't be afraid to make mistakes - that's how you learn! 🚀

Good luck with your project! 💪

