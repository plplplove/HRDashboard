document.addEventListener('DOMContentLoaded', function () {
    const deleteModal = document.getElementById('deleteModal');
    const closeDeleteModalBtn = document.getElementById('closeDeleteModal');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const resetBtn = document.getElementById('resetBtn');
    const addEmployeeBtn = document.getElementById('addEmployeeBtn');
    const addEmployeeContainer = document.getElementById('addEmployeeContainer');

    let employeeToDelete = null;

    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
                        window.location.href = 'manage_employees.php';
        });
    }

    const nameSearch = document.getElementById('nameSearch');
    const deptFilter = document.getElementById('deptFilter');
    const onLeaveFilter = document.getElementById('onLeaveFilter');

    function debounce(func, delay) {
        let timeout;
        return function () {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, arguments), delay);
        };
    }

    function addEventListenersToButtons() {
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                loadEditEmployeeModal(id);
            });
        });
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                showDeleteModal(id);
            });
        });
    }

    function showDeleteModal(id) {
        employeeToDelete = id;
        window.showModal('deleteModal');
    }

    function hideDeleteModal() {
        employeeToDelete = null;
        window.hideModal('deleteModal');
    }

    if (closeDeleteModalBtn) closeDeleteModalBtn.addEventListener('click', hideDeleteModal);
    if (cancelDeleteBtn) cancelDeleteBtn.addEventListener('click', hideDeleteModal);
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function () {
            if (employeeToDelete) {
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Usuwanie...';
                this.disabled = true;
                
                fetch(`php/delete_employee.php?id=${employeeToDelete}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Server error');
                        }
                        window.location.href = 'manage_employees.php?delete_success=1';
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        window.location.href = 'manage_employees.php?delete_error=1';
                    });
            }
        });
    }

    if (addEmployeeBtn) {
        addEmployeeBtn.addEventListener('click', () => {
            loadAddEmployeeModal();
        });
    }

    function loadAddEmployeeModal() {
        if (addEmployeeBtn) {
            addEmployeeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ładowanie...';
            addEmployeeBtn.disabled = true;
        }

        fetch('php/add_employee.php', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            addEmployeeContainer.innerHTML = html;

            if (addEmployeeBtn) {
                addEmployeeBtn.innerHTML = '<i class="fas fa-plus"></i> Dodaj pracownika';
                addEmployeeBtn.disabled = false;
            }

            const modal = document.getElementById('addEmployeeModal');
            if (modal) {
                modal.style.display = 'flex';
                document.body.classList.add('modal-open');
                
                document.getElementById('closeAddModal')?.addEventListener('click', closeAddModal);
                document.getElementById('cancelAddBtn')?.addEventListener('click', closeAddModal);
                document.getElementById('saveAddBtn')?.addEventListener('click', saveAddEmployee);
            }
        })
        .catch(error => {
            console.error('Error:', error);

            if (addEmployeeBtn) {
                addEmployeeBtn.innerHTML = '<i class="fas fa-plus"></i> Dodaj pracownika';
                addEmployeeBtn.disabled = false;
            }
        });
    }

    function closeAddModal() {
        const modal = document.getElementById('addEmployeeModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
            
            setTimeout(() => {
                addEmployeeContainer.innerHTML = '';
            }, 300);
        }
    }

    function loadEditEmployeeModal(id) {
        window.showNotification('Ładowanie danych pracownika...', 'info');

        fetch(`php/edit_employee.php?id=${id}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            const container = document.getElementById('editEmployeeContainer');
            container.innerHTML = html;
            
            const modal = document.getElementById('editEmployeeModal');
            if (modal) {
                modal.style.display = 'flex';
                document.body.classList.add('modal-open');
                
                document.getElementById('closeEditModal')?.addEventListener('click', closeEditModal);
                document.getElementById('cancelEditBtn')?.addEventListener('click', closeEditModal);
                document.getElementById('saveEditBtn')?.addEventListener('click', () => {
                    saveEditEmployee(id);
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.showNotification('Błąd podczas ładowania danych: ' + error.message, 'error');
        });
    }

    function closeEditModal() {
        const modal = document.getElementById('editEmployeeModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');

            setTimeout(() => {
                document.getElementById('editEmployeeContainer').innerHTML = '';
            }, 300);
        }
    }

    function saveAddEmployee() {
        const imie = document.getElementById('imie').value.trim();
        const nazwisko = document.getElementById('nazwisko').value.trim();
        const dzial = document.getElementById('dzial').value.trim();
        const stanowisko = document.getElementById('stanowisko').value.trim();
        
        if (!imie || !nazwisko || !dzial || !stanowisko) {
            window.showNotification('Proszę wypełnić wszystkie wymagane pola (Imię, Nazwisko, Dział, Stanowisko).', 'error');
            
            if (!imie) document.getElementById('imie').classList.add('input-error');
            if (!nazwisko) document.getElementById('nazwisko').classList.add('input-error');
            if (!dzial) document.getElementById('dzial').classList.add('input-error');
            if (!stanowisko) document.getElementById('stanowisko').classList.add('input-error');
            
            return;
        }

        const formData = window.collectFormData('addEmployeeForm');
        if (!formData) return;

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'php/save_employee.php', true);
        xhr.onload = function () {
            if (xhr.status === 200) {
                closeAddModal();
                window.location.reload();
            } else {
                window.showNotification('Wystąpił błąd podczas zapisywania danych.', 'error');
            }
        };
        xhr.send(formData);
    }

    function saveEditEmployee(id) {
        // Validate required fields
        const imie = document.getElementById('imie').value.trim();
        const nazwisko = document.getElementById('nazwisko').value.trim();
        const dzial = document.getElementById('dzial').value.trim();
        const stanowisko = document.getElementById('stanowisko').value.trim();
        
        // Check if required fields are filled
        if (!imie || !nazwisko || !dzial || !stanowisko) {
            window.showNotification('Proszę wypełnić wszystkie wymagane pola (Imię, Nazwisko, Dział, Stanowisko).', 'error');
            
            // Highlight empty required fields
            if (!imie) document.getElementById('imie').classList.add('input-error');
            if (!nazwisko) document.getElementById('nazwisko').classList.add('input-error');
            if (!dzial) document.getElementById('dzial').classList.add('input-error');
            if (!stanowisko) document.getElementById('stanowisko').classList.add('input-error');
            
            return;
        }

        const formData = window.collectFormData('editEmployeeForm');
        if (!formData) return;
        formData.append('id', id);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'php/update_employee.php', true);
        xhr.onload = function () {
            if (xhr.status === 200) {
                closeEditModal();
                window.location.reload();
            } else {
                window.showNotification('Wystąpił błąd podczas zapisywania danych.', 'error');
            }
        };
        xhr.send(formData);
    }

    function setupFormListeners(formId) {
        const form = document.getElementById(formId);
        if (form) {
            const requiredFields = form.querySelectorAll('input[required], select[required]');
            requiredFields.forEach(field => {
                field.addEventListener('input', function() {
                    this.classList.remove('input-error');
                });
            });
        }
    }

    const originalLoadAddEmployeeModal = loadAddEmployeeModal;
    loadAddEmployeeModal = function() {
        originalLoadAddEmployeeModal();
        setTimeout(() => setupFormListeners('addEmployeeForm'), 500);
    }

    const originalLoadEditEmployeeModal = loadEditEmployeeModal;
    loadEditEmployeeModal = function(id) {
        originalLoadEditEmployeeModal(id);
        setTimeout(() => setupFormListeners('editEmployeeForm'), 500);
    }

    addEventListenersToButtons();
});