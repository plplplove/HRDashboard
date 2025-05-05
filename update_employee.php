<?php
session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    exit('Brak dostępu');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metoda niedozwolona');
}

// Połączenie z bazą danych
$host = 'localhost';
$db = 'HRDASHBOARD';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    exit('Błąd połączenia z bazą danych');
}

// Pobierz ID pracownika
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
    http_response_code(400);
    exit('Nieprawidłowe ID pracownika');
}

// Pobierz dane z formularza
$imie = isset($_POST['imie']) ? $conn->real_escape_string(trim($_POST['imie'])) : '';
$nazwisko = isset($_POST['nazwisko']) ? $conn->real_escape_string(trim($_POST['nazwisko'])) : '';
$dzial = isset($_POST['dzial']) ? $conn->real_escape_string(trim($_POST['dzial'])) : '';
$stanowisko = isset($_POST['stanowisko']) ? $conn->real_escape_string(trim($_POST['stanowisko'])) : '';
$telefon = isset($_POST['telefon']) ? $conn->real_escape_string(trim($_POST['telefon'])) : '';
$email = isset($_POST['email']) ? $conn->real_escape_string(trim($_POST['email'])) : '';
$urlop = isset($_POST['urlop']) ? 1 : 0;

// Walidacja danych
if (empty($imie) || empty($nazwisko) || empty($dzial) || empty($stanowisko)) {
    http_response_code(400);
    exit('Wypełnij wszystkie wymagane pola');
}

// Aktualizacja danych w bazie
$sql = "UPDATE pracownicy SET imie = ?, nazwisko = ?, dzial = ?, 
        stanowisko = ?, telefon = ?, email = ?, urlop = ? WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssii", $imie, $nazwisko, $dzial, $stanowisko, $telefon, $email, $urlop, $id);

if ($stmt->execute()) {
    http_response_code(200);
    exit('Pracownik zaktualizowany pomyślnie');
} else {
    http_response_code(500);
    exit('Błąd podczas aktualizacji pracownika: ' . $conn->error);
}
?>
