<?php
/**
 * Receipt Generation Functions
 * KopuGive - MRSM Kota Putra Donation System
 * 
 * Handles PDF receipt generation and email notifications
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Generate PDF receipt for a donation
 * 
 * @param array $donation Donation details
 * @param array $campaign Campaign details
 * @return array ['success' => bool, 'path' => string, 'message' => string]
 */
function generateReceipt($donation, $campaign) {
    try {
        // Create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('KopuGive');
        $pdf->SetAuthor('MRSM Kota Putra');
        $pdf->SetTitle('Donation Receipt #' . $donation['donation_id']);
        $pdf->SetSubject('Donation Receipt');
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', '', 10);
        
        // Colors
        $maroon = array(128, 0, 0);
        $gold = array(255, 215, 0);
        $darkGray = array(51, 51, 51);
        $lightGray = array(240, 240, 240);
        
        // Header with maroon background
        $pdf->SetFillColor($maroon[0], $maroon[1], $maroon[2]);
        $pdf->Rect(0, 0, 210, 40, 'F');
        
        // Organization name
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->SetXY(15, 15);
        $pdf->Cell(0, 10, 'KopuGive', 0, 1, 'L');
        
        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetXY(15, 25);
        $pdf->Cell(0, 5, 'MRSM Kota Putra Donation System', 0, 1, 'L');
        
        // Receipt title
        $pdf->SetY(50);
        $pdf->SetTextColor($maroon[0], $maroon[1], $maroon[2]);
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'DONATION RECEIPT', 0, 1, 'C');
        
        // Receipt details box
        $pdf->SetY(65);
        $pdf->SetTextColor($darkGray[0], $darkGray[1], $darkGray[2]);
        $pdf->SetFont('helvetica', '', 10);
        
        // Receipt info
        $pdf->SetFillColor($lightGray[0], $lightGray[1], $lightGray[2]);
        $pdf->Cell(90, 8, 'Receipt No:', 1, 0, 'L', true);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(90, 8, 'RCP-' . str_pad($donation['donation_id'], 6, '0', STR_PAD_LEFT), 1, 1, 'L');
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(90, 8, 'Transaction ID:', 1, 0, 'L', true);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(90, 8, $donation['transaction_id'] ?? 'N/A', 1, 1, 'L');
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(90, 8, 'Date:', 1, 0, 'L', true);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(90, 8, date('d F Y, H:i', strtotime($donation['donation_date'])), 1, 1, 'L');
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(90, 8, 'Payment Method:', 1, 0, 'L', true);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(90, 8, ucfirst(str_replace('_', ' ', $donation['payment_method'])), 1, 1, 'L');
        
        // Spacing
        $pdf->Ln(10);
        
        // Donor Information
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetTextColor($maroon[0], $maroon[1], $maroon[2]);
        $pdf->Cell(0, 8, 'DONOR INFORMATION', 0, 1, 'L');
        $pdf->SetTextColor($darkGray[0], $darkGray[1], $darkGray[2]);
        $pdf->Ln(2);
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(90, 7, 'Name:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 10);
        $donorName = $donation['is_anonymous'] ? 'Anonymous Donor' : $donation['donor_name'];
        $pdf->Cell(90, 7, $donorName, 0, 1, 'L');
        
        if (!$donation['is_anonymous'] && $donation['donor_email']) {
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(90, 7, 'Email:', 0, 0, 'L');
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(90, 7, $donation['donor_email'], 0, 1, 'L');
        }
        
        // Spacing
        $pdf->Ln(10);
        
        // Campaign Information
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetTextColor($maroon[0], $maroon[1], $maroon[2]);
        $pdf->Cell(0, 8, 'CAMPAIGN INFORMATION', 0, 1, 'L');
        $pdf->SetTextColor($darkGray[0], $darkGray[1], $darkGray[2]);
        $pdf->Ln(2);
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(90, 7, 'Campaign:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->MultiCell(90, 7, $campaign['campaign_name'], 0, 'L');
        
        if (!empty($donation['donation_message'])) {
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(90, 7, 'Message:', 0, 0, 'L');
            $pdf->SetFont('helvetica', 'I', 9);
            $pdf->MultiCell(90, 7, '"' . $donation['donation_message'] . '"', 0, 'L');
        }
        
        // Spacing
        $pdf->Ln(10);
        
        // Amount box - highlighted
        $pdf->SetFillColor($maroon[0], $maroon[1], $maroon[2]);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 12, 'DONATION AMOUNT: RM ' . number_format($donation['amount'], 2), 1, 1, 'C', true);
        
        // Spacing
        $pdf->Ln(15);
        
        // Thank you message
        $pdf->SetTextColor($darkGray[0], $darkGray[1], $darkGray[2]);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Thank you for your generous donation!', 0, 1, 'C');
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->MultiCell(0, 5, 'Your contribution makes a real difference in supporting our students and programs at MRSM Kota Putra. We are grateful for your support.', 0, 'C');
        
        // Footer
        $pdf->SetY(-30);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetTextColor(128, 128, 128);
        $pdf->Cell(0, 5, 'This is a computer-generated receipt and does not require a signature.', 0, 1, 'C');
        $pdf->Cell(0, 5, 'For inquiries, please contact us at ' . SMTP_FROM_EMAIL, 0, 1, 'C');
        $pdf->Cell(0, 5, 'Generated on ' . date('d F Y, H:i:s'), 0, 1, 'C');
        
        // Create receipts directory if it doesn't exist
        $receiptDir = __DIR__ . '/../uploads/receipts/';
        if (!file_exists($receiptDir)) {
            mkdir($receiptDir, 0755, true);
        }
        
        // Generate unique filename
        $filename = 'receipt_' . $donation['donation_id'] . '_' . time() . '.pdf';
        $filepath = $receiptDir . $filename;
        $relativePath = 'uploads/receipts/' . $filename;
        
        // Save PDF to file
        $pdf->Output($filepath, 'F');
        
        return [
            'success' => true,
            'path' => $relativePath,
            'filepath' => $filepath,
            'message' => 'Receipt generated successfully'
        ];
        
    } catch (Exception $e) {
        error_log("Receipt Generation Error: " . $e->getMessage());
        return [
            'success' => false,
            'path' => null,
            'message' => 'Failed to generate receipt: ' . $e->getMessage()
        ];
    }
}

