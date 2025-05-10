document.addEventListener('DOMContentLoaded', function() {
    setupMonthNavigation();
    setupCalendarDayEvents();
    loadEmployeeSchedules();
    
    const modal = document.getElementById('editScheduleModal');
    if (modal) {
        modal.style.display = 'none';
    }

    const addScheduleBtn = document.getElementById('addScheduleBtn');
    if (addScheduleBtn) {
        const newAddBtn = addScheduleBtn.cloneNode(true);
        addScheduleBtn.parentNode.replaceChild(newAddBtn, addScheduleBtn);
        newAddBtn.addEventListener('click', showAddScheduleModal);
    }
    
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
        monthInput.value = currentMonth;
        yearInput.value = currentYear;
        
        const daysInNewMonth = new Date(currentYear, currentMonth, 0).getDate();
        if (currentDay > daysInNewMonth) {
            currentDay = daysInNewMonth;
            dayInput.value = currentDay;
        }
        
        const date = new Date(currentYear, currentMonth - 1, 1);
        const options = { month: 'long', year: 'numeric' };
        currentDateDisplay.textContent = date.toLocaleDateString('pl-PL', options);
        
        window.location.href = `manage_time.php?month=${currentMonth}&year=${currentYear}&day=${currentDay}`;
    }
}

function setupCalendarDayEvents() {
    const calendarDays = document.querySelectorAll('.calendar-day:not(.empty)');
    calendarDays.forEach(day => {
        day.addEventListener('click', function() {
            document.querySelectorAll('.calendar-day').forEach(d => {
                d.classList.remove('selected');
            });

            this.classList.add('selected');
            const selectedDate = this.getAttribute('data-date');
            if (!selectedDate) return;
            const date = new Date(selectedDate);
            document.getElementById('dayInput').value = date.getDate();
            if (document.getElementById('selectedDateDisplay')) {
                const options = { day: 'numeric', month: 'long', year: 'numeric' };
                document.getElementById('selectedDateDisplay').textContent = date.toLocaleDateString('pl-PL', options);
            }
            loadEmployeeSchedules();
        });
    });
}

function loadEmployeeSchedules() {
    const dayInput = document.getElementById('dayInput');
    const monthInput = document.getElementById('monthInput');
    const yearInput = document.getElementById('yearInput');
    const employeeScheduleList = document.getElementById('employeeScheduleList');
    
    const date = `${yearInput.value}-${monthInput.value.padStart(2, '0')}-${dayInput.value.padStart(2, '0')}`;
    
    employeeScheduleList.innerHTML = `
        <tr>
            <td colspan="7" class="text-center">
                <div class="spinner">
                    <i class="fas fa-spinner fa-spin"></i> Wczytywanie danych...
                </div>
            </td>
        </tr>
    `;

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
        setupScheduleActionButtons();
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

function updateSummary(hours, total, present, absent) {
    document.getElementById('totalHours').textContent = hours + 'h';
    document.getElementById('totalEmployees').textContent = total;
    document.getElementById('presentEmployees').textContent = present;
    document.getElementById('absentEmployees').textContent = absent;
}

function setupScheduleActionButtons() {
    document.querySelectorAll('.edit-schedule').forEach(btn => {
        btn.addEventListener('click', function() {
            const scheduleId = this.getAttribute('data-id');
            loadEditScheduleModal(scheduleId);
        });
    });
    
    document.querySelectorAll('.delete-schedule').forEach(btn => {
        btn.addEventListener('click', function() {
            const scheduleId = this.getAttribute('data-id');
            showDeleteScheduleModal(scheduleId);
        });
    });
}

let scheduleToDelete = null;

function showDeleteScheduleModal(scheduleId) {
    scheduleToDelete = scheduleId;
    const modal = document.getElementById('deleteScheduleModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.classList.add('modal-open');
        setupDeleteScheduleModalEvents();
    }
}
function setupDeleteScheduleModalEvents() {
    const closeBtn = document.getElementById('closeDeleteScheduleModal');
    if (closeBtn) {
        closeBtn.onclick = closeDeleteScheduleModal;
    }

    const cancelBtn = document.getElementById('cancelDeleteScheduleBtn');
    if (cancelBtn) {
        cancelBtn.onclick = closeDeleteScheduleModal;
    }

    const confirmBtn = document.getElementById('confirmDeleteScheduleBtn');
    if (confirmBtn) {
        confirmBtn.onclick = confirmDeleteSchedule;
    }

    const modal = document.getElementById('deleteScheduleModal');
    if (modal) {
        const modalContent = modal.querySelector('.modal-content');
        
        modal.onclick = function(e) {
            if (e.target === modal) {
                closeDeleteScheduleModal();
            }
        };

        if (modalContent) {
            modalContent.onclick = function(e) {
                e.stopPropagation();
            };
        }
    }
}

function closeDeleteScheduleModal() {
    const modal = document.getElementById('deleteScheduleModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');
    }
    scheduleToDelete = null;
}

