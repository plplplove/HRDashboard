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
    die("Connection failed: " . $conn->connect_error);
}

$departments = $conn->query("SELECT DISTINCT dzial FROM pracownicy ORDER BY dzial");
$employees = $conn->query("SELECT id, imie, nazwisko FROM pracownicy ORDER BY nazwisko, imie");

$selectedMonth = $_GET['month'] ?? date('m');
$selectedYear = $_GET['year'] ?? date('Y');
$selectedDay = $_GET['day'] ?? date('d');
$selectedDate = sprintf('%04d-%02d-%02d', $selectedYear, $selectedMonth, $selectedDay);
$daysInMonth = date('t', strtotime($selectedDate));
$firstDayOfMonth = date('N', strtotime("$selectedYear-$selectedMonth-01"));

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if ($isAjax && $_GET['action'] === 'getEmployeesByDate') {
    $date = $_GET['date'] ?? date('Y-m-d');
    $stmt = $conn->prepare("SELECT g.id AS schedule_id, p.id, p.imie, p.nazwisko, p.dzial,
        g.godzina_rozpoczecia, g.godzina_zakonczenia, g.status,
        TIMEDIFF(g.godzina_zakonczenia, g.godzina_rozpoczecia) AS czas_pracy
        FROM grafik_pracy g
        INNER JOIN pracownicy p ON g.pracownik_id = p.id
        WHERE g.data = ? ORDER BY p.nazwisko, p.imie");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $czas = ($row['status'] !== 'leave') ? explode(':', $row['czas_pracy']) : [0, 0];
        $godziny = ($row['status'] !== 'leave') ? $czas[0] . 'h' . ($czas[1] !== '00' ? $czas[1] . 'm' : '') : '0h';
        $data[] = [
            'id' => $row['id'],
            'schedule_id' => $row['schedule_id'],
            'name' => $row['nazwisko'] . ' ' . $row['imie'],
            'department' => $row['dzial'],
            'start_time' => $row['status'] === 'leave' ? '00:00' : substr($row['godzina_rozpoczecia'], 0, 5),
            'end_time' => $row['status'] === 'leave' ? '00:00' : substr($row['godzina_zakonczenia'], 0, 5),
            'hours' => $godziny,
            'status' => $row['status']
        ];
    }
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>System HR - Zarządzanie czasem pracy</title>
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/forms-modals.css">
    <link rel="stylesheet" href="css/time_management.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="theme-light">
    <div class="dashboard-container">
    <aside class="sidebar">
        <div class="logo"><h2>HR System</h2></div>
        <nav class="menu">
            <a href="dashboard.php" class="menu-item"><i class="fas fa-home"></i><span>Strona główna</span></a>
            <a href="manage_employees.php" class="menu-item"><i class="fas fa-users"></i><span>Zarządzaj pracownikami</span></a>
            <a href="manage_time.php" class="menu-item active"><i class="fas fa-clock"></i><span>Zarządzaj czasem pracy</span></a>
            <a href="manage_leave.php" class="menu-item"><i class="fas fa-calendar-alt"></i><span>Wnioski urlopowe</span></a>
        </nav>
    </aside>
        <div class="main-content">
        <header class="header">
            <h1 class="welcome-message">Zarządzaj grafikami pracy</h1>
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
            <div class="time-management-container">
                <!-- Calendar View -->
                <div class="calendar-container">
                    <div class="calendar-wrapper">
                        <h3 class="calendar-section-title">Wybierz datę, aby zobaczyć harmonogram</h3>
                        <div class="filter-group date-navigation">
                            <div class="date-picker">
                                <button type="button" class="btn btn-icon" id="prevMonth">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <div class="current-date-display">
                                    <span id="currentDate"><?= date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)) ?></span>
                                    <input type="hidden" name="day" id="dayInput" value="<?= $selectedDay ?>">
                                    <input type="hidden" name="month" id="monthInput" value="<?= $selectedMonth ?>">
                                    <input type="hidden" name="year" id="yearInput" value="<?= $selectedYear ?>">
                                </div>
                                <button type="button" class="btn btn-icon" id="nextMonth">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="calendar" id="workCalendar">
                            <div class="calendar-header">
                                <div class="weekday">PON</div>
                                <div class="weekday">WT</div>
                                <div class="weekday">ŚR</div>
                                <div class="weekday">CZW</div>
                                <div class="weekday">PT</div>
                                <div class="weekday">S/B</div>
                                <div class="weekday">NE</div>
                            </div>
                            <div class="calendar-days">
                                <?php
                                for ($i = 1; $i < $firstDayOfMonth; $i++) {
                                    echo '<div class="calendar-day empty"></div>';
                                }
        
                                for ($day = 1; $day <= $daysInMonth; $day++) {
                                    $dateYmd = sprintf('%04d-%02d-%02d', $selectedYear, $selectedMonth, $day);
                                    $isToday = ($day == date('j') && $selectedMonth == date('m') && $selectedYear == date('Y'));
                                    $isSelected = ($day == $selectedDay);
                                    $isWeekend = date('N', strtotime($dateYmd)) >= 6;
                                    $dayClass = 'calendar-day';
                                    if ($isToday) $dayClass .= ' today';
                                    if ($isSelected) $dayClass .= ' selected';
                                    if ($isWeekend) $dayClass .= ' weekend';
                                    
                                    echo "<div class='$dayClass' data-date='$dateYmd'>";
                                    echo "<div class='day-number'>$day</div>";
                                    echo "</div>";
                                }
                                
                                $lastDayOfMonth = date('N', mktime(0, 0, 0, $selectedMonth, $daysInMonth, $selectedYear));
                                if ($lastDayOfMonth < 7) {
                                    for ($i = $lastDayOfMonth + 1; $i <= 7; $i++) {
                                        echo '<div class="calendar-day empty"></div>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="schedule-container">
                    <div class="card">
                        <div class="card-header">
                            <h2>Harmonogram pracowników - <span id="selectedDateDisplay"><?= date('d F Y', strtotime($selectedDate)) ?></span></h2>
                            <button class="btn btn-primary" id="addScheduleBtn">
                                <i class="fas fa-plus"></i> Dodaj wpis
                            </button>
                        </div>
                        <div class="time-table-container">
                            <table class="data-table time-table">
                                <thead>
                                    <tr>
                                        <th>Pracownik</th>
                                        <th>Dział</th>
                                        <th>Godzina rozpoczęcia</th>
                                        <th>Godzina zakończenia</th>
                                        <th>Łączna ilość godzin</th>
                                        <th>Status</th>
                                        <th>Akcje</th>
                                    </tr>
                                </thead>
                                <tbody id="employeeScheduleList">
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <div class="spinner">
                                                <i class="fas fa-spinner fa-spin"></i> Wczytywanie danych...
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="summary-section">
                            <div class="summary-item">
                                <span class="summary-label">Łączna ilość godzin:</span>
                                <span class="summary-value" id="totalHours">0h</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Ilość pracowników na grafiku:</span>
                                <span class="summary-value" id="totalEmployees">0</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Obecnych pracowników:</span>
                                <span class="summary-value" id="presentEmployees">0</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Pracowników na urlopie:</span>
                                <span class="summary-value" id="absentEmployees">0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div id="editScheduleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Edytuj harmonogram</h2>
                <button class="close-button" id="closeEditScheduleModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="editScheduleForm">
                    <input type="hidden" id="scheduleId" name="id">
                    <div class="form-group">
                        <label for="startTime">Godzina rozpoczęcia</label>
                        <input type="time" id="startTime" name="godzina_rozpoczecia" required>
                    </div>
                    <div class="form-group">
                        <label for="endTime">Godzina zakończenia</label>
                        <input type="time" id="endTime" name="godzina_zakonczenia" required>
                    </div>
                    <div class="form-group">
                        <label for="status">Status obecności</label>
                        <select id="status" name="status" required>
                            <option value="present">Obecny</option>
                            <option value="leave">Urlop</option>
                            <option value="vacation">Wakacje</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="saveScheduleBtn">
                    <i class="fas fa-save"></i> Zapisz zmiany
                </button>
                <button type="button" class="btn btn-secondary" id="cancelScheduleBtn">
                    <i class="fas fa-times"></i> Anuluj
                </button>
            </div>
        </div>
    </div>
    
    <div id="addScheduleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-plus-circle"></i> Dodaj harmonogram pracy</h2>
                <button class="close-button" id="closeAddScheduleModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="addScheduleForm">
                    <input type="hidden" id="selectedDate" name="selectedDate">
                    
                    <div class="form-group">
                        <label for="employeeSelect">Pracownik</label>
                        <select id="employeeSelect" name="pracownik_id" required>
                            <option value="">Wybierz pracownika</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="departmentDisplay">Dział</label>
                        <input type="text" id="departmentDisplay" disabled readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="addStartTime">Godzina rozpoczęcia</label>
                        <input type="time" id="addStartTime" name="godzina_rozpoczecia" required value="08:00">
                    </div>
                    
                    <div class="form-group">
                        <label for="addEndTime">Godzina zakończenia</label>
                        <input type="time" id="addEndTime" name="godzina_zakonczenia" required value="16:00">
                    </div>
                    
                    <div class="form-group">
                        <label for="addStatus">Status obecności</label>
                        <select id="addStatus" name="status" required>
                            <option value="present">Obecny</option>
                            <option value="leave">Urlop</option>
                            <option value="vacation">Wakacje</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="saveAddScheduleBtn">
                    <i class="fas fa-save"></i> Zapisz
                </button>
                <button type="button" class="btn btn-secondary" id="cancelAddScheduleBtn">
                    <i class="fas fa-times"></i> Anuluj
                </button>
            </div>
        </div>
    </div>
    
    <div id="deleteScheduleModal" class="modal">
        <div class="modal-content delete-modal">
            <div class="modal-header delete-header">
                <h2><i class="fas fa-exclamation-triangle"></i> Potwierdzenie usunięcia</h2>
                <button class="close-button" id="closeDeleteScheduleModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p>Czy na pewno chcesz usunąć ten wpis z harmonogramu?</p>
                <p class="warning-text">Ta operacja jest nieodwracalna.</p>
            </div>
            <div class="modal-footer">
                <button id="confirmDeleteScheduleBtn" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Tak, usuń
                </button>
                <button id="cancelDeleteScheduleBtn" class="btn btn-secondary">
                    <i class="fas fa-ban"></i> Anuluj
                </button>
            </div>
        </div>
    </div>
    
    <script src="js/calendar.js"></script>
    <script src="js/common.js"></script>
</body>
</html>
<?php $conn->close(); ?>
