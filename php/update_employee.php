<?php
session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    exit('Brak dostępu');
}

if (!isset($_POST['id']) || !isset($_POST['imie']) || !isset($_POST['nazwisko']) || 
    !isset($_POST['dzial']) || !isset($_POST['stanowisko'])) {
    http_response_code(400);
    exit('Niepoprawne dane');
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

$id = intval($_POST['id']);
$imie = $conn->real_escape_string($_POST['imie']);
$nazwisko = $conn->real_escape_string($_POST['nazwisko']);
$dzial = $conn->real_escape_string($_POST['dzial']);
$stanowisko = $conn->real_escape_string($_POST['stanowisko']);
$telefon = isset($_POST['telefon']) ? $conn->real_escape_string($_POST['telefon']) : '';
$email = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : '';
$urlop = isset($_POST['urlop']) ? 1 : 0;

$sql = "UPDATE pracownicy SET 
        imie = ?,
        nazwisko = ?,
        dzial = ?,
        stanowisko = ?,
        telefon = ?,
        email = ?,
        urlop = ?
        WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssiii", $imie, $nazwisko, $dzial, $stanowisko, $telefon, $email, $urlop, $id);

if ($stmt->execute()) {
    $_SESSION['edit_success'] = true;
    http_response_code(200);
    echo 'Dane pracownika zaktualizowane pomyślnie';
} else {
    $_SESSION['edit_error'] = true;
    http_response_code(500);
    echo 'Błąd podczas aktualizacji danych: ' . $stmt->error;
}

$stmt->close();
$conn->close();
?>
