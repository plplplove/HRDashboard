<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit();
}

$deleteSuccess = $_SESSION['delete_success'] ?? false;
$deleteError = $_SESSION['delete_error'] ?? false;
$addSuccess = $_SESSION['add_success'] ?? false;
$addError = $_SESSION['add_error'] ?? false;
$editSuccess = $_SESSION['edit_success'] ?? false;
$editError = $_SESSION['edit_error'] ?? false;

unset($_SESSION['delete_success'], $_SESSION['delete_error'], $_SESSION['add_success'], 
      $_SESSION['add_error'], $_SESSION['edit_success'], $_SESSION['edit_error']);

$conn = new mysqli('localhost', 'root', '', 'HRDASHBOARD');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
$nameSearch = $_GET['name'] ?? '';
$deptFilter = $_GET['department'] ?? '';
$onLeaveFilter = isset($_GET['on_leave']);

$sql = "SELECT * FROM pracownicy WHERE 1=1";
if (!empty($nameSearch)) {
    $search = $conn->real_escape_string($nameSearch);
    $sql .= " AND (imie LIKE '%$search%' OR nazwisko LIKE '%$search%')";
}
if (!empty($deptFilter)) {
    $dept = $conn->real_escape_string($deptFilter);
    $sql .= " AND dzial = '$dept'";
}
if ($onLeaveFilter) {
    $sql .= " AND urlop = 1";
}
$sql .= " ORDER BY id ASC";
$result = $conn->query($sql);

if ($isAjax) {
    $output = '';
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $onLeave = $row['urlop']
                ? '<span class="status-badge on-leave"><i class="fas fa-check"></i></span>'
                : '<span class="status-badge not-on-leave"><i class="fas fa-times"></i></span>';
            $output .= "<tr>
                <td>{$row['id']}</td>
                <td>{$row['imie']}</td>
                <td>{$row['nazwisko']}</td>
                <td>{$row['dzial']}</td>
                <td>{$row['stanowisko']}</td>
                <td>{$row['telefon']}</td>
                <td>{$row['email']}</td>
                <td class='center'>{$onLeave}</td>
                <td class='action-buttons-cell'>
                    <button class='action-btn edit-btn' data-id='{$row['id']}'><i class='fas fa-edit'></i></button>
                    <button class='action-btn delete-btn' data-id='{$row['id']}'><i class='fas fa-trash-alt'></i></button>
                </td>
            </tr>";
        }
    } else {
        $output = "<tr><td colspan='9' class='no-data'>Brak danych pracowników</td></tr>";
    }
    echo $output;
    $conn->close();
    exit();
}

$departments = $conn->query("SELECT DISTINCT dzial FROM pracownicy ORDER BY dzial");
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>System HR - Zarządzanie Pracownikami</title>
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/employees.css">
    <link rel="stylesheet" href="css/forms-modals.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="theme-light">
