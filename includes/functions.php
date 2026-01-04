<?php
// Helper functions for Expense Tracker

function formatCurrency($amount) {
    return 'Rs ' . number_format($amount, 2);
}

function getCategoryIcon($category_id) {
    $icons = [
        1 => '🍔', // Food & Dining
        2 => '✈️', // Travel & Transport
        3 => '📄', // Bills & Utilities
        4 => '🛍️', // Shopping
        5 => '🎬', // Entertainment
        6 => '💊', // Health & Medical
        7 => '📚', // Educationaa
        8 => '📦'  // Other
    ];
    
    return $icons[$category_id] ?? '📦';
}

function getCategoryColor($category_id) {
    $colors = [
        1 => '#ef4444',
        2 => '#3b82f6',
        3 => '#f59e0b',
        4 => '#ec4899',
        5 => '#8b5cf6',
        6 => '#10b981',
        7 => '#06b6d4',
        8 => '#6b7280'
    ];
    
    return $colors[$category_id] ?? '#6b7280';
}

function getCurrentUserId() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['user_id'] = 1; // Default user for demo
    }
    return $_SESSION['user_id'];
}

function sanitizeInput($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input);
    return $input;
}

function showJsonError($message) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}
?>