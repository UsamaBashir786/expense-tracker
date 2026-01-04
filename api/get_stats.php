<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$conn = getDBConnection();

// Check connection
if ($conn->connect_error) {
    echo json_encode([
        'error' => 'Database connection failed',
        'total' => 0,
        'month' => 0,
        'count' => 0,
        'top_category' => 'None',
        'top_category_icon' => '📊'
    ]);
    exit;
}

$stats = [];

// Total expenses
$result = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM expenses");
if ($result) {
    $row = $result->fetch_assoc();
    $stats['total'] = floatval($row['total']);
} else {
    $stats['total'] = 0;
}

// This month expenses
$current_month = date('Y-m');
$result = $conn->query("SELECT COALESCE(SUM(amount), 0) as month_total FROM expenses WHERE DATE_FORMAT(expense_date, '%Y-%m') = '$current_month'");
if ($result) {
    $row = $result->fetch_assoc();
    $stats['month'] = floatval($row['month_total']);
} else {
    $stats['month'] = 0;
}

// Total count
$result = $conn->query("SELECT COUNT(*) as count FROM expenses");
if ($result) {
    $row = $result->fetch_assoc();
    $stats['count'] = intval($row['count']);
} else {
    $stats['count'] = 0;
}

// Top category
$result = $conn->query("
    SELECT c.name, c.icon, COALESCE(SUM(e.amount), 0) as total 
    FROM expenses e 
    JOIN categories c ON e.category_id = c.id 
    GROUP BY c.id, c.name, c.icon
    ORDER BY total DESC 
    LIMIT 1
");

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $stats['top_category'] = $row['name'];
    $stats['top_category_icon'] = $row['icon'];
} else {
    $stats['top_category'] = 'None';
    $stats['top_category_icon'] = '📊';
}


// Add debug info
$stats['debug'] = [
    'database' => 'connected',
    'expenses_table' => $conn->query("SHOW TABLES LIKE 'expenses'")->num_rows > 0,
    'categories_table' => $conn->query("SHOW TABLES LIKE 'categories'")->num_rows > 0
];

echo json_encode($stats);
$conn->close();
?>