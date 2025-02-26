document.addEventListener('DOMContentLoaded', function() {
    const settingsForm = document.querySelector('.settings-form');
    const maintenanceModeCheckbox = document.getElementById('maintenance_mode');

    // Client-side form validation
    settingsForm.addEventListener('submit', function(event) {
        const siteName = document.getElementById('site_name');
        const emailFrom = document.getElementById('email_from');

        let isValid = true;

        // Site name validation
        if (siteName.value.trim() === '') {
            isValid = false;
            alert('Site Name cannot be empty');
        }

        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailFrom.value)) {
            isValid = false;
            alert('Please enter a valid email address');
        }

        if (!isValid) {
            event.preventDefault();
        }
    });

    // Optional: Show confirmation when maintenance mode is toggled
    maintenanceModeCheckbox.addEventListener('change', function() {
        if (this.checked) {
            alert('When Maintenance Mode is enabled, only administrators can access the site.');
        }
    });
});
