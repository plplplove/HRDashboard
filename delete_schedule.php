<?php
// Enable detailed error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check session
session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    exit(json_encode(["success" => false, "message" => "Brak dostępu"]));
}

header('Content-Type: application/json');

// Database connection parameters
$host = 'localhost';
$db = 'HRDASHBOARD';
$user = 'root';
$pass = '';

// Get schedule ID from request
$scheduleId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Log the requested ID
error_log("Attempting to delete schedule with ID: " . $scheduleId);

// Validate ID
if ($scheduleId <= 0) {
    http_response_code(400);
    exit(json_encode(["success" => false, "message" => "Nieprawidłowe ID harmonogramu"]));
}

// Connect to database
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    http_response_code(500);
    exit(json_encode(["success" => false, "message" => "Błąd połączenia z bazą danych"]));
}

// Use simple direct query for reliability
$sql = "DELETE FROM grafik_pracy WHERE id = $scheduleId";
error_log("Executing SQL: $sql");

if ($conn->query($sql)) {
    $affectedRows = $conn->affected_rows;
    error_log("Affected rows: " . $affectedRows);
    
    if ($affectedRows > 0) {
        echo json_encode([
            "success" => true, 
            "message" => "Harmonogram został usunięty pomyślnie",
            "id" => $scheduleId,
            "rows" => $affectedRows
        ]);
    } else {
        echo json_encode([
            "success" => false, 
            "message" => "Nie znaleziono harmonogramu o ID: " . $scheduleId
        ]);
    }
} else {
    error_log("SQL Error: " . $conn->error);
    echo json_encode([
        "success" => false,
        "message" => "Błąd podczas usuwania: " . $conn->error
    ]);
}

$conn->close();
?>
