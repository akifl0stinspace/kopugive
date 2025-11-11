# Campaign Supporting Documents Feature

## Overview

Added functionality to allow campaign creators to upload supporting documents (like principal approval letters, budget plans, project proposals) when creating or editing campaigns. Admins can view and download these documents for verification and transparency.

## Features Implemented

### 1. Database Schema
**New Table:** `campaign_documents`

```sql
CREATE TABLE campaign_documents (
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
    FOREIGN KEY (uploaded_by) REFERENCES users(user_id) ON DELETE SET NULL
);
```

**Fields:**
- `document_id` - Unique identifier
- `campaign_id` - Links to campaigns table
- `document_name` - Original filename
- `document_path` - File path on server (e.g., `uploads/documents/filename.pdf`)
- `document_type` - File extension (PDF, DOC, DOCX, etc.)
- `file_size` - File size in bytes
- `description` - User-provided description (e.g., "Principal Approval Letter")
- `uploaded_by` - User who uploaded the document
- `uploaded_at` - Upload timestamp

### 2. File Upload Support

**Supported File Types:**
- PDF (.pdf)
- Word Documents (.doc, .docx)
- Excel Spreadsheets (.xls, .xlsx)
- Images (.jpg, .jpeg, .png)

**Upload Directory:** `uploads/documents/`

**File Size Limit:** 5MB (configurable in `config/config.php`)

### 3. Campaign Creation (campaign_add.php)

**New Features:**
- Multiple document upload fields
- Dynamic "Add Another Document" button
- Description field for each document
- Upload validation and error handling
- Documents are linked to campaign after creation

**UI Elements:**
- File input with accept filter
- Description text input
- Add/Remove document fields dynamically
- Tips section updated with document requirements
- New info card highlighting document requirements

### 4. Campaign Editing (campaign_edit.php)

**New Features:**
- View existing documents with details
- Download existing documents
- Delete existing documents (with confirmation)
- Upload additional documents
- All changes are logged to activity logs

**Document Display:**
- Document name and type
- File size
- Upload date and uploader name
- Description (if provided)
- Download and delete buttons

### 5. Campaign Viewing (campaign_view.php)

**New Features:**
- Dedicated "Supporting Documents" section
- Card-based layout for each document
- File type icons (PDF, generic document)
- Document metadata display
- Download buttons for each document
- Only shown if documents exist

**Information Displayed:**
- Document name
- Description
- File type and size
- Uploader name
- Upload date and time
- Download link

## Installation Steps

### Step 1: Run Database Migration

1. Navigate to: `http://localhost/kopugive/add_campaign_documents.php`
2. The script will:
   - Create the `campaign_documents` table
   - Create the `uploads/documents/` directory
   - Display success message
3. **Important:** Delete `add_campaign_documents.php` after running it

### Step 2: Verify Directory Permissions

Ensure the `uploads/documents/` directory is writable:

**Windows:**
- Right-click `uploads/documents/` → Properties → Security
- Ensure Users have Write permissions

**Linux/Mac:**
```bash
chmod 755 uploads/documents/
```

### Step 3: Test the Feature

1. **Create a new campaign:**
   - Go to Admin → Campaigns → Create New Campaign
   - Fill in campaign details
   - Upload supporting documents (e.g., approval letter)
   - Add descriptions for each document
   - Save the campaign

2. **View the campaign:**
   - Go to Campaign View page
   - Verify documents are displayed in the "Supporting Documents" section
   - Test downloading documents

3. **Edit the campaign:**
   - Go to Edit Campaign
   - View existing documents
   - Upload additional documents
   - Delete documents if needed

## Usage Guide

### For Campaign Creators

**When Creating a Campaign:**

1. Fill in all required campaign information
2. Scroll to "Supporting Documents" section
3. Click "Choose File" to select a document
4. Enter a description (e.g., "Principal Approval Letter")
5. Click "Add Another Document" to upload more files
6. Submit the form

**Recommended Documents to Upload:**
- Principal or school administration approval letter
- Budget breakdown/financial plan
- Project proposal or detailed plan
- Any official documentation
- Supporting images or diagrams

**When Editing a Campaign:**

1. View existing documents in the "Existing Documents" section
2. Download or delete documents as needed
3. Upload new documents in the "Add New Documents" section
4. Save changes

