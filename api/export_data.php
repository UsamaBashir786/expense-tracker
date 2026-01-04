<?php
require_once '../config/database.php';

$conn = getDBConnection();

$sql = "SELECT e.amount, c.name as category, e.description, e.expense_date 
        FROM expenses e 
        JOIN categories c ON e.category_id = c.id 
        ORDER BY e.expense_date DESC";
$result = $conn->query($sql);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="expenses_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Date', 'Category', 'Description', 'Amount (Rs)']);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['expense_date'],
        $row['category'],
        $row['description'],
        number_format($row['amount'], 2)
    ]);
}

fclose($output);
$conn->close();
?>