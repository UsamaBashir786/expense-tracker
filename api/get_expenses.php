<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$conn = getDBConnection();
$user_id = 1; // For demo

$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$category = isset($_GET['category']) ? intval($_GET['category']) : null;

$sql = "SELECT e.*, c.name as category_name, c.icon, c.color 
        FROM expenses e 
        JOIN categories c ON e.category_id = c.id 
        WHERE 1=1";

if ($category) {
    $sql .= " AND e.category_id = $category";
}

$sql .= " ORDER BY e.expense_date DESC, e.created_at DESC LIMIT $limit";

$result = $conn->query($sql);
$expenses = [];

while ($row = $result->fetch_assoc()) {
    $expenses[] = [
        'id' => $row['id'],
        'amount' => $row['amount'],
        'category' => $row['category_name'],
        'category_id' => $row['category_id'],
        'icon' => $row['icon'],
        'color' => $row['color'],
        'description' => $row['description'],
        'date' => $row['expense_date'],
        'formatted_date' => date('M d, Y', strtotime($row['expense_date']))
    ];
}

echo json_encode($expenses);
$conn->close();
?>