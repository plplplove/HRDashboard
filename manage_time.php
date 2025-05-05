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

// AJAX request handling for getting employees by date
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if ($isAjax && isset($_GET['action']) && $_GET['action'] === 'getEmployeesByDate') {
    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    
    // Query the database for employee schedules on the selected date
    $employeeSchedules = [];
    
    // Prepare SQL query to join grafik_pracy with pracownicy (including schedule_id)
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
    <style>
    /* User dropdown menu styles */
    .user-info {
        position: relative;
    }
    
    .user-name {
        cursor: pointer;
        display: flex;
        align-items: center;
    }
    
    .user-name::after {
        content: '\f107';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        margin-left: 5px;
        transition: transform 0.2s;
    }
    
    .user-name.active::after {
        transform: rotate(180deg);
    }
    
    .user-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        min-width: 200px;
        z-index: 100;
        margin-top: 10px;
        display: none;
        overflow: hidden;
    }
    
    .user-dropdown.active {
        display: block;
        animation: fadeIn 0.2s ease;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .user-dropdown a,
    .user-dropdown button {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        color: #333;
        text-decoration: none;
        transition: background-color 0.2s ease;
        width: 100%;
        text-align: left;
        border: none;
        background: none;
        cursor: pointer;
        font-size: 14px;
    }
    
    .user-dropdown a:hover,
    .user-dropdown button:hover {
        background-color: #f5f7fb;
    }
    
    .user-dropdown i {
        width: 20px;
        margin-right: 10px;
        text-align: center;
    }
    
    /* Theme toggle button styles */
    .theme-toggle {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 1.2rem;
        margin-right: 15px;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.2s;
    }
    
    .theme-toggle:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }
    
    /* Enhanced dark theme styles to match reference */
    body.theme-dark {
        background-color: #1e1e2d;
        color: #ffffff;
    }
    
    body.theme-dark .sidebar {
        background: #1e1e2d;
        box-shadow: none;
    }
    
    body.theme-dark .menu-item.active {
        background: linear-gradient(90deg, #00aeff 0%, #3f6fff 100%);
        box-shadow: 0 0 10px rgba(0, 174, 255, 0.5);
        color: white;
    }
    
    body.theme-dark .menu-item:hover:not(.active) {
        background-color: rgba(255, 255, 255, 0.1);
    }
    
    body.theme-dark .main-content {
        background-color: #1e1e2d;
    }
    
    body.theme-dark .header {
        border-bottom-color: #2b2b40;
    }
    
    body.theme-dark .user-info {
        background-color: #2b2b40;
        color: #ffffff;
    }
    
    body.theme-dark .page-title h1,
    body.theme-dark .user-name,
    body.theme-dark label,
    body.theme-dark .notification-content,
    body.theme-dark .modal-header h2,
    body.theme-dark th,
    body.theme-dark td,
    body.theme-dark .btn,
    body.theme-dark p {
        color: #ffffff;
    }
    
    body.theme-dark .theme-toggle {
        color: #ffffff;
    }
    
    body.theme-dark .theme-toggle:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }
    
    body.theme-dark .user-dropdown {
        background-color: #2b2b40;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
        border: none;
    }
    
    body.theme-dark .user-dropdown a,
    body.theme-dark .user-dropdown button {
        color: #ffffff;
    }
    
    body.theme-dark .user-dropdown a:hover,
    body.theme-dark .user-dropdown button:hover {
        background-color: #323248;
    }
    
    body.theme-dark .time-management-panel,
    body.theme-dark .calendar-container,
    body.theme-dark .time-entry-modal .modal-content,
    body.theme-dark .options-panel {
        background-color: #2b2b40;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        border: none;
        border-radius: 8px;
    }
    
    body.theme-dark .calendar-header,
    body.theme-dark .modal-header,
    body.theme-dark .modal-footer {
        background-color: #2b2b40;
        border-color: #3a3a5a;
    }
    
    body.theme-dark .calendar th {
        background-color: #323248;
        color: #ffffff;
        border-bottom: 1px solid #3a3a5a;
    }
    
    body.theme-dark .calendar td {
        border-color: #3a3a5a;
        color: #ffffff;
    }
    
    body.theme-dark .calendar .today {
        background-color: #39394f;
    }
    
    body.theme-dark .calendar .has-events {
        background-color: #323283;
    }
    
    body.theme-dark .btn-secondary {
        background-color: #323248;
        color: #ffffff;
        border: none;
    }
    
    body.theme-dark .btn-secondary:hover {
        background-color: #3a3a5a;
    }
    
    body.theme-dark .btn-primary {
        background: linear-gradient(135deg, #00aeff 0%, #3f6fff 100%);
        border: none;
    }
    
    body.theme-dark input,
    body.theme-dark select,
    body.theme-dark textarea {
        background-color: #323248;
        border: 1px solid #3a3a5a;
        color: #ffffff;
    }
    
    body.theme-dark input:focus,
    body.theme-dark select:focus,
    body.theme-dark textarea:focus {
        background-color: #383854;
        border-color: #3f6fff;
    }
    
    body.theme-dark * {
        color: #ffffff;
    }
    
    body.theme-dark .loading-overlay {
        background-color: rgba(30, 30, 45, 0.8);
    }
    
    body.theme-dark .spinner {
        color: #00aeff;
    }
    
    body.theme-dark .calendar-container {
        background-color: #2b2b40;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
        border: none;
        border-radius: 8px;
    }
    
    body.theme-dark .calendar-nav {
        background-color: #323248;
        border-bottom: 1px solid #3a3a5a;
    }
    
    body.theme-dark .calendar-nav button {
        color: #ffffff;
        background-color: transparent;
        border: none;
    }
    
    body.theme-dark .calendar-nav button:hover {
        background-color: #3f3f5f;
    }
    
    body.theme-dark .calendar-header {
        background-color: #323248;
        color: #ffffff;
        border-bottom: 1px solid #3a3a5a;
    }
    
    body.theme-dark .calendar-title {
        color: #ffffff;
    }
    
    body.theme-dark .calendar table {
        background-color: #2b2b40;
    }
    
    body.theme-dark .calendar th {
        background-color: #323248;
        color: #ffffff;
        border-color: #3a3a5a;
    }
    
    body.theme-dark .calendar td {
        border-color: #3a3a5a;
        color: #ffffff;
    }
    
    body.theme-dark .calendar td.day {
        background-color: #2b2b40;
    }
    
    body.theme-dark .calendar td.day:hover {
        background-color: #3a3a5a;
    }
    
    body.theme-dark .calendar td.today {
        background-color: #3a3a5a;
        color: #ffffff;
        box-shadow: inset 0 0 0 2px rgba(0, 174, 255, 0.5);
    }
    
    body.theme-dark .calendar td.inactive {
        color: #6c6c7e;
        background-color: #272736;
    }
    
    body.theme-dark .calendar td.has-events {
        background-color: #1c3b5a;
        color: #ffffff;
    }
    
    body.theme-dark .calendar td.has-events:hover {
        background-color: #264b73;
    }
    
    body.theme-dark .events-panel,
    body.theme-dark .details-panel,
    body.theme-dark .side-panel {
        background-color: #2b2b40;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
        border: none;
        border-radius: 8px;
    }
    
    body.theme-dark .events-list .event-item,
    body.theme-dark .event-details {
        border-bottom: 1px solid #3a3a5a;
        background-color: #323248;
    }
    
    body.theme-dark .events-list .event-item:hover {
        background-color: #3a3a5a;
    }
    
    body.theme-dark .events-list .event-title,
    body.theme-dark .event-details .event-title {
        color: #ffffff;
    }
    
    body.theme-dark .events-list .event-time,
    body.theme-dark .event-details .event-time {
        color: #b6b6c9;
    }
    
    body.theme-dark .events-list .no-events {
        color: #b6b6c9;
    }
    
    body.theme-dark .events-header,
    body.theme-dark .panel-header {
        background-color: #323248;
        border-bottom: 1px solid #3a3a5a;
        color: #ffffff;
    }
    
    body.theme-dark .prev-month-btn,
    body.theme-dark .next-month-btn,
    body.theme-dark .calendar-nav-btn {
        color: #ffffff;
        background-color: transparent;
    }
    
    body.theme-dark .prev-month-btn:hover,
    body.theme-dark .next-month-btn:hover,
    body.theme-dark .calendar-nav-btn:hover {
        background-color: #3f3f5f;
        color: #00aeff;
    }
    
    body.theme-dark .add-event-btn {
        background: linear-gradient(135deg, #00aeff 0%, #3f6fff 100%);
        color: #ffffff;
        border: none;
    }
    
    body.theme-dark .add-event-btn:hover {
        opacity: 0.9;
        box-shadow: 0 5px 15px rgba(0, 174, 255, 0.3);
    }
    
    body.theme-dark .time-block {
        background-color: #323248;
        border: 1px solid #3a3a5a;
    }
    
    body.theme-dark .time-block:hover {
        background-color: #3a3a5a;
    }
    
    body.theme-dark .time-block.approved {
        background-color: #0d392e;
        border-color: #00e396;
    }
    
    body.theme-dark .time-block.pending {
        background-color: #392f28;
        border-color: #ffb74d;
    }
    
    body.theme-dark .time-block.rejected {
        background-color: #3e1a1a;
        border-color: #ff4d6d;
    }
    
    body.theme-dark .time-entry-form label {
        color: #ffffff;
    }
    
    body.theme-dark .time-entry-form input,
    body.theme-dark .time-entry-form select,
    body.theme-dark .time-entry-form textarea {
        background-color: #323248;
        border: 1px solid #3a3a5a;
        color: #ffffff;
    }
    
    body.theme-dark .time-entry-form input:focus,
    body.theme-dark .time-entry-form select:focus,
    body.theme-dark .time-entry-form textarea:focus {
        background-color: #383854;
        border-color: #3f6fff;
    }
    
    body.theme-dark .stats-container,
    body.theme-dark .chart-container {
        background-color: #2b2b40;
        border: none;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
    }
    
    body.theme-dark .stats-header,
    body.theme-dark .chart-header {
        background-color: #323248;
        border-bottom: 1px solid #3a3a5a;
        color: #ffffff;
    }
    
    body.theme-dark .stat-value {
        color: #00aeff;
    }
    
    body.theme-dark .stat-label {
        color: #b6b6c9;
    }
    
    body.theme-dark .calendar-days {
        background-color: #1e1e2d;
    }
    
    body.theme-dark .calendar-day {
        background-color: #2b2b40 !important;
        color: #ffffff !important;
        border: 1px solid #3a3a5a;
    }
    
    body.theme-dark .calendar-day.empty {
        background-color: #1e1e2d !important;
        border: none;
    }
    
    body.theme-dark .calendar-day.selected {
        background-color: #1c3b5a !important;
        box-shadow: 0 0 0 2px #00aeff;
    }
    
    body.theme-dark .calendar-day.weekend {
        background-color: #272736 !important;
    }
    
    body.theme-dark .calendar-day.today {
        background-color: #323283 !important;
    }
    
    body.theme-dark .calendar-day .day-number {
        color: #ffffff !important;
    }
    
    body.theme-dark .calendar-header {
        background-color: #323248 !important;
    }
    
    body.theme-dark .calendar-header .weekday {
        color: #ffffff !important;
        border-color: #3a3a5a;
    }
    
    body.theme-dark .card {
        background-color: #2b2b40 !important;
        border: none !important;
    }
    
    body.theme-dark .card-header {
        background-color: #323248 !important;
        border-bottom: 1px solid #3a3a5a !important;
    }
    
    body.theme-dark .card-header h2 {
        color: #ffffff !important;
    }
    
    body.theme-dark .data-table {
        background-color: #2b2b40 !important;
        border: 1px solid #3a3a5a !important;
        color: #ffffff !important;
    }
    
    body.theme-dark .data-table th,
    body.theme-dark .data-table td {
        border-color: #3a3a5a !important;
        background-color: #2b2b40 !important;
        color: #ffffff !important;
    }
    
    body.theme-dark .data-table th {
        background-color: #323248 !important;
    }
    
    body.theme-dark .data-table tr:nth-child(even) td {
        background-color: #323248 !important;
    }
    
    body.theme-dark .data-table tr:hover td {
        background-color: #3a3a5a !important;
    }
    
    body.theme-dark .spinner {
        color: #00aeff !important;
    }
    
    body.theme-dark .time-table-container {
        background-color: #2b2b40 !important;
        border: none !important;
    }
    
    body.theme-dark .summary-section {
        background-color: #323248 !important;
        border-top: 1px solid #3a3a5a !important;
        color: #ffffff !important;
    }
    
    body.theme-dark .summary-label {
        color: #b6b6c9 !important;
    }
    
    body.theme-dark .summary-value {
        color: #00aeff !important;
    }
    
    body.theme-dark .current-date-display {
        color: #ffffff !important;
    }
    
    body.theme-dark #currentDate {
        color: #ffffff !important;
    }
    
    body.theme-dark .date-navigation {
        background-color: #323248 !important;
        border: 1px solid #3a3a5a !important;
    }
    
    body.theme-dark #selectedDateDisplay {
        color: #00aeff !important;
    }
    
    body.theme-dark .status-present {
        background-color: #0d392e !important;
        color: #00e396 !important;
    }
    
    body.theme-dark .status-leave {
        background-color: #392f28 !important;
        color: #ffb74d !important;
    }
    
    body.theme-dark .status-vacation {
        background-color: #1c3b5a !important;
        color: #00aeff !important;
    }
    
    body.theme-dark .calendar-section-title {
        color: #ffffff !important;
    }
    
    body.theme-dark .calendar-wrapper {
        background-color: #2b2b40 !important;
        border: none !important;
    }

    /* Enhanced dark theme for modals, calendar navigation, and UI elements */
    
    /* Modal dialogs in dark theme */
    body.theme-dark .modal {
        background-color: rgba(15, 15, 25, 0.85) !important;
    }
    
    body.theme-dark .modal-content {
        background-color: #1e1e2d !important;
        border: 1px solid #2b2b40 !important;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.5) !important;
    }
    
    body.theme-dark .modal-header {
        background-color: #2b2b40 !important;
        border-bottom: 1px solid #3a3a5a !important;
    }
    
    body.theme-dark .modal-body {
        background-color: #1e1e2d !important;
    }
    
    body.theme-dark .modal-footer {
        background-color: #2b2b40 !important;
        border-top: 1px solid #3a3a5a !important;
    }
    
    body.theme-dark .close-button {
        color: #ffffff !important;
        background-color: transparent !important;
    }
    
    body.theme-dark .close-button:hover {
        color: #ff4d6d !important;
    }
    
    /* Calendar navigation arrows */
    body.theme-dark #prevMonth,
    body.theme-dark #nextMonth {
        background-color: #2b2b40 !important;
        border: 1px solid #3a3a5a !important;
        color: white !important;
    }
    
    body.theme-dark #prevMonth i,
    body.theme-dark #nextMonth i {
        color: white !important;
    }
    
    body.theme-dark #prevMonth:hover,
    body.theme-dark #nextMonth:hover {
        background-color: #323248 !important;
    }
    
    /* Date navigation container */
    body.theme-dark .date-picker {
        background-color: #2b2b40 !important;
    }
    
    body.theme-dark .date-picker .btn-icon {
        background-color: #323248 !important;
        border: 1px solid #3a3a5a !important;
        border-radius: 50% !important;
    }
    
    body.theme-dark .date-picker .btn-icon:hover {
        background-color: #3f3f5f !important;
    }
    
    /* Month and year display */
    body.theme-dark #currentDate {
        color: white !important;
    }
    
    /* Ensuring dark background for all containers */
    body.theme-dark .time-management-container {
        background-color: #1e1e2d !important;
    }
    
    body.theme-dark .card {
        background-color: #2b2b40 !important;
        border: none !important;
    }
    
    body.theme-dark .time-table-container {
        background-color: #2b2b40 !important;
    }
    
    /* Delete modal specific styling */
    body.theme-dark .delete-modal,
    body.theme-dark .delete-header {
        background-color: #1e1e2d !important;
        border-color: #3a3a5a !important;
    }
    
    body.theme-dark .btn-danger {
        background-color: #d32f2f !important;
        color: white !important;
        border: none !important;
    }
    
    body.theme-dark .btn-danger:hover {
        background-color: #b71c1c !important;
    }
    
    body.theme-dark .warning-text {
        color: #ff4d6d !important;
        font-weight: bold !important;
    }
    
    /* Ensure all form elements in modals are dark */
    body.theme-dark .form-group label {
        color: white !important;
    }
    
    body.theme-dark .form-group input,
    body.theme-dark .form-group select,
    body.theme-dark .form-group textarea {
        background-color: #323248 !important;
        border: 1px solid #3a3a5a !important;
        color: white !important;
    }
    
    body.theme-dark .form-group input:focus,
    body.theme-dark .form-group select:focus,
    body.theme-dark .form-group textarea:focus {
        background-color: #383854 !important;
        border-color: #3f6fff !important;
    }
    
    body.theme-dark .form-group input:disabled,
    body.theme-dark .form-group select:disabled,
    body.theme-dark .form-group textarea:disabled {
        background-color: #262636 !important;
        border-color: #2b2b40 !important;
        color: #9999aa !important;
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
                                // Add cells for each day of the month
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
                                
                                // Calculate how many empty cells we need to add at the end
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
                
                <!-- Daily Employee Schedules -->
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
        // User dropdown functionality
        const userNameDropdown = document.getElementById('userNameDropdown');
        const userDropdown = document.getElementById('userDropdown');
        
        // Toggle dropdown when clicking on username
        userNameDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
            userNameDropdown.classList.toggle('active');
            userDropdown.classList.toggle('active');
        });
        
        // Close dropdown when clicking elsewhere on the page
        document.addEventListener('click', function(e) {
            if (!userDropdown.contains(e.target) && e.target !== userNameDropdown) {
                userNameDropdown.classList.remove('active');
                userDropdown.classList.remove('active');
            }
        });
        
        // Handle password change button click
        document.getElementById('changePasswordBtn').addEventListener('click', function() {
            // You can implement password change functionality here
            alert('Zmiana hasła - funkcjonalność będzie dostępna wkrótce');
        });
        
        // Theme switching functionality
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;
        const themeIcon = themeToggle.querySelector('i');
        
        // Check for saved theme preference or use default
        const currentTheme = localStorage.getItem('theme') || 'theme-light';
        body.className = currentTheme;
        
        // Update icon based on current theme
        if (currentTheme === 'theme-dark') {
            themeIcon.className = 'fas fa-moon';
        } else {
            themeIcon.className = 'fas fa-sun';
        }
        
        // Add event listener for theme toggle
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
        
        // ...existing calendar.js initialization and other functions...
    });
    </script>
</body>
</html>
<?php $conn->close(); ?>
