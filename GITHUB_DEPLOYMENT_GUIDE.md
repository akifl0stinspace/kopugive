# GitHub Deployment Guide - KopuGive

## 📦 For You (Project Owner): Pushing to GitHub

### Step 1: Prepare Your Project

1. **Create a `.gitignore` file** (if not exists)

Create a file named `.gitignore` in your project root with this content:

```
# Ignore uploaded files (users will create their own)
uploads/campaigns/*
uploads/receipts/*
uploads/documents/*
!uploads/campaigns/.gitkeep
!uploads/receipts/.gitkeep
!uploads/documents/.gitkeep

# Ignore logs
logs/*.log
!logs/.gitkeep

# Ignore temporary files
*.tmp
*.bak
*~

# Ignore migration scripts (they'll run from database folder)
fix_*.php
add_*.php

# Ignore OS files
.DS_Store
Thumbs.db
desktop.ini

# Ignore IDE files
.vscode/
.idea/
*.swp
*.swo
```

2. **Create `.gitkeep` files** to preserve empty directories:

```bash
# In Git Bash or PowerShell (in your project directory)
New-Item -ItemType File -Path "uploads/campaigns/.gitkeep" -Force
New-Item -ItemType File -Path "uploads/receipts/.gitkeep" -Force
New-Item -ItemType File -Path "uploads/documents/.gitkeep" -Force
New-Item -ItemType File -Path "logs/.gitkeep" -Force
```

### Step 2: Create Proper Migration Files

Instead of temporary PHP files, create proper SQL migration files:

**Create:** `database/migrations/001_add_campaign_documents.sql`

```sql
-- Migration: Add campaign documents table
-- Date: 2025-11-11
-- Description: Adds support for uploading campaign documents

CREATE TABLE IF NOT EXISTS campaign_documents (
    document_id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    document_path VARCHAR(255) NOT NULL,
    document_type VARCHAR(50),
    file_size INT,
    description TEXT,
    uploaded_by INT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campaign_id) REFERENCES campaigns(campaign_id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_campaign (campaign_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Create:** `database/migrations/002_add_campaign_approval.sql`

```sql
-- Migration: Add campaign approval system
-- Date: 2025-11-11
-- Description: Adds approval workflow for campaigns

-- Update status enum to include new statuses
ALTER TABLE campaigns 
MODIFY COLUMN status ENUM('draft', 'pending_approval', 'active', 'completed', 'closed', 'rejected') 
DEFAULT 'draft';

-- Add approval tracking fields
ALTER TABLE campaigns 
ADD COLUMN IF NOT EXISTS approved_by INT NULL AFTER status,
ADD COLUMN IF NOT EXISTS approved_at TIMESTAMP NULL AFTER approved_by,
ADD COLUMN IF NOT EXISTS rejection_reason TEXT NULL AFTER approved_at;

-- Add foreign key for approver
ALTER TABLE campaigns 
ADD CONSTRAINT fk_campaigns_approved_by 
FOREIGN KEY (approved_by) REFERENCES users(user_id) ON DELETE SET NULL;
```

### Step 3: Create Setup Script for New Users

**Create:** `setup.php`

```php
<?php
/**
 * KopuGive Setup Script
 * Run this after cloning the repository
 */
