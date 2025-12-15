-- Add Stripe-specific fields to donations table
-- Run this migration to support Stripe payment integration

ALTER TABLE donations 
ADD COLUMN stripe_payment_intent_id VARCHAR(255) NULL AFTER transaction_id,
ADD COLUMN stripe_checkout_session_id VARCHAR(255) NULL AFTER stripe_payment_intent_id,
ADD COLUMN payment_status VARCHAR(50) DEFAULT 'pending' AFTER status,
ADD INDEX idx_stripe_payment_intent (stripe_payment_intent_id),
ADD INDEX idx_stripe_checkout_session (stripe_checkout_session_id);

-- Update existing records to have payment_status
UPDATE donations SET payment_status = 'pending' WHERE payment_status IS NULL;

