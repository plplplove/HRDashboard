<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit();
}

$host = 'localhost';
$db = 'HRDASHBOARD';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['error' => 'Invalid schedule ID']);
    exit();
}

$scheduleId = intval($_GET['id']);

$sql = "SELECT 
    id, 
    pracownik_id, 
    data, 
    godzina_rozpoczecia, 
    godzina_zakonczenia, 
    status 
FROM grafik_pracy 
WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $scheduleId);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $row = $result->fetch_assoc()) {
    header('Content-Type: application/json');
    echo json_encode($row);
} else {
    header("HTTP/1.1 404 Not Found");
    echo json_encode(['error' => 'Schedule not found']);
}

$conn->close();
