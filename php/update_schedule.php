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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$schedule_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$start_time = isset($_POST['godzina_rozpoczecia']) ? $_POST['godzina_rozpoczecia'] : '';
$end_time = isset($_POST['godzina_zakonczenia']) ? $_POST['godzina_zakonczenia'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : '';

if (!$schedule_id || !$start_time || !$end_time || !$status) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Wszystkie pola są wymagane']);
    exit();
}

$dbStatus = 'obecny'; 
switch($status) {
    case 'present':
        $dbStatus = 'obecny';
        break;
    case 'leave':
        $dbStatus = 'urlop';
        break;
    case 'vacation':
        $dbStatus = 'wakacje';
        break;
}

$sql = "UPDATE grafik_pracy 
        SET godzina_rozpoczecia = ?, 
            godzina_zakonczenia = ?, 
            status = ? 
        WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssi", $start_time, $end_time, $dbStatus, $schedule_id);
$result = $stmt->execute();

if ($result) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Harmonogram został zaktualizowany pomyślnie']);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Błąd podczas aktualizacji harmonogramu: ' . $conn->error]);
}

$conn->close();
