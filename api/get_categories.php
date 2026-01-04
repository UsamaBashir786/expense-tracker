<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$conn = getDBConnection();
$result = $conn->query("SELECT id, name, color, icon FROM categories ORDER BY name");

$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

echo json_encode($categories);
$conn->close();
?>