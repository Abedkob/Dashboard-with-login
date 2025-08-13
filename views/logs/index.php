<?php include __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../../public/config.php'; ?>

<!-- Custom Styles -->
<style>
    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
    }

    .log-action {
        font-family: 'Courier New', monospace;
        background: #f8f9fa;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }

    .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 1px solid #dee2e6;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        transition: background-color 0.15s ease-in-out;
    }

    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    @media (max-width: 768px) {
        .btn-toolbar {
            flex-direction: column;
            gap: 0.5rem;
        }
    }

    /* Modal specific styles */
    .modal-content {
        border: none;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 1px solid #dee2e6;
    }

    .modal-footer {
        border-top: 1px solid #dee2e6;
    }

    .view-full-btn {
        cursor: pointer;
        color: #0d6efd;
    }

    .view-full-btn:hover {
        text-decoration: underline;
    }

    /* DataTables custom positioning */
    .dataTables_wrapper .top-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding: 0 0.75rem;
        background: #f8f9fa;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }

    .dataTables_wrapper .dataTables_length {
        margin: 0;
    }

    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 1.5rem;
    }

    .dataTables_wrapper .dt-buttons {
        margin: 0;
    }

    @media (max-width: 768px) {
        .dataTables_wrapper .top-controls {
            flex-direction: column;
            gap: 1rem;
        }
    }

    /* User-friendly description styles */
    .description-item {
        background: #f8f9fa;
        border-left: 4px solid #007bff;
        padding: 0.75rem;
        margin-bottom: 0.5rem;
        border-radius: 0.25rem;
    }

    .description-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.25rem;
    }

    .description-value {
        color: #6c757d;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .json-data {
        background: #2d3748;
        color: #e2e8f0;
        padding: 1rem;
        border-radius: 0.375rem;
        font-family: 'Courier New', monospace;
        font-size: 0.875rem;
        max-height: 300px;
        overflow-y: auto;
    }
</style>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <div class="d-flex align-items-center">
        <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3">
            <i class="fas fa-history text-primary fs-4"></i>
        </div>
        <h1 class="h2 mb-0 text-dark fw-bold">Activity Logs</h1>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-outline-secondary shadow-sm" onclick="location.reload()">
                <i class="fas fa-sync-alt me-2"></i>Refresh
            </button>
        </div>
    </div>
</div>

