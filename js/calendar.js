// Calendar and Time Management JavaScript Functions

document.addEventListener('DOMContentLoaded', function() {
    // Initialize calendar functionality, but don't show modal
    setupMonthNavigation();
    setupCalendarDayEvents();
    loadEmployeeSchedules();
    
    // Make sure modal is hidden
    const modal = document.getElementById('editScheduleModal');
    if (modal) {
        modal.style.display = 'none';
    }

    // Connect the Add Schedule button - ONLY wire up the click handler, don't show modal yet
    const addScheduleBtn = document.getElementById('addScheduleBtn');
    if (addScheduleBtn) {
        // Remove any existing handlers first
        const newAddBtn = addScheduleBtn.cloneNode(true);
        addScheduleBtn.parentNode.replaceChild(newAddBtn, addScheduleBtn);
        newAddBtn.addEventListener('click', showAddScheduleModal);
    }
    
    // Make sure modals are hidden initially
    const addModal = document.getElementById('addScheduleModal');
    if (addModal) {
        addModal.style.display = 'none';
    }
    
    const editModal = document.getElementById('editScheduleModal');
    if (editModal) {
        editModal.style.display = 'none';
    }

    const deleteModal = document.getElementById('deleteScheduleModal');
    if (deleteModal) {
        deleteModal.style.display = 'none';
    }
});

// Setup month navigation (prev/next month)
function setupMonthNavigation() {
    const prevMonthBtn = document.getElementById('prevMonth');
    const nextMonthBtn = document.getElementById('nextMonth');
    const monthInput = document.getElementById('monthInput');
    const yearInput = document.getElementById('yearInput');
    const dayInput = document.getElementById('dayInput');
    const currentDateDisplay = document.getElementById('currentDate');
    
    let currentMonth = parseInt(monthInput.value);
    let currentYear = parseInt(yearInput.value);
    let currentDay = parseInt(dayInput.value);
    
    prevMonthBtn.addEventListener('click', function(e) {
        e.preventDefault();
        currentMonth--;
        if (currentMonth < 1) {
            currentMonth = 12;
            currentYear--;
        }
        updateMonthDisplay();
    });
    
    nextMonthBtn.addEventListener('click', function(e) {
        e.preventDefault();
        currentMonth++;
        if (currentMonth > 12) {
            currentMonth = 1;
            currentYear++;
        }
        updateMonthDisplay();
    });
    
    function updateMonthDisplay() {
        // Update hidden inputs
        monthInput.value = currentMonth;
        yearInput.value = currentYear;
        
        // Ensure the day is valid for the new month
        const daysInNewMonth = new Date(currentYear, currentMonth, 0).getDate();
        if (currentDay > daysInNewMonth) {
            currentDay = daysInNewMonth;
            dayInput.value = currentDay;
        }
        
        // Update the date display with month and year
        const date = new Date(currentYear, currentMonth - 1, 1);
        const options = { month: 'long', year: 'numeric' };
        currentDateDisplay.textContent = date.toLocaleDateString('pl-PL', options);
        
        // Reload the page with new month/year but preserve the day
        window.location.href = `manage_time.php?month=${currentMonth}&year=${currentYear}&day=${currentDay}`;
    }
}

// Keep the original setupCalendarDayEvents function to maintain the right panel functionality
function setupCalendarDayEvents() {
    const calendarDays = document.querySelectorAll('.calendar-day:not(.empty)');
    calendarDays.forEach(day => {
        day.addEventListener('click', function() {
            // Remove selected class from all days
            document.querySelectorAll('.calendar-day').forEach(d => {
                d.classList.remove('selected');
            });
            
            // Add selected class to clicked day
            this.classList.add('selected');
            
            // Get selected date
            const selectedDate = this.getAttribute('data-date');
            if (!selectedDate) return;
            
            // Update form inputs but keep the month and year the same
            const date = new Date(selectedDate);
            document.getElementById('dayInput').value = date.getDate();
            
            // Update display for the right side panel
            if (document.getElementById('selectedDateDisplay')) {
                const options = { day: 'numeric', month: 'long', year: 'numeric' };
                document.getElementById('selectedDateDisplay').textContent = date.toLocaleDateString('pl-PL', options);
            }
            
            // Load employee schedules for the selected day to display in right panel
            loadEmployeeSchedules();
        });
    });
}

