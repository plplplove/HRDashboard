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

function getStatusInfo($statusRaw) {
    $statusClean = strtolower(trim($statusRaw));
    
    if (strpos($statusClean, 'oczek') !== false) {
        return [
            'class' => 'status-pending',
            'text' => 'Oczekujący',
            'actions' => function($id) {
                return '<div class="actions">
                    <i class="fas fa-check-circle approve-btn" data-id="'.$id.'" title="Zatwierdź"></i>
                    <i class="fas fa-times-circle reject-btn" data-id="'.$id.'" title="Odrzuć"></i>
                </div>';
            }
        ];
    } elseif (strpos($statusClean, 'zatwierdz') !== false) {
        return [
            'class' => 'status-approved',
            'text' => 'Zatwierdzony',
            'actions' => function() {
                return '<span class="action-disabled">Zatwierdzony</span>';
            }
        ];
    } elseif (strpos($statusClean, 'odrzuc') !== false) {
        return [
            'class' => 'status-rejected',
            'text' => 'Odrzucony',
            'actions' => function() {
                return '<span class="action-disabled">Odrzucony</span>';
            }
        ];
    } else {
        return [
            'class' => 'status-unknown',
            'text' => 'Status nieznany',
            'actions' => function() {
                return '<span class="action-disabled">Status nieznany</span>';
            }
        ];
    }
}

if (isset($_POST['action']) && isset($_POST['leave_id'])) {
    header('Content-Type: application/json');
    
    $leave_id = intval($_POST['leave_id']);
    $action = $_POST['action'];
    
    if ($action === 'approve') {
        $sql = "UPDATE urlopy SET status = 'zatwierdzony' WHERE id = $leave_id";
    } elseif ($action === 'reject') {
        $sql = "UPDATE urlopy SET status = 'odrzucony' WHERE id = $leave_id";
    }
    
    if (isset($sql) && $conn->query($sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
    
    $conn->close();
    exit();
}

$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'data_rozpoczecia';
$sortOrder = isset($_GET['order']) ? $_GET['order'] : 'ASC';

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($isAjax) {
    $whereClause = "1=1";
    
    if (!empty($statusFilter)) {
        $statusFilter = $conn->real_escape_string($statusFilter);
        $whereClause .= " AND u.status = '$statusFilter'";
    }
    
    $sql = "SELECT u.id, u.pracownik_id, p.imie, p.nazwisko, p.dzial, 
                  u.data_rozpoczecia, u.data_zakonczenia, u.powod, u.status 
           FROM urlopy u 
           INNER JOIN pracownicy p ON u.pracownik_id = p.id 
           WHERE $whereClause 
           ORDER BY u.$sortBy $sortOrder";
    
    $result = $conn->query($sql);
    
    $output = '';
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $status = getStatusInfo($row['status']);
            $actions = $status['actions']($row['id']);
            
            $output .= '<tr>
                <td>'.$row['id'].'</td>
                <td>'.$row['nazwisko'].' '.$row['imie'].'</td>
                <td>'.$row['dzial'].'</td>
                <td>'.date('d.m.Y', strtotime($row['data_rozpoczecia'])).'</td>
                <td>'.date('d.m.Y', strtotime($row['data_zakonczenia'])).'</td>
                <td>'.$row['powod'].'</td>
                <td><span class="status-badge '.$status['class'].'">'.$status['text'].'</span></td>
                <td class="actions-cell">'.$actions.'</td>
            </tr>';
        }
    } else {
        $output = '<tr><td colspan="8" class="no-data">Brak wniosków urlopowych</td></tr>';
    }
    
    echo $output;
    $conn->close();
    exit();
}

$sql = "SELECT u.id, u.pracownik_id, p.imie, p.nazwisko, p.dzial, 
              u.data_rozpoczecia, u.data_zakonczenia, u.powod, u.status 
       FROM urlopy u 
       INNER JOIN pracownicy p ON u.pracownik_id = p.id 
       ORDER BY u.data_rozpoczecia ASC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>System HR - Wnioski Urlopowe</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/employees.css">
    <link rel="stylesheet" href="css/leave.css">
    <link rel="stylesheet" href="css/common.css">
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
                <a href="manage_employees.php" class="menu-item">
                    <i class="fas fa-users"></i>
                    <span>Zarządzaj pracownikami</span>
                </a>
                <a href="manage_time.php" class="menu-item">
                    <i class="fas fa-clock"></i>
                    <span>Zarządzaj czasem pracy</span>
                </a>
                <a href="manage_leave.php" class="menu-item active">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Wnioski urlopowe</span>
                </a>
            </div>
        </div>
        <div class="main-content">
            <div class="header">
                <div class="page-title">
                    <h1>Wnioski urlopowe</h1>
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
            <div class="filter-panel">
                <form id="filterForm" method="get">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="statusFilter">Status:</label>
                            <select id="statusFilter" name="status">
                                <option value="">Wszystkie</option>
                                <option value="oczekujacy">Oczekujące</option>
                                <option value="zatwierdzony">Zatwierdzone</option>
                                <option value="odrzucony">Odrzucone</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="sortOrder">Sortuj wg daty:</label>
                            <select id="sortOrder" name="order">
                                <option value="ASC">Od najstarszych</option>
                                <option value="DESC">Od najnowszych</option>
                            </select>
                        </div>
                        <div class="filter-actions">
                            <button type="button" id="applyFiltersBtn" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Filtruj
                            </button>
                            <button type="button" id="resetFiltersBtn" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Resetuj
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="leave-table-container">
                <table class="leave-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pracownik</th>
                            <th>Dział</th>
                            <th>Data rozpoczęcia</th>
                            <th>Data zakończenia</th>
                            <th>Powód</th>
                            <th>Status</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody id="leaveTableBody">
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $status = getStatusInfo($row['status']);
                                $actions = $status['actions']($row['id']);
                                ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= $row['nazwisko'] . ' ' . $row['imie'] ?></td>
                                    <td><?= $row['dzial'] ?></td>
                                    <td><?= date('d.m.Y', strtotime($row['data_rozpoczecia'])) ?></td>
                                    <td><?= date('d.m.Y', strtotime($row['data_zakonczenia'])) ?></td>
                                    <td><?= $row['powod'] ?></td>
                                    <td><span class="status-badge <?= $status['class'] ?>"><?= $status['text'] ?></span></td>
                                    <td class="actions-cell"><?= $actions ?></td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="8" class="no-data">Brak wniosków urlopowych</td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="loadingOverlay" class="loading-overlay">
        <div class="spinner">
            <i class="fas fa-spinner fa-spin"></i>
        </div>
    </div>
    
    <div class="modal" id="confirmModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-question-circle"></i> Potwierdzenie</h2>
                <button class="close-button" id="closeConfirmModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p id="confirmMessage">Czy na pewno chcesz wykonać tę akcję?</p>
            </div>
            <div class="modal-footer">
                <button id="confirmActionBtn" class="btn btn-primary">
                    <i class="fas fa-check"></i> Tak, wykonaj
                </button>
                <button id="cancelActionBtn" class="btn btn-secondary">
                    <i class="fas fa-ban"></i> Anuluj
                </button>
            </div>
        </div>
    </div>

    <div id="notificationContainer"></div>
    
    <script src="js/common.js"></script>
    <script src="js/leave.js"></script>
</body>
</html>
<?php $conn->close(); ?>
