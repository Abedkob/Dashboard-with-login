    <?php include __DIR__ . '/../layouts/header.php'; ?>
    <!-- Custom Styles -->
    <style>
        .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid #dee2e6;
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.15s ease-in-out;
        }

        .badge-light {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            color: #495057;
        }

        /* Action dropdown styles */
        .action-dropdown {
            min-width: 180px;
        }

        .action-filter-btn {
            border: 1px solid #dee2e6;
            background: white;
        }

        .action-filter-btn:hover {
            background: #f8f9fa;
        }

        .action-filter-item {
            cursor: pointer;
            padding: 0.25rem 1rem;
        }

        .action-filter-item:hover {
            background-color: #f8f9fa;
        }

        .action-filter-item.active {
            background-color: #e9ecef;
            font-weight: 500;
        }

        /* DataTables pagination positioning */
        .dataTables_paginate {
            float: left !important;
            padding-top: 0.5rem;
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
    </style>

    <!-- Page Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
        <div class="d-flex align-items-center">
            <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3">
                <i class="fas fa-history text-primary fs-4"></i>
            </div>
            <h1 class="h2 mb-0 text-dark fw-bold">Activity Logs</h1>
        </div>
    </div>

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
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-2"></i> Apply Filters
                        </button>
                        <button type="reset" class="btn btn-outline-secondary ms-2" id="resetFilters">
                            <i class="fas fa-undo me-2"></i> Reset
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table Card -->
    <div class="card shadow-sm">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 d-flex align-items-center">
                    <i class="fas fa-table me-2 text-primary"></i>
                    Activity Logs
                </h5>
                <div class="d-flex align-items-center">
                    <!-- Action Filter Dropdown -->
                    <div class="dropdown me-3">
                        <button class="btn btn-light dropdown-toggle action-filter-btn" type="button"
                            id="actionFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bolt me-1"></i> Action
                        </button>
                        <ul class="dropdown-menu action-dropdown" aria-labelledby="actionFilterDropdown"
                            id="actionFilterMenu">
                            <li><a class="dropdown-item action-filter-item active" href="#" data-action="">All Actions</a>
                            </li>
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
                <table id="logsTable" class="table table-hover mb-0" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th><i class="fas fa-hashtag me-1"></i>ID</th>
                            <th><i class="fas fa-user me-1"></i>User</th>
                            <th><i class="fas fa-bolt me-1"></i>Action</th>
                            <th><i class="fas fa-align-left me-1"></i>Description</th>
                            <th><i class="fas fa-network-wired me-1"></i>IP Address</th>
                            <th><i class="fas fa-clock me-1"></i>Date/Time</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for full description -->
    <div class="modal fade" id="descriptionModal" tabindex="-1" aria-labelledby="descriptionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="descriptionModalLabel">
                        <i class="fas fa-align-left me-2"></i>Full Description
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

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {
            var logsTable = $('#logsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '<?= url('logs/datatable') ?>',
                    type: 'POST',
                    data: function (d) {
                        d.date_from = $('#dateFrom').val();
                        d.date_to = $('#dateTo').val();
                        d.action_filter = $('.action-filter-item.active').data('action') || '';
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
                            return `<span class="fw-semibold">${data}</span>`;
                        }
                    },
                    {
                        data: 'action',
                        render: function (data) {
                            return `<span class="badge badge-light">${data}</span>`;
                        }
                    },
                    {
                        data: 'description',
                        render: function (data, type, row) {
                            const truncated = data.length > 50 ? data.substring(0, 50) + '...' : data;
                            return `<span>${truncated}</span> 
                                    <span class="view-full-btn" data-id="${row.id}" title="View full description">
                                        <i class="fas fa-eye ms-2"></i>
                                    </span>`;
                        }
                    },
                    {
                        data: 'ip_address',
                        render: function (data) {
                            return `<span class="badge badge-light">${data}</span>`;
                        }
                    },
                    {
                        data: 'created_at',
                        render: function (data) {
                            return `<span class="badge badge-light">
                                <i class="fas fa-clock me-1"></i>${data}
                            </span>`;
                        }
                    }
                ],
                order: [[0, 'desc']],
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                language: {
                    processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>',
                    emptyTable: '<div class="text-center py-4"><i class="fas fa-inbox fa-3x text-muted mb-3"></i><br><h5 class="text-muted">No activity logs found</h5><p class="text-muted">Try adjusting your filters</p></div>',
                    zeroRecords: '<div class="text-center py-4"><i class="fas fa-search fa-3x text-muted mb-3"></i><br><h5 class="text-muted">No matching records found</h5><p class="text-muted">Try different search terms</p></div>'
                },
                drawCallback: function () {
                    updateTableInfo();
                },
                // Move pagination to the left
                dom: '<"top"f>rt<"bottom"lip><"clear">'
            });

            // Load action filter options
            function loadActionFilterOptions() {
                $.get('<?= url('logs/get-actions') ?>', function (data) {
                    const menu = $('#actionFilterMenu');
                    // Keep the "All Actions" option and divider
                    const staticItems = menu.find('li:first, li:nth-child(2)');
                    menu.empty().append(staticItems);

                    if (data && data.length > 0) {
                        data.forEach(action => {
                            menu.append(`
                                <li><a class="dropdown-item action-filter-item" href="#" data-action="${action}">
                                    ${action}
                                </a></li>
                            `);
                        });
                    }
                }).fail(function () {
                    console.error('Failed to load action filter options');
                });
            }

            // Initialize action filters
            loadActionFilterOptions();

            // Action filter handler
            $(document).on('click', '.action-filter-item', function (e) {
                e.preventDefault();
                const action = $(this).data('action');
                const actionText = $(this).text();

                // Update the dropdown button text
                $('#actionFilterDropdown').html(
                    `<i class="fas fa-bolt me-1"></i> ${action ? actionText : 'All Actions'}`
                );

                // Remove active class from all filters
                $('.action-filter-item').removeClass('active');
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

                logsTable.ajax.reload();
            });

            // Reset filters and reload table
            $('#resetFilters').on('click', function () {
                $('#logsFilterForm')[0].reset();
                // Also reset the action filter
                $('.action-filter-item').removeClass('active');
                $('.action-filter-item[data-action=""]').addClass('active');
                $('#actionFilterDropdown').html('<i class="fas fa-bolt me-1"></i> All Actions');
                logsTable.ajax.reload();
            });

            // Show full description modal
            $('#logsTable').on('click', '.view-full-btn', function (e) {
                e.preventDefault();
                const logId = $(this).data('id');
                const modal = new bootstrap.Modal(document.getElementById('descriptionModal'));

                // Show loading state
                $('#modalDescriptionContent').html(`
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading description...</p>
                    </div>
                `);

                // Fetch full description
                $.ajax({
                    url: '<?= url('logs/get-description') ?>',
                    type: 'POST',
                    data: { id: logId },
                    success: function (response) {
                        $('#modalDescriptionContent').text(response.description || 'No description available');
                    },
                    error: function () {
                        $('#modalDescriptionContent').html(`
                            <div class="alert alert-danger">
                                Failed to load description. Please try again.
                            </div>
                        `);
                    }
                });

                modal.show();
            });

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