### For Admins

**Viewing Campaign Documents:**

1. Go to Admin → Campaigns
2. Click "View" on any campaign
3. Scroll to "Supporting Documents" section
4. Review all uploaded documents
5. Download documents for verification
6. Check uploader and upload date

**Document Verification:**
- Review documents before activating campaigns
- Ensure proper authorization documents are present
- Verify budget plans align with target amount
- Check document authenticity

## File Structure

```
kopugive/
├── uploads/
│   └── documents/              # New directory for campaign documents
│       └── *.pdf, *.doc, etc.
├── admin/
│   ├── campaign_add.php        # Updated with document upload
│   ├── campaign_edit.php       # Updated with document management
│   └── campaign_view.php       # Updated with document display
├── add_campaign_documents.php  # Migration script (delete after use)
└── CAMPAIGN_DOCUMENTS_FEATURE.md  # This file
```

## Database Queries

### Get all documents for a campaign:
```php
$stmt = $db->prepare("
    SELECT cd.*, u.full_name as uploader_name
    FROM campaign_documents cd
    LEFT JOIN users u ON cd.uploaded_by = u.user_id
    WHERE cd.campaign_id = ?
    ORDER BY cd.uploaded_at DESC
");
$stmt->execute([$campaignId]);
$documents = $stmt->fetchAll();
```

### Insert a new document:
```php
$stmt = $db->prepare("
    INSERT INTO campaign_documents 
    (campaign_id, document_name, document_path, document_type, file_size, description, uploaded_by) 
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
$stmt->execute([
    $campaignId,
    $fileName,
    $filePath,
    $fileType,
    $fileSize,
    $description,
    $userId
]);
```

### Delete a document:
```php
$stmt = $db->prepare("DELETE FROM campaign_documents WHERE document_id = ?");
$stmt->execute([$documentId]);
```

## Security Considerations

1. **File Type Validation:** Only allowed file types can be uploaded
2. **File Size Limit:** Maximum 5MB per file
3. **Authentication:** Only logged-in admins can upload/delete documents
4. **Path Security:** Files are stored with unique names to prevent conflicts
5. **Database Relations:** Documents are automatically deleted when campaign is deleted (CASCADE)
6. **Activity Logging:** All document operations are logged

## Benefits

### For Transparency
- Donors can see that campaigns are officially approved
- Budget plans are available for review
- Accountability is increased

### For Administration
- Easy verification of campaign legitimacy
- Centralized document storage
- Audit trail of uploads

### For Campaign Creators
- Simple upload process
- Multiple document support
- Edit/update documents anytime

## Troubleshooting

### Documents not uploading:
1. Check `uploads/documents/` directory exists
2. Verify directory is writable
3. Check file size is under 5MB
4. Ensure file type is supported
5. Check PHP error logs at `logs/php_errors.log`

### Documents not displaying:
1. Verify database migration was run
2. Check documents exist in database
3. Verify file paths are correct
4. Check browser console for errors

### Download links not working:
1. Verify file exists on server
2. Check file path in database
3. Ensure Apache is serving files from uploads directory

## Future Enhancements

Potential improvements:
- Document preview (PDF viewer)
- Document versioning
- Document approval workflow
- Email notifications on document upload
- Document categories/tags
- Bulk document upload
- Document expiry dates

## Technical Details

**Modified Files:**
1. `admin/campaign_add.php` - Added document upload form and processing
2. `admin/campaign_edit.php` - Added document management (view, upload, delete)
3. `admin/campaign_view.php` - Added document display section
4. `includes/functions.php` - Already supports file uploads (no changes needed)

**New Files:**
1. `add_campaign_documents.php` - Database migration script
2. `CAMPAIGN_DOCUMENTS_FEATURE.md` - This documentation

**Database Changes:**
1. New table: `campaign_documents`
2. New directory: `uploads/documents/`

## Support

If you encounter any issues:
1. Check `logs/php_errors.log` for error messages
2. Verify database migration was successful
3. Ensure directory permissions are correct
4. Test with a small PDF file first
5. Check browser developer console for JavaScript errors

---

**Version:** 1.0
**Date:** November 11, 2025
**Status:** ✅ Complete and ready to use

