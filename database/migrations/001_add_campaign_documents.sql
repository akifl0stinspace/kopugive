-- Migration: Add campaign documents table
-- Date: 2025-11-11
-- Description: Adds support for uploading campaign documents (approval letters, budgets, etc.)

CREATE TABLE IF NOT EXISTS campaign_documents (
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
    FOREIGN KEY (uploaded_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_campaign (campaign_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

