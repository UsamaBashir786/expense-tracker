<?php
// delete_expense.php
header('Content-Type: application/json');
require_once '../config/database.php';

// Accept both DELETE and GET methods
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$id && $_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = isset($input['id']) ? intval($input['id']) : null;
}

if (!$id || $id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid ID']);
    exit;
}

$conn = getDBConnection();

if (!$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$sql = "DELETE FROM expenses WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $affectedRows = $stmt->affected_rows;
    echo json_encode([
        'success' => true, 
        'message' => 'Expense deleted successfully',
        'affected_rows' => $affectedRows
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$stmt->close();
$conn->close();
?>