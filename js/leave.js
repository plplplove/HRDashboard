document.addEventListener('DOMContentLoaded', function() {
    let currentLeaveId = null;
    let currentAction = null;

    const confirmModal = document.getElementById('confirmModal');
    const confirmMessage = document.getElementById('confirmMessage');
    const confirmActionBtn = document.getElementById('confirmActionBtn');
    const cancelActionBtn = document.getElementById('cancelActionBtn');
    const closeConfirmModal = document.getElementById('closeConfirmModal');
    const overlay = document.getElementById('loadingOverlay');

    function attachActionHandlers() {
        document.querySelectorAll('.approve-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                currentLeaveId = this.getAttribute('data-id');
                currentAction = 'approve';
                confirmMessage.textContent = 'Czy na pewno chcesz zatwierdzić ten wniosek?';
                confirmModal.style.display = 'flex';
            });
        });

        document.querySelectorAll('.reject-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                currentLeaveId = this.getAttribute('data-id');
                currentAction = 'reject';
                confirmMessage.textContent = 'Czy na pewno chcesz odrzucić ten wniosek?';
                confirmModal.style.display = 'flex';
            });
        });
    }

    if (closeConfirmModal) {
        closeConfirmModal.addEventListener('click', function() {
            confirmModal.style.display = 'none';
        });
    }

    if (cancelActionBtn) {
        cancelActionBtn.addEventListener('click', function() {
            confirmModal.style.display = 'none';
        });
    }

    window.addEventListener('click', function(event) {
        if (event.target === confirmModal) {
            confirmModal.style.display = 'none';
        }
    });

    if (confirmActionBtn) {
        confirmActionBtn.addEventListener('click', function() {
            confirmModal.style.display = 'none';
            if (currentLeaveId && currentAction) {
                updateLeaveStatus(currentLeaveId, currentAction);
            }
        });
    }

    function updateLeaveStatus(leaveId, action) {
        if (overlay) overlay.style.display = 'flex';

        const formData = new FormData();
        formData.append('leave_id', leaveId);
        formData.append('action', action);

        fetch('manage_leave.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            return response.json().catch(() => ({
                success: false,
                message: 'Nieprawidłowa odpowiedź z serwera'
            }));
        })
        .then(data => {
            if (overlay) overlay.style.display = 'none';

            if (data.success) {
                showNotification('Akcja została wykonana pomyślnie!', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showNotification('Wystąpił błąd: ' + (data.message || 'Nieznany błąd'), 'error');
            }
        })
        .catch(error => {
            if (overlay) overlay.style.display = 'none';
            showNotification('Błąd połączenia z serwerem', 'error');
            console.error(error);
        });
    }

    const applyFiltersBtn = document.getElementById('applyFiltersBtn');
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', function() {
            loadFilteredData();
        });
    }

    const resetFiltersBtn = document.getElementById('resetFiltersBtn');
    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', function() {
            document.getElementById('statusFilter').value = '';
            document.getElementById('sortOrder').value = 'ASC';
            loadFilteredData();
        });
    }

    function loadFilteredData() {
        if (overlay) overlay.style.display = 'flex';

        const status = document.getElementById('statusFilter').value;
        const order = document.getElementById('sortOrder').value;

        fetch(`manage_leave.php?status=${status}&order=${order}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            if (overlay) overlay.style.display = 'none';
            document.getElementById('leaveTableBody').innerHTML = html;
            attachActionHandlers();
        })
        .catch(error => {
            if (overlay) overlay.style.display = 'none';
            showNotification('Błąd podczas ładowania danych', 'error');
            console.error(error);
        });
    }

    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 4000);
    }

    attachActionHandlers();
    document.addEventListener('contentRefreshed', attachActionHandlers);
});
