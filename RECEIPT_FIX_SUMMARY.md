# Receipt Upload Path Fix - Summary

## Problem Identified

The receipt file `68f08f75dd105_1760595829.jpg` was not accessible at the URL:
```
http://localhost/kopugive/uploads/receipts/68f08f75dd105_1760595829.jpg
```

### Root Cause

The `uploadFile()` function in `includes/functions.php` was using relative paths, which caused files to be uploaded to different locations depending on where the function was called from:

- When called from `donor/campaign_view.php`, receipts were saved to `donor/uploads/receipts/`
- When called from `admin/campaign_add.php`, campaigns were saved with `../uploads/campaigns/` path
- This inconsistency caused receipts to be stored in the wrong directory

## Solution Implemented

### 1. Fixed the `uploadFile()` Function

**File:** `includes/functions.php`

**Changes:**
- Modified the function to convert relative paths to absolute paths from the project root
- Added `$projectRoot = dirname(__DIR__) . '/';` to get the project root directory
- Files are now always uploaded to the correct location regardless of where the function is called from
- The function still returns relative paths for database storage (e.g., `uploads/receipts/filename.jpg`)

### 2. Standardized Upload Paths

**Files Updated:**
- `admin/campaign_add.php` - Changed from `../uploads/campaigns/` to `uploads/campaigns/`
- `admin/campaign_edit.php` - Updated file deletion logic to use absolute paths
- `donor/campaign_view.php` - Already using correct path `uploads/receipts/`

### 3. Moved Existing Receipt File

**Action Taken:**
- Copied the receipt file from `donor/uploads/receipts/68f08f75dd105_1760595829.jpg`
- To the correct location: `uploads/receipts/68f08f75dd105_1760595829.jpg`
- The receipt is now accessible at the correct URL

### 4. Created Migration Script

**File:** `fix_receipt_paths.php`

This script:
- Scans the database for donations with receipt paths
- Updates any paths starting with `donor/uploads/receipts/` to `uploads/receipts/`
- Provides a summary of changes made

**Usage:**
1. Navigate to: `http://localhost/kopugive/fix_receipt_paths.php`
2. Review the changes
3. Delete the file after use for security

### 5. Created Test Page

**File:** `test_receipt.php`

This page helps verify:
- If receipt files exist on the server
- If they are accessible via web browser
- Lists all receipt files in the uploads directory

**Usage:**
1. Navigate to: `http://localhost/kopugive/test_receipt.php`
2. Check if receipts are accessible
3. Delete the file after testing

## Directory Structure

The correct directory structure for uploads is:

```
kopugive/
├── uploads/
│   ├── campaigns/          # Campaign banner images
│   │   └── *.jpg
│   └── receipts/           # Donation receipts
│       └── *.jpg
├── admin/
├── donor/
└── ...
```

**Note:** The `donor/uploads/` directory should NOT be used and can be removed.

## Testing the Fix

1. **Test Receipt Access:**
   - Visit: `http://localhost/kopugive/test_receipt.php`
   - Verify the receipt image loads correctly

2. **Test New Uploads:**
   - Make a new donation with a receipt upload
   - Verify the receipt is saved to `uploads/receipts/`
   - Verify the receipt is accessible from the donations page

3. **Fix Existing Data:**
   - Visit: `http://localhost/kopugive/fix_receipt_paths.php`
   - This will update any existing database entries with incorrect paths

## Files Modified

1. `includes/functions.php` - Fixed uploadFile() function
2. `admin/campaign_add.php` - Standardized upload path
3. `admin/campaign_edit.php` - Fixed file deletion and upload path
4. `uploads/receipts/68f08f75dd105_1760595829.jpg` - Copied to correct location

## Files Created (Temporary - Delete After Use)

1. `fix_receipt_paths.php` - Database migration script
2. `test_receipt.php` - Testing page
3. `RECEIPT_FIX_SUMMARY.md` - This documentation

## Security Notes

**Important:** After verifying the fix works correctly, delete these temporary files:
- `fix_receipt_paths.php`
- `test_receipt.php`
- `fix_password.php` (if still present)

These files should not be left on a production server as they could expose sensitive information or allow unauthorized access.

## Prevention

The fix ensures that:
- All future uploads will go to the correct directory
- The upload path is consistent regardless of where the function is called from
- Relative paths in the database remain consistent for proper display in admin and donor sections

## Verification Checklist

- [x] Receipt file copied to correct location
- [x] `uploadFile()` function fixed to use absolute paths
- [x] All upload calls standardized to use `uploads/` prefix
- [x] Migration script created for existing data
- [x] Test page created for verification
- [ ] Run `fix_receipt_paths.php` to update database
- [ ] Test new receipt uploads
- [ ] Delete temporary files after verification

## Support

If you encounter any issues:
1. Check that the `uploads/receipts/` directory exists and has write permissions
2. Verify Apache is running in XAMPP
3. Check `logs/php_errors.log` for any error messages
4. Ensure the database connection is working properly

