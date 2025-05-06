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

$deptQuery = "SELECT DISTINCT dzial FROM pracownicy ORDER BY dzial";
$departments = $conn->query($deptQuery);

$employeesQuery = "SELECT id, imie, nazwisko FROM pracownicy ORDER BY nazwisko, imie";
$employees = $conn->query($employeesQuery);

$selectedMonth = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
$selectedYear = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));
$selectedDay = isset($_GET['day']) ? intval($_GET['day']) : intval(date('d'));
$selectedEmployee = isset($_GET['employee']) ? intval($_GET['employee']) : 0;
$selectedDept = isset($_GET['department']) ? $_GET['department'] : '';

$selectedDate = sprintf('%04d-%02d-%02d', $selectedYear, $selectedMonth, $selectedDay);
$monthName = date('F', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear));
$daysInMonth = date('t', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear));
$firstDayOfMonth = date('N', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear));

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if ($isAjax && isset($_GET['action']) && $_GET['action'] === 'getEmployeesByDate') {
    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    
    $employeeSchedules = [];
    
    $sql = "SELECT g.id AS schedule_id, p.id, p.imie, p.nazwisko, p.dzial, g.godzina_rozpoczecia, g.godzina_zakonczenia, 
                   g.status, TIMEDIFF(g.godzina_zakonczenia, g.godzina_rozpoczecia) AS czas_pracy
            FROM grafik_pracy g
            INNER JOIN pracownicy p ON g.pracownik_id = p.id
            WHERE g.data = ?";
    
    $sql .= " ORDER BY p.nazwisko, p.imie";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Convert time difference to hours format
            $hours = '0h';
            if ($row['status'] != 'leave') {
                $timeParts = explode(':', $row['czas_pracy']);
                $hours = $timeParts[0] . 'h';
                if ($timeParts[1] != '00') {
                    $hours .= $timeParts[1] . 'm';
                }
            }
            
            $employeeSchedules[] = [
                'id' => $row['id'],
                'schedule_id' => $row['schedule_id'], // Include schedule_id
                'name' => $row['nazwisko'] . ' ' . $row['imie'],
                'department' => $row['dzial'],
                'start_time' => $row['status'] == 'leave' ? '00:00' : substr($row['godzina_rozpoczecia'], 0, 5),
                'end_time' => $row['status'] == 'leave' ? '00:00' : substr($row['godzina_zakonczenia'], 0, 5),
                'hours' => $hours,
                'status' => $row['status']
            ];
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($employeeSchedules);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>System HR - Zarządzanie czasem pracy</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/forms.css">
    <link rel="stylesheet" href="css/time_management.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="theme-light">
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="logo">
                <h2>HR System</h2>
            </div>
            <div class="menu">
                <a href="dashboard.php" class="menu-item">
                    <i class="fas fa-home"></i>
                    <span>Strona główna</span>
                </a>
                <a href="manage_employees.php" class="menu-item">
                    <i class="fas fa-users"></i>
                    <span>Zarządzaj pracownikami</span>
                </a>
                <a href="manage_time.php" class="menu-item active">
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
                <div class="page-title">
                    <h1>Zarządzanie czasem pracy</h1>
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
            <div class="time-management-container">
                <!-- Calendar View -->
                <div class="calendar-container">
                    <div class="calendar-wrapper">
                        <h3 class="calendar-section-title">Wybierz datę, aby zobaczyć harmonogram</h3>
                        
                        <!-- Month selector moved here exclusively -->
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
                                // Add empty cells for the days before the first day of the month
                                for ($i = 1; $i < $firstDayOfMonth; $i++) {
                                    echo '<div class="calendar-day empty"></div>';
                                }
        
                                for ($day = 1; $day <= $daysInMonth; $day++) {
                                    $dateYmd = sprintf('%04d-%02d-%02d', $selectedYear, $selectedMonth, $day);
                                    $isToday = ($day == date('j') && $selectedMonth == date('m') && $selectedYear == date('Y'));
                                    $isSelected = ($day == $selectedDay);
                                    $isWeekend = date('N', strtotime($dateYmd)) >= 6; // 6 = Saturday, 7 = Sunday
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
                                    <!-- Employee schedules will be loaded dynamically via JavaScript -->
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
    
    <!-- Schedule Edit Modal -->
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
    
    <!-- Add Schedule Modal -->
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
                            <!-- Options will be loaded dynamically -->
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
    
    <!-- Delete Schedule Modal -->
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
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const userNameDropdown = document.getElementById('userNameDropdown');
        const userDropdown = document.getElementById('userDropdown');
    
        userNameDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
            userNameDropdown.classList.toggle('active');
            userDropdown.classList.toggle('active');
        });
        
        document.addEventListener('click', function(e) {
            if (!userDropdown.contains(e.target) && e.target !== userNameDropdown) {
                userNameDropdown.classList.remove('active');
                userDropdown.classList.remove('active');
            }
        });
        document.getElementById('changePasswordBtn').addEventListener('click', function() {
            alert('Zmiana hasła - funkcjonalność będzie dostępna wkrótce');
        });
        
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;
        const themeIcon = themeToggle.querySelector('i');
        
        const currentTheme = localStorage.getItem('theme') || 'theme-light';
        body.className = currentTheme;
        
        if (currentTheme === 'theme-dark') {
            themeIcon.className = 'fas fa-moon';
        } else {
            themeIcon.className = 'fas fa-sun';
        }
        

        themeToggle.addEventListener('click', function() {
            if (body.classList.contains('theme-dark')) {
                body.classList.replace('theme-dark', 'theme-light');
                themeIcon.className = 'fas fa-sun';
                localStorage.setItem('theme', 'theme-light');
            } else {
                body.classList.replace('theme-light', 'theme-dark');
                themeIcon.className = 'fas fa-moon';
                localStorage.setItem('theme', 'theme-dark');
            }
        });
        
    });
    </script>
</body>
</html>
<?php $conn->close(); ?>
