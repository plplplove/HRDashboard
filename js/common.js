/**
 * Common JavaScript functions for the HR Dashboard
 */

document.addEventListener('DOMContentLoaded', function() {
    // Theme switching functionality
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        const body = document.body;
        const themeIcon = themeToggle.querySelector('i');
        
        // Check for saved theme preference or use default
        const currentTheme = localStorage.getItem('theme') || 'theme-light';
        body.className = currentTheme;
        
        // Update icon based on current theme
        if (currentTheme === 'theme-dark') {
            themeIcon.className = 'fas fa-moon';
        } else {
            themeIcon.className = 'fas fa-sun';
        }
        
        // Add event listener for theme toggle
        themeToggle.addEventListener('click', function() {
            if (body.classList.contains('theme-dark')) {
                body.classList.replace('theme-dark', 'theme-light');
                themeIcon.className = 'fas fa-sun';
                localStorage.setItem('theme', 'theme-light');
            } else {
                body.classList.replace('theme-light', 'theme-dark');
                themeIcon.className = 'fas fa-moon';
                localStorage.setItem('theme', 'theme-dark');
            }
        });
    }
    
    // User dropdown functionality
    const userNameDropdown = document.getElementById('userNameDropdown');
    const userDropdown = document.getElementById('userDropdown');
    
    if (userNameDropdown && userDropdown) {
        // Toggle dropdown when clicking on username
        userNameDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
            userNameDropdown.classList.toggle('active');
            userDropdown.classList.toggle('active');
        });
        
        // Close dropdown when clicking elsewhere on the page
        document.addEventListener('click', function(e) {
            if (!userDropdown.contains(e.target) && e.target !== userNameDropdown) {
                userNameDropdown.classList.remove('active');
                userDropdown.classList.remove('active');
            }
        });
        
        // Handle password change button click
        const changePasswordBtn = document.getElementById('changePasswordBtn');
        if (changePasswordBtn) {
            changePasswordBtn.addEventListener('click', function() {
                alert('Zmiana hasła - funkcjonalność będzie доступна вkrótce');
            });
        }
    }
    
    // Modal functionality
    window.showModal = function(modalId, callback = null) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            document.body.classList.add('modal-open');
            if (callback && typeof callback === 'function') {
                callback();
            }
        }
    };
    
    window.hideModal = function(modalId, callback = null) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
            if (callback && typeof callback === 'function') {
                callback();
            }
        }
    };
    
    // Setup close button for all modals
    document.querySelectorAll('.modal .close-button, .modal .btn-secondary[id*="cancel"]').forEach(button => {
        const modal = button.closest('.modal');
        if (modal) {
            button.addEventListener('click', function() {
                hideModal(modal.id);
            });
        }
    });
    
    // Close modal when clicking outside
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                hideModal(this.id);
            }
        });
    });
    
    // Notification functionality
    window.showNotification = function(message, type = 'success') {
        const container = document.getElementById('notificationContainer');
        if (!container) return;
        
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        
        const icon = type === 'success' ? 'check-circle' : 'exclamation-circle';
        
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${icon}"></i>
                <span>${message}</span>
            </div>
        `;
        
        container.appendChild(notification);
        
        // Remove notification after 5 seconds
        setTimeout(() => {
            notification.classList.add('fade-out');
            setTimeout(() => {
                container.removeChild(notification);
            }, 500);
        }, 4500);
    };
    
    // Form utilities
    window.resetForm = function(formId) {
        const form = document.getElementById(formId);
        if (form) {
            form.reset();
        }
    };
    
    window.collectFormData = function(formId) {
        const form = document.getElementById(formId);
        if (!form) return null;
        return new FormData(form);
    };
});
