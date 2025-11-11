<?php
/**
 * Helper Functions
 * KopuGive - MRSM Kota Putra Donation System
 */

// Sanitize input
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Format currency (MYR)
function formatCurrency($amount) {
    return 'RM ' . number_format($amount, 2);
}

// Calculate percentage
function calculatePercentage($current, $target) {
    if ($target == 0) return 0;
    $percentage = ($current / $target) * 100;
    return min(100, round($percentage, 2));
}

// Format date
function formatDate($date, $format = 'd M Y') {
    return date($format, strtotime($date));
}

// Time ago function
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    if ($diff < 2592000) return floor($diff / 604800) . ' weeks ago';
    
    return formatDate($datetime);
}

// Generate random string
function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length / 2));
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Redirect function
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Flash message
function setFlashMessage($type, $message) {
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_message'] = $message;
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = [
            'type' => $_SESSION['flash_type'],
            'message' => $_SESSION['flash_message']
        ];
        unset($_SESSION['flash_type']);
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

// Upload file
function uploadFile($file, $uploadDir = 'uploads/', $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf']) {
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'message' => 'Invalid file upload'];
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error occurred'];
    }
    
    if ($file['size'] > 5000000) { // 5MB
        return ['success' => false, 'message' => 'File size exceeds 5MB'];
    }
    
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($fileExt, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    $newFilename = uniqid() . '_' . time() . '.' . $fileExt;
    
    // Convert relative path to absolute path from project root
    $projectRoot = dirname(__DIR__) . '/';
    $absoluteUploadDir = $projectRoot . $uploadDir;
    $destination = $absoluteUploadDir . $newFilename;
    
    if (!is_dir($absoluteUploadDir)) {
        mkdir($absoluteUploadDir, 0755, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // Return the relative path for database storage
        return ['success' => true, 'filename' => $newFilename, 'path' => $uploadDir . $newFilename];
    }
    
    return ['success' => false, 'message' => 'Failed to move uploaded file'];
}

// Log activity
function logActivity($db, $userId, $action, $entityType = null, $entityId = null) {
    try {
        $stmt = $db->prepare("INSERT INTO activity_logs (user_id, action, entity_type, entity_id, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $userId,
            $action,
            $entityType,
            $entityId,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    } catch (Exception $e) {
        error_log("Activity log error: " . $e->getMessage());
    }
}

// Get setting value
function getSetting($db, $key, $default = null) {
    try {
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

// Pagination
function paginate($totalItems, $itemsPerPage, $currentPage) {
    $totalPages = ceil($totalItems / $itemsPerPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $itemsPerPage;
    
    return [
        'total_items' => $totalItems,
        'items_per_page' => $itemsPerPage,
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'offset' => $offset,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}
?>

