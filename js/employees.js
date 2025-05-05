// Employee Management JavaScript Functions

document.addEventListener('DOMContentLoaded', function() {
    const deleteModal = document.getElementById('deleteModal');
    const closeDeleteModalBtn = document.getElementById('closeDeleteModal');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const resetBtn = document.getElementById('resetBtn');
    const addEmployeeBtn = document.getElementById('addEmployeeBtn');
    const addEmployeeContainer = document.getElementById('addEmployeeContainer');
    
    // Save button references that don't exist yet
    let saveAddBtn, saveEditBtn;
    
    // Variable to store the ID of the employee to delete
    let employeeToDelete = null;
    
    // Reset button functionality
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            window.location.href = 'manage_employees.php';
        });
    }
    
    // Filter functionality
    const nameSearch = document.getElementById('nameSearch');
    const deptFilter = document.getElementById('deptFilter');
    const onLeaveFilter = document.getElementById('onLeaveFilter');
    
    // Debounce function for search input
    function debounce(func, delay) {
        let timeout;
        return function() {
            const context = this;
            const args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), delay);
        };
    }
    
    // Function to fetch employees based on filters
    function fetchEmployees() {
        const name = nameSearch.value;
        const dept = deptFilter.value;
        const onLeave = onLeaveFilter.checked ? '1' : '';
        
        document.getElementById('searchSpinner').style.display = 'block';
        
        const xhr = new XMLHttpRequest();
        xhr.open('GET', `manage_employees.php?name=${encodeURIComponent(name)}&department=${encodeURIComponent(dept)}&on_leave=${onLeave}`, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                document.getElementById('employeesTableBody').innerHTML = xhr.responseText;
                addEventListenersToButtons();
            }
            document.getElementById('searchSpinner').style.display = 'none';
        };
        
        xhr.send();
    }
    
    // Add debounced search
    if (nameSearch) {
        nameSearch.addEventListener('input', debounce(fetchEmployees, 500));
    }
    
    // Add filter change events
    if (deptFilter) {
        deptFilter.addEventListener('change', fetchEmployees);
    }
    
    if (onLeaveFilter) {
        onLeaveFilter.addEventListener('change', fetchEmployees);
    }
    
    // Add event listeners to dynamically loaded buttons
    function addEventListenersToButtons() {
        // Edit buttons
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                const employeeId = this.getAttribute('data-id');
                loadEditEmployeeModal(employeeId);
            });
        });
        
        // Delete buttons
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const employeeId = this.getAttribute('data-id');
                showDeleteModal(employeeId);
            });
        });
    }
    
    // Initial setup for buttons
    addEventListenersToButtons();
    
    // Delete modal functionality
    if (closeDeleteModalBtn) {
        closeDeleteModalBtn.addEventListener('click', hideDeleteModal);
    }
    
    if (cancelDeleteBtn) {
        cancelDeleteBtn.addEventListener('click', hideDeleteModal);
    }
    
    // When user clicks outside of the modal, close it
    window.addEventListener('click', function(event) {
        if (event.target === deleteModal) {
            hideDeleteModal();
        }
    });
    
    // Confirm delete action
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            if (employeeToDelete) {
                // Send delete request to server
                window.location.href = `delete_employee.php?id=${employeeToDelete}`;
            }
        });
    }
    
    // Add Employee Modal Functionality
    if (addEmployeeBtn) {
        addEmployeeBtn.addEventListener('click', function() {
            loadAddEmployeeModal();
        });
    }
    
    // Delete modal functions
    function showDeleteModal(employeeId) {
        employeeToDelete = employeeId;
        window.showModal('deleteModal');
    }
    
    function hideDeleteModal() {
        employeeToDelete = null;
        window.hideModal('deleteModal');
    }
    
    // Add employee modal functions
    function loadAddEmployeeModal() {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'add_employee.php', true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                addEmployeeContainer.innerHTML = xhr.responseText;
                window.showModal('addEmployeeModal');
                
                // Set up event listeners for the modal
                document.getElementById('closeAddModal').addEventListener('click', closeAddModal);
                document.getElementById('cancelAddBtn').addEventListener('click', closeAddModal);
                document.getElementById('saveAddBtn').addEventListener('click', saveAddEmployee);
            }
        };
        
        xhr.send();
    }
    
    function closeAddModal() {
        window.hideModal('addEmployeeModal', () => {
            addEmployeeContainer.innerHTML = '';
        });
    }
    
    function saveAddEmployee() {
        const formData = window.collectFormData('addEmployeeForm');
        if (!formData) return;
        
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'save_employee.php', true);
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                closeAddModal();
                fetchEmployees();
                window.showNotification('Pracownik został dodany pomyślnie!', 'success');
            } else {
                window.showNotification('Wystąpił błąd podczas zapisywania danych.', 'error');
            }
        };
        
        xhr.send(formData);
    }
    
    // Edit Employee Modal Functionality
    function loadEditEmployeeModal(employeeId) {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', `edit_employee.php?id=${employeeId}`, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                const editEmployeeContainer = document.getElementById('editEmployeeContainer');
                editEmployeeContainer.innerHTML = xhr.responseText;
                window.showModal('editEmployeeModal');
                
                // Set up event listeners for the modal
                document.getElementById('closeEditModal').addEventListener('click', closeEditModal);
                document.getElementById('cancelEditBtn').addEventListener('click', closeEditModal);
                document.getElementById('saveEditBtn').addEventListener('click', function() {
                    saveEditEmployee(employeeId);
                });
            }
        };
        
        xhr.send();
    }
    
    function closeEditModal() {
        window.hideModal('editEmployeeModal', () => {
            document.getElementById('editEmployeeContainer').innerHTML = '';
        });
    }
    
    function saveEditEmployee(employeeId) {
        const formData = window.collectFormData('editEmployeeForm');
        if (!formData) return;
        
        formData.append('id', employeeId);
        
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'update_employee.php', true);
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                closeEditModal();
                fetchEmployees();
                window.showNotification('Dane pracownika zostały zaktualizowane!', 'success');
            } else {
                window.showNotification('Wystąpił błąd podczas zapisywania danych.', 'error');
            }
        };
        
        xhr.send(formData);
    }
});
