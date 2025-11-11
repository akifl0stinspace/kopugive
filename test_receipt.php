<?php
/**
 * Test Receipt Access
 * This page tests if receipt files are accessible
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Receipt Access - KopuGive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Test Receipt Access</h1>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5>Receipt File Test</h5>
            </div>
            <div class="card-body">
                <?php
                $receiptFile = 'uploads/receipts/68f08f21c0831_1760595745.jpg';
                $receiptPath = __DIR__ . '/' . $receiptFile;
                
                echo "<h6>Testing Receipt File:</h6>";
                echo "<p><code>$receiptFile</code></p>";
                
                echo "<h6>Full Path:</h6>";
                echo "<p><code>$receiptPath</code></p>";
                
                echo "<h6>File Exists Check:</h6>";
                if (file_exists($receiptPath)) {
                    echo "<p class='text-success'>✓ File exists on server</p>";
                    echo "<p><strong>File size:</strong> " . filesize($receiptPath) . " bytes</p>";
                    
                    echo "<h6>Preview:</h6>";
                    echo "<img src='$receiptFile' class='img-fluid' style='max-width: 500px;' alt='Receipt'>";
                    
                    echo "<h6 class='mt-3'>Direct Link:</h6>";
                    echo "<p><a href='$receiptFile' target='_blank' class='btn btn-primary'>Open Receipt in New Tab</a></p>";
                } else {
                    echo "<p class='text-danger'>✗ File does NOT exist on server</p>";
                    
                    // Check alternative locations
                    echo "<h6>Checking Alternative Locations:</h6>";
                    $altLocations = [
                        'donor/uploads/receipts/68f08f21c0831_1760595745.jpg',
                        'admin/uploads/receipts/68f08f21c0831_1760595745.jpg',
                    ];
                    
                    foreach ($altLocations as $altPath) {
                        $fullAltPath = __DIR__ . '/' . $altPath;
                        if (file_exists($fullAltPath)) {
                            echo "<p class='text-warning'>⚠ Found at: <code>$altPath</code></p>";
                        }
                    }
                }
                ?>
                
                <hr>
                
                <h6>All Receipt Files:</h6>
                <?php
                $receiptsDir = __DIR__ . '/uploads/receipts/';
                if (is_dir($receiptsDir)) {
                    $files = scandir($receiptsDir);
                    $files = array_diff($files, ['.', '..']);
                    
                    if (count($files) > 0) {
                        echo "<ul class='list-group'>";
                        foreach ($files as $file) {
                            $filePath = 'uploads/receipts/' . $file;
                            echo "<li class='list-group-item'>";
                            echo "<strong>$file</strong> ";
                            echo "<a href='$filePath' target='_blank' class='btn btn-sm btn-outline-primary float-end'>View</a>";
                            echo "</li>";
                        }
                        echo "</ul>";
                    } else {
                        echo "<p class='text-muted'>No receipt files found</p>";
                    }
                } else {
                    echo "<p class='text-danger'>Receipts directory does not exist</p>";
                }
                ?>
            </div>
        </div>
        
        <div class="mt-3">
            <a href="index.php" class="btn btn-secondary">Back to Homepage</a>
            <a href="fix_receipt_paths.php" class="btn btn-warning">Fix Receipt Paths in Database</a>
        </div>
    </div>
</body>
</html>

