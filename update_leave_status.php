<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    exit(json_encode(["success" => false, "message" => "Brak dostępu"]));
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(["success" => false, "message" => "Metoda niedozwolona"]));
}

$leaveId = isset($_POST['id']) ? intval($_POST['id']) : 0;
$newStatus = isset($_POST['status']) ? $_POST['status'] : '';

if ($leaveId <= 0) {
    http_response_code(400);
    exit(json_encode(["success" => false, "message" => "Nieprawidłowe ID wniosku"]));
}

if (!in_array($newStatus, ['zatwierdzony', 'odrzucony'])) {
    http_response_code(400);
    exit(json_encode(["success" => false, "message" => "Nieprawidłowy status"]));
}

$host = 'localhost';
$db = 'HRDASHBOARD';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    exit(json_encode(["success" => false, "message" => "Błąd połączenia z bazą danych"]));
}

$checkSql = "SELECT status FROM urlopy WHERE id = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("i", $leaveId);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows === 0) {
    $checkStmt->close();
    http_response_code(404);
    exit(json_encode(["success" => false, "message" => "Nie znaleziono wniosku o podanym ID"]));
}

$row = $result->fetch_assoc();
if ($row['status'] !== 'oczekujacy') {
    $checkStmt->close();
    http_response_code(400);
    exit(json_encode([
        "success" => false, 
        "message" => "Tylko wnioski o statusie 'oczekujący' mogą być zmienione"
    ]));
}

$checkStmt->close();


$sql = "UPDATE urlopy SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $newStatus, $leaveId);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            "success" => true,
            "message" => $newStatus === 'zatwierdzony' 
                ? "Wniosek został zatwierdzony" 
                : "Wniosek został odrzucony",
            "status" => $newStatus
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Nie udało się zaktualizować statusu wniosku"
        ]);
    }
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Błąd podczas aktualizacji statusu: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
