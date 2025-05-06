document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard JS loaded');

    const approveButtons = document.querySelectorAll('.approve-btn');
    const rejectButtons = document.querySelectorAll('.reject-btn');
    
    approveButtons.forEach(button => {
        button.addEventListener('click', function() {
            const leaveId = this.getAttribute('data-id');
            if (leaveId) {
                if (confirm('Czy na pewno chcesz zatwierdzić ten wniosek?')) {
                    showNotification('Wniosek zatwierdzony pomyślnie', 'success');
                }
            } else {
                showNotification('Przejdź do sekcji wniosków urlopowych, aby zatwierdzić wniosek', 'info');
            }
        });
    });
    
    rejectButtons.forEach(button => {
        button.addEventListener('click', function() {
            const leaveId = this.getAttribute('data-id');
            if (leaveId) {
                if (confirm('Czy na pewno chcesz odrzucić ten wniosek?')) {
                    showNotification('Wniosek odrzucony', 'success');
                }
            } else {
                showNotification('Przejdź do sekcji wniosków urlopowych, aby odrzucić wniosek', 'info');
            }
        });
    });
});
