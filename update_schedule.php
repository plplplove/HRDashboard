<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    exit(json_encode(["success" => false, "message" => "Brak dostępu"]));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(["success" => false, "message" => "Metoda niedozwolona"]));
}

$conn = new mysqli('localhost', 'root', '', 'HRDASHBOARD');
if ($conn->connect_error) {
    http_response_code(500);
    exit(json_encode(["success" => false, "message" => "Błąd połączenia z bazą danych: " . $conn->connect_error]));
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$start = $_POST['godzina_rozpoczecia'] ?? '';
$end = $_POST['godzina_zakonczenia'] ?? '';
$status = $_POST['status'] ?? '';

if ($id <= 0 || !$start || !$end || !$status) {
    http_response_code(400);
    exit(json_encode(["success" => false, "message" => "Nieprawidłowe lub brakujące dane"]));
}

$statusMap = [
    'present' => 'obecny',
    'leave' => 'urlop',
    'vacation' => 'wakacje'
];
$dbStatus = $statusMap[$status] ?? $status;

$check = $conn->prepare("SELECT id FROM grafik_pracy WHERE id = ?");
$check->bind_param("i", $id);
$check->execute();
$result = $check->get_result();
$check->close();

if ($result->num_rows === 0) {
    http_response_code(404);
    exit(json_encode(["success" => false, "message" => "Rekord nie istnieje"]));
}

$stmt = $conn->prepare("UPDATE grafik_pracy SET godzina_rozpoczecia = ?, godzina_zakonczenia = ?, status = ? WHERE id = ?");
$stmt->bind_param("sssi", $start, $end, $dbStatus, $id);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => $stmt->affected_rows > 0
            ? "Harmonogram został zaktualizowany pomyślnie"
            : "Nie wprowadzono zmian"
    ]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Błąd SQL: " . $stmt->error]);
}
$stmt->close();
$conn->close();
?>