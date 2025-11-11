<?php
/**
 * Check Database Receipt Paths
 * Quick check to see what receipt paths are stored in the database
 */
require_once 'config/database.php';

try {
    $db = (new Database())->getConnection();
    
    echo "<h2>KopuGive - Database Receipt Paths Check</h2>";
    
    // Get all donations with receipts
    $stmt = $db->query("
        SELECT 
            d.donation_id,
            d.donor_name,
            d.amount,
            d.receipt_path,
            c.campaign_name
        FROM donations d
        LEFT JOIN campaigns c ON d.campaign_id = c.campaign_id
        WHERE d.receipt_path IS NOT NULL AND d.receipt_path != ''
        ORDER BY d.donation_id DESC
    ");
    $donations = $stmt->fetchAll();
    
    echo "<p>Found <strong>" . count($donations) . "</strong> donations with receipt paths.</p>";
    
    if (count($donations) > 0) {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>Donation ID</th>";
        echo "<th>Donor</th>";
        echo "<th>Campaign</th>";
        echo "<th>Amount</th>";
        echo "<th>Receipt Path</th>";
        echo "<th>Status</th>";
        echo "</tr>";
        
        foreach ($donations as $donation) {
            $path = $donation['receipt_path'];
            $projectRoot = __DIR__ . '/';
            $fullPath = $projectRoot . $path;
            $fileExists = file_exists($fullPath);
            
            $status = $fileExists ? 
                "<span style='color: green;'>✓ File exists</span>" : 
                "<span style='color: red;'>✗ File missing</span>";
            
            $pathColor = (strpos($path, 'donor/uploads/') === 0) ? 
                'color: red; font-weight: bold;' : 
                'color: green;';
            
            echo "<tr>";
            echo "<td>#{$donation['donation_id']}</td>";
            echo "<td>" . htmlspecialchars($donation['donor_name']) . "</td>";
            echo "<td>" . htmlspecialchars($donation['campaign_name']) . "</td>";
            echo "<td>RM " . number_format($donation['amount'], 2) . "</td>";
            echo "<td style='$pathColor'><code>$path</code></td>";
            echo "<td>$status</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Check for incorrect paths
        $incorrectPaths = 0;
        foreach ($donations as $donation) {
            if (strpos($donation['receipt_path'], 'donor/uploads/') === 0) {
                $incorrectPaths++;
            }
        }
        
        if ($incorrectPaths > 0) {
            echo "<div style='background: #fff3cd; padding: 15px; margin-top: 20px; border: 1px solid #ffc107;'>";
            echo "<h3 style='color: #856404;'>⚠ Action Required</h3>";
            echo "<p><strong>$incorrectPaths</strong> donation(s) have incorrect receipt paths (starting with 'donor/uploads/').</p>";
            echo "<p><a href='fix_receipt_paths.php' style='display: inline-block; padding: 10px 20px; background: #ffc107; color: #000; text-decoration: none; border-radius: 5px;'>Run Fix Script</a></p>";
            echo "</div>";
        } else {
            echo "<div style='background: #d4edda; padding: 15px; margin-top: 20px; border: 1px solid #28a745;'>";
            echo "<h3 style='color: #155724;'>✓ All Good!</h3>";
            echo "<p>All receipt paths are correct.</p>";
            echo "</div>";
        }
    } else {
        echo "<p style='color: #666;'>No donations with receipts found in the database.</p>";
    }
    
    echo "<hr>";
    echo "<p><a href='test_receipt.php'>Test Receipt Access</a> | ";
    echo "<a href='admin/donations.php'>View Donations</a> | ";
    echo "<a href='index.php'>Homepage</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>ERROR:</strong> " . $e->getMessage() . "</p>";
}
?>

