# Campaign Approval System - Complete Guide

## 🎯 Overview

Implemented a comprehensive campaign approval workflow where:
- Campaign creators submit campaigns for admin approval
- Admins review uploaded documents before approving
- Admins can approve or reject campaigns with reasons
- Full audit trail of approvals

## ✅ Features Implemented

### 1. New Campaign Statuses

**Status Flow:**
1. **Draft** - Campaign creator is still editing
2. **Pending Approval** - Submitted for admin review (⏰ REQUIRES ADMIN ACTION)
3. **Active** - Approved by admin, accepting donations
4. **Rejected** - Admin rejected with reason
5. **Completed** - Target amount reached
6. **Closed** - Manually closed

### 2. Database Changes

**New Fields in `campaigns` table:**
- `status` - Updated ENUM to include 'pending_approval' and 'rejected'
- `approved_by` - User ID of admin who approved
- `approved_at` - Timestamp of approval
- `rejection_reason` - Reason if rejected

### 3. Campaign Creation (`campaign_add.php`)

**Changes:**
- Status options: "Save as Draft" or "Submit for Approval"
- Info message: "Campaigns must be approved by admin before going active"
- No direct "Active" option - must go through approval

### 4. Campaign Approval Interface (`campaign_view.php`)

**For Pending Campaigns:**
- ⚠️ Yellow warning card: "Pending Approval"
- ✅ Green "Approve Campaign" button
- ❌ Red "Reject Campaign" button
- Review documents before deciding

**For Approved Campaigns:**
- ✅ Green success card showing:
  - Who approved
  - When approved

**For Rejected Campaigns:**
- ❌ Red danger card showing:
  - Rejection reason
  - Feedback for campaign creator

### 5. Rejection Modal

**Features:**
- Warning message about notifying creator
- Required rejection reason textarea
- Clear explanation needed
- Cancel or confirm rejection

### 6. Campaign Listing (`campaigns.php`)

**Status Badges with Icons:**
- 📄 Draft (Gray)
- ⏰ Pending Approval (Yellow/Warning)
- ✅ Active (Green)
- 🏁 Completed (Blue)
- 🔒 Closed (Red)
- ❌ Rejected (Red)

### 7. Campaign Editing (`campaign_edit.php`)

**Updated Status Options:**
- All statuses available for admin control
- Info message about approval requirement
- Can resubmit rejected campaigns

## 📋 Installation Steps

### Step 1: Run Database Migration

**IMPORTANT: Run this first!**

```
http://localhost/kopugive/add_campaign_approval_system.php
```

This will:
- Add new status options
- Create approval fields
- Set up foreign keys

### Step 2: Also Run Document Table Migration (if not done)

```
http://localhost/kopugive/fix_campaign_documents_table.php
```

### Step 3: Test the System

1. **Create a test campaign** as admin
2. **Set status to "Submit for Approval"**
3. **Upload supporting documents**
4. **View the campaign** - see approval interface
5. **Test approval** - click approve button
6. **Test rejection** - try rejecting with reason

## 🎮 How to Use

### For Campaign Creators

**Creating a Campaign:**
1. Go to Admin → Campaigns → New Campaign
2. Fill in all details
3. Upload supporting documents (approval letters, budgets, etc.)
4. Choose status:
   - **Save as Draft** - Keep editing
   - **Submit for Approval** - Send to admin for review
5. Submit

**After Submission:**
- Campaign status shows "Pending Approval" (⏰)
- Wait for admin review
- If rejected, check rejection reason and resubmit

### For Admins

**Reviewing Campaigns:**
1. Go to Admin → Campaigns
2. Look for campaigns with ⏰ "Pending Approval" status
3. Click "View" to review campaign

**In Campaign View:**
1. **Review all details:**
   - Campaign description
   - Target amount
   - Dates
   - **Supporting Documents** (scroll down)

2. **Review Documents:**
   - Principal approval letters
   - Budget plans
   - Project proposals
   - Any uploaded documents

3. **Make Decision:**
   
   **To Approve:**
   - Click green "Approve Campaign" button
   - Confirm approval
   - Campaign becomes "Active"
   - Starts accepting donations

   **To Reject:**
   - Click red "Reject Campaign" button
   - Enter rejection reason (required)
   - Be specific and helpful
   - Creator will see this reason

