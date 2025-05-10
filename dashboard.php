<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'HRDASHBOARD');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$employeeCount = $conn->query("SELECT COUNT(*) as total FROM pracownicy")->fetch_assoc()['total'];
$leaveRequestsCount = $conn->query("SELECT COUNT(*) as total FROM urlopy")->fetch_assoc()['total'];
$pendingLeaveCount = $conn->query("SELECT COUNT(*) as total FROM urlopy WHERE status LIKE '%oczek%'")->fetch_assoc()['total'];

$avgHoursResult = $conn->query("SELECT AVG(TIME_TO_SEC(TIMEDIFF(godzina_zakonczenia, godzina_rozpoczecia))/3600) as avg_hours FROM grafik_pracy WHERE status = 'obecny'");
$avgHours = ($avgHoursResult && $row = $avgHoursResult->fetch_assoc()) ? round($row['avg_hours'], 2) : 40.00;
if ($avgHours <= 0) $avgHours = 40.00;

$recentLeavesResult = $conn->query("SELECT u.id, p.imie, p.nazwisko, u.powod, u.data_rozpoczecia, u.status FROM urlopy u JOIN pracownicy p ON u.pracownik_id = p.id ORDER BY u.data_rozpoczecia DESC LIMIT 3");
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>System HR - Panel zarządzania</title>
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/leave.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="theme-light">
<div class="dashboard-container">
    <aside class="sidebar">
        <div class="logo"><h2>HR System</h2></div>
        <nav class="menu">
            <a href="dashboard.php" class="menu-item active"><i class="fas fa-home"></i><span>Strona główna</span></a>
            <a href="manage_employees.php" class="menu-item"><i class="fas fa-users"></i><span>Zarządzaj pracownikami</span></a>
            <a href="manage_time.php" class="menu-item"><i class="fas fa-clock"></i><span>Zarządzaj czasem pracy</span></a>
            <a href="manage_leave.php" class="menu-item"><i class="fas fa-calendar-alt"></i><span>Wnioski urlopowe</span></a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="header">
            <h1 class="welcome-message">Witaj w systemie HR</h1>
            <div class="user-info">
                <div class="user-box" id="userToggle">
                    <i class="fas fa-sun theme-icon" id="themeIcon"></i>
                    <span class="user-name"> <?= htmlspecialchars($_SESSION['user']) ?> </span>
                    <i class="fas fa-chevron-down dropdown-icon" id="dropdownToggle"></i>
                    <div class="avatar">
                        <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="User Avatar">
                    </div>
                </div>
                <div class="user-dropdown" id="userDropdown">
                    <a href='php/logout.php'><i class="fas fa-sign-out-alt"></i> Wyloguj się</a>
                </div>
            </div>
        </header>

        <section class="stats-container">
            <div class="stat-card"><div class="stat-icon"><i class="fas fa-users"></i></div><div class="stat-info"><h3>Pracownicy</h3><div class="stat-value"><?= $employeeCount ?></div></div></div>
            <div class="stat-card"><div class="stat-icon"><i class="fas fa-clock"></i></div><div class="stat-info"><h3>Średni czas pracy</h3><div class="stat-value"><?= $avgHours ?>h</div></div></div>
            <div class="stat-card"><div class="stat-icon"><i class="fas fa-calendar-check"></i></div><div class="stat-info"><h3>Wnioski urlopowe</h3><div class="stat-value"><?= $leaveRequestsCount ?></div></div></div>
            <div class="stat-card"><div class="stat-icon"><i class="fas fa-hourglass-half"></i></div><div class="stat-info"><h3>Oczekujące wnioski</h3><div class="stat-value"><?= $pendingLeaveCount ?></div></div></div>
        </section>

        <section class="table-section">
            <div class="card">
                <div class="card-header"><h2>Ostatnie wnioski urlopowe</h2></div>
                <div class="leave-table-container">
                    <table class="leave-table">
                        <thead>
                        <tr><th>Pracownik</th><th>Typ</th><th>Data początkowa</th><th>Status</th><th>Akcje</th></tr>
                        </thead>
                        <tbody>
                        <?php
                        if ($recentLeavesResult && $recentLeavesResult->num_rows > 0) {
                            while ($leave = $recentLeavesResult->fetch_assoc()) {
                                $statusClass = 'status-pending';
                                $statusText = 'Oczekujący';
                                $actions = '<i class="fas fa-eye"></i>';
                                $statusLower = strtolower($leave['status']);

                                if (strpos($statusLower, 'zatwierdz') !== false) {
                                    $statusClass = 'status-approved';
                                    $statusText = 'Zatwierdzony';
                                    $actions = '<span class="action-disabled">Zatwierdzony</span>';
                                } elseif (strpos($statusLower, 'odrzuc') !== false) {
                                    $statusClass = 'status-rejected';
                                    $statusText = 'Odrzucony';
                                    $actions = '<span class="action-disabled">Odrzucony</span>';
                                } elseif (strpos($statusLower, 'oczek') !== false) {
                                    $actions = '<div class="actions">
                                        <i class="fas fa-check-circle approve-btn"></i>
                                        <i class="fas fa-times-circle reject-btn"></i>
                                    </div>';
                                }

                                echo '<tr>
                                    <td>' . htmlspecialchars($leave['nazwisko'] . ' ' . $leave['imie']) . '</td>
                                    <td>' . htmlspecialchars($leave['powod']) . '</td>
                                    <td>' . date('d-m-Y', strtotime($leave['data_rozpoczecia'])) . '</td>
                                    <td><span class="status-badge ' . $statusClass . '">' . $statusText . '</span></td>
                                    <td class="actions-cell">' . $actions . '</td>
                                </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="5" class="no-data">Brak wniosków urlopowych</td></tr>';
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="manage_leave.php" class="view-all-btn">Zobacz wszystkie wnioski</a>
                </div>
            </div>
        </section>
    </main>
</div>
<div id="notificationContainer"></div>
<script src="js/common.js"></script>
<script src="js/dashboard.js"></script>
</body>
</html>
<?php $conn->close(); ?>