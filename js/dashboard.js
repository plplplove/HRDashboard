/**
 * Dashboard JavaScript functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard JS loaded');
    
    // Dashboard-specific functionality
    
    // Handle dashboard-specific functionality for approve/reject buttons
    const approveButtons = document.querySelectorAll('.approve-btn');
    const rejectButtons = document.querySelectorAll('.reject-btn');
    
    approveButtons.forEach(button => {
        button.addEventListener('click', function() {
            const leaveId = this.getAttribute('data-id');
            if (leaveId) {
                if (confirm('Czy na pewno chcesz zatwierdzić ten wniosek?')) {
                    // Functionality to approve leave request
                    showNotification('Wniosek zatwierdzony pomyślnie', 'success');
                    // In real implementation, you would redirect to manage_leave.php with proper parameters
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
                    // Functionality to reject leave request
                    showNotification('Wniosek odrzucony', 'success');
                    // In real implementation, you would redirect to manage_leave.php with proper parameters
                }
            } else {
                showNotification('Przejdź do sekcji wniosków urlopowych, aby odrzucić wniosek', 'info');
            }
        });
    });
});
