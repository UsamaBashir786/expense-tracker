<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../includes/functions.php';

$conn = getDBConnection();
$period = isset($_GET['period']) ? intval($_GET['period']) : 30;

// Trend data - ensure we have data for all days in the period
$trend_data = [];
$dates = [];

// Generate all dates in the period
for ($i = $period - 1; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dates[] = $date;
    $trend_data[$date] = 0; // Initialize with 0
}

// Get actual expenses for these dates
$start_date = date('Y-m-d', strtotime("-" . ($period - 1) . " days"));
$end_date = date('Y-m-d');

$sql = "SELECT DATE(expense_date) as date, SUM(amount) as total 
        FROM expenses 
        WHERE expense_date BETWEEN '$start_date' AND '$end_date'
        GROUP BY DATE(expense_date) 
        ORDER BY date";

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $trend_data[$row['date']] = floatval($row['total']);
}

// Category data
$category_data = [];
$cat_result = $conn->query("SELECT c.id, c.name, c.color, c.icon, COALESCE(SUM(e.amount), 0) as total 
                           FROM categories c 
                           LEFT JOIN expenses e ON c.id = e.category_id 
                           WHERE e.expense_date BETWEEN '$start_date' AND '$end_date' OR e.id IS NULL
                           GROUP BY c.id, c.name, c.color, c.icon
                           ORDER BY total DESC");

while ($row = $cat_result->fetch_assoc()) {
    if ($row['total'] > 0) {
        $category_data[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'color' => $row['color'],
            'icon' => $row['icon'],
            'total' => floatval($row['total'])
        ];
    }
}

// Ensure we have at least some category data
if (empty($category_data)) {
    // Get all categories with 0 amount
    $cat_result = $conn->query("SELECT id, name, color, icon FROM categories ORDER BY name");
    while ($row = $cat_result->fetch_assoc()) {
        $category_data[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'color' => $row['color'],
            'icon' => $row['icon'],
            'total' => 0
        ];
    }
}

echo json_encode([
    'trend' => $trend_data,
    'categories' => $category_data
]);

$conn->close();
?>