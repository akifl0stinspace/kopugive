# Receipt Upload Fix - Step-by-Step Instructions

## Problem Summary

The receipt at `http://localhost/kopugive/uploads/receipts/68f08f75dd105_1760595829.jpg` was not accessible because:

1. Receipts were being uploaded to `donor/uploads/receipts/` instead of `uploads/receipts/`
2. The `uploadFile()` function was using relative paths, causing inconsistent upload locations
3. The database had incorrect paths stored

## What Has Been Fixed

✅ **Fixed the `uploadFile()` function** to use absolute paths from project root
✅ **Moved the receipt file** to the correct location (`uploads/receipts/`)
✅ **Standardized all upload paths** across the codebase
✅ **Created helper scripts** to verify and fix the issue

## Step-by-Step Instructions

### Step 1: Verify the Fix

1. Open your web browser
2. Navigate to: `http://localhost/kopugive/check_database_receipts.php`
3. This will show you:
   - All donations with receipts
   - Whether the files exist
   - Which paths need to be fixed (if any)

### Step 2: Fix Database Paths (If Needed)

If Step 1 shows any receipts with incorrect paths (starting with `donor/uploads/`):

1. Navigate to: `http://localhost/kopugive/fix_receipt_paths.php`
2. Review the changes that will be made
3. The script will automatically update the database
4. You'll see a summary of what was fixed

### Step 3: Test Receipt Access

1. Navigate to: `http://localhost/kopugive/test_receipt.php`
2. Verify that:
   - The receipt image loads correctly
   - The direct link works
   - All receipt files are listed

### Step 4: Test the Original URL

Now try accessing the receipt directly:

```
http://localhost/kopugive/uploads/receipts/68f08f75dd105_1760595829.jpg
```

The image should now display correctly! 🎉

### Step 5: Test New Uploads

1. Log in as a donor
2. Go to a campaign page
3. Make a test donation with a receipt upload
4. Verify the receipt is saved to `uploads/receipts/`
5. Check that you can view the receipt from the donations page

### Step 6: Clean Up (Important!)

After verifying everything works, delete these temporary files for security:

```
- check_database_receipts.php
- fix_receipt_paths.php
- test_receipt.php
- fix_password.php (if it exists)
- FIX_INSTRUCTIONS.md (this file)
```

You can keep `RECEIPT_FIX_SUMMARY.md` for documentation purposes.

## Quick Test Commands

### Check if receipt file exists:
```
http://localhost/kopugive/uploads/receipts/68f08f75dd105_1760595829.jpg
```

### View all donations with receipts:
```
http://localhost/kopugive/admin/donations.php
```

### Check database paths:
```
http://localhost/kopugive/check_database_receipts.php
```

## Troubleshooting

### If the receipt still doesn't show:

1. **Check Apache is running** in XAMPP Control Panel
2. **Verify file permissions** - the `uploads` folder should be writable
3. **Check the file exists**:
   ```
   C:\xampp\htdocs\kopugive\uploads\receipts\68f08f75dd105_1760595829.jpg
   ```
4. **Clear browser cache** (Ctrl + F5)
5. **Check PHP error logs** at `logs/php_errors.log`

### If new uploads fail:

1. Make sure the `uploads/receipts/` directory exists
2. Check folder permissions (should be writable)
3. Verify the `uploadFile()` function changes were saved in `includes/functions.php`

## What Changed in the Code

### `includes/functions.php`
- Modified `uploadFile()` to use absolute paths
- Files now always upload to the correct location

### `admin/campaign_add.php`
- Changed upload path from `../uploads/campaigns/` to `uploads/campaigns/`

### `admin/campaign_edit.php`
- Updated file deletion to use absolute paths
- Standardized upload path

## Files You Can Delete After Testing

- ✅ `check_database_receipts.php`
- ✅ `fix_receipt_paths.php`
- ✅ `test_receipt.php`
- ✅ `fix_password.php`
- ✅ `FIX_INSTRUCTIONS.md`

## Need Help?

If you encounter any issues:

1. Check `logs/php_errors.log` for error messages
2. Run `check_database_receipts.php` to see the current state
3. Verify XAMPP Apache and MySQL are running
4. Make sure you're accessing via `http://localhost/kopugive/` (not file://)

---

**Note:** The fix has been implemented in the code. Just follow the steps above to verify everything works and clean up the temporary files.

