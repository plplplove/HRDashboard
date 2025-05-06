<?php
session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    exit('Brak dostępu');
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_GET['id'])) {
    http_response_code(400);
    exit('Nieprawidłowe żądanie');
}

$host = 'localhost';
$db = 'HRDASHBOARD';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    exit('Błąd połączenia z bazą danych');
}

$id = intval($_GET['id']);

$sql = "SELECT * FROM grafik_pracy WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    header('Content-Type: application/json');
    echo json_encode($row);
} else {
    http_response_code(404);
    exit('Nie znaleziono wpisu');
}

$stmt->close();
$conn->close();
?>