/**
 * Send receipt email to donor
 * 
 * @param array $donation Donation details
 * @param string $receiptPath Path to receipt PDF file
 * @return array ['success' => bool, 'message' => string]
 */
function sendReceiptEmail($donation, $receiptPath) {
    // Skip if no email address
    if (empty($donation['donor_email'])) {
        return [
            'success' => false,
            'message' => 'No email address provided'
        ];
    }
    
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($donation['donor_email'], $donation['donor_name']);
        
        // Attachment
        if (file_exists($receiptPath)) {
            $mail->addAttachment($receiptPath, 'donation_receipt.pdf');
        }
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Thank You for Your Donation - Receipt #' . $donation['donation_id'];
        
        $donorName = $donation['is_anonymous'] ? 'Valued Donor' : $donation['donor_name'];
        
        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #800000; color: white; padding: 20px; text-align: center; }
                .content { background-color: #f9f9f9; padding: 30px; }
                .amount { background-color: #800000; color: white; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                .button { display: inline-block; padding: 12px 30px; background-color: #800000; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>KopuGive</h1>
                    <p>MRSM Kota Putra Donation System</p>
                </div>
                <div class="content">
                    <h2>Thank You for Your Generous Donation!</h2>
                    <p>Dear ' . htmlspecialchars($donorName) . ',</p>
                    <p>We are deeply grateful for your generous donation. Your support makes a real difference in the lives of our students at MRSM Kota Putra.</p>
                    
                    <div class="amount">
                        Donation Amount: RM ' . number_format($donation['amount'], 2) . '
                    </div>
                    
                    <p><strong>Receipt Details:</strong></p>
                    <ul>
                        <li>Receipt No: RCP-' . str_pad($donation['donation_id'], 6, '0', STR_PAD_LEFT) . '</li>
                        <li>Transaction ID: ' . htmlspecialchars($donation['transaction_id'] ?? 'N/A') . '</li>
                        <li>Date: ' . date('d F Y, H:i', strtotime($donation['donation_date'])) . '</li>
                        <li>Payment Method: ' . ucfirst(str_replace('_', ' ', $donation['payment_method'])) . '</li>
                    </ul>
                    
                    <p>Your official receipt is attached to this email as a PDF document. Please keep it for your records.</p>
                    
                    <p>Your contribution will help us continue to provide quality education and support to our students. We truly appreciate your commitment to our mission.</p>
                    
                    <p style="text-align: center;">
                        <a href="' . APP_URL . '/donor/my_donations.php" class="button">View My Donations</a>
                    </p>
                    
                    <p>If you have any questions about your donation, please don\'t hesitate to contact us.</p>
                    
                    <p>With gratitude,<br>
                    <strong>The KopuGive Team</strong><br>
                    MRSM Kota Putra</p>
                </div>
                <div class="footer">
                    <p>This is an automated email. Please do not reply to this message.</p>
                    <p>For inquiries, contact us at ' . SMTP_FROM_EMAIL . '</p>
                    <p>&copy; ' . date('Y') . ' KopuGive - MRSM Kota Putra. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ';
        
        $mail->AltBody = "Dear {$donorName},\n\n"
            . "Thank you for your generous donation of RM " . number_format($donation['amount'], 2) . "!\n\n"
            . "Receipt No: RCP-" . str_pad($donation['donation_id'], 6, '0', STR_PAD_LEFT) . "\n"
            . "Transaction ID: " . ($donation['transaction_id'] ?? 'N/A') . "\n"
            . "Date: " . date('d F Y, H:i', strtotime($donation['donation_date'])) . "\n\n"
            . "Your official receipt is attached to this email.\n\n"
            . "Thank you for supporting MRSM Kota Putra!\n\n"
            . "Best regards,\nThe KopuGive Team";
        
        $mail->send();
        
        return [
            'success' => true,
            'message' => 'Receipt email sent successfully'
        ];
        
    } catch (Exception $e) {
        error_log("Email Error: " . $mail->ErrorInfo);
        return [
            'success' => false,
            'message' => 'Failed to send email: ' . $mail->ErrorInfo
        ];
    }
}

/**
 * Generate and email receipt for a donation
 * 
 * @param int $donationId Donation ID
 * @param PDO $db Database connection
 * @return array ['success' => bool, 'message' => string]
 */
function processReceiptForDonation($donationId, $db) {
    try {
        // Get donation details
        $stmt = $db->prepare("
            SELECT d.*, c.campaign_name, c.campaign_description
            FROM donations d
            LEFT JOIN campaigns c ON d.campaign_id = c.campaign_id
            WHERE d.donation_id = ?
        ");
        $stmt->execute([$donationId]);
        $donation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$donation) {
            return [
                'success' => false,
                'message' => 'Donation not found'
            ];
        }
        
        // Generate receipt
        $campaign = [
            'campaign_name' => $donation['campaign_name'],
            'campaign_description' => $donation['campaign_description']
        ];
        
        $receiptResult = generateReceipt($donation, $campaign);
        
        if (!$receiptResult['success']) {
            return $receiptResult;
        }
        
        // Update donation record with receipt path
        $stmt = $db->prepare("
            UPDATE donations 
            SET receipt_path = ?
            WHERE donation_id = ?
        ");
        $stmt->execute([$receiptResult['path'], $donationId]);
        
        // Send email with receipt
        $emailResult = sendReceiptEmail($donation, $receiptResult['filepath']);
        
        return [
            'success' => true,
            'receipt_path' => $receiptResult['path'],
            'email_sent' => $emailResult['success'],
            'message' => 'Receipt generated' . ($emailResult['success'] ? ' and emailed' : ' but email failed')
        ];
        
    } catch (Exception $e) {
        error_log("Process Receipt Error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to process receipt: ' . $e->getMessage()
        ];
    }
}
?>