function confirmDeleteSchedule() {
    if (!scheduleToDelete) {
        alert('Błąd: Nie znaleziono ID harmonogramu do usunięcia.');
        closeDeleteScheduleModal();
        return;
    }
    
    document.body.style.cursor = 'wait';
    const confirmBtn = document.getElementById('confirmDeleteScheduleBtn');
    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Usuwanie...';
    }
    
    fetch('php/delete_schedule.php?id='+scheduleToDelete, {
        method: 'GET',
        cache: 'no-cache' 
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Server returned ${response.status}: ${response.statusText}`);
        }
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch(e) {
                console.error("Error parsing JSON:", text);
                throw new Error("Invalid server response: " + text.substring(0, 100));
            }
        });
    })
    .then(data => {
        closeDeleteScheduleModal();
        
        if (data.success) {
            alert(data.message || 'Harmonogram został usunięty pomyślnie!');
        } else {
            alert(data.message || 'Wystąpił błąd podczas usuwania harmonogramu.');
        }
        loadEmployeeSchedules();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Wystąpił błąd podczas usuwania: ' + error.message);
    })
    .finally(() => {
        document.body.style.cursor = 'default';
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="fas fa-trash"></i> Tak, usuń';
        }
    });
}

function loadEditScheduleModal(scheduleId) {
    document.body.style.cursor = 'wait';
    
    fetch('php/get_schedule.php?id='+scheduleId)
    .then(response => {
        if (!response.ok) {
            throw new Error(`Server returned ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        
        document.getElementById('scheduleId').value = data.id;
        document.getElementById('startTime').value = data.godzina_rozpoczecia.substring(0, 5);
        document.getElementById('endTime').value = data.godzina_zakonczenia.substring(0, 5);
        
        let formStatus = 'present'; 
        
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
                formStatus = data.status; 
        }
        
        document.getElementById('status').value = formStatus;
        
        const modal = document.getElementById('editScheduleModal');
        modal.style.display = 'flex';
        document.body.classList.add('modal-open');
        
        setupEditModalEvents();
        
        document.body.style.cursor = 'default';
    })
    .catch(error => {
        console.error('Error fetching schedule data:', error);
        alert('Wystąpił błąd podczas pobierania danych harmonogramu: ' + error.message);
        document.body.style.cursor = 'default';
    });
}

function closeEditScheduleModal() {
    const modal = document.getElementById('editScheduleModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');
    }
}

function setupEditModalEvents() {
    const closeBtn = document.getElementById('closeEditScheduleModal');
    if (closeBtn) {
        closeBtn.onclick = closeEditScheduleModal;
    }
    
    const cancelBtn = document.getElementById('cancelScheduleBtn');
    if (cancelBtn) {
        cancelBtn.onclick = closeEditScheduleModal;
    }
    
    const saveBtn = document.getElementById('saveScheduleBtn');
    if (saveBtn) {
        saveBtn.onclick = saveScheduleChanges;
    }
    
    const modal = document.getElementById('editScheduleModal');
    const modalContent = modal.querySelector('.modal-content');
    
    if (modal) {
        modal.onclick = function(e) {
            if (e.target === modal) {
                closeEditScheduleModal();
            }
        };
        
        if (modalContent) {
            modalContent.onclick = function(e) {
                e.stopPropagation();
            };
        }
    }
}

