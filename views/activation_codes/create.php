<?php
require_once __DIR__ . '/../../public/config.php';
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-plus"></i> Create New License</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= BASE_URL ?>/activation-codes" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>

    </div>
</div>

<?php

if (isset($_SESSION['errors'])): ?>
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
                <form method="POST" action="<?= BASE_URL ?>/activation-codes/create" id="licenseForm">

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
                                    <button class="btn btn-outline-secondary" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#extendOptions" aria-expanded="false"
                                        aria-controls="extendOptions">
                                        <i class="fas fa-clock"></i> Extend
                                    </button>
                                </div>

                                <!-- Collapse section for extend options -->
                                <div class="collapse mt-3" id="extendOptions">
                                    <div class="card card-body">
                                        <div class="d-grid gap-2">
                                            <button type="button" class="btn btn-outline-primary"
                                                onclick="extendDate(1)">
                                                <i class="fas fa-plus-circle me-2"></i>Add 1 Month
                                            </button>
                                            <button type="button" class="btn btn-outline-primary"
                                                onclick="extendDate(3)">
                                                <i class="fas fa-plus-circle me-2"></i>Add 3 Months
                                            </button>
                                            <button type="button" class="btn btn-outline-primary"
                                                onclick="extendDate(6)">
                                                <i class="fas fa-plus-circle me-2"></i>Add 6 Months
                                            </button>
                                            <button type="button" class="btn btn-outline-primary"
                                                onclick="extendDate(12)">
                                                <i class="fas fa-plus-circle me-2"></i>Add 12 Months
                                            </button>
                                        </div>
                                        <div class="mt-3">
                                            <button type="button" class="btn btn-outline-secondary w-100"
                                                onclick="resetDate()">
                                                <i class="fas fa-undo me-2"></i>Reset to Default
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?= BASE_URL ?>/activation-codes" class="btn btn-secondary">
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
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let result = '';
        for (let i = 0; i < 20; i++) {
            if (i > 0 && i % 4 === 0) result += '-';
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('license').value = result;
    }

    function extendDate(months) {
        const dateInput = document.getElementById('valid_to');
        let currentDate = new Date(dateInput.value);

        if (isNaN(currentDate.getTime())) {
            currentDate = new Date(document.getElementById('valid_from').value || new Date());
        }

        currentDate.setMonth(currentDate.getMonth() + months);

        const year = currentDate.getFullYear();
        const month = String(currentDate.getMonth() + 1).padStart(2, '0');
        const day = String(currentDate.getDate()).padStart(2, '0');

        dateInput.value = `${year}-${month}-${day}`;

        showToast(`Added ${months} month${months > 1 ? 's' : ''} to expiration date`, 'success');
    }

    function resetDate() {
        const today = new Date(document.getElementById('valid_from').value || new Date());
        today.setFullYear(today.getFullYear() + 1);

        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');

        document.getElementById('valid_to').value = `${year}-${month}-${day}`;
        showToast('Reset to default expiration date (1 year)', 'info');
    }

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
            if (toast.parentNode) toast.parentNode.removeChild(toast);
        }, 3000);
    }

    document.getElementById('licenseForm').addEventListener('submit', function (e) {
        const validFrom = new Date(document.getElementById('valid_from').value);
        const validTo = new Date(document.getElementById('valid_to').value);

        if (validTo <= validFrom) {
            e.preventDefault();
            showToast('Valid To date must be after Valid From date', 'danger');
            return false;
        }
    });

    document.getElementById('valid_from').addEventListener('change', function () {
        const validFromDate = this.value;
        document.getElementById('valid_to').min = validFromDate;

        const validToDate = new Date(document.getElementById('valid_to').value);
        const newValidFrom = new Date(validFromDate);

        if (validToDate < newValidFrom) {
            resetDate();
        }
    });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>