# Reports Page - Bug Fix

## Issue
The reports page was showing a blank white screen after the enhancement.

## Root Cause
**SQL Error**: `SQLSTATE[23000]: Integrity constraint violation: 1052 Column 'status' in field list is ambiguous`

### Explanation
Both the `donations` and `campaigns` tables have a `status` column. When performing a JOIN between these tables, the query needs to explicitly specify which table's `status` column to use.

## Error Details
```
Location: admin/reports.php line 86
Error: Column 'status' in field list is ambiguous
Query: SELECT COUNT(*) as total, status FROM donations d 
       LEFT JOIN campaigns c ON d.campaign_id = c.campaign_id 
       WHERE 1=1 GROUP BY status
```

## Solution

### Fix 1: Ambiguous Status Column
**Changed line 83-85:**
```php
// BEFORE (Ambiguous)
$query = "SELECT COUNT(*) as total, status FROM donations d 
          LEFT JOIN campaigns c ON d.campaign_id = c.campaign_id 
          WHERE 1=1" . $campaignCondition . $dateCondition . " GROUP BY status";

// AFTER (Fixed)
$query = "SELECT COUNT(*) as total, d.status FROM donations d 
          LEFT JOIN campaigns c ON d.campaign_id = c.campaign_id 
          WHERE 1=1" . $campaignCondition . $dateCondition . " GROUP BY d.status";
```

### Fix 2: Date Column Reference
**Changed line 49:**
```php
// BEFORE
$dateCondition = " AND DATE(donation_date) BETWEEN :start_date AND :end_date";

// AFTER
$dateCondition = " AND DATE(d.donation_date) BETWEEN :start_date AND :end_date";
```

## Testing
Created test script to verify all queries work correctly:
- ✅ Donation stats query
- ✅ Total raised query
- ✅ Monthly data query

All tests passed successfully.

## Files Modified
1. `admin/reports.php` - Fixed ambiguous column references

## Status
✅ **FIXED** - Page now loads correctly

## Verification Steps
1. Navigate to admin panel
2. Click "Reports & Analytics"
3. Page should load with all data displayed
4. Test filters to ensure they work
5. Try exporting CSV
6. Try printing report

## Prevention
When writing SQL queries with JOINs:
- Always use table aliases (e.g., `d.status` instead of just `status`)
- Explicitly specify which table's column you're referencing
- Test queries with actual data before deployment

## Date Fixed
December 2, 2024

## Developer Notes
The issue occurred because the enhancement added JOIN clauses to queries that previously didn't need them. When both tables in a JOIN have columns with the same name, MySQL requires explicit table qualification.

---

**Status**: ✅ Resolved and Tested

