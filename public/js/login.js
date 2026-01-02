        document.addEventListener('DOMContentLoaded', function() {
            const passwordField = document.getElementById('contrasena');
            const togglePassword = document.getElementById('togglePassword');
            togglePassword.style.display = 'none';

            passwordField.addEventListener('input', function() {
                if (passwordField.value.length > 0) {
                    togglePassword.style.display = 'block';
                } else {
                    togglePassword.style.display = 'none';
                }
            });
            togglePassword.addEventListener('click', function() {
                const type = passwordField.type === 'password' ? 'text' : 'password';
                passwordField.type = type;
                this.classList.toggle('bx-hide');
                this.classList.toggle('bx-show');
                passwordField.focus();
            });
        });
        setTimeout(() => {
            const errorMessage = document.getElementById('error-message');
            if (errorMessage) {
                errorMessage.style.display = 'none';
            }
        }, 10000);