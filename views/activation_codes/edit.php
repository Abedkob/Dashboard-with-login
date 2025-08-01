<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-edit"></i> Edit License</h1>
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
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">License Information</h5>
                <small class="text-muted">ID: <?= htmlspecialchars($code['id']) ?></small>
            </div>
            <div class="card-body">
                <form method="POST" action="/Practice_php/public/activation-codes/edit?id=<?= $code['id'] ?>"
                    id="licenseForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="full_name" class="form-label">
                                    <i class="fas fa-user"></i> Full Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="<?= htmlspecialchars($code['name']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="license" class="form-label">
                                    <i class="fas fa-key"></i> License Key <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="license" name="license"
                                        value="<?= htmlspecialchars($code['license']) ?>" required>
                                    <button type="button" class="btn btn-outline-secondary"
                                        onclick="copyToClipboard('<?= htmlspecialchars($code['license']) ?>')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
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
                                    value="<?= htmlspecialchars($code['valid_from']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="valid_to" class="form-label">
                                    <i class="fas fa-calendar-times"></i> Valid To <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="date" class="form-control" id="valid_to" name="valid_to"
                                        value="<?= htmlspecialchars($code['valid_to']) ?>" required>
                                    <button type="button" class="btn btn-outline-secondary" id="extend-btn" data-bs-toggle="dropdown">
                                        <i class="fas fa-clock"></i> Extend
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><button type="button" class="dropdown-item" onclick="extendDate(1)"><i class="fas fa-plus-circle me-2"></i>Add 1 Month</button></li>
                                        <li><button type="button" class="dropdown-item" onclick="extendDate(3)"><i class="fas fa-plus-circle me-2"></i>Add 3 Months</button></li>
                                        <li><button type="button" class="dropdown-item" onclick="extendDate(6)"><i class="fas fa-plus-circle me-2"></i>Add 6 Months</button></li>
                                        <li><button type="button" class="dropdown-item" onclick="extendDate(12)"><i class="fas fa-plus-circle me-2"></i>Add 12 Months</button></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><button type="button" class="dropdown-item" onclick="resetDate()"><i class="fas fa-undo me-2"></i>Reset to Original</button></li>
                                    </ul>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="extendDate(1)">+1M</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="extendDate(3)">+3M</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="extendDate(6)">+6M</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="extendDate(12)">+12M</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Information -->
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-info-circle"></i> Current Status</label>
                        <div class="p-3 bg-light rounded">
                            <?php
                            $validTo = strtotime($code['valid_to']);
                            $now = time();
                            $daysUntilExpiry = ($validTo - $now) / (24 * 60 * 60);

                            if ($validTo < $now) {
                                echo '<span class="badge bg-danger me-2"><i class="fas fa-times-circle"></i> Expired</span>';
                                echo '<span class="text-muted">Expired ' . abs(floor($daysUntilExpiry)) . ' days ago</span>';
                            } elseif ($daysUntilExpiry <= 7) {
                                echo '<span class="badge bg-warning me-2"><i class="fas fa-exclamation-triangle"></i> Expiring Soon</span>';
                                echo '<span class="text-muted">Expires in ' . floor($daysUntilExpiry) . ' days</span>';
                            } else {
                                echo '<span class="badge bg-success me-2"><i class="fas fa-check-circle"></i> Active</span>';
                                echo '<span class="text-muted">Expires in ' . floor($daysUntilExpiry) . ' days</span>';
                            }
                            ?>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="/Practice_php/public/activation-codes" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update License
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Store original date for reset functionality
    const originalDate = '<?= htmlspecialchars($code['valid_to']) ?>';
    
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function () {
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-white bg-success border-0 position-fixed top-0 end-0 m-3';
            toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">License key copied to clipboard!</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
            document.body.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();

            setTimeout(() => {
                document.body.removeChild(toast);
            }, 3000);
        });
    }

    // Extend the expiration date by months
    function extendDate(months) {
        const dateInput = document.getElementById('valid_to');
        let currentDate = new Date(dateInput.value);
        
        // If no date is set, use today's date
        if (isNaN(currentDate.getTime())) {
            currentDate = new Date();
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

    // Reset to original date
    function resetDate() {
        document.getElementById('valid_to').value = originalDate;
        showToast('Reset to original expiration date', 'info');
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
    });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>