<div class="dashboard-container">
    <aside class="sidebar">
        <div class="logo"><h2>HR System</h2></div>
        <nav class="menu">
            <a href="dashboard.php" class="menu-item"><i class="fas fa-home"></i><span>Strona główna</span></a>
            <a href="manage_employees.php" class="menu-item active"><i class="fas fa-users"></i><span>Zarządzaj pracownikami</span></a>
            <a href="manage_time.php" class="menu-item"><i class="fas fa-clock"></i><span>Zarządzaj czasem pracy</span></a>
            <a href="manage_leave.php" class="menu-item"><i class="fas fa-calendar-alt"></i><span>Wnioski urlopowe</span></a>
        </nav>
    </aside>


    <main class="main-content">
        <header class="header">
            <h1 class="welcome-message">Zarządzaj pracownikami</h1>
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

        <?php if ($deleteSuccess): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> Pracownik został usunięty.</div>
        <?php elseif ($deleteError): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Błąd podczas usuwania.</div>
        <?php elseif ($addSuccess): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> Pracownik został dodany pomyślnie.</div>
        <?php elseif ($addError): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Błąd podczas dodawania pracownika.</div>
        <?php elseif ($editSuccess): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> Dane pracownika zostały zaktualizowane.</div>
        <?php elseif ($editError): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Błąd podczas aktualizacji danych pracownika.</div>
        <?php endif; ?>

        <section class="filter-panel">
            <form id="filterForm" method="get">
                <div class="filter-row">
                    <div class="filter-group">
                        <input type="text" name="name" id="nameSearch" placeholder="Imię/Nazwisko" value="<?= htmlspecialchars($nameSearch) ?>">
                        <div class="search-spinner" id="searchSpinner"><i class="fas fa-spinner fa-spin"></i></div>
                    </div>
                    <div class="filter-group">
                        <select name="department" id="deptFilter">
                            <option value="">Wszystkie działy</option>
                            <?php while($dept = $departments->fetch_assoc()): ?>
                                <option value="<?= $dept['dzial'] ?>" <?= ($dept['dzial'] === $deptFilter) ? 'selected' : '' ?>><?= $dept['dzial'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="filter-group checkbox-group">
                        <input type="checkbox" id="onLeaveFilter" name="on_leave" <?= $onLeaveFilter ? 'checked' : '' ?>>
                        <label for="onLeaveFilter">Tylko na urlopie</label>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filtruj</button>
                        <button type="button" class="btn btn-secondary" id="resetBtn"><i class="fas fa-redo"></i> Resetuj</button>
                    </div>
                </div>
            </form>
        </section>

        <div class="action-buttons">
            <button class="btn btn-primary" id="addEmployeeBtn"><i class="fas fa-plus"></i> Dodaj pracownika</button>
        </div>

        <section class="employees-table-container">
            <table class="employees-table">
                <thead>
                    <tr><th>ID</th><th>Imię</th><th>Nazwisko</th><th>Dział</th><th>Stanowisko</th><th>Telefon</th><th>Email</th><th>Na urlopie</th><th>Akcje</th></tr>
                </thead>
                <tbody id="employeesTableBody">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <?php $onLeave = $row['urlop'] ? '<span class="status-badge on-leave"><i class="fas fa-check"></i></span>' : '<span class="status-badge not-on-leave"><i class="fas fa-times"></i></span>'; ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= $row['imie'] ?></td>
                                <td><?= $row['nazwisko'] ?></td>
                                <td><?= $row['dzial'] ?></td>
                                <td><?= $row['stanowisko'] ?></td>
                                <td><?= $row['telefon'] ?></td>
                                <td><?= $row['email'] ?></td>
                                <td class="center"><?= $onLeave ?></td>
                                <td class="action-buttons-cell">
                                    <button class="action-btn edit-btn" data-id="<?= $row['id'] ?>"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn delete-btn" data-id="<?= $row['id'] ?>"><i class="fas fa-trash-alt"></i></button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="9" class="no-data">Brak danych pracowników</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>

<!-- Modale -->
<div id="deleteModal" class="modal">
    <div class="modal-content delete-modal">
        <div class="modal-header delete-header">
            <h2><i class="fas fa-exclamation-triangle"></i> Potwierdzenie usunięcia</h2>
            <button class="close-button" id="closeDeleteModal"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p>Czy na pewno chcesz usunąć tego pracownika?</p>
            <p class="warning-text">Ta operacja jest nieodwracalna.</p>
        </div>
        <div class="modal-footer">
            <button id="confirmDeleteBtn" class="btn btn-danger"><i class="fas fa-trash"></i> Tak, usuń</button>
            <button id="cancelDeleteBtn" class="btn btn-secondary"><i class="fas fa-ban"></i> Anuluj</button>
        </div>
    </div>
</div>

<div id="addEmployeeContainer"></div>
<div id="editEmployeeContainer"></div>
<div id="notificationContainer"></div>

<script src="js/common.js"></script>
<script src="js/employees.js"></script>
</body>
</html>
<?php $conn->close(); ?>
