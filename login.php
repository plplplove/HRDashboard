<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

$host = 'localhost';
$db = 'HRDASHBOARD';
$user = 'root';
$pass = ''; 

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error: " . $conn->connect_error);
}

$username = $_POST['username'];
$password = $_POST['password'];

$username = $conn->real_escape_string($username);

$hashed_password = hash('sha256', $password);

$sql = "SELECT * FROM users WHERE username = '$username' AND password = '$hashed_password'";
$result = $conn->query($sql);

if ($result->num_rows === 1) {
    $_SESSION['user'] = $username;
    header("Location: dashboard.php");
    exit();
} else {
    header("Location: login.html?error=1");
    exit();
}
?>