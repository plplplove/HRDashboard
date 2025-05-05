<?php
session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    exit(json_encode(["success" => false, "message" => "Brak dostępu"]));
}

// Database connection
$host = 'localhost';
$db = 'HRDASHBOARD';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    exit(json_encode(["success" => false, "message" => "Błąd połączenia z bazą danych"]));
}

// Get employees sorted by last name and first name
$sql = "SELECT id, imie, nazwisko, dzial FROM pracownicy ORDER BY nazwisko, imie";
$result = $conn->query($sql);

$employees = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
}

// Return as JSON
header('Content-Type: application/json');
echo json_encode($employees);

$conn->close();
?>
