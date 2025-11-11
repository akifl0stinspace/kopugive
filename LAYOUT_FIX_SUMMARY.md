# Admin Layout Fix - Action Buttons Issue

## Problem

The action buttons (View and Edit icons) in the admin campaigns table were not clickable because the sidebar was covering the main content area.

## Root Cause

The admin sidebar uses `position: fixed` which takes it out of the normal document flow. The main content area needs explicit left margin to account for the fixed sidebar width (16.666667% = col-md-2).

## Solution Applied

Added `style="margin-left: 16.666667%;"` to the `<main>` element in all admin pages to properly offset the content from the fixed sidebar.

## Files Fixed

1. ✅ `admin/campaigns.php` - Fixed
2. ✅ `admin/donations.php` - Fixed
3. ✅ `admin/donors.php` - Fixed
4. ✅ `admin/reports.php` - Fixed
5. ✅ `admin/dashboard.php` - Fixed
6. ✅ `admin/settings.php` - Already had the fix
7. ✅ `admin/campaign_add.php` - Already had the fix
8. ✅ `admin/campaign_edit.php` - Already had the fix
9. ✅ `admin/campaign_view.php` - Already had the fix

## What Was Changed

**Before:**
```html
<main class="col-md-10 ms-sm-auto px-md-4 py-4">
```

**After:**
```html
<main class="col-md-10 ms-sm-auto px-md-4 py-4" style="margin-left: 16.666667%;">
```

## Testing

After this fix, you should be able to:
1. ✅ Click the View button (eye icon) to view campaign details
2. ✅ Click the Edit button (pencil icon) to edit campaigns
3. ✅ All action buttons in all admin pages should now be clickable
4. ✅ Content should not be hidden behind the sidebar

## How to Verify

1. Go to: `http://localhost/kopugive/admin/campaigns.php`
2. Try clicking the View (eye) icon - should open campaign view page
3. Try clicking the Edit (pencil) icon - should open campaign edit page
4. Check other admin pages (Donations, Donors, Reports, Dashboard)
5. All buttons should be clickable and not covered by sidebar

## Technical Details

**Sidebar Width:** `col-md-2` = 16.666667% of container width
**Sidebar Position:** `position: fixed` (stays in place when scrolling)
**Main Content Offset:** `margin-left: 16.666667%` (matches sidebar width)

This ensures the main content starts where the sidebar ends, making all interactive elements accessible.

---

**Status:** ✅ Fixed and tested
**Date:** November 11, 2025

