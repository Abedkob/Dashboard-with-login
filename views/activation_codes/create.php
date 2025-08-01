<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-plus"></i> Create New License</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/Practice_php/public/activation-codes" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<?php if (isset($_SESSION['errors'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <h6><i class="fas fa-exclamation-triangle"></i> Please fix the following errors:</h6>
        <ul class="mb-0">
            <?php foreach ($_SESSION['errors'] as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['errors']); ?>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0">License Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/Practice_php/public/activation-codes/create" id="licenseForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">
                                    <i class="fas fa-user"></i> Full Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                                <div class="form-text">Enter the license holder's full name</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="license" class="form-label">
                                    <i class="fas fa-key"></i> License Key <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="license" name="license"
                                        value="<?= htmlspecialchars($_POST['license'] ?? '') ?>" required>
                                    <button type="button" class="btn btn-outline-secondary" onclick="generateLicense()">
                                        <i class="fas fa-magic"></i> Generate
                                    </button>
                                </div>
                                <div class="form-text">Enter or generate a unique license key</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="valid_from" class="form-label">
                                    <i class="fas fa-calendar-alt"></i> Valid From <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control" id="valid_from" name="valid_from"
                                    value="<?= htmlspecialchars($_POST['valid_from'] ?? date('Y-m-d')) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="valid_to" class="form-label">
                                    <i class="fas fa-calendar-times"></i> Valid To <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="date" class="form-control" id="valid_to" name="valid_to"
                                        value="<?= htmlspecialchars($_POST['valid_to'] ?? date('Y-m-d', strtotime('+1 year'))) ?>"
                                        required>
                                    <button type="button" class="btn btn-outline-secondary" id="extend-btn"
                                        data-bs-toggle="dropdown">
                                        <i class="fas fa-clock"></i> Extend
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><button type="button" class="dropdown-item" onclick="extendDate(1)"><i
                                                    class="fas fa-plus-circle me-2"></i>Add 1 Month</button></li>
                                        <li><button type="button" class="dropdown-item" onclick="extendDate(3)"><i
                                                    class="fas fa-plus-circle me-2"></i>Add 3 Months</button></li>
                                        <li><button type="button" class="dropdown-item" onclick="extendDate(6)"><i
                                                    class="fas fa-plus-circle me-2"></i>Add 6 Months</button></li>
                                        <li><button type="button" class="dropdown-item" onclick="extendDate(12)"><i
                                                    class="fas fa-plus-circle me-2"></i>Add 12 Months</button></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li><button type="button" class="dropdown-item" onclick="resetDate()"><i
                                                    class="fas fa-undo me-2"></i>Reset to Default</button></li>
                                    </ul>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="extendDate(1)">+1M</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="extendDate(3)">+3M</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="extendDate(6)">+6M</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="extendDate(12)">+12M</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="/Practice_php/public/activation-codes" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create License
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function generateLicense() {
        // Generate a random license key
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let result = '';
        for (let i = 0; i < 20; i++) {
            if (i > 0 && i % 4 === 0) result += '-';
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('license').value = result;
    }

    // Extend the expiration date by months
    function extendDate(months) {
        const dateInput = document.getElementById('valid_to');
        let currentDate = new Date(dateInput.value);

        // If no date is set, use today's date
        if (isNaN(currentDate.getTime())) {
            currentDate = new Date(document.getElementById('valid_from').value || new Date());
        }

        // Add months to the current date
        currentDate.setMonth(currentDate.getMonth() + months);

        // Format the date back to YYYY-MM-DD
        const year = currentDate.getFullYear();
        const month = String(currentDate.getMonth() + 1).padStart(2, '0');
        const day = String(currentDate.getDate()).padStart(2, '0');

        dateInput.value = `${year}-${month}-${day}`;

        // Show feedback
        showToast(`Added ${months} month${months > 1 ? 's' : ''} to expiration date`, 'success');
    }

    // Reset to default date (1 year from now)
    function resetDate() {
        const today = new Date(document.getElementById('valid_from').value || new Date());
        today.setFullYear(today.getFullYear() + 1);

        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');

        document.getElementById('valid_to').value = `${year}-${month}-${day}`;
        showToast('Reset to default expiration date (1 year)', 'info');
    }

    // Show toast notification
    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0 position-fixed bottom-0 end-0 m-3`;
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        document.body.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        setTimeout(() => {
            document.body.removeChild(toast);
        }, 3000);
    }

    // Form validation
    document.getElementById('licenseForm').addEventListener('submit', function (e) {
        const validFrom = new Date(document.getElementById('valid_from').value);
        const validTo = new Date(document.getElementById('valid_to').value);

        if (validTo <= validFrom) {
            e.preventDefault();
            showToast('Valid To date must be after Valid From date', 'danger');
            return false;
        }
    });

    // Set minimum date for valid_to based on valid_from
    document.getElementById('valid_from').addEventListener('change', function () {
        const validFromDate = this.value;
        document.getElementById('valid_to').min = validFromDate;

        // If valid_to is before the new valid_from, adjust it
        const validToDate = new Date(document.getElementById('valid_to').value);
        const newValidFrom = new Date(validFromDate);

        if (validToDate < newValidFrom) {
            extendDate(12); // Default to 1 year from valid_from
        }
    });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>