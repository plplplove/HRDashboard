<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// Параметри підключення до бази
$host = 'localhost';
$db = 'HRDASHBOARD';
$user = 'root';
$pass = ''; // Залежить від вашої конфігурації

$conn = new mysqli($host, $user, $pass, $db);

// Перевірка підключення
if ($conn->connect_error) {
    die("Помилка підключення: " . $conn->connect_error);
}

// Отримуємо дані з форми
$username = $_POST['username'];
$password = $_POST['password'];

// Захищаємо від SQL-ін'єкцій
$username = $conn->real_escape_string($username);

// Хешуємо пароль
$hashed_password = hash('sha256', $password);

// Пошук користувача
$sql = "SELECT * FROM users WHERE username = '$username' AND password = '$hashed_password'";
$result = $conn->query($sql);

if ($result->num_rows === 1) {
    $_SESSION['user'] = $username;
    header("Location: dashboard.php");
    exit();
} else {
    // Redirect back to login page with error parameter instead of showing message directly
    header("Location: login.html?error=1");
    exit();
}
?>