## 🔍 Approval Checklist

Before approving a campaign, verify:

- [ ] Campaign has clear, appropriate name
- [ ] Description is detailed and accurate
- [ ] Target amount is reasonable
- [ ] Dates are appropriate
- [ ] **Supporting documents uploaded:**
  - [ ] Principal/admin approval letter
  - [ ] Budget breakdown
  - [ ] Project proposal (if applicable)
- [ ] All information is legitimate
- [ ] Campaign aligns with school policies

## 📊 Status Badge Guide

| Status | Badge | Icon | Meaning |
|--------|-------|------|---------|
| Draft | Gray | 📄 | Still being edited |
| Pending Approval | Yellow | ⏰ | **Needs admin review** |
| Active | Green | ✅ | Approved, accepting donations |
| Rejected | Red | ❌ | Not approved, see reason |
| Completed | Blue | 🏁 | Target reached |
| Closed | Red | 🔒 | Manually closed |

## 🔔 Notifications & Alerts

**Visual Indicators:**
- Pending campaigns show yellow warning badge
- Approval interface appears in campaign view
- Rejection reasons displayed prominently
- Success/error messages after actions

## 🛡️ Security & Audit Trail

**Activity Logging:**
- All approvals logged with user ID and timestamp
- All rejections logged
- Full audit trail in activity_logs table

**Permissions:**
- Only admins can approve/reject
- Campaign creators can only submit for approval
- Status changes are tracked

## 📝 Database Queries

### Find pending campaigns:
```sql
SELECT * FROM campaigns WHERE status = 'pending_approval' ORDER BY created_at ASC;
```

### Find approved campaigns:
```sql
SELECT c.*, u.full_name as approved_by_name 
FROM campaigns c
LEFT JOIN users u ON c.approved_by = u.user_id
WHERE c.status = 'active' AND c.approved_by IS NOT NULL;
```

### Find rejected campaigns:
```sql
SELECT * FROM campaigns WHERE status = 'rejected' ORDER BY updated_at DESC;
```

## 🎨 UI/UX Improvements

**Color Coding:**
- 🟡 Yellow = Needs attention (pending)
- 🟢 Green = Approved/active
- 🔴 Red = Rejected/closed
- 🔵 Blue = Completed
- ⚫ Gray = Draft

**Icons:**
- ⏰ Clock = Pending
- ✅ Check = Approved
- ❌ X = Rejected
- 📄 File = Draft
- 🏁 Flag = Completed
- 🔒 Lock = Closed

## 🔧 Troubleshooting

### "Table doesn't exist" error:
- Run `add_campaign_approval_system.php`
- Check database migration completed

### Approval button not working:
- Check you're logged in as admin
- Verify campaign is in "pending_approval" status
- Check PHP error logs

### Documents not showing:
- Run `fix_campaign_documents_table.php`
- Verify `campaign_documents` table exists
- Check `uploads/documents/` directory exists

## 📈 Benefits

### For Administration:
- ✅ Quality control before campaigns go live
- ✅ Review supporting documents
- ✅ Ensure legitimacy and compliance
- ✅ Audit trail of all approvals
- ✅ Provide feedback via rejection reasons

### For Transparency:
- ✅ All campaigns are vetted
- ✅ Supporting documents required
- ✅ Clear approval process
- ✅ Accountability

### For Campaign Creators:
- ✅ Clear submission process
- ✅ Know approval status
- ✅ Receive feedback if rejected
- ✅ Can resubmit after improvements

## 🚀 Next Steps

1. **Run migrations** (both approval system and documents table)
2. **Test the workflow** with a sample campaign
3. **Train admins** on approval process
4. **Communicate** new process to campaign creators
5. **Monitor** pending approvals regularly

## 📞 Support

If you encounter issues:
1. Check `logs/php_errors.log`
2. Verify both migrations ran successfully
3. Test with a simple campaign first
4. Check database for new fields

---

**Version:** 1.0
**Date:** November 11, 2025
**Status:** ✅ Complete and ready to use

**All campaigns now require admin approval before going active!**

