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

$employeeData = null;

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_employees.php");
    exit();
}

$employeeId = intval($_GET['id']);

$sql = "SELECT * FROM pracownicy WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employeeId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $errorMessage = "Pracownik nie został znaleziony.";
} else {
    $employeeData = $result->fetch_assoc();
}
$stmt->close();

$deptQuery = "SELECT DISTINCT dzial FROM pracownicy ORDER BY dzial";
$departments = $conn->query($deptQuery);

$defaultDepartments = ['HR', 'IT', 'Finanse', 'Marketing', 'Administracja'];

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($isAjax) {
?>
<div class="modal" id="editEmployeeModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-user-edit"></i> Edytuj pracownika</h2>
            <button class="close-button" id="closeEditModal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <?php if($employeeData): ?>
            <form id="editEmployeeForm">
                <div class="form-group">
                    <label for="imie">Imię</label>
                    <input type="text" id="imie" name="imie" value="<?= htmlspecialchars($employeeData['imie']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="nazwisko">Nazwisko</label>
                    <input type="text" id="nazwisko" name="nazwisko" value="<?= htmlspecialchars($employeeData['nazwisko']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="dzial">Dział</label>
                    <select id="dzial" name="dzial" required>
                        <option value="">Wybierz dział</option>
                        <?php
                        if ($departments && $departments->num_rows > 0) {
                            while($dept = $departments->fetch_assoc()) {
                                $selected = ($employeeData['dzial'] == $dept['dzial']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($dept['dzial']) . "' $selected>" . htmlspecialchars($dept['dzial']) . "</option>";
                            }
                        } else {
                            foreach ($defaultDepartments as $dept) {
                                $selected = ($employeeData['dzial'] == $dept) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($dept) . "' $selected>" . htmlspecialchars($dept) . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="stanowisko">Stanowisko</label>
                    <input type="text" id="stanowisko" name="stanowisko" value="<?= htmlspecialchars($employeeData['stanowisko']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="telefon">Telefon</label>
                    <input type="tel" id="telefon" name="telefon" value="<?= htmlspecialchars($employeeData['telefon']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($employeeData['email']) ?>" required>
                </div>
                
                <div class="form-group checkbox-group">
                    <input type="checkbox" id="urlop" name="urlop" <?= $employeeData['urlop'] ? 'checked' : '' ?>>
                    <label for="urlop">Czy na urlopie</label>
                </div>
            </form>
            <?php else: ?>
            <div class="no-data">
                Nie można znaleźć danych pracownika.
            </div>
            <?php endif; ?>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="saveEditBtn">
                <i class="fas fa-save"></i> Zapisz zmiany
            </button>
            <button type="button" class="btn btn-secondary" id="cancelEditBtn">
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