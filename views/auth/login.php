<?php include __DIR__ . '/../layouts/header_login.php'; ?>

<div class="login-container">
    <div class="card shadow-lg" style="max-width: 450px; margin: 0 auto;">
        <div class="card-header bg-primary text-white text-center py-4">
            <div class="mb-3">
                <i class="fas fa-key fa-3x"></i>
            </div>
            <h3 class="mb-1">Welcome Back</h3>
            <p class="mb-0 opacity-75">Please sign in to your account</p>
        </div>

        <div class="card-body p-4">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="/Practice_php/public/login/submit" method="POST" id="loginForm">
                <div class="mb-4">
                    <label for="username" class="form-label fw-semibold">
                        <i class="fas fa-user me-2 text-primary"></i>Username
                    </label>
                    <input type="text" class="form-control form-control-lg" id="username" name="username"
                        placeholder="Enter your username" required autofocus>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold">
                        <i class="fas fa-lock me-2 text-primary"></i>Password
                    </label>
                    <div class="input-group input-group-lg">
                        <input type="password" class="form-control" id="password" name="password"
                            placeholder="Enter your password" required>
                        <button class="btn btn-outline-secondary px-3" type="button" id="togglePassword"
                            title="Show password">
                            <i class="fas fa-eye" id="toggleIcon" ,color=blue></i>
                        </button>
                    </div>
                </div>


                <div class="d-grid gap-2 mb-4">
                    <button type="submit" class="btn btn-primary btn-lg py-3 fw-semibold">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                    </button>
                </div>


            </form>

            <div class="text-center p-3 bg-light rounded">
                <small class="text-muted">
                    <i class="fas fa-shield-alt me-2"></i>
                    Your connection is secured with 256-bit SSL encryption
                </small>
            </div>
        </div>
    </div>
</div>

<style>
    .login-container {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: calc(100vh - 140px);
        /* Adjust for header/footer */
        padding: 20px;
    }

    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
    }

    .input-group-lg .btn {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    .form-control-lg {
        padding: 1rem 1rem;
        font-size: 1.1rem;
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(13, 110, 253, 0.3);
        transition: all 0.2s ease;
    }

    .card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
    }

    .card-header {
        border-bottom: none;
    }

    #togglePassword {
        border-left: none;
        background: #f8f9fa;
        transition: all 0.2s ease;
    }

    #togglePassword:hover {
        background: #e9ecef;
        color: #0d6efd;
    }

    #togglePassword:focus {
        box-shadow: none;
        border-color: #0d6efd;
    }

    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
</style>

<script>
    // Enhanced password toggle functionality
    const togglePassword = document.getElementById('togglePassword');
    const passwordField = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');

    togglePassword.addEventListener('click', function () {
        const isPasswordVisible = passwordField.type === 'text';

        // Toggle password visibility
        passwordField.type = isPasswordVisible ? 'password' : 'text';

        // Update icon and tooltip with smooth transition
        if (isPasswordVisible) {
            toggleIcon.classList.replace('fa-eye-slash', 'fa-eye');
            this.setAttribute('title', 'Show password');
            this.setAttribute('aria-label', 'Show password');
        } else {
            toggleIcon.classList.replace('fa-eye', 'fa-eye-slash');
            this.setAttribute('title', 'Hide password');
            this.setAttribute('aria-label', 'Hide password');
        }

        // Focus back on password field
        passwordField.focus();

        // Move cursor to end of input
        passwordField.setSelectionRange(passwordField.value.length, passwordField.value.length);
    });

    // Initialize Bootstrap tooltips if available
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        new bootstrap.Tooltip(togglePassword);
    }

    // Form validation and loading state
    document.getElementById('loginForm').addEventListener('submit', function (e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;

        // Basic validation
        if (username.length < 3) {
            e.preventDefault();
            showAlert('Username must be at least 3 characters long.', 'danger');
            return false;
        }

        if (password.length < 4) {
            e.preventDefault();
            showAlert('Password must be at least 4 characters long.', 'danger');
            return false;
        }

        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing In...';
        submitBtn.disabled = true;
        submitBtn.classList.add('loading');
    });

    // Show alert function
    function showAlert(message, type) {
        const existingAlert = document.querySelector('.alert');
        if (existingAlert) {
            existingAlert.remove();
        }

        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
        <i class="fas fa-exclamation-triangle me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

        const form = document.getElementById('loginForm');
        form.insertBefore(alert, form.firstChild);

        // Auto-remove alert after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    }

    // Auto-focus on username field
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('username').focus();
    });

    // Enhanced keyboard shortcuts
    document.addEventListener('keydown', function (e) {
        // Alt + L to focus on username
        if (e.altKey && e.key === 'l') {
            e.preventDefault();
            document.getElementById('username').focus();
        }

        // Alt + P to focus on password
        if (e.altKey && e.key === 'p') {
            e.preventDefault();
            document.getElementById('password').focus();
        }

        // Ctrl + Shift + P to toggle password visibility
        if (e.ctrlKey && e.shiftKey && e.key === 'P') {
            e.preventDefault();
            document.getElementById('togglePassword').click();
        }
    });

    // Add smooth transitions for form elements
    document.querySelectorAll('.form-control').forEach(input => {
        input.addEventListener('focus', function () {
            this.style.transform = 'translateY(-1px)';
            this.style.transition = 'all 0.2s ease';
        });

        input.addEventListener('blur', function () {
            this.style.transform = 'translateY(0)';
        });
    });
</script>

<?php include __DIR__ . '/../layouts/footer_login.php'; ?>