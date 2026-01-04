<?php
// store_lead.php (No database version)
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $name = isset($input['name']) ? trim($input['name']) : '';
    $email = isset($input['email']) ? trim($input['email']) : '';
    $phone = isset($input['phone']) ? trim($input['phone']) : '';
    $company = isset($input['company']) ? trim($input['company']) : '';
    $requirements = isset($input['requirements']) ? trim($input['requirements']) : '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    
    try {
        // Create logs directory if it doesn't exist
        $logDir = dirname(__DIR__) . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Log lead to file
        $logFile = $logDir . '/app_leads.csv';
        $timestamp = date('Y-m-d H:i:s');
        
        // Create CSV header if file doesn't exist
        if (!file_exists($logFile)) {
            $header = "Timestamp,Name,Email,Phone,Company,Requirements,IP Address\n";
            file_put_contents($logFile, $header, FILE_APPEND);
        }
        
        // Append lead data
        $csvData = [
            $timestamp,
            str_replace(',', ';', $name),
            str_replace(',', ';', $email),
            str_replace(',', ';', $phone),
            str_replace(',', ';', $company),
            str_replace([',', "\n", "\r"], [';', ' ', ' '], $requirements),
            $ip
        ];
        
        $logLine = '"' . implode('","', $csvData) . '"' . "\n";
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
        
        // Send email notification (optional)
        sendLeadNotification($name, $email, $phone, $company, $requirements);
        
        echo json_encode(['success' => true, 'message' => 'Lead saved successfully']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

function sendLeadNotification($name, $email, $phone, $company, $requirements) {
    // Replace with your email
    $to = "your.email@example.com";
    $subject = "New App Development Lead - Expense Tracker";
    
    $message = "
    <html>
    <head>
        <title>New App Development Lead</title>
    </head>
    <body>
        <h2>New Lead for Mobile App Development</h2>
        <p><strong>Name:</strong> {$name}</p>
        <p><strong>Email:</strong> {$email}</p>
        <p><strong>Phone:</strong> {$phone}</p>
        <p><strong>Company:</strong> {$company}</p>
        <p><strong>Requirements:</strong><br>{$requirements}</p>
        <p><strong>IP Address:</strong> {$_SERVER['REMOTE_ADDR']}</p>
        <p><strong>Date:</strong> " . date('Y-m-d H:i:s') . "</p>
        <br>
        <p>This lead downloaded the app proposal from Expense Tracker Pro.</p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Expense Tracker <noreply@expense-tracker.com>" . "\r\n";
    
    @mail($to, $subject, $message, $headers);
    
    // Also log email attempt
    $emailLog = dirname(__DIR__) . '/logs/email_notifications.log';
    $logMessage = date('Y-m-d H:i:s') . " - Email sent to {$to} for lead from {$name}\n";
    file_put_contents($emailLog, $logMessage, FILE_APPEND | LOCK_EX);
}
?>