<!-- Success Alert -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <strong>Success!</strong> <?= $_SESSION['success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<!-- Filters Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="mb-0 d-flex align-items-center">
            <i class="fas fa-filter me-2 text-primary"></i>
            Filters
        </h5>
    </div>
    <div class="card-body">
        <form id="logsFilterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="dateFrom" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="dateFrom" name="date_from">
                </div>
                <div class="col-md-3">
                    <label for="dateTo" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="dateTo" name="date_to">
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-2"></i> Apply Filters
                    </button>
                    <button type="reset" class="btn btn-outline-secondary" id="resetFilters">
                        <i class="fas fa-undo me-2"></i> Reset
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Logs Table -->
<div class="card shadow-sm">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0 d-flex align-items-center">
                <i class="fas fa-table me-2 text-primary"></i>
                Activity Logs Database
            </h5>
            <div class="d-flex">
                <!-- Action Filter Dropdown -->
                <div class="dropdown me-2">
                    <button class="btn btn-light dropdown-toggle" type="button" id="actionFilterDropdown"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bolt me-1"></i> Action
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="actionFilterDropdown" id="actionFilterMenu">
                        <li><a class="dropdown-item action-filter active" href="#" data-action="">All Actions</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <!-- Action options will be populated dynamically -->
                    </ul>
                </div>
                <small class="text-muted" id="datatable-info">
                    <i class="fas fa-info-circle me-1"></i>
                    Loading logs...
                </small>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="logs-table" class="table table-hover mb-0" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th><i class="fas fa-hashtag me-1"></i>ID</th>
                        <th><i class="fas fa-user me-1"></i>User</th>
                        <th><i class="fas fa-bolt me-1"></i>Action</th>
                        <th><i class="fas fa-align-left me-1"></i>Description</th>
                        <th><i class="fas fa-network-wired me-1"></i>IP Address</th>
                        <th><i class="fas fa-clock me-1"></i>Date/Time</th>
                        <th width="100" class="text-center"><i class="fas fa-cogs me-1"></i>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for full description -->
<div class="modal fade" id="descriptionModal" tabindex="-1" aria-labelledby="descriptionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="descriptionModalLabel">
                    <i class="fas fa-align-left me-2"></i>Activity Log Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalDescriptionContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading description...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/select/1.3.4/css/select.bootstrap5.min.css">

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/select/1.3.4/js/dataTables.select.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

<script>
    $(document).ready(function () {
        let logsTable = null;
        let descriptionModal = new bootstrap.Modal(document.getElementById('descriptionModal'));

        // Initialize DataTable
        logsTable = $('#logs-table').DataTable({
            serverSide: true,
            ajax: {
                url: '<?= BASE_URL ?>/logs/datatable',
                type: 'POST',
                data: function (d) {
                    d.date_from = $('#dateFrom').val();
                    d.date_to = $('#dateTo').val();
                    d.user_filter = $('#userFilter').val();
                    d.action_filter = $('.action-filter.active').data('action') || '';
                }
            },
            columns: [
                {
                    data: 'id',
                    className: 'fw-bold text-primary'
                },
                {
                    data: 'user',
                    render: function (data, type, row) {
                        if (!data) return '<span class="text-muted">System</span>';
                        return `<div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                                <i class="fas fa-user text-primary"></i>
                            </div>
                            <span class="fw-semibold">${data}</span>
                        </div>`;
                    }
                },
                {
                    data: 'action',
                    render: function (data, type, row) {
                        return `<span class="badge bg-light text-dark border">
                            <i class="fas fa-bolt me-1"></i>${data}
                        </span>`;
                    }
                },
                {
                    data: 'description',
                    render: function (data, type, row) {
                        if (!data) return '<span class="text-muted">-</span>';
                        const truncated = data.length > 50 ? data.substring(0, 50) + '...' : data;
                        return `<span title="${data}">${truncated}</span>`;
                    }
                },
                {
                    data: 'ip_address',
                    render: function (data, type, row) {
                        return `<span class="badge bg-light text-dark border">
                            <i class="fas fa-network-wired me-1"></i>${data}
                        </span>`;
                    }
                },
                {
                    data: 'created_at',
                    render: function (data, type, row) {
                        return `<span class="badge bg-light text-dark border">
                            <i class="fas fa-clock me-1"></i>${data}
                        </span>`;
                    }
                },
                {
                    data: 'id',
                    orderable: false,
                    className: 'text-center',
                    render: function (data, type, row) {
                        return `<div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary"
                                     onclick="showDescriptionModal(${data})" title="View Full Description">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>`;
                    }
                }
            ],
            order: [[0, 'desc']],
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            dom: '<"top-controls"<"d-flex justify-content-between align-items-center"<"dt-buttons"B>l>>f<"mt-3"rt><"bottom"ip>',
            buttons: [
                {
                    extend: 'csv',
                    text: '<i class="fas fa-file-csv me-2"></i>CSV',
                    className: 'btn btn-light btn-sm border mx-1',
                    exportOptions: {
                        columns: ':visible',
                        modifier: { search: 'applied' }
                    }
                },
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel me-2"></i>Excel',
                    className: 'btn btn-light btn-sm border mx-1',
                    exportOptions: {
                        columns: ':visible',
                        modifier: { search: 'applied' }
                    }
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf me-2"></i>PDF',
                    className: 'btn btn-light btn-sm border mx-1',
                    exportOptions: {
                        columns: ':visible',
                        modifier: { search: 'applied' }
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print me-2"></i>Print',
                    className: 'btn btn-light btn-sm border mx-1',
                    exportOptions: {
                        columns: ':visible',
                        modifier: { search: 'applied' }
                    }
                }
            ],
            language: {
                processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>',
                emptyTable: '<div class="text-center py-4"><i class="fas fa-inbox fa-3x text-muted mb-3"></i><br><h5 class="text-muted">No activity logs found</h5><p class="text-muted">Try adjusting your filters</p></div>',
                zeroRecords: '<div class="text-center py-4"><i class="fas fa-search fa-3x text-muted mb-3"></i><br><h5 class="text-muted">No matching records found</h5><p class="text-muted">Try different search terms</p></div>'
            },
            drawCallback: function () {
                updateTableInfo();
            }
        });

        // Load filter options
        loadActionFilterOptions();
        loadUserFilterOptions();

        // Load action filter options
        function loadActionFilterOptions() {
            $.get('<?= BASE_URL ?>/logs/get-actions', function (data) {
                const menu = $('#actionFilterMenu');
                // Keep the "All Actions" option and divider
                const staticItems = menu.find('li:first, li:nth-child(2)');
                menu.empty().append(staticItems);

                if (data && data.length > 0) {
                    data.forEach(action => {
                        menu.append(`
                        <li><a class="dropdown-item action-filter" href="#" data-action="${action}">
                            ${action}
                        </a></li>
                    `);
                    });
                }
            }).fail(function () {
                console.error('Failed to load action filter options');
            });
        }


        // Action filter handler
        $(document).on('click', '.action-filter', function (e) {
            e.preventDefault();
            const action = $(this).data('action');
            const actionText = $(this).text();

            // Update the dropdown button text
            $('#actionFilterDropdown').html(
                `<i class="fas fa-bolt me-1"></i> ${action ? actionText : 'All Actions'}`
            );

            // Remove active class from all filters
            $('.action-filter').removeClass('active');
            // Add active class to clicked filter
            $(this).addClass('active');

            // Reload the table with the new filter
            logsTable.ajax.reload(function () {
                showToast(`Filtered by: ${action ? actionText : 'All Actions'}`, 'info');
            });
        });

        // Filter submit reloads table with validation
        $('#logsFilterForm').on('submit', function (e) {
            e.preventDefault();
            const fromDate = $('#dateFrom').val();
            const toDate = $('#dateTo').val();

            if (fromDate && toDate && new Date(toDate) < new Date(fromDate)) {
                showToast("'To Date' cannot be earlier than 'From Date'. Please correct the dates.", 'danger');
                return;
            }

            logsTable.ajax.reload(function () {
                showToast('Filters applied successfully!', 'success');
            });
        });

        // Reset filters and reload table
        $('#resetFilters').on('click', function () {
            $('#logsFilterForm')[0].reset();
            // Also reset the action filter
            $('.action-filter').removeClass('active');
            $('.action-filter[data-action=""]').addClass('active');
            $('#actionFilterDropdown').html('<i class="fas fa-bolt me-1"></i> All Actions');
            logsTable.ajax.reload(function () {
                showToast('Filters reset successfully!', 'info');
            });
        });

        // Show description modal with user-friendly formatting
        window.showDescriptionModal = function (logId) {
            $('#modalDescriptionContent').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading description...</p>
            </div>
        `);

            descriptionModal.show();

            // Fetch full description
            $.ajax({
                url: '<?= BASE_URL ?>/logs/get-description',
                type: 'POST',
                data: { id: logId },
                success: function (response) {
                    if (response.description) {
                        let formattedContent = formatLogDescription(response);
                        $('#modalDescriptionContent').html(formattedContent);
                    } else {
                        $('#modalDescriptionContent').html(`
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            No description available for this log entry.
                        </div>
                    `);
                    }
                },
                error: function () {
                    $('#modalDescriptionContent').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Failed to load description. Please try again.
                    </div>
                `);
                }
            });
        };

        // Format log description in a user-friendly way
        function formatLogDescription(response) {
            let html = `
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="description-item">
                        <div class="description-label">
                            <i class="fas fa-hashtag me-2"></i>Log ID
                        </div>
                        <div class="description-value">${response.id || 'N/A'}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="description-item">
                        <div class="description-label">
                            <i class="fas fa-user me-2"></i>User
                        </div>
                        <div class="description-value">${response.user || 'System'}</div>
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="description-item">
                        <div class="description-label">
                            <i class="fas fa-bolt me-2"></i>Action
                        </div>
                        <div class="description-value">
                            <span class="badge bg-primary">${response.action || 'N/A'}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="description-item">
                        <div class="description-label">
                            <i class="fas fa-network-wired me-2"></i>IP Address
                        </div>
                        <div class="description-value">${response.ip_address || 'N/A'}</div>
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-12">
                    <div class="description-item">
                        <div class="description-label">
                            <i class="fas fa-clock me-2"></i>Date & Time
                        </div>
                        <div class="description-value">${response.created_at || 'N/A'}</div>
                    </div>
                </div>
            </div>
        `;

            // Try to parse description as JSON for better formatting
            let description = response.description;
            let parsedData = null;

            try {
                parsedData = JSON.parse(description);
            } catch (e) {
                // Not JSON, treat as plain text
            }

            if (parsedData && typeof parsedData === 'object') {
                html += `
                <div class="mb-4">
                    <div class="description-label mb-3">
                        <i class="fas fa-info-circle me-2"></i>Activity Details
                    </div>
                    <div class="row">
            `;

                // Format common log fields in a user-friendly way
                Object.keys(parsedData).forEach(key => {
                    let value = parsedData[key];
                    let displayKey = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

                    // Format specific fields
                    if (key === 'timestamp' || key === 'created_at' || key === 'updated_at') {
                        value = new Date(value).toLocaleString();
                    } else if (key === 'user_id' && value) {
                        displayKey = 'User ID';
                    } else if (key === 'license_id' && value) {
                        displayKey = 'License ID';
                    } else if (key === 'payment_id' && value) {
                        displayKey = 'Payment ID';
                    } else if (typeof value === 'object') {
                        value = JSON.stringify(value, null, 2);
                    }

                    html += `
                    <div class="col-md-6 mb-3">
                        <div class="description-item">
                            <div class="description-label">${displayKey}</div>
                            <div class="description-value">${value || 'N/A'}</div>
                        </div>
                    </div>
                `;
                });

                html += `
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="description-label mb-2">
                        <i class="fas fa-code me-2"></i>Raw Data (JSON)
                    </div>
                    <div class="json-data">${JSON.stringify(parsedData, null, 2)}</div>
                </div>
            `;
            } else {
                // Plain text description
                html += `
                <div class="mb-3">
                    <div class="description-label mb-2">
                        <i class="fas fa-align-left me-2"></i>Description
                    </div>
                    <div class="description-item">
                        <div class="description-value">${description}</div>
                    </div>
                </div>
            `;
            }

            return html;
        }

        function updateTableInfo() {
            const info = logsTable.page.info();
            $('#datatable-info').html(
                `<i class="fas fa-info-circle me-1"></i>Showing ${info.start + 1} to ${info.end} of ${info.recordsTotal} logs`
            );
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
    });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>