// Load employee schedules for a selected date
function loadEmployeeSchedules() {
    const dayInput = document.getElementById('dayInput');
    const monthInput = document.getElementById('monthInput');
    const yearInput = document.getElementById('yearInput');
    const employeeScheduleList = document.getElementById('employeeScheduleList');
    
    const date = `${yearInput.value}-${monthInput.value.padStart(2, '0')}-${dayInput.value.padStart(2, '0')}`;
    
    // Show loading spinner
    employeeScheduleList.innerHTML = `
        <tr>
            <td colspan="7" class="text-center">
                <div class="spinner">
                    <i class="fas fa-spinner fa-spin"></i> Wczytywanie danych...
                </div>
            </td>
        </tr>
    `;
    
    // Make AJAX request to get employee schedules (without department filter)
    fetch(`manage_time.php?action=getEmployeesByDate&date=${date}&_=${Date.now()}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.length === 0) {
            employeeScheduleList.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center">
                        <div class="no-data">Brak wpisów w grafiku dla wybranego dnia</div>
                    </td>
                </tr>
            `;
            updateSummary(0, 0, 0, 0);
            return;
        }
        
        let output = '';
        let totalHours = 0;
        let totalEmployees = data.length;
        let present = 0;
        let absent = 0;
        
        data.forEach(employee => {
            const hoursMatch = employee.hours.match(/^(\d+)h$/);
            const hours = hoursMatch ? parseInt(hoursMatch[1]) : 0;
            totalHours += hours;
            
            if (employee.status === 'leave') {
                absent++;
            } else {
                present++;
            }
            
            const statusClass = employee.status === 'leave' ? 'day-off' : 
                              (employee.status === 'half-day' ? 'half-day' : '');
            
            const statusText = employee.status === 'leave' ? 'Na urlopie' : 
                             (employee.status === 'half-day' ? 'Pół dnia' : 'Obecny');
            
            const scheduleId = employee.schedule_id || employee.id;
            
            output += `
                <tr>
                    <td>${employee.name}</td>
                    <td>${employee.department}</td>
                    <td>${employee.start_time}</td>
                    <td>${employee.end_time}</td>
                    <td>${employee.hours}</td>
                    <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                    <td class="actions">
                        <i class="fas fa-edit edit-schedule" data-id="${scheduleId}" title="Edytuj"></i>
                        <i class="fas fa-trash-alt delete-schedule" data-id="${scheduleId}" title="Usuń"></i>
                    </td>
                </tr>
            `;
        });
        
        employeeScheduleList.innerHTML = output;
        
        // Add event listeners to the new action buttons
        setupScheduleActionButtons();
        
        // Update summary information
        updateSummary(totalHours, totalEmployees, present, absent);
    })
    .catch(error => {
        console.error('Error fetching employee schedules:', error);
        employeeScheduleList.innerHTML = `
            <tr>
                <td colspan="7" class="text-center">
                    <div class="error">Wystąpił błąd podczas wczytywania danych.</div>
                </td>
            </tr>
        `;
        updateSummary(0, 0, 0, 0);
    });
}

// Update summary section with calculated values
function updateSummary(hours, total, present, absent) {
    document.getElementById('totalHours').textContent = hours + 'h';
    document.getElementById('totalEmployees').textContent = total;
    document.getElementById('presentEmployees').textContent = present;
    document.getElementById('absentEmployees').textContent = absent;
}

// Set up schedule management buttons
function setupScheduleButtons() {
    const addScheduleBtn = document.getElementById('addScheduleBtn');
    if (addScheduleBtn) {
        addScheduleBtn.addEventListener('click', function() {
            // In a real application, this would open a modal to add a new schedule entry
            alert('Dodaj nowy wpis do grafiku - ta funkcja będzie dostępna wkrótce.');
        });
    }
}

// Set up action buttons for each employee schedule entry
function setupScheduleActionButtons() {
    // Edit buttons
    document.querySelectorAll('.edit-schedule').forEach(btn => {
        btn.addEventListener('click', function() {
            const scheduleId = this.getAttribute('data-id');
            loadEditScheduleModal(scheduleId);
        });
    });
    
    // Delete buttons - add event listeners to show confirmation dialog
    document.querySelectorAll('.delete-schedule').forEach(btn => {
        btn.addEventListener('click', function() {
            const scheduleId = this.getAttribute('data-id');
            showDeleteScheduleModal(scheduleId);
        });
    });
}

