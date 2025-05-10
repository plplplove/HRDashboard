<?php
session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    exit('Brak dostępu');
}

if (!isset($_POST['imie']) || !isset($_POST['nazwisko']) || !isset($_POST['dzial']) || !isset($_POST['stanowisko'])) {
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

$imie = $conn->real_escape_string($_POST['imie']);
$nazwisko = $conn->real_escape_string($_POST['nazwisko']);
$dzial = $conn->real_escape_string($_POST['dzial']);
$stanowisko = $conn->real_escape_string($_POST['stanowisko']);
$telefon = isset($_POST['telefon']) ? $conn->real_escape_string($_POST['telefon']) : '';
$email = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : '';
$urlop = isset($_POST['urlop']) ? 1 : 0;

$sql = "INSERT INTO pracownicy (imie, nazwisko, dzial, stanowisko, telefon, email, urlop) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssi", $imie, $nazwisko, $dzial, $stanowisko, $telefon, $email, $urlop);

if ($stmt->execute()) {
    $_SESSION['add_success'] = true;
    http_response_code(200);
    echo 'Pracownik dodany pomyślnie';
} else {
    $_SESSION['add_error'] = true;
    http_response_code(500);
    echo 'Błąd podczas dodawania pracownika: ' . $stmt->error;
}

$stmt->close();
$conn->close();
?>
