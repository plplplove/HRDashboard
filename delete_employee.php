<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_employees.php");
    exit();
}

$host = 'localhost';
$db = 'HRDASHBOARD';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$employeeId = intval($_GET['id']);

$sql = "DELETE FROM pracownicy WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employeeId);

if ($stmt->execute()) {
    $_SESSION['delete_success'] = true;
    header("Location: manage_employees.php");
} else {
    $_SESSION['delete_error'] = true;
    header("Location: manage_employees.php");
}

$stmt->close();
$conn->close();
?>