// Variables to store the ID of the schedule to delete
let scheduleToDelete = null;

// Function to show delete confirmation modal
function showDeleteScheduleModal(scheduleId) {
    scheduleToDelete = scheduleId;
    const modal = document.getElementById('deleteScheduleModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.classList.add('modal-open');
        setupDeleteScheduleModalEvents();
    }
}

// Set up event listeners for the delete confirmation modal
function setupDeleteScheduleModalEvents() {
    // Close button (X)
    const closeBtn = document.getElementById('closeDeleteScheduleModal');
    if (closeBtn) {
        closeBtn.onclick = closeDeleteScheduleModal;
    }
    
    // Cancel button
    const cancelBtn = document.getElementById('cancelDeleteScheduleBtn');
    if (cancelBtn) {
        cancelBtn.onclick = closeDeleteScheduleModal;
    }
    
    // Confirm delete button
    const confirmBtn = document.getElementById('confirmDeleteScheduleBtn');
    if (confirmBtn) {
        confirmBtn.onclick = confirmDeleteSchedule;
    }
    
    // Close when clicking outside
    const modal = document.getElementById('deleteScheduleModal');
    if (modal) {
        modal.onclick = function(e) {
            if (e.target === modal) {
                closeDeleteScheduleModal();
            }
        };
    }
}

// Function to close delete modal
function closeDeleteScheduleModal() {
    const modal = document.getElementById('deleteScheduleModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');
    }
    scheduleToDelete = null;
}

// Function to confirm and process schedule deletion - improved with better reliability
function confirmDeleteSchedule() {
    if (!scheduleToDelete) {
        alert('Błąd: Nie znaleziono ID harmonogramu do usunięcia.');
        closeDeleteScheduleModal();
        return;
    }
    
    console.log("Attempting to delete schedule ID:", scheduleToDelete);
    
    // Show loading state
    document.body.style.cursor = 'wait';
    const confirmBtn = document.getElementById('confirmDeleteScheduleBtn');
    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Usuwanie...';
    }
    
    // Send delete request
    fetch(`delete_schedule.php?id=${scheduleToDelete}`, {
        method: 'GET',
        cache: 'no-cache' // Prevent caching
    })
    .then(response => {
        console.log("Delete response status:", response.status);
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch(e) {
                console.error("Error parsing JSON:", text);
                throw new Error("Invalid server response");
            }
        });
    })
    .then(data => {
        console.log("Delete response:", data);
        closeDeleteScheduleModal();
        
        if (data.success) {
            alert(data.message || 'Harmonogram został usunięty pomyślnie!');
        } else {
            alert(data.message || 'Wystąpił błąd podczas usuwania harmonogramu.');
        }
        
        // Always reload the data
        loadEmployeeSchedules();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Wystąpił błąd: ' + error.message);
    })
    .finally(() => {
        document.body.style.cursor = 'default';
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="fas fa-trash"></i> Tak, usuń';
        }
    });
}

// Function to load edit modal with schedule data (only called when clicking edit button)
function loadEditScheduleModal(scheduleId) {
    // Show loading indicator
    document.body.style.cursor = 'wait';
    
    console.log("Loading schedule data for ID:", scheduleId);
    
    // Fetch schedule data
    fetch(`get_schedule.php?id=${scheduleId}`)
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log("Received schedule data:", data);
        
        // Populate form with fetched data
        document.getElementById('scheduleId').value = data.id;
        document.getElementById('startTime').value = data.godzina_rozpoczecia.substring(0, 5);
        document.getElementById('endTime').value = data.godzina_zakonczenia.substring(0, 5);
        
        // Map database status values to form status values
        let formStatus = 'present'; // Default value
        
        // Map database status values to form select options
        switch (data.status) {
            case 'obecny':
                formStatus = 'present';
                break;
            case 'urlop':
                formStatus = 'leave';
                break;
            case 'wakacje':
                formStatus = 'vacation';
                break;
            default:
                formStatus = data.status; // Use original if no mapping needed
        }
        
        // Set the dropdown to the correct value
        document.getElementById('status').value = formStatus;
        
        // Show modal
        const modal = document.getElementById('editScheduleModal');
        modal.style.display = 'flex';
        document.body.classList.add('modal-open');
        
        // Setup modal buttons
        setupEditModalEvents();
        
        // Reset cursor
        document.body.style.cursor = 'default';
    })
    .catch(error => {
        console.error('Error fetching schedule data:', error);
        alert('Wystąpił błąd podczas pobierania danych harmonogramu.');
        document.body.style.cursor = 'default';
    });
}

