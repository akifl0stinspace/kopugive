-- Migration: Add campaign approval system
-- Date: 2025-11-11
-- Description: Adds approval workflow for campaigns with admin review

-- Update status enum to include new statuses
ALTER TABLE campaigns 
MODIFY COLUMN status ENUM('draft', 'pending_approval', 'active', 'completed', 'closed', 'rejected') 
DEFAULT 'draft';

-- Add approval tracking fields
ALTER TABLE campaigns 
ADD COLUMN approved_by INT NULL AFTER status,
ADD COLUMN approved_at TIMESTAMP NULL AFTER approved_by,
ADD COLUMN rejection_reason TEXT NULL AFTER approved_at;

-- Add foreign key for approver (if not exists)
SET @fk_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'campaigns' 
    AND CONSTRAINT_NAME = 'fk_campaigns_approved_by');

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE campaigns ADD CONSTRAINT fk_campaigns_approved_by FOREIGN KEY (approved_by) REFERENCES users(user_id) ON DELETE SET NULL',
    'SELECT "Foreign key already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