require_once 'config/database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>KopuGive Setup</title>
    <style>
        body { font-family: Arial; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; padding: 15px; margin: 10px 0; border-radius: 5px; color: #155724; }
        .error { background: #f8d7da; padding: 15px; margin: 10px 0; border-radius: 5px; color: #721c24; }
        .info { background: #d1ecf1; padding: 15px; margin: 10px 0; border-radius: 5px; color: #0c5460; }
        h1 { color: #333; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>";

echo "<h1>🚀 KopuGive Setup</h1>";

try {
    $db = (new Database())->getConnection();
    echo "<div class='success'>✓ Database connection successful!</div>";
    
    // Check if migrations are needed
    $needsMigration = false;
    
    // Check for campaign_documents table
    $tables = $db->query("SHOW TABLES LIKE 'campaign_documents'")->fetchAll();
    if (empty($tables)) {
        echo "<div class='info'>⚠ campaign_documents table not found. Running migration...</div>";
        $sql = file_get_contents(__DIR__ . '/database/migrations/001_add_campaign_documents.sql');
        $db->exec($sql);
        echo "<div class='success'>✓ campaign_documents table created!</div>";
        $needsMigration = true;
    } else {
        echo "<div class='success'>✓ campaign_documents table exists</div>";
    }
    
    // Check for approval fields
    $columns = $db->query("SHOW COLUMNS FROM campaigns LIKE 'approved_by'")->fetchAll();
    if (empty($columns)) {
        echo "<div class='info'>⚠ Approval fields not found. Running migration...</div>";
        $sql = file_get_contents(__DIR__ . '/database/migrations/002_add_campaign_approval.sql');
        $db->exec($sql);
        echo "<div class='success'>✓ Approval system added!</div>";
        $needsMigration = true;
    } else {
        echo "<div class='success'>✓ Approval system exists</div>";
    }
    
    // Create upload directories
    $dirs = ['uploads/campaigns', 'uploads/receipts', 'uploads/documents', 'logs'];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            echo "<div class='success'>✓ Created directory: $dir</div>";
        } else {
            echo "<div class='success'>✓ Directory exists: $dir</div>";
        }
    }
    
    if (!$needsMigration) {
        echo "<div class='success'><h3>✓ Setup Complete!</h3><p>Your database is up to date. No migrations needed.</p></div>";
    } else {
        echo "<div class='success'><h3>✓ Setup Complete!</h3><p>All migrations have been applied successfully.</p></div>";
    }
    
    echo "<div class='info'>";
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li><a href='admin/dashboard.php'>Go to Admin Dashboard</a></li>";
    echo "<li><a href='index.php'>Go to Homepage</a></li>";
    echo "<li><strong>Delete this file (setup.php) for security!</strong></li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'><h3>✗ Error</h3><p>" . $e->getMessage() . "</p></div>";
    echo "<div class='info'>";
    echo "<h3>Troubleshooting:</h3>";
    echo "<ol>";
    echo "<li>Make sure MySQL is running in XAMPP</li>";
    echo "<li>Check database credentials in <code>config/database.php</code></li>";
    echo "<li>Ensure database 'kopugive' exists</li>";
    echo "<li>Run <code>database/schema.sql</code> first if database is empty</li>";
    echo "</ol>";
    echo "</div>";
}

echo "</body></html>";
?>
```

### Step 4: Update README with Setup Instructions

**Update:** `README.md` (add this section)

```markdown
## 🚀 Quick Setup for New Developers

### Prerequisites
- XAMPP (Apache + MySQL)
- Git
- Web browser

### Installation Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/YOUR_USERNAME/kopugive.git
   cd kopugive
   ```

2. **Move to XAMPP htdocs**
   ```bash
   # Windows
   Copy the kopugive folder to: C:\xampp\htdocs\
   ```

3. **Start XAMPP**
   - Start Apache
   - Start MySQL

4. **Create Database**
   - Open phpMyAdmin: http://localhost/phpmyadmin
   - Create database: `kopugive`
   - Import: `database/schema.sql`
   - (Optional) Import: `database/seed.sql` for sample data

5. **Run Setup Script**
   ```
   http://localhost/kopugive/setup.php
   ```
   This will:
   - Check database connection
   - Run any pending migrations
   - Create required directories
   - Verify everything is set up correctly

6. **Access the Application**
   - Homepage: http://localhost/kopugive/
   - Admin: http://localhost/kopugive/admin/dashboard.php
   - Default admin: admin@mrsmkp.edu.my / admin123

7. **Delete Setup File**
   - Delete `setup.php` after successful setup

### What's New in This Version

- ✅ Campaign document uploads (approval letters, budgets)
- ✅ Admin approval workflow for campaigns
- ✅ Approve/reject campaigns with reasons
- ✅ Document review interface
- ✅ Status tracking and audit trail

See `CAMPAIGN_APPROVAL_SYSTEM.md` for detailed documentation.
```

### Step 5: Initialize Git and Push to GitHub

```bash
# In PowerShell or Git Bash, in your project directory

# Initialize git (if not already done)
git init

# Add all files
git add .

# Commit
git commit -m "Add campaign approval system and document uploads"

# Create repository on GitHub first, then:
git remote add origin https://github.com/YOUR_USERNAME/kopugive.git

# Push to GitHub
git branch -M main
git push -u origin main
```

---

## 📥 For Other User: Cloning and Setup

### Step 1: Clone the Repository

```bash
# Open Git Bash or PowerShell
cd C:\xampp\htdocs

# Clone the repository
git clone https://github.com/YOUR_USERNAME/kopugive.git

# Navigate to project
cd kopugive
```

### Step 2: Start XAMPP

1. Open XAMPP Control Panel
2. Start Apache
3. Start MySQL

### Step 3: Create Database

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Click "New" to create database
3. Database name: `kopugive`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"

### Step 4: Import Base Schema

1. Select `kopugive` database
2. Click "Import" tab
3. Choose file: `database/schema.sql` from the cloned project
4. Click "Go"
5. Wait for success message

### Step 5: (Optional) Import Sample Data

1. Still in Import tab
2. Choose file: `database/seed.sql`
3. Click "Go"

### Step 6: Run Setup Script

Open browser and go to:
```
http://localhost/kopugive/setup.php
```

This will:
- ✅ Check database connection
- ✅ Create `campaign_documents` table
- ✅ Add approval system fields
- ✅ Create upload directories
- ✅ Verify everything is ready

### Step 7: Test the Application

1. **Homepage:** `http://localhost/kopugive/`
2. **Admin Login:** `http://localhost/kopugive/auth/login.php`
   - Email: `admin@mrsmkp.edu.my`
   - Password: `admin123`

### Step 8: Clean Up

Delete `setup.php` for security:
```bash
rm setup.php
```

---

## 🔄 Keeping Up to Date

### For Other User: Getting Latest Changes

```bash
# Navigate to project directory
cd C:\xampp\htdocs\kopugive

# Pull latest changes
git pull origin main

# Run setup script to apply any new migrations
# Open: http://localhost/kopugive/setup.php
```

### For You: Pushing New Changes

```bash
# Add changed files
git add .

# Commit with message
git commit -m "Description of changes"

# Push to GitHub
git push origin main
```

---

## 📋 Files to Include in GitHub

**Include:**
- ✅ All PHP files
- ✅ `database/schema.sql`
- ✅ `database/seed.sql`
- ✅ `database/migrations/*.sql`
- ✅ `setup.php`
- ✅ Documentation (*.md files)
- ✅ `.gitignore`
- ✅ Empty directories with `.gitkeep`

**Exclude (via .gitignore):**
- ❌ `uploads/` contents (except .gitkeep)
- ❌ `logs/*.log`
- ❌ Temporary migration files (`fix_*.php`, `add_*.php`)
- ❌ OS/IDE specific files

---

## 🆘 Troubleshooting

### "Database connection failed"
- Check MySQL is running in XAMPP
- Verify credentials in `config/database.php`
- Ensure database `kopugive` exists

### "Table doesn't exist"
- Run `setup.php` to apply migrations
- Or manually run SQL files from `database/migrations/`

### "Permission denied" on uploads
- Right-click `uploads` folder → Properties → Security
- Give Users write permissions

### Git push fails
- Make sure you created the repository on GitHub first
- Check you're using correct repository URL
- Verify you're logged into GitHub

---

## 📞 Support

If other user has issues:
1. Check they ran `setup.php`
2. Verify database was created
3. Check `schema.sql` was imported first
4. Look at `logs/php_errors.log`
5. Ensure XAMPP Apache and MySQL are running

---

**That's it! The other user can now clone and set up the project easily!** 🎉

