<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit();
}

// Database connection
$host = 'localhost';
$db = 'HRDASHBOARD';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch actual data from database for statistics
// Count of employees
$employeeCountQuery = "SELECT COUNT(*) as total FROM pracownicy";
$employeeResult = $conn->query($employeeCountQuery);
$employeeCount = $employeeResult->fetch_assoc()['total'];

// Count of leave requests
$leaveRequestsQuery = "SELECT COUNT(*) as total FROM urlopy";
$leaveResult = $conn->query($leaveRequestsQuery);
$leaveRequestsCount = $leaveResult->fetch_assoc()['total'];

// Count of pending leave requests
$pendingLeaveQuery = "SELECT COUNT(*) as total FROM urlopy WHERE status LIKE '%oczek%'";
$pendingResult = $conn->query($pendingLeaveQuery);
$pendingLeaveCount = $pendingResult->fetch_assoc()['total'];

// Average work hours calculation
$avgHoursQuery = "SELECT 
                    AVG(TIME_TO_SEC(TIMEDIFF(godzina_zakonczenia, godzina_rozpoczecia))/3600) as avg_hours 
                  FROM grafik_pracy 
                  WHERE status = 'obecny'";
$avgHoursResult = $conn->query($avgHoursQuery);
$avgHours = 0;
if ($avgHoursResult && $row = $avgHoursResult->fetch_assoc()) {
    $avgHours = round($row['avg_hours'], 2);
}
if ($avgHours <= 0) $avgHours = 40.00; // Fallback if no data or calculation errors

// Get recent leave requests
$recentLeavesQuery = "SELECT 
                        u.id, 
                        p.imie, 
                        p.nazwisko, 
                        u.powod, 
                        u.data_rozpoczecia, 
                        u.status 
                      FROM urlopy u 
                      JOIN pracownicy p ON u.pracownik_id = p.id 
                      ORDER BY u.data_rozpoczecia DESC 
                      LIMIT 3";
$recentLeavesResult = $conn->query($recentLeavesQuery);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>System HR - Panel zarządzania</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/leave.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
    /* Add this in the appropriate place where dark theme styling is defined */
    body.theme-dark .leave-table-container {
        background-color: #2b2b40;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        border: none;
        border-radius: 8px;
    }

    body.theme-dark .leave-table th {
        background-color: #323248;
        color: #ffffff;
        border-bottom: 1px solid #3a3a5a;
    }

    body.theme-dark .leave-table td {
        border-bottom-color: #3a3a5a;
        color: #ffffff;
    }

    body.theme-dark .leave-table tr:hover {
        background-color: #323248;
    }

    /* Status badges in dark theme - more vibrant colors */
    body.theme-dark .status-badge.status-pending {
        background-color: #392f28;
        color: #ffb74d;
    }

    body.theme-dark .status-badge.status-approved {
        background-color: #0d392e;
        color: #00e396;
    }

    body.theme-dark .status-badge.status-rejected {
        background-color: #3e1a1a;
        color: #ff4d6d;
    }

    body.theme-dark .status-badge.status-unknown {
        background-color: #333349;
        color: #b6b6c9;
    }

    /* Fix alignment of action icons in the dashboard table */
    .leave-table .actions-cell .actions {
        display: flex;
        gap: 10px;
        justify-content: flex-start; /* Align to the left instead of center */
        padding-left: 5px; /* Add slight padding from the left */
    }
    
    /* Ensure the action column header and content are aligned */
    .leave-table th:last-child {
        text-align: left;
        padding-left: 15px;
    }
    
    .leave-table .actions-cell {
        text-align: left;
        padding-left: 8px;
    }
    </style>
</head>
<body class="theme-light">
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="logo">
                <h2>HR System</h2>
            </div>
            <div class="menu">
                <a href="dashboard.php" class="menu-item active">
                    <i class="fas fa-home"></i>
                    <span>Strona główna</span>
                </a>
                <a href="manage_employees.php" class="menu-item">
                    <i class="fas fa-users"></i>
                    <span>Zarządzaj pracownikami</span>
                </a>
                <a href="manage_time.php" class="menu-item">
                    <i class="fas fa-clock"></i>
                    <span>Zarządzaj czasem pracy</span>
                </a>
                <a href="manage_leave.php" class="menu-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Wnioski urlopowe</span>
                </a>
            </div>
        </div>
        <div class="main-content">
            <div class="header">
                <div class="welcome-message">
                    Witaj w systemie HR
                </div>
                <div class="user-info">
                    <button class="theme-toggle" id="themeToggle" title="Przełącz motyw">
                        <i class="fas fa-sun"></i>
                    </button>
                    <span class="user-name" id="userNameDropdown"><?= htmlspecialchars($_SESSION['user']) ?></span>
                    <div class="user-dropdown" id="userDropdown">
                        <button type="button" id="changePasswordBtn">
                            <i class="fas fa-key"></i> Zmień hasło
                        </button>
                        <a href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Wyloguj się
                        </a>
                    </div>
                    <div class="avatar">
                        <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="User Avatar">
                    </div>
                </div>
            </div>
            
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Pracownicy</h3>
                        <div class="stat-value"><?= $employeeCount ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Średni czas pracy</h3>
                        <div class="stat-value"><?= $avgHours ?>h</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Wnioski urlopowe</h3>
                        <div class="stat-value"><?= $leaveRequestsCount ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Oczekujące wnioski</h3>
                        <div class="stat-value"><?= $pendingLeaveCount ?></div>
                    </div>
                </div>
            </div>
            
            <div class="table-section">
                <div class="card">
                    <div class="card-header">
                        <h2>Ostatnie wnioski urlopowe</h2>
                    </div>
                    <div class="leave-table-container">
                        <table class="leave-table">
                            <thead>
                                <tr>
                                    <th>Pracownik</th>
                                    <th>Typ</th>
                                    <th>Data początkowa</th>
                                    <th>Status</th>
                                    <th>Akcje</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($recentLeavesResult && $recentLeavesResult->num_rows > 0) {
                                    while ($leave = $recentLeavesResult->fetch_assoc()) {
                                        // Define status class and text
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
                                            // Add approve/reject buttons for pending requests
                                            $actions = '
                                                <div class="actions">
                                                    <i class="fas fa-check-circle approve-btn" title="Zatwierdź"></i>
                                                    <i class="fas fa-times-circle reject-btn" title="Odrzuć"></i>
                                                </div>
                                            ';
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
            </div>
        </div>
    </div>
    
    <!-- Notification container -->
    <div id="notificationContainer"></div>

    <!-- Make sure these scripts are loaded and not blocked -->
    <script src="js/common.js"></script>
    <script src="js/dashboard.js"></script>
</body>
</html>
<?php $conn->close(); ?>