// Function to close edit modal
function closeEditScheduleModal() {
    const modal = document.getElementById('editScheduleModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');
    }
}

// Function to set up the modal event listeners
function setupEditModalEvents() {
    // Close button (X)
    const closeBtn = document.getElementById('closeEditScheduleModal');
    if (closeBtn) {
        closeBtn.onclick = closeEditScheduleModal;
    }
    
    // Cancel button
    const cancelBtn = document.getElementById('cancelScheduleBtn');
    if (cancelBtn) {
        cancelBtn.onclick = closeEditScheduleModal;
    }
    
    // Save button - explicitly set onclick
    const saveBtn = document.getElementById('saveScheduleBtn');
    if (saveBtn) {
        saveBtn.onclick = saveScheduleChanges;
    }
    
    // Close when clicking outside
    const modal = document.getElementById('editScheduleModal');
    if (modal) {
        modal.onclick = function(e) {
            if (e.target === modal) {
                closeEditScheduleModal();
            }
        };
    }
}

// Function to save schedule changes when "Save Changes" button is clicked
function saveScheduleChanges() {
    // Get form data
    const form = document.getElementById('editScheduleForm');
    
    // Basic validation
    const scheduleId = document.getElementById('scheduleId').value;
    const startTime = document.getElementById('startTime').value;
    const endTime = document.getElementById('endTime').value;
    const status = document.getElementById('status').value;
    
    console.log("Saving schedule:", {id: scheduleId, start: startTime, end: endTime, status: status});
    
    if (!scheduleId || !startTime || !endTime || !status) {
        alert('Proszę wypełnić wszystkie wymagane pola.');
        return;
    }
    
    // Show loading state
    document.body.style.cursor = 'wait';
    
    // Prepare form data manually to ensure correct values
    const formData = new FormData();
    formData.append('id', scheduleId);
    formData.append('godzina_rozpoczecia', startTime);
    formData.append('godzina_zakonczenia', endTime);
    formData.append('status', status);
    
    // Debug log the data being sent
    console.log("Form data being sent:");
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
    
    // Send AJAX request
    fetch('update_schedule.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log("Response status:", response.status);
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error(`Server error (${response.status}): ${text}`);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log("Server response:", data);
        if (data.success) {
            alert(data.message || 'Harmonogram został zaktualizowany pomyślnie!');
            closeEditScheduleModal();
            loadEmployeeSchedules(); // Reload the table
        } else {
            alert(data.message || 'Wystąpił błąd podczas aktualizacji harmonogramu.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Wystąpił błąd: ' + error.message);
    })
    .finally(() => {
        document.body.style.cursor = 'default';
    });
}

// Add Schedule Modal functionality - opens only when clicking the Add button
function showAddScheduleModal() {
    // Set the current selected date
    const dayInput = document.getElementById('dayInput');
    const monthInput = document.getElementById('monthInput');
    const yearInput = document.getElementById('yearInput');
    const selectedDate = `${yearInput.value}-${monthInput.value.padStart(2, '0')}-${dayInput.value.padStart(2, '0')}`;
    
    document.getElementById('selectedDate').value = selectedDate;
    
    // Load employees for the dropdown
    loadEmployeesForDropdown();
    
    // Show modal
    const modal = document.getElementById('addScheduleModal');
    modal.style.display = 'flex';
    document.body.classList.add('modal-open');
    
    // Set up event listeners
    setupAddModalEvents();
}

function loadEmployeesForDropdown() {
    // Show loading in the dropdown
    const employeeSelect = document.getElementById('employeeSelect');
    employeeSelect.innerHTML = '<option value="">Loading...</option>';
    
    // Fetch employees from server
    fetch('get_employees.php')
        .then(response => response.json())
        .then(data => {
            let options = '<option value="">Wybierz pracownika</option>';
            data.forEach(employee => {
                options += `<option value="${employee.id}" data-department="${employee.dzial}">${employee.nazwisko} ${employee.imie}</option>`;
            });
            employeeSelect.innerHTML = options;
            
            // Add change event to update department
            employeeSelect.addEventListener('change', updateDepartment);
        })
        .catch(error => {
            console.error('Error loading employees:', error);
            employeeSelect.innerHTML = '<option value="">Error loading employees</option>';
        });
}

function updateDepartment() {
    const employeeSelect = document.getElementById('employeeSelect');
    const departmentDisplay = document.getElementById('departmentDisplay');
    const selectedOption = employeeSelect.options[employeeSelect.selectedIndex];
    
    if (selectedOption && selectedOption.value) {
        departmentDisplay.value = selectedOption.dataset.department || '';
    } else {
        departmentDisplay.value = '';
    }
}

// Improved modal event setup function with proper closing
function setupAddModalEvents() {
    // Close button (X) - ensure it works
    const closeBtn = document.getElementById('closeAddScheduleModal');
    if (closeBtn) {
        // Replace any existing handler to avoid duplicates
        const newCloseBtn = closeBtn.cloneNode(true);
        closeBtn.parentNode.replaceChild(newCloseBtn, closeBtn);
        newCloseBtn.addEventListener('click', closeAddScheduleModal);
    }
    
    // Cancel button - ensure it works
    const cancelBtn = document.getElementById('cancelAddScheduleBtn');
    if (cancelBtn) {
        // Replace any existing handler to avoid duplicates
        const newCancelBtn = cancelBtn.cloneNode(true);
        cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
        newCancelBtn.addEventListener('click', closeAddScheduleModal);
    }
    
    // Save button
    const saveBtn = document.getElementById('saveAddScheduleBtn');
    if (saveBtn) {
        // Replace any existing handler to avoid duplicates
        const newSaveBtn = saveBtn.cloneNode(true);
        saveBtn.parentNode.replaceChild(newSaveBtn, saveBtn);
        newSaveBtn.addEventListener('click', saveAddSchedule);
    }
    
    // Close when clicking outside the modal
    const modal = document.getElementById('addScheduleModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddScheduleModal();
            }
        });
    }
}

// Properly close the add schedule modal
function closeAddScheduleModal() {
    const modal = document.getElementById('addScheduleModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');
        
        // Reset form fields
        const form = document.getElementById('addScheduleForm');
        if (form) form.reset();
        
        // Reset department display
        const deptDisplay = document.getElementById('departmentDisplay');
        if (deptDisplay) deptDisplay.value = '';
    }
}

function saveAddSchedule() {
    // Get form data
    const form = document.getElementById('addScheduleForm');
    
    // Validate form
    const employeeId = document.getElementById('employeeSelect').value;
    const startTime = document.getElementById('addStartTime').value;
    const endTime = document.getElementById('addEndTime').value;
    const status = document.getElementById('addStatus').value;
    
    // Basic validation
    if (!employeeId) {
        alert('Proszę wybrać pracownika.');
        return;
    }
    
    if (!startTime || !endTime) {
        alert('Proszę wprowadzić godziny rozpoczęcia i zakończenia.');
        return;
    }
    
    // Check if end time is after start time (only for present status)
    if (status === 'present' && startTime >= endTime) {
        alert('Godzina zakończenia musi być późniejsza niż godzina rozpoczęcia.');
        return;
    }
    
    // Show loading state
    document.body.style.cursor = 'wait';
    const saveBtn = document.getElementById('saveAddScheduleBtn');
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Zapisywanie...';
    }
    
    // Create form data for submission
    const formData = new FormData(form);
    
    // Send AJAX request
    fetch('add_schedule.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || 'Server error');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert(data.message || 'Harmonogram został dodany pomyślnie!');
            closeAddScheduleModal();
            loadEmployeeSchedules(); // Refresh the schedule view
        } else {
            alert(data.message || 'Wystąpił błąd podczas dodawania harmonogramu.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Wystąpił błąd: ' + error.message);
    })
    .finally(() => {
        document.body.style.cursor = 'default';
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save"></i> Zapisz';
        }
    });
}
