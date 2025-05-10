document.addEventListener('DOMContentLoaded', function () {
  const themeIcon = document.getElementById('themeIcon');
  const dropdownToggle = document.getElementById('dropdownToggle');
  const userDropdown = document.getElementById('userDropdown');

  const currentTheme = localStorage.getItem('theme') || 'theme-light';
  document.body.className = currentTheme;
  if (themeIcon) {
    themeIcon.classList.add(currentTheme === 'theme-dark' ? 'fa-moon' : 'fa-sun');
  }

  themeIcon?.addEventListener('click', (e) => {
    e.stopPropagation();
    const isDark = document.body.classList.contains('theme-dark');
    document.body.classList.toggle('theme-dark', !isDark);
    document.body.classList.toggle('theme-light', isDark);

    themeIcon.classList.replace(isDark ? 'fa-moon' : 'fa-sun', isDark ? 'fa-sun' : 'fa-moon');
    localStorage.setItem('theme', isDark ? 'theme-light' : 'theme-dark');
  });

  dropdownToggle?.addEventListener('click', (e) => {
    e.stopPropagation();
    const isOpen = userDropdown.style.display === 'flex';
    userDropdown.style.display = isOpen ? 'none' : 'flex';
    dropdownToggle.classList.toggle('active', !isOpen);
  });

  document.addEventListener('click', (e) => {
    if (!userDropdown.contains(e.target) && e.target !== dropdownToggle) {
      userDropdown.style.display = 'none';
      dropdownToggle.classList.remove('active');
    }
  });

  window.showModal = function (modalId, callback = null) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.style.display = 'flex';
      document.body.classList.add('modal-open');
      callback?.();
    }
  };

  window.hideModal = function (modalId, callback = null) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.style.display = 'none';
      document.body.classList.remove('modal-open');
      callback?.();
    }
  };

  document.querySelectorAll('.modal .close-button, .modal .btn-secondary[id*="cancel"]').forEach(button => {
    const modal = button.closest('.modal');
    modal?.addEventListener('click', () => hideModal(modal.id));
  });

  document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', (e) => {
      if (e.target === modal) hideModal(modal.id);
    });
  });

  window.showNotification = function (message, type = 'success') {
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
    setTimeout(() => {
      notification.classList.add('fade-out');
      setTimeout(() => notification.remove(), 500);
    }, 4500);
  };

  window.resetForm = function (formId) {
    document.getElementById(formId)?.reset();
  };

  window.collectFormData = function (formId) {
    const form = document.getElementById(formId);
    return form ? new FormData(form) : null;
  };
});