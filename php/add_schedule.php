<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit();
}

error_reporting(0);
header('Content-Type: application/json');

$host = 'localhost';
$db = 'HRDASHBOARD';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$pracownik_id = isset($_POST['pracownik_id']) ? intval($_POST['pracownik_id']) : 0;
$selectedDate = isset($_POST['selectedDate']) ? $_POST['selectedDate'] : '';
$start_time = isset($_POST['godzina_rozpoczecia']) ? $_POST['godzina_rozpoczecia'] : '';
$end_time = isset($_POST['godzina_zakonczenia']) ? $_POST['godzina_zakonczenia'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : '';

if (!$pracownik_id || !$selectedDate || !$start_time || !$end_time || !$status) {
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

$checkSql = "SELECT id FROM grafik_pracy WHERE pracownik_id = ? AND data = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("is", $pracownik_id, $selectedDate);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Ten pracownik ma już wpis w grafiku na wybrany dzień']);
    exit();
}

$sql = "INSERT INTO grafik_pracy (pracownik_id, data, godzina_rozpoczecia, godzina_zakonczenia, status) 
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("issss", $pracownik_id, $selectedDate, $start_time, $end_time, $dbStatus);
$result = $stmt->execute();

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Harmonogram został dodany pomyślnie']);
} else {
    echo json_encode(['success' => false, 'message' => 'Błąd podczas dodawania harmonogramu: ' . $conn->error]);
}

$conn->close();
