<?php include __DIR__ . '/layouts/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> <?= $_SESSION['success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Licenses</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total'] ?? 0 ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-key fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Licenses</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['active'] ?? 0 ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Expired Licenses</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['expired'] ?? 0 ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Expiring Soon</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['expiring'] ?? 0 ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">License Status Distribution</h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="statusPieChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Monthly License Trends</h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="trendsBarChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity and Alerts -->
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($stats['recent'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>License</th>
                                    <th>Valid To</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['recent'] as $update): ?>
                                    <?php
                                    $validTo = new DateTime($update['valid_to']);
                                    $now = new DateTime('today'); // today at 00:00:00 to ignore time part
                            
                                    $interval = $now->diff($validTo);
                                    $daysRemaining = (int) $interval->format('%r%a'); // signed difference in days
                            
                                    // Expired if validTo date is before today (not equal)
                                    $isExpired = $validTo < $now;

                                    // Expiring soon if not expired and days remaining between 0 and 7
                                    $isExpiringSoon = !$isExpired && $daysRemaining >= 0 && $daysRemaining <= 7;
                                    ?>

                                    <tr>
                                        <td><?= htmlspecialchars($update['name']) ?></td>
                                        <td><code><?= htmlspecialchars(substr($update['license'], 0, 8)) ?>...</code></td>
                                        <td><?= $validTo->format('Y-m-d') ?></td>
                                        <td>
                                            <?php if ($isExpired): ?>
                                                <span class="badge bg-danger">Expired</span>
                                            <?php elseif ($isExpiringSoon): ?>
                                                <span class="badge bg-warning">Expiring Soon (<?= $daysRemaining ?> days)</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No recent activity found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-warning">Expiry Alerts</h6>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($stats['expiring_details'])): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($stats['expiring_details'] as $expiring): ?>
                            <?php
                            $isCritical = $expiring['days_remaining'] <= 3;
                            ?>
                            <div class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= htmlspecialchars($expiring['name']) ?></h6>
                                    <small class="text-<?= $isCritical ? 'danger' : 'warning' ?>">
                                        <?= $isCritical ? 'Critical' : 'Warning' ?>
                                    </small>
                                </div>
                                <small class="text-muted d-block">License:
                                    <?= htmlspecialchars(substr($expiring['license'], 0, 8)) ?>...</small>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small>Expires: <?= date('M j, Y', strtotime($expiring['valid_to'])) ?></small>
                                    <span class="badge bg-<?= $isCritical ? 'danger' : 'warning' ?>">
                                        <?= $expiring['days_remaining'] ?> day<?= $expiring['days_remaining'] != 1 ? 's' : '' ?>
                                        left
                                    </span>
                                </div>
                                <a href="#" class="btn btn-sm btn-outline-primary mt-2 w-100 renew-license-btn"
                                    data-id="<?= $expiring['id'] ?>" data-bs-toggle="modal" data-bs-target="#renewLicenseModal">
                                    <i class="fas fa-edit"></i> Renew
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <p class="text-muted">No licenses expiring soon</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="renewLicenseModal" tabindex="-1" aria-labelledby="renewLicenseModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg"> <!-- Added modal-lg class here -->
        <div class="modal-content">
            <!-- Rest of your modal content remains the same -->
            <div class="modal-header">
                <h5 class="modal-title" id="renewLicenseModalLabel">Renew License</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="renewLicenseModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading renewal form...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveRenewalChanges">Save changes</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Pie Chart for License Status
    document.addEventListener('DOMContentLoaded', function () {
        const statusCtx = document.getElementById('statusPieChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: ['Active', 'Expired', 'Expiring Soon'],
                datasets: [{
                    data: [
                        <?= $stats['active'] ?? 0 ?>,
                        <?= $stats['expired'] ?? 0 ?>,
                        <?= $stats['expiring'] ?? 0 ?>
                    ],
                    backgroundColor: ['#28a745', '#dc3545', '#ffc107'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Bar Chart for Monthly Trends
        const trendsCtx = document.getElementById('trendsBarChart').getContext('2d');
        const monthlyData = <?= json_encode($stats['monthly_data'] ?? [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'new_licenses' => [0, 0, 0, 0, 0, 0],
            'expired_licenses' => [0, 0, 0, 0, 0, 0]
        ]) ?>;

        new Chart(trendsCtx, {
            type: 'bar',
            data: {
                labels: monthlyData.labels,
                datasets: [
                    {
                        label: 'New Licenses',
                        data: monthlyData.new_licenses,
                        backgroundColor: '#4e73df',
                        borderColor: '#4e73df',
                        borderWidth: 1
                    },
                    {
                        label: 'Expired Licenses',
                        data: monthlyData.expired_licenses,
                        backgroundColor: '#e74a3b',
                        borderColor: '#e74a3b',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Licenses'
                        },
                        ticks: {
                            stepSize: 1
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return context.dataset.label + ': ' + context.raw;
                            }
                        }
                    },
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // When modal is opened
        $('#renewLicenseModal').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            const licenseId = button.data('id');
            const modal = $(this);

            // Load the edit form via AJAX
            $.get(`/Practice_php/public/activation-codes/edit?id=${licenseId}`, function (data) {
                modal.find('.modal-body').html(data);

                // Hide the original Update button inside the form
                modal.find('#submitBtn').hide();
            }).fail(function () {
                modal.find('.modal-body').html(`
                <div class="alert alert-danger">
                    Failed to load renewal form. Please try again.
                </div>
            `);
            });
        });

        // When Save Changes button in modal footer is clicked
        $('#saveRenewalChanges').on('click', function () {
            const form = $('#renewLicenseModalBody form');
            if (form.length) {
                // Clear previous errors
                $('.date-error').remove();
                $('.is-invalid').removeClass('is-invalid');

                // Validate dates
                const validFromVal = $('#valid_from').val();
                const validToVal = $('#valid_to').val();

                const validFrom = new Date(validFromVal);
                const validTo = new Date(validToVal);

                let hasError = false;

                if (!validFromVal || isNaN(validFrom.getTime())) {
                    $('#valid_from').addClass('is-invalid')
                        .after('<div class="invalid-feedback date-error">Please provide a valid start date.</div>');
                    hasError = true;
                }

                if (!validToVal || isNaN(validTo.getTime())) {
                    $('#valid_to').addClass('is-invalid')
                        .after('<div class="invalid-feedback date-error">Please provide a valid end date.</div>');
                    hasError = true;
                } else if (!hasError && validTo <= validFrom) {
                    $('#valid_to').addClass('is-invalid')
                        .after('<div class="invalid-feedback date-error">Valid To must be after Valid From.</div>');
                    hasError = true;
                }

                // Stop if validation failed
                if (hasError) return;

                // Submit form via AJAX
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    beforeSend: function () {
                        $('#saveRenewalChanges')
                            .prop('disabled', true)
                            .html('<i class="fas fa-spinner fa-spin"></i> Saving...');
                    },
                    success: function (response) {
                        $('#renewLicenseModal').modal('hide');
                        location.reload();
                    },
                    error: function (xhr) {
                        $('#renewLicenseModalBody').html(xhr.responseText);
                    },
                    complete: function () {
                        $('#saveRenewalChanges')
                            .prop('disabled', false)
                            .html('<i class="fas fa-save"></i> Save changes');
                    }
                });
            }
        });
    });

</script>

<?php include __DIR__ . '/layouts/footer.php'; ?>