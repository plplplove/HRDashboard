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
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid schedule ID']);
    exit();
}

$scheduleId = intval($_GET['id']);

$sql = "DELETE FROM grafik_pracy WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $scheduleId);
$result = $stmt->execute();

if ($result) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Harmonogram został usunięty pomyślnie']);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Błąd podczas usuwania harmonogramu: ' . $conn->error]);
}

$conn->close();
