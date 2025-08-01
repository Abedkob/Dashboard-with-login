<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-chart-bar"></i> Reports & Analytics</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>
</div>

<!-- Export Options -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-download"></i> Export Reports</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/Practice_php/public/export" class="row g-3">
                    <div class="col-md-6">
                        <label for="export_format" class="form-label">Format</label>
                        <select class="form-select" id="export_format" name="format" required>
                            <option value="">Select Format</option>
                            <option value="csv">CSV File</option>
                            <option value="pdf">PDF Report</option>
                            <option value="excel">Excel File</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="export_type" class="form-label">Report Type</label>
                        <select class="form-select" id="export_type" name="type" required>
                            <option value="">Select Type</option>
                            <option value="all">All Licenses</option>
                            <option value="active">Active Only</option>
                            <option value="expired">Expired Only</option>
                            <option value="expiring">Expiring Soon</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="date_from" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="date_from" name="date_from">
                    </div>
                    <div class="col-md-6">
                        <label for="date_to" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="date_to" name="date_to">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-download"></i> Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-clock"></i> Quick Reports</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="/Practice_php/public/export?format=pdf&type=expiring" class="btn btn-outline-warning">
                        <i class="fas fa-exclamation-triangle"></i> Expiring Licenses Report
                    </a>
                    <a href="/Practice_php/public/export?format=csv&type=expired" class="btn btn-outline-danger">
                        <i class="fas fa-times-circle"></i> Expired Licenses CSV
                    </a>
                    <a href="/Practice_php/public/export?format=excel&type=all" class="btn btn-outline-primary">
                        <i class="fas fa-file-excel"></i> Complete License Database
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Analytics Charts -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-line"></i> License Trends (Last 6 Months)</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="trendsChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-pie"></i> Status Distribution</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Statistics -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-table"></i> License Statistics Summary</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Metric</th>
                                <th>Count</th>
                                <th>Percentage</th>
                                <th>Trend</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><i class="fas fa-key text-primary"></i> Total Licenses</td>
                                <td><strong><?= $stats['total'] ?? 0 ?></strong></td>
                                <td>100%</td>
                                <td><span class="badge bg-info">Baseline</span></td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-check-circle text-success"></i> Active Licenses</td>
                                <td><strong><?= $stats['active'] ?? 0 ?></strong></td>
                                <td><?= $stats['total'] > 0 ? round(($stats['active'] / $stats['total']) * 100, 1) : 0 ?>%
                                </td>
                                <td><span class="badge bg-success"><i class="fas fa-arrow-up"></i> Good</span></td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-times-circle text-danger"></i> Expired Licenses</td>
                                <td><strong><?= $stats['expired'] ?? 0 ?></strong></td>
                                <td><?= $stats['total'] > 0 ? round(($stats['expired'] / $stats['total']) * 100, 1) : 0 ?>%
                                </td>
                                <td><span class="badge bg-danger"><i class="fas fa-arrow-down"></i> Needs
                                        Attention</span></td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-exclamation-triangle text-warning"></i> Expiring Soon</td>
                                <td><strong><?= $stats['expiring'] ?? 0 ?></strong></td>
                                <td><?= $stats['total'] > 0 ? round(($stats['expiring'] / $stats['total']) * 100, 1) : 0 ?>%
                                </td>
                                <td><span class="badge bg-warning"><i class="fas fa-clock"></i> Monitor</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Trends Chart
    const trendsCtx = document.getElementById('trendsChart').getContext('2d');
    new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'New Licenses',
                data: [12, 19, 3, 5, 2, 3],
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4
            }, {
                label: 'Expired',
                data: [2, 3, 20, 5, 1, 4],
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.4
            }, {
                label: 'Renewed',
                data: [8, 12, 15, 18, 20, 22],
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Expired', 'Expiring Soon'],
            datasets: [{
                data: [<?= $stats['active'] ?? 0 ?>, <?= $stats['expired'] ?? 0 ?>, <?= $stats['expiring'] ?? 0 ?>],
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
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>