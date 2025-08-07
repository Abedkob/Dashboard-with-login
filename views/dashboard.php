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

<!-- License Statistics Cards -->
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

<!-- Payment Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Revenue</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">$<?= number_format($stats['payments']['total_revenue'] ?? 0, 2) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">This Month Revenue</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">$<?= number_format($stats['payments']['this_month_revenue'] ?? 0, 2) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Payments</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['payments']['total_payments'] ?? 0 ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-credit-card fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-secondary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Average Payment</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">$<?= number_format($stats['payments']['average_payment'] ?? 0, 2) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-lg-4">
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

    <div class="col-lg-4">
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

    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">Monthly Revenue</h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="revenueBarChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Tables Row -->
<div class="row mb-4">
    <!-- Top Paying Clients -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">Top Paying Clients (Last 6 Months)</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($stats['payments']['top_paying_clients'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Total Paid</th>
                                    <th>Payments</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['payments']['top_paying_clients'] as $client): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($client['client_name'] ?? 'Unknown') ?></td>
                                        <td><strong class="text-success">$<?= number_format($client['total_paid'], 2) ?></strong></td>
                                        <td><span class="badge bg-primary"><?= $client['payment_count'] ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No payment data available for the last 6 months.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Payments -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info">Recent Payments</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($stats['payments']['recent_payments'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['payments']['recent_payments'] as $payment): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($payment['client_name'] ?? 'Unknown') ?></td>
                                        <td><strong class="text-success">$<?= number_format($payment['amount'], 2) ?></strong></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($payment['method']) ?></span></td>
                                        <td><?= date('M j, Y', strtotime($payment['payment_date'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No recent payments found.</p>
                <?php endif; ?>
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
                                    $now = new DateTime('today');
                                    $interval = $now->diff($validTo);
                                    $daysRemaining = (int) $interval->format('%r%a');
                                    $isExpired = $validTo < $now;
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
                            <?php $isCritical = $expiring['days_remaining'] <= 3; ?>
                            <div class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= htmlspecialchars($expiring['name']) ?></h6>
                                    <small class="text-<?= $isCritical ? 'danger' : 'warning' ?>">
                                        <?= $isCritical ? 'Critical' : 'Warning' ?>
                                    </small>
                                </div>
                                <small class="text-muted d-block">License: <?= htmlspecialchars(substr($expiring['license'], 0, 8)) ?>...</small>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small>Expires: <?= date('M j, Y', strtotime($expiring['valid_to'])) ?></small>
                                    <span class="badge bg-<?= $isCritical ? 'danger' : 'warning' ?>">
                                        <?= $expiring['days_remaining'] ?> day<?= $expiring['days_remaining'] != 1 ? 's' : '' ?> left
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

<!-- Renewal Modal -->
<div class="modal fade" id="renewLicenseModal" tabindex="-1" aria-labelledby="renewLicenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="renewLicenseModalLabel">Renew License</h5>
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
document.addEventListener('DOMContentLoaded', function () {
    // Pie Chart for License Status
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

    // Bar Chart for Monthly License Trends
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

    // Bar Chart for Monthly Revenue
    const revenueCtx = document.getElementById('revenueBarChart').getContext('2d');
    const revenueData = <?= json_encode($stats['monthly_revenue'] ?? [
        'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        'revenue' => [0, 0, 0, 0, 0, 0],
        'payment_count' => [0, 0, 0, 0, 0, 0]
    ]) ?>;

    new Chart(revenueCtx, {
        type: 'bar',
        data: {
            labels: revenueData.labels,
            datasets: [
                {
                    label: 'Revenue ($)',
                    data: revenueData.revenue,
                    backgroundColor: '#28a745',
                    borderColor: '#28a745',
                    borderWidth: 1,
                    yAxisID: 'y'
                },
                {
                    label: 'Payment Count',
                    data: revenueData.payment_count,
                    backgroundColor: '#17a2b8',
                    borderColor: '#17a2b8',
                    borderWidth: 1,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Month'
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Revenue ($)'
                    },
                    beginAtZero: true
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Payment Count'
                    },
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.dataset.label === 'Revenue ($)') {
                                label += '$' + context.raw.toLocaleString();
                            } else {
                                label += context.raw;
                            }
                            return label;
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

// Modal handling code (existing code)
document.addEventListener('DOMContentLoaded', function () {
    $('#renewLicenseModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const licenseId = button.data('id');
        const modal = $(this);

        $.get(`/Practice_php/public/activation-codes/edit?id=${licenseId}`, function (data) {
            modal.find('.modal-body').html(data);
            modal.find('#submitBtn').hide();
            modal.find('#closev').hide();
        }).fail(function () {
            modal.find('.modal-body').html(`
                <div class="alert alert-danger">
                    Failed to load renewal form. Please try again.
                </div>
            `);
        });
    });

    $('#saveRenewalChanges').on('click', function () {
        const form = $('#renewLicenseModalBody form');
        if (form.length) {
            $('.date-error').remove();
            $('.is-invalid').removeClass('is-invalid');

            const validFromVal = $('#valid_from').val();
            const validToVal = $('#valid_to').val();
            const validFrom = new Date(validFromVal);
            const validTo = new Date(validToVal);
            let hasError = false;

            if (!validFromVal || isNaN(validFrom.getTime())) {
                $('#valid_from').addClass('is-invalid')
                    .after(`
                        <div class="invalid-feedback date-error" style="
                            display: flex; align-items: center; gap: 0.5rem; margin-top: 0.25rem; 
                            padding: 0.5rem; font-size: 0.9rem; color: #b91c1c; background: #fee2e2; 
                            border-radius: 0.375rem;
                        ">
                            <svg style="width: 1rem; height: 1rem; flex-shrink: 0;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd"/>
                            </svg>
                            <span>Valid From date is required.</span>
                        </div>
                    `);
                hasError = true;
            }

            if (!validToVal || isNaN(validTo.getTime())) {
                $('#valid_to').addClass('is-invalid')
                    .after(`
                        <div class="invalid-feedback date-error" style="
                            display: flex; align-items: center; gap: 0.5rem; margin-top: 0.25rem; 
                            padding: 0.5rem; font-size: 0.9rem; color: #b91c1c; background: #fee2e2; 
                            border-radius: 0.375rem;
                        ">
                            <svg style="width: 1rem; height: 1rem; flex-shrink: 0;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd"/>
                            </svg>
                            <span>Valid To date is required.</span>
                        </div>
                    `);
                hasError = true;
            } else if (!hasError && validTo <= validFrom) {
                $('#valid_to').addClass('is-invalid')
                    .after(`
                        <div class="invalid-feedback date-error" style="
                            display: flex; align-items: center; gap: 0.5rem; margin-top: 0.25rem; 
                            padding: 0.5rem; font-size: 0.9rem; color: #b91c1c; background: #fee2e2; 
                            border-radius: 0.375rem;
                        ">
                            <svg style="width: 1rem; height: 1rem; flex-shrink: 0;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd"/>
                            </svg>
                            <span>Valid To must be after Valid From.</span>
                        </div>
                    `);
                hasError = true;
            }

            if (hasError) return;

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
