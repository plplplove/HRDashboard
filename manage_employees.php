<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit();
}

$deleteSuccess = isset($_SESSION['delete_success']) && $_SESSION['delete_success'] === true;
$deleteError = isset($_SESSION['delete_error']) && $_SESSION['delete_error'] === true;

if (isset($_SESSION['delete_success'])) {
    unset($_SESSION['delete_success']);
}
if (isset($_SESSION['delete_error'])) {
    unset($_SESSION['delete_error']);
}

$host = 'localhost';
$db = 'HRDASHBOARD';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

$nameSearch = isset($_GET['name']) ? $_GET['name'] : '';
$deptFilter = isset($_GET['department']) ? $_GET['department'] : '';
$onLeaveFilter = isset($_GET['on_leave']) ? true : false;

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
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $onLeave = $row['urlop'] ? 
                '<span class="status-badge on-leave"><i class="fas fa-check"></i></span>' : 
                '<span class="status-badge not-on-leave"><i class="fas fa-times"></i></span>';
            
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
                    <button class='action-btn edit-btn' data-id='{$row['id']}' title='Edytuj'>
                        <i class='fas fa-edit'></i>
                    </button>
                    <button class='action-btn delete-btn' data-id='{$row['id']}' title='Usuń'>
                        <i class='fas fa-trash-alt'></i>
                    </button>
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

$deptQuery = "SELECT DISTINCT dzial FROM pracownicy ORDER BY dzial";
$departments = $conn->query($deptQuery);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>System HR - Zarządzanie Pracownikami</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/employees.css">
    <link rel="stylesheet" href="css/forms-modals.css">
    <link rel="stylesheet" href="css/theme.css">
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
                <a href="manage_employees.php" class="menu-item active">
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
        <div class="main-content blur-when-modal-open">
            <div class="header">
                <div class="page-title">
                    <h1>Lista pracowników</h1>
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
            
            <?php if($deleteSuccess): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Pracownik został pomyślnie usunięty.
            </div>
            <?php endif; ?>
            
            <?php if($deleteError): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> Wystąpił błąd podczas usuwania pracownika.
            </div>
            <?php endif; ?>
            
            <div class="filter-panel">
                <form id="filterForm" method="get" action="manage_employees.php">
                    <div class="filter-row">
                        <div class="filter-group">
                            <div class="search-input-wrapper">
                                <input type="text" name="name" id="nameSearch" placeholder="Imię/Nazwisko" 
                                    value="<?= htmlspecialchars($nameSearch) ?>">
                                <div class="search-spinner" id="searchSpinner">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="filter-group">
                            <select name="department" id="deptFilter">
                                <option value="">Wszystkie działy</option>
                                <?php
                                if ($departments && $departments->num_rows > 0) {
                                    while($dept = $departments->fetch_assoc()) {
                                        $selected = ($dept['dzial'] == $deptFilter) ? 'selected' : '';
                                        echo "<option value='{$dept['dzial']}' $selected>{$dept['dzial']}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="filter-group checkbox-group">
                            <input type="checkbox" id="onLeaveFilter" name="on_leave" <?= $onLeaveFilter ? 'checked' : '' ?>>
                            <label for="onLeaveFilter">Tylko pracownicy na urlopie</label>
                        </div>
                        
                        <div class="filter-actions">
                            <button type="submit" class="btn btn-primary" id="filterBtn">
                                <i class="fas fa-filter"></i> Filtruj
                            </button>
                            <button type="button" class="btn btn-secondary" id="resetBtn">
                                <i class="fas fa-redo"></i> Resetuj
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="action-buttons">
                <button class="btn btn-primary" id="addEmployeeBtn">
                    <i class="fas fa-plus"></i> Dodaj nowego pracownika
                </button>
            </div>
            
            <div class="employees-table-container">
                <table class="employees-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Imię</th>
                            <th>Nazwisko</th>
                            <th>Dział</th>
                            <th>Stanowisko</th>
                            <th>Telefon</th>
                            <th>Email</th>
                            <th>Na urlopie</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody id="employeesTableBody">
                        <?php
                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $onLeave = $row['urlop'] ? 
                                    '<span class="status-badge on-leave"><i class="fas fa-check"></i></span>' : 
                                    '<span class="status-badge not-on-leave"><i class="fas fa-times"></i></span>';
                                
                                echo "<tr>
                                    <td>{$row['id']}</td>
                                    <td>{$row['imie']}</td>
                                    <td>{$row['nazwisko']}</td>
                                    <td>{$row['dzial']}</td>
                                    <td>{$row['stanowisko']}</td>
                                    <td>{$row['telefon']}</td>
                                    <td>{$row['email']}</td>
                                    <td class='center'>{$onLeave}</td>
                                    <td class='action-buttons-cell'>
                                        <button class='action-btn edit-btn' data-id='{$row['id']}' title='Edytuj'>
                                            <i class='fas fa-edit'></i>
                                        </button>
                                        <button class='action-btn delete-btn' data-id='{$row['id']}' title='Usuń'>
                                            <i class='fas fa-trash-alt'></i>
                                        </button>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='9' class='no-data'>Brak danych pracowników</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div id="deleteModal" class="modal">
        <div class="modal-content delete-modal">
            <div class="modal-header delete-header">
                <h2><i class="fas fa-exclamation-triangle"></i> Potwierdzenie usunięcia</h2>
                <button class="close-button" id="closeDeleteModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p>Czy na pewno chcesz usunąć tego pracownika?</p>
                <p class="warning-text">Ta operacja jest nieodwracalna.</p>
            </div>
            <div class="modal-footer">
                <button id="confirmDeleteBtn" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Tak, usuń
                </button>
                <button id="cancelDeleteBtn" class="btn btn-secondary">
                    <i class="fas fa-ban"></i> Anuluj
                </button>
            </div>
        </div>
    </div>
    
    <div id="addEmployeeContainer"></div>
    <div id="editEmployeeContainer"></div>
    
    <script src="js/common.js"></script>
    <script src="js/employees.js"></script>
</body>
</html>
<?php $conn->close(); ?>
