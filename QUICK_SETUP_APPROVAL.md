# Quick Setup: Campaign Approval System

## 🚨 CRITICAL: Run These 2 URLs First!

### Step 1: Create Documents Table
```
http://localhost/kopugive/fix_campaign_documents_table.php
```
✅ Creates `campaign_documents` table
✅ Creates `uploads/documents/` directory

### Step 2: Add Approval System
```
http://localhost/kopugive/add_campaign_approval_system.php
```
✅ Adds approval fields to campaigns
✅ Adds new statuses (pending_approval, rejected)
✅ Tracks who approved and when

### Step 3: Delete Migration Files
After both run successfully, delete:
- `fix_campaign_documents_table.php`
- `add_campaign_approval_system.php`

## ✅ What's New

### Campaign Creators Can:
- ✅ Upload supporting documents (approval letters, budgets)
- ✅ Submit campaigns for approval
- ✅ See approval status
- ✅ View rejection reasons

### Admins Can:
- ✅ Review uploaded documents
- ✅ Approve campaigns (makes them active)
- ✅ Reject campaigns with reasons
- ✅ See full audit trail

## 🎯 Quick Test

1. **Create a campaign:**
   ```
   http://localhost/kopugive/admin/campaign_add.php
   ```
   - Fill in details
   - Upload a test document (any PDF)
   - Set status: "Submit for Approval"

2. **View the campaign:**
   - Go to campaigns list
   - Look for ⏰ "Pending Approval" badge
   - Click "View"

3. **Approve or Reject:**
   - Review documents section
   - Click "Approve" or "Reject"
   - If rejecting, provide reason

## 📊 Status Flow

```
Draft → Pending Approval → Active
                        ↘ Rejected → (can resubmit)
```

## 🎨 Visual Guide

**Pending Approval:**
- Yellow badge with clock icon ⏰
- Yellow warning card in campaign view
- Green "Approve" button
- Red "Reject" button

**Approved:**
- Green badge with checkmark ✅
- Shows who approved and when

**Rejected:**
- Red badge with X ❌
- Shows rejection reason

## 🔥 Key Features

1. **Document Upload** - Campaigns can attach approval letters, budgets, etc.
2. **Admin Review** - Admins see all documents before approving
3. **Rejection Feedback** - Creators see why campaign was rejected
4. **Audit Trail** - Track who approved and when
5. **Status Badges** - Clear visual indicators

## ⚠️ Important Notes

- **Campaigns cannot go directly to "Active"** - must be approved first
- **Documents are optional** but recommended for approval
- **Rejection reasons are required** - be specific and helpful
- **All actions are logged** for audit purposes

---

**Ready to use!** Just run the 2 migration URLs above and start testing! 🚀

