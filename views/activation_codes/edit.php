<?php
require_once __DIR__ . '/../../public/config.php';
?>

<body>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css" rel="stylesheet">
</body>

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
    <div class="col-20 col-md-15 col-lg-12">
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">License Information</h5>
                <small class="text-muted">ID: <?= htmlspecialchars($code['id']) ?></small>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/activation-codes/edit?id=<?= $code['id'] ?>">



                    <!-- Name and License -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">
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

                    <!-- Valid From / To -->
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
                                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse"
                                        data-bs-target="#extendOptions" aria-expanded="false"
                                        aria-controls="extendOptions">
                                        <i class="fas fa-clock"></i> Extend
                                    </button>
                                </div>

                                <!-- Collapsible Extend Section -->
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
                                                <i class="fas fa-undo me-2"></i>Reset to Original
                                            </button>
                                        </div>
                                    </div>
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
                        <a href="<?= BASE_URL ?>/activation-codes" class="btn btn-secondary">
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
    const originalDate = '<?= htmlspecialchars($code['valid_to']) ?>';

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function () {
            showToast('License key copied to clipboard!', 'success');
        });
    }

    function extendDate(months) {
        const dateInput = document.getElementById('valid_to');
        let currentDate = new Date(dateInput.value);

        if (isNaN(currentDate.getTime())) {
            currentDate = new Date();
        }

        currentDate.setMonth(currentDate.getMonth() + months);

        const year = currentDate.getFullYear();
        const month = String(currentDate.getMonth() + 1).padStart(2, '0');
        const day = String(currentDate.getDate()).padStart(2, '0');

        dateInput.value = `${year}-${month}-${day}`;

        showToast(`Added ${months} month${months > 1 ? 's' : ''} to expiration date`, 'success');
    }

    function resetDate() {
        document.getElementById('valid_to').value = originalDate;
        showToast('Reset to original expiration date', 'info');
    }

    function showToast(message, type) {
        const toastId = 'toast-' + Date.now();
        const iconMap = {
            'success': 'fa-check-circle',
            'danger': 'fa-exclamation-circle',
            'warning': 'fa-exclamation-triangle',
            'info': 'fa-info-circle'
        };

        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = `toast align-items-center text-white bg-${type} border-0 position-fixed shadow-lg`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center">
                <i class="fas ${iconMap[type]} me-2 fs-5"></i>
                <span>${message}</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
        `;

        document.body.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast, { delay: 4000 });
        bsToast.show();

        setTimeout(() => {
            if (document.getElementById(toastId)) {
                document.body.removeChild(toast);
            }
        }, 5000);
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
    });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>