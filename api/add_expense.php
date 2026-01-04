<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDBConnection();
    
    $amount = floatval($data['amount']);
    $category_id = intval($data['category_id']);
    $description = $conn->real_escape_string($data['description']);
    $date = $conn->real_escape_string($data['date']);
    $user_id = 1; // For demo
    
    $sql = "INSERT INTO expenses (amount, category_id, description, expense_date) 
            VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("diss", $amount, $category_id, $description, $date);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    
    $stmt->close();
    $conn->close();
}
?>