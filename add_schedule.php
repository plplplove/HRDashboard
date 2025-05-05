<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    exit(json_encode(["success" => false, "message" => "Brak dostępu"]));
}

// Verify we have POST data
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(["success" => false, "message" => "Metoda niedozwolona"]));
}

// Database connection
$host = 'localhost';
$db = 'HRDASHBOARD';
$user = 'root';
$pass = '';

try {
    $conn = new mysqli($host, $user, $pass, $db);
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    // Get and sanitize data
    $pracownikId = isset($_POST['pracownik_id']) ? intval($_POST['pracownik_id']) : 0;
    $date = isset($_POST['selectedDate']) ? $conn->real_escape_string($_POST['selectedDate']) : '';
    $startTime = isset($_POST['godzina_rozpoczecia']) ? $conn->real_escape_string($_POST['godzina_rozpoczecia']) : '';
    $endTime = isset($_POST['godzina_zakonczenia']) ? $conn->real_escape_string($_POST['godzina_zakonczenia']) : '';
    $status = isset($_POST['status']) ? $conn->real_escape_string($_POST['status']) : '';
    
    // Validate data
    if ($pracownikId <= 0) {
        throw new Exception("Nie wybrano pracownika");
    }
    
    if (empty($date) || empty($startTime) || empty($endTime) || empty($status)) {
        throw new Exception("Wszystkie pola są wymagane");
    }
    
    // Map status values to database values
    switch ($status) {
        case 'present':
            $dbStatus = 'obecny';
            break;
        case 'leave':
            $dbStatus = 'urlop';
            break;
        case 'vacation':
            $dbStatus = 'wakacje';
            break;
        default:
            $dbStatus = $status;
    }
    
    // Check if employee already has an entry for this date
    $checkSql = "SELECT id FROM grafik_pracy WHERE pracownik_id = ? AND data = ?";
    $checkStmt = $conn->prepare($checkSql);
    
    if (!$checkStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $checkStmt->bind_param("is", $pracownikId, $date);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $checkStmt->close();
        throw new Exception("Dla tego pracownika istnieje już wpis na wybrany dzień");
    }
    
    $checkStmt->close();
    
    // Insert new record
    $insertSql = "INSERT INTO grafik_pracy (pracownik_id, data, godzina_rozpoczecia, godzina_zakonczenia, status) 
                 VALUES (?, ?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertSql);
    
    if (!$insertStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $insertStmt->bind_param("issss", $pracownikId, $date, $startTime, $endTime, $dbStatus);
    
    if (!$insertStmt->execute()) {
        throw new Exception("Execute failed: " . $insertStmt->error);
    }
    
    $insertStmt->close();
    $conn->close();
    
    // Success response
    echo json_encode([
        "success" => true, 
        "message" => "Harmonogram został dodany pomyślnie"
    ]);
    
} catch (Exception $e) {
    // Error response
    http_response_code(400);
    echo json_encode([
        "success" => false, 
        "message" => $e->getMessage()
    ]);
    
    if (isset($conn)) {
        $conn->close();
    }
}
?>
