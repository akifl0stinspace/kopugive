# Quick Setup: Campaign Documents Feature

## 🚀 Quick Start (3 Steps)

### Step 1: Run Database Migration
```
http://localhost/kopugive/add_campaign_documents.php
```
- Creates `campaign_documents` table
- Creates `uploads/documents/` directory
- **Delete this file after running!**

### Step 2: Test Creating a Campaign
1. Go to: `http://localhost/kopugive/admin/campaign_add.php`
2. Fill in campaign details
3. Scroll to "Supporting Documents" section
4. Upload a test document (PDF, DOC, or image)
5. Add description (e.g., "Principal Approval Letter")
6. Click "Add Another Document" to upload more
7. Save the campaign

### Step 3: Verify It Works
1. View the campaign you just created
2. Check the "Supporting Documents" section appears
3. Click download to test document access
4. Try editing the campaign to add/remove documents

## ✅ What's New

### Campaign Creation
- ✅ Upload multiple supporting documents
- ✅ Add descriptions for each document
- ✅ Dynamic add/remove document fields
- ✅ Supports PDF, DOC, DOCX, XLS, XLSX, images

### Campaign Editing
- ✅ View existing documents
- ✅ Download documents
- ✅ Delete documents
- ✅ Upload additional documents

### Campaign Viewing (Admin)
- ✅ Beautiful document display section
- ✅ File type icons
- ✅ Document metadata (size, date, uploader)
- ✅ Download buttons

## 📁 Supported File Types

- **Documents:** PDF, DOC, DOCX
- **Spreadsheets:** XLS, XLSX
- **Images:** JPG, JPEG, PNG
- **Max Size:** 5MB per file

## 💡 Use Cases

**Documents to Upload:**
- ✅ Principal approval letters
- ✅ Budget breakdowns
- ✅ Project proposals
- ✅ Official authorization documents
- ✅ Supporting images/diagrams

## 🎯 Benefits

**For Transparency:**
- Donors see official approvals
- Budget plans are visible
- Increased accountability

**For Admins:**
- Easy campaign verification
- Centralized document storage
- Audit trail

**For Campaign Creators:**
- Simple upload process
- Multiple documents supported
- Edit anytime

## 🔧 Troubleshooting

**Documents not uploading?**
- Check `uploads/documents/` exists and is writable
- Verify file is under 5MB
- Check file type is supported

**Documents not showing?**
- Verify you ran the migration script
- Check browser console for errors
- Verify files exist in `uploads/documents/`

## 📋 Files Modified

1. ✅ `admin/campaign_add.php` - Document upload form
2. ✅ `admin/campaign_edit.php` - Document management
3. ✅ `admin/campaign_view.php` - Document display

## 📋 Files Created

1. ✅ `add_campaign_documents.php` - Migration script (delete after use)
2. ✅ `CAMPAIGN_DOCUMENTS_FEATURE.md` - Full documentation
3. ✅ `QUICK_SETUP_DOCUMENTS.md` - This file

## 🗑️ Cleanup

After testing, delete:
- `add_campaign_documents.php` (migration script)
- `QUICK_SETUP_DOCUMENTS.md` (this file)

Keep for reference:
- `CAMPAIGN_DOCUMENTS_FEATURE.md` (full documentation)

---

**Ready to use!** Start by running the migration script, then create a test campaign with documents.

