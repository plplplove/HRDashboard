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

$sql = "SELECT id, imie, nazwisko, dzial FROM pracownicy ORDER BY nazwisko, imie";
$result = $conn->query($sql);

if ($result) {
    $employees = array();
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode($employees);
} else {
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(['error' => 'Query failed: ' . $conn->error]);
}

$conn->close();
