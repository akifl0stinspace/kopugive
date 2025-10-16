-- Sample data for testing KopuGive system
USE kopugive;

-- Sample campaigns
INSERT INTO campaigns (campaign_name, description, target_amount, current_amount, start_date, end_date, category, status, created_by) VALUES
('Tabung Komputer Lab', 'Kempen untuk membeli 30 unit komputer baharu untuk makmal komputer sekolah. Dana ini akan digunakan untuk meningkatkan kemudahan pembelajaran pelajar.', 50000.00, 15000.00, '2025-01-01', '2025-06-30', 'infrastructure', 'active', 1),
('Program Bantuan Pelajar', 'Bantuan kewangan untuk pelajar kurang berkemampuan bagi memastikan tiada pelajar ketinggalan dalam pendidikan.', 30000.00, 8500.00, '2025-02-01', '2025-12-31', 'welfare', 'active', 1),
('Pembinaan Dewan Serbaguna', 'Projek pembinaan dewan serbaguna untuk aktiviti kokurikulum dan acara sekolah.', 200000.00, 45000.00, '2025-01-15', '2025-12-31', 'infrastructure', 'active', 1);

-- Sample donors (password: admin123)
INSERT INTO users (full_name, email, phone, password_hash, role) VALUES
('Ahmad bin Abdullah', 'ahmad@example.com', '0123456788', '$2y$10$YJGwM7RCLDSqYC0LvJqyJuVG9QVvzHPFQ6dWzKxLm8HvmVGFZGONe', 'donor'),
('Siti Nurhaliza', 'siti@example.com', '0198765432', '$2y$10$YJGwM7RCLDSqYC0LvJqyJuVG9QVvzHPFQ6dWzKxLm8HvmVGFZGONe', 'donor'),
('Muhammad Hisyam', 'hisyam@example.com', '0176543210', '$2y$10$YJGwM7RCLDSqYC0LvJqyJuVG9QVvzHPFQ6dWzKxLm8HvmVGFZGONe', 'donor');

-- Sample donations
INSERT INTO donations (campaign_id, donor_id, donor_name, donor_email, amount, payment_method, transaction_id, status, verified_by, verified_at) VALUES
(1, 2, 'Ahmad bin Abdullah', 'ahmad@example.com', 500.00, 'online_banking', 'TXN001234567', 'verified', 1, NOW()),
(1, 3, 'Siti Nurhaliza', 'siti@example.com', 1000.00, 'online_banking', 'TXN001234568', 'verified', 1, NOW()),
(2, 4, 'Muhammad Hisyam', 'hisyam@example.com', 250.00, 'ewallet', 'TXN001234569', 'verified', 1, NOW()),
(3, 2, 'Ahmad bin Abdullah', 'ahmad@example.com', 2000.00, 'online_banking', 'TXN001234570', 'pending', NULL, NULL);

-- Sample campaign updates
INSERT INTO campaign_updates (campaign_id, title, content, posted_by) VALUES
(1, 'Terima kasih atas sumbangan anda!', 'Alhamdulillah, setakat ini kami telah berjaya mengumpul RM15,000. Terima kasih kepada semua penderma yang telah menyumbang.', 1),
(2, 'Program Bantuan Fasa 1 Bermula', 'Bantuan pertama telah diagihkan kepada 20 pelajar. Semoga dana ini memberi manfaat kepada mereka.', 1);

