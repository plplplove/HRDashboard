<?php
session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    exit('Brak dostępu');
}

$host = 'localhost';
$db = 'HRDASHBOARD';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    exit('Błąd połączenia z bazą danych');
}

$departments = $conn->query("SELECT DISTINCT dzial FROM pracownicy ORDER BY dzial");

$defaultDepartments = [
    'Dział HR', 
    'Dział Operacyjny', 
    'Dział Obsługi technicznej',
    'Dział Sprzedaży i baru',
    'Dział Finansowy',
    'Zarząd'
];

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($isAjax) {
?>
<div class="modal" id="addEmployeeModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-user-plus"></i> Dodaj nowego pracownika</h2>
            <button class="close-button" id="closeAddModal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="addEmployeeForm">
                <div class="form-group">
                    <label for="imie">Imię</label>
                    <input type="text" id="imie" name="imie" required>
                </div>
                
                <div class="form-group">
                    <label for="nazwisko">Nazwisko</label>
                    <input type="text" id="nazwisko" name="nazwisko" required>
                </div>
                
                <div class="form-group">
                    <label for="dzial">Dział</label>
                    <select id="dzial" name="dzial" required>
                        <option value="">Wybierz dział</option>
                        <?php
                        if ($departments && $departments->num_rows > 0) {
                            while($dept = $departments->fetch_assoc()) {
                                echo "<option value='" . htmlspecialchars($dept['dzial']) . "'>" . htmlspecialchars($dept['dzial']) . "</option>";
                            }
                        } else {
                            foreach ($defaultDepartments as $dept) {
                                echo "<option value='" . htmlspecialchars($dept) . "'>" . htmlspecialchars($dept) . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="stanowisko">Stanowisko</label>
                    <input type="text" id="stanowisko" name="stanowisko" required>
                </div>
                
                <div class="form-group">
                    <label for="telefon">Telefon</label>
                    <input type="tel" id="telefon" name="telefon">
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email">
                </div>
                
                <div class="form-group checkbox-group">
                    <input type="checkbox" id="urlop" name="urlop">
                    <label for="urlop">Czy na urlopie</label>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="saveAddBtn">
                <i class="fas fa-save"></i> Zapisz
            </button>
            <button type="button" class="btn btn-secondary" id="cancelAddBtn">
                <i class="fas fa-times"></i> Anuluj
            </button>
        </div>
    </div>
</div>
<?php 
    $conn->close();
    exit();
}

header("Location: manage_employees.php");
exit();
?>