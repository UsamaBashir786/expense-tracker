<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$conn = getDBConnection();

// Calculate daily average for current month
$current_month = date('Y-m');
$result = $conn->query("
    SELECT 
        COUNT(DISTINCT DATE(expense_date)) as days,
        SUM(amount) as total,
        ROUND(SUM(amount) / COUNT(DISTINCT DATE(expense_date)), 2) as daily_avg
    FROM expenses 
    WHERE DATE_FORMAT(expense_date, '%Y-%m') = '$current_month'
");
$avg = $result->fetch_assoc();

// Get highest spending day
$result = $conn->query("
    SELECT 
        DATE(expense_date) as date,
        SUM(amount) as total
    FROM expenses 
    GROUP BY DATE(expense_date) 
    ORDER BY total DESC 
    LIMIT 1
");
$highest = $result->fetch_assoc();

// Calculate savings rate (example: assume income is 50000)
$income = 50000; // This could come from user settings
$result = $conn->query("
    SELECT SUM(amount) as total_expenses 
    FROM expenses 
    WHERE DATE_FORMAT(expense_date, '%Y-%m') = '$current_month'
");
$expenses = $result->fetch_assoc();
$savings = $income - $expenses['total_expenses'];
$savings_rate = $income > 0 ? round(($savings / $income) * 100, 1) : 0;

$insights = [
    'daily_avg' => $avg['daily_avg'] ?? 0,
    'highest_day' => $highest ? date('M d', strtotime($highest['date'])) . ': Rs ' . $highest['total'] : 'No data',
    'savings_rate' => $savings_rate . '%'
];

echo json_encode($insights);
$conn->close();
?>