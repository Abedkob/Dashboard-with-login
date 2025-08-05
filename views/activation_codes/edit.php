<?php
require_once __DIR__ . '/../../public/config.php';
?>

<div id="editLicenseContainer">
    <!-- Error Alert (will be shown when there are errors) -->
    <div id="formErrorAlert" class="alert alert-danger alert-dismissible fade show mb-4"
        style="<?= empty($_SESSION['errors']) ? 'display:none;' : '' ?>" role="alert">
        <h6><i class="fas fa-exclamation-triangle"></i> Please fix the following errors:</h6>
        <ul id="errorList" class="mb-0">
            <?php if (!empty($_SESSION['errors'])): ?>
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
                <?php unset($_SESSION['errors']); ?>
            <?php endif; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <div class="row justify-content-center">
        <div class="col-20 col-md-15 col-lg-12">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">License Information</h5>
                    <small class="text-muted">ID: <?= htmlspecialchars($code['id']) ?></small>
                </div>
                <div class="card-body">
                    <form id="editLicenseForm" method="POST"
                        action="<?= BASE_URL ?>/activation-codes/edit?id=<?= $code['id'] ?>">

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
                                        <i class="fas fa-calendar-alt"></i> Valid From <span
                                            class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control" id="valid_from" name="valid_from"
                                        value="<?= htmlspecialchars($code['valid_from']) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="valid_to" class="form-label">
                                        <i class="fas fa-calendar-times"></i> Valid To <span
                                            class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="date" class="form-control" id="valid_to" name="valid_to"
                                            value="<?= htmlspecialchars($code['valid_to']) ?>" required>
                                        <button type="button" class="btn btn-outline-secondary"
                                            data-bs-toggle="collapse" data-bs-target="#extendOptions"
                                            aria-expanded="false" aria-controls="extendOptions">
                                            <i class="fas fa-clock"></i> Extend
                                        </button>
                                    </div>

                                    <!-- Extend Options -->
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
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-save"></i> Update License
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Store original date for reset functionality
    $.get('<?= BASE_URL ?>' + `/activation-codes/edit?id=${id}`, function (data) {
        $('#editLicenseModalBody').html(data);

        // Set originalDate dynamically here
        originalDate = $('#valid_to').val();

        // Re-bind form submission handler
        bindEditFormHandler();
    });
    let isSubmitting = false;

    $(document).ready(function () {
        // Set initial min date for valid_to
        const validFrom = $('#valid_from').val();
        if (validFrom) {
            $('#valid_to').attr('min', validFrom);
        }

        // Validate dates when they change
        $('#valid_from, #valid_to').on('change', function () {
            validateDates();
        });

        // Form submission handler
        $('#editLicenseForm').on('submit', function (e) {
            e.preventDefault();

            if (isSubmitting) return;
            isSubmitting = true;

            // Clear previous errors
            $('#formErrorAlert').hide();
            $('#errorList').empty();

            // Validate dates before submission
            if (!validateDates()) {
                isSubmitting = false;
                return;
            }

            const form = $(this);
            const submitBtn = $('#submitBtn');

            // Show loading state
            submitBtn.prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...');

            $.ajax({
                type: 'POST',
                url: form.attr('action'),
                data: form.serialize(),
                dataType: 'json', // keep if server returns JSON only
                success: function (response) {
                    if (response.success) {
                        showToast('License updated successfully!', 'success');
                        $('#editLicenseModal').modal('hide');

                        // Delay reload to avoid race conditions
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showErrors(response.errors || ['An unknown error occurred']);
                    }
                },
                error: function (xhr, status, error) {
                    if (status === 'parsererror') {
                        // This happens if response is not valid JSON (e.g., redirect or HTML)
                        showErrors(['Unexpected response from server. Please try again.']);
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        showErrors(xhr.responseJSON.errors);
                    } else {
                        let errorMsg = 'Failed to update license';
                        if (xhr.responseText) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                showErrors(response.errors || [errorMsg]);
                            } catch {
                                showErrors([errorMsg]);
                            }
                        } else {
                            showErrors([errorMsg]);
                        }
                    }
                },
                complete: function () {
                    isSubmitting = false;
                    submitBtn.prop('disabled', false)
                        .html('<i class="fas fa-save"></i> Update License');
                }
            });

        });
    });

    function validateDates() {
        const validFrom = new Date($('#valid_from').val());
        const validTo = new Date($('#valid_to').val());

        if (validTo <= validFrom) {
            showErrors(['Valid To date must be after Valid From date']);
            return false;
        }
        return true;
    }

    function showErrors(errors) {
        const errorList = $('#errorList');
        errorList.empty();

        errors.forEach(error => {
            errorList.append(`<li>${error}</li>`);
        });

        $('#formErrorAlert').show();
        showToast('Please fix the errors in the form', 'danger');
    }

    function extendDate(months) {
        const dateInput = $('#valid_to');
        let currentDate = new Date(dateInput.val());

        if (isNaN(currentDate.getTime())) {
            currentDate = new Date();
        }

        currentDate.setMonth(currentDate.getMonth() + months);
        dateInput.val(currentDate.toISOString().split('T')[0]);
        showToast(`Added ${months} month${months > 1 ? 's' : ''} to expiration date`, 'success');
        validateDates();
    }

    function resetDate() {
        $('#valid_to').val(originalDate);
        showToast('Reset to original expiration date', 'info');
        validateDates();
    }

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function () {
            showToast('License key copied to clipboard!', 'success');
        }).catch(function () {
            showToast('Failed to copy license key', 'danger');
        });
    }

    function showToast(message, type) {
        const toastId = 'toast-' + Date.now();
        const iconMap = {
            'success': 'fa-check-circle',
            'danger': 'fa-exclamation-circle',
            'warning': 'fa-exclamation-triangle',
            'info': 'fa-info-circle'
        };

        const toast = $(`
            <div id="${toastId}" class="toast align-items-center text-white bg-${type} border-0 position-fixed shadow-lg" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center">
                        <i class="fas ${iconMap[type]} me-2 fs-5"></i>
                        <span>${message}</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);

        $('body').append(toast);
        const bsToast = new bootstrap.Toast(toast[0], { delay: 4000 });
        bsToast.show();

        toast.on('hidden.bs.toast', function () {
            toast.remove();
        });
    }
</script>