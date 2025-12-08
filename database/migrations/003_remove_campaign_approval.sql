-- Migration: Remove campaign approval system
-- Date: 2025-12-02
-- Description: Removes approval workflow, keeps only draft/active/completed/closed statuses

-- Remove approval tracking fields
ALTER TABLE campaigns 
DROP FOREIGN KEY IF EXISTS fk_campaigns_approved_by;

ALTER TABLE campaigns 
DROP COLUMN IF EXISTS approved_by,
DROP COLUMN IF EXISTS approved_at,
DROP COLUMN IF EXISTS rejection_reason;

-- Revert status enum to original values
ALTER TABLE campaigns 
MODIFY COLUMN status ENUM('draft', 'active', 'completed', 'closed') 
DEFAULT 'draft';

-- Update any pending_approval or rejected campaigns to draft
UPDATE campaigns 
SET status = 'draft' 
WHERE status IN ('pending_approval', 'rejected');

