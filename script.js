// FILE: js/script.js
// Client-side script for Dark Mode and basic form validation.

document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const toggleButton = document.getElementById('dark-mode-toggle');
    
    // Check local storage for theme preference or default to 'light-mode'
    const currentTheme = localStorage.getItem('theme') || 'light-mode';
    body.classList.add(currentTheme);
    updateToggleIcon(currentTheme);

    // Dark Mode Toggle Functionality
    if (toggleButton) {
        toggleButton.addEventListener('click', () => {
            if (body.classList.contains('light-mode')) {
                body.classList.replace('light-mode', 'dark-mode');
                localStorage.setItem('theme', 'dark-mode');
                updateToggleIcon('dark-mode');
            } else {
                body.classList.replace('dark-mode', 'light-mode');
                localStorage.setItem('theme', 'light-mode');
                updateToggleIcon('light-mode');
            }
        });
    }

    /**
     * Updates the icon on the dark mode toggle button.
     * @param {string} theme - The current theme ('light-mode' or 'dark-mode').
     */
    function updateToggleIcon(theme) {
        if (toggleButton) {
            const icon = toggleButton.querySelector('i');
            if (theme === 'light-mode') {
                // Currently light mode, show Moon icon to switch to dark mode
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            } else {
                // Currently dark mode, show Sun icon to switch to light mode
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            }
        }
    }

    // Generic Client-Side Form Validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            let isValid = true;
            const requiredInputs = form.querySelectorAll('[required]');

            requiredInputs.forEach(input => {
                const errorMessageElementId = input.id + '-error';
                let errorMessageElement = document.getElementById(errorMessageElementId);

                // Create error element if it doesn't exist
                if (!errorMessageElement) {
                    errorMessageElement = document.createElement('span');
                    errorMessageElement.id = errorMessageElementId;
                    errorMessageElement.classList.add('error-message');
                    // Insert the error message after the input's parent td
                    const inputParentTd = input.closest('td');
                    if (inputParentTd) {
                        inputParentTd.appendChild(errorMessageElement);
                    }
                }

                if (!input.value.trim()) {
                    isValid = false;
                    errorMessageElement.textContent = `This field is required.`;
                    input.classList.add('input-error');
                } else {
                    errorMessageElement.textContent = '';
                    input.classList.remove('input-error');
                }
            });

            if (!isValid) {
                event.preventDefault(); // Stop form submission
            }
        });
    });

});