function saveScheduleChanges() {
    const form = document.getElementById('editScheduleForm');
    
    const scheduleId = document.getElementById('scheduleId').value;
    const startTime = document.getElementById('startTime').value;
    const endTime = document.getElementById('endTime').value;
    const status = document.getElementById('status').value;
    
    if (!scheduleId || !startTime || !endTime || !status) {
        alert('Proszę wypełnić wszystkie wymagane pola.');
        return;
    }
    
    document.body.style.cursor = 'wait';
    
    const formData = new FormData();
    formData.append('id', scheduleId);
    formData.append('godzina_rozpoczecia', startTime);
    formData.append('godzina_zakonczenia', endTime);
    formData.append('status', status);
    
    fetch('php/update_schedule.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error(`Server error (${response.status}): ${text}`);
            });
        }
        return response.json();
    })
    .then(data => {
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

function showAddScheduleModal() {
    const dayInput = document.getElementById('dayInput');
    const monthInput = document.getElementById('monthInput');
    const yearInput = document.getElementById('yearInput');
    const selectedDate = `${yearInput.value}-${monthInput.value.padStart(2, '0')}-${dayInput.value.padStart(2, '0')}`;
    
    document.getElementById('selectedDate').value = selectedDate;
    
    loadEmployeesForDropdown();
    
    const modal = document.getElementById('addScheduleModal');
    modal.style.display = 'flex';
    document.body.classList.add('modal-open');
    
    setupAddModalEvents();
}

function loadEmployeesForDropdown() {
    const employeeSelect = document.getElementById('employeeSelect');
    employeeSelect.innerHTML = '<option value="">Loading...</option>';
    
    fetch('php/get_employees.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`Server returned ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            let options = '<option value="">Wybierz pracownika</option>';
            data.forEach(employee => {
                options += `<option value="${employee.id}" data-department="${employee.dzial}">${employee.nazwisko} ${employee.imie}</option>`;
            });
            employeeSelect.innerHTML = options;
            
            employeeSelect.addEventListener('change', updateDepartment);
        })
        .catch(error => {
            console.error('Error loading employees:', error);
            employeeSelect.innerHTML = '<option value="">Błąd podczas wczytywania pracowników</option>';
            alert('Nie udało się załadować listy pracowników: ' + error.message);
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

function setupAddModalEvents() {
    const modal = document.getElementById('addScheduleModal');
    const modalContent = modal.querySelector('.modal-content');
    
    const closeBtn = document.getElementById('closeAddScheduleModal');
    const cancelBtn = document.getElementById('cancelAddScheduleBtn');
    const saveBtn = document.getElementById('saveAddScheduleBtn');
    
    document.querySelectorAll('#closeAddScheduleModal, #cancelAddScheduleBtn, #saveAddScheduleBtn').forEach(btn => {
        const newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);
    });
    
    const closeButton = document.getElementById('closeAddScheduleModal');
    const cancelButton = document.getElementById('cancelAddScheduleBtn');
    const saveButton = document.getElementById('saveAddScheduleBtn');
    
    if (closeButton) {
        closeButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeAddScheduleModal();
        });
    }
    
    if (cancelButton) {
        cancelButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeAddScheduleModal();
        });
    }
    
    if (saveButton) {
        saveButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            this.disabled = true;
            
            saveAddSchedule();
        });
    }
    
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeAddScheduleModal();
        }
    });
    if (modalContent) {
        modalContent.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
}

function closeAddScheduleModal() {
    const modal = document.getElementById('addScheduleModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');
        
        const form = document.getElementById('addScheduleForm');
        if (form) form.reset();
        
        const deptDisplay = document.getElementById('departmentDisplay');
        if (deptDisplay) deptDisplay.value = '';
        
        const saveBtn = document.getElementById('saveAddScheduleBtn');
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save"></i> Zapisz';
        }
    }
}

function saveAddSchedule() {
    
    const form = document.getElementById('addScheduleForm');
    
    const employeeId = document.getElementById('employeeSelect').value;
    const startTime = document.getElementById('addStartTime').value;
    const endTime = document.getElementById('addEndTime').value;
    const status = document.getElementById('addStatus').value;
    
    if (!employeeId) {
        alert('Proszę wybrać pracownika.');
        enableSaveButton();
        return;
    }
    
    if (!startTime || !endTime) {
        alert('Proszę wprowadzić godziny rozpoczęcia i zakończenia.');
        enableSaveButton();
        return;
    }

    if (status === 'present' && startTime >= endTime) {
        alert('Godzina zakończenia musi być późniejsza niż godzina rozpoczęcia.');
        enableSaveButton();
        return;
    }
    
    document.body.style.cursor = 'wait';
    const saveBtn = document.getElementById('saveAddScheduleBtn');
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Zapisywanie...';
    }
    
    const formData = new FormData(form);
    
    fetch('php/add_schedule.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        
        return response.text().then(text => {

            try {
                const data = JSON.parse(text);
                return {
                    ok: response.ok,
                    data: data
                };
            } catch (e) {
                console.error("Failed to parse JSON:", e);
                return {
                    ok: false,
                    data: { success: false, message: "Invalid server response: " + text.substring(0, 50) }
                };
            }
        });
    })
    .then(result => {
        if (result.ok && result.data.success) {

            alert(result.data.message || 'Harmonogram został dodany pomyślnie!');
            closeAddScheduleModal();
            loadEmployeeSchedules(); 
        } else {
            console.error("Save failed:", result.data);
            alert('Wystąpił błąd podczas przetwarzania odpowiedzi serwera. Proszę odświeżyć stronę, aby sprawdzić, czy dane zostały zapisane.');
            closeAddScheduleModal();
            loadEmployeeSchedules(); 
        }
    })
    .catch(error => {
        console.error('Network error:', error);
        alert('Wystąpił błąd sieciowy: ' + error.message + '\nProszę odświeżyć stronę, aby sprawdzić, czy dane zostały zapisane.');
        closeAddScheduleModal();
        loadEmployeeSchedules(); 
    })
    .finally(() => {
        document.body.style.cursor = 'default';
        enableSaveButton();
    });
}

function enableSaveButton() {
    const saveBtn = document.getElementById('saveAddScheduleBtn');
    if (saveBtn) {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fas fa-save"></i> Zapisz';
    }
}

