<?php include __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../../public/config.php'; ?>

<!-- Custom Styles -->
<style>
    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
    }

    .license-key {
        font-family: 'Courier New', monospace;
        background: #f8f9fa;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }

    .bulk-actions {
        background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
        border: 1px solid #e1bee7;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1rem;
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

    .search-container {
        position: relative;
    }

    .search-icon {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
    }

    .search-input {
        padding-left: 2.5rem;
    }

    @media (max-width: 768px) {
        .btn-toolbar {
            flex-direction: column;
            gap: 0.5rem;
        }

        .bulk-actions .row {
            flex-direction: column;
            gap: 1rem;
        }
    }

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

    .delete-modal-icon {
        font-size: 3rem;
        color: #dc3545;
    }

    .total-display {
        background: #f8f9fa;
        padding: 12px 16px;
        border-top: 2px solid #007bff;
        font-weight: 600;
        text-align: right;
        color: #495057;
    }
</style>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <div class="d-flex align-items-center">
        <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3">
            <i class="fas fa-list text-primary fs-4"></i>
        </div>
        <h1 class="h2 mb-0 text-dark fw-bold">License Manager</h1>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal"
                data-bs-target="#addLicenseModal">
                <i class="fas fa-plus me-2"></i>Add New License
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

<!-- Add License Modal -->
<div class="modal fade" id="addLicenseModal" tabindex="-1" aria-labelledby="addLicenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addLicenseModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Add New License
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="addLicenseModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading license form...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit License Modal -->
<div class="modal fade" id="editLicenseModal" tabindex="-1" aria-labelledby="editLicenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editLicenseModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit License
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="editLicenseModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading license details...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteLicenseModal" tabindex="-1" aria-labelledby="deleteLicenseModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteLicenseModalLabel" style="color:black">
                    <i style="color: orange;" class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="delete-modal-icon mb-3 fs-1 text-danger">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <h5 class="text-dark">Are you sure you want to delete this license?</h5>
                <p class="text-muted">This action cannot be undone. All data associated with this license will be
                    permanently removed.</p>
                <div class="license-info bg-white border p-3 rounded mt-3 mb-3 shadow-sm">
                    <p class="mb-1"><strong>License ID:</strong> <span id="deleteLicenseId"></span></p>
                    <p class="mb-1"><strong>Name:</strong> <span id="deleteLicenseName"></span></p>
                    <p class="mb-0"><strong>Key:</strong> <span id="deleteLicenseKey"></span></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash-alt me-2"></i>Delete License
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Payment For License Modal -->
<div class="modal fade" id="addPaymentForLicenseModal" tabindex="-1" aria-labelledby="addPaymentForLicenseModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPaymentForLicenseModalLabel">
                    <i class="fas fa-dollar-sign me-2"></i>Add Payment for License <span id="modalLicenseName"
                        class="text-primary"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="addPaymentForLicenseModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading payment form...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="submitPaymentForLicense">Save Payment</button>
            </div>
        </div>
    </div>
</div>

<!-- License Table -->
<div class="card shadow-sm">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0 d-flex align-items-center">
                <i class="fas fa-table me-2 text-primary"></i>
                License Database
            </h5>
            <div class="d-flex">
                <!-- Status Filter Dropdown -->
                <div class="dropdown me-2">
                    <button class="btn btn-light dropdown-toggle" type="button" id="statusFilterDropdown"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-filter me-1"></i> Status
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="statusFilterDropdown">
                        <li><a class="dropdown-item status-filter" href="#" data-status="">All Statuses</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item status-filter" href="#" data-status="active">Active</a></li>
                        <li><a class="dropdown-item status-filter" href="#" data-status="expiring">Expiring Soon</a>
                        </li>
                        <li><a class="dropdown-item status-filter" href="#" data-status="expired">Expired</a></li>
                    </ul>
                </div>
                <small class="text-muted" id="datatable-info">
                    <i class="fas fa-info-circle me-1"></i>
                    Loading licenses...
                </small>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="licenses-table" class="table table-hover mb-0" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th><i class="fas fa-hashtag me-1"></i>ID</th>
                        <th><i class="fas fa-user me-1"></i>Full Name</th>
                        <th><i class="fas fa-key me-1"></i>License Key</th>
                        <th><i class="fas fa-calendar-plus me-1"></i>Valid From</th>
                        <th><i class="fas fa-calendar-times me-1"></i>Valid To</th>
                        <th><i class="fas fa-info-circle me-1"></i>Status</th>
                        <th><i class="fas fa-clock me-1"></i>Days Left</th>
                        <th width="150" class="text-center"><i class="fas fa-cogs me-1"></i>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
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
        // Initialize modals
        let addLicenseModal = new bootstrap.Modal(document.getElementById('addLicenseModal'));
        let editLicenseModal = new bootstrap.Modal(document.getElementById('editLicenseModal'));
        let deleteLicenseModal = new bootstrap.Modal(document.getElementById('deleteLicenseModal'));
        let addPaymentForLicenseModal = new bootstrap.Modal(document.getElementById('addPaymentForLicenseModal'));

        let currentLicenseId = null;
        let table = null;

        // Initialize DataTable
        table = $('#licenses-table').DataTable({
            serverSide: true,
            ajax: {
                url: '<?= BASE_URL ?>/activation-codes/datatable',
                type: 'POST',
                data: function (d) {
                    const selectedStatus = $('.status-filter.active').data('status') || '';
                    d.status = selectedStatus;
                }
            },
            columns: [
                {
                    data: 'id',
                    className: 'fw-bold text-primary'
                },
                {
                    data: 'name',
                    render: function (data, type, row) {
                        return `<div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                                <i class="fas fa-user text-primary"></i>
                            </div>
                            <span class="fw-semibold">${data}</span>
                        </div>`;
                    }
                },
                {
                    data: 'license',
                    render: function (data, type, row) {
                        if (type === 'display') {
                            return `<div class="d-flex align-items-center">
                                <code class="license-key me-2">${data.substring(0, 12)}...</code>
                                <button class="btn btn-sm btn-outline-primary" onclick="copyToClipboard('${data}')" title="Copy to clipboard">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>`;
                        }
                        return data;
                    }
                },
                {
                    data: 'valid_from',
                    render: function (data, type, row) {
                        return `<span class="badge bg-light text-dark border">
                            <i class="fas fa-calendar-plus me-1"></i>${data}
                        </span>`;
                    }
                },
                {
                    data: 'valid_to',
                    render: function (data, type, row) {
                        return `<span class="badge bg-light text-dark border">
                            <i class="fas fa-calendar-times me-1"></i>${data}
                        </span>`;
                    }
                },
                {
                    data: 'status',
                    render: function (data, type, row) {
                        if (type === 'display') {
                            let badgeClass = '';
                            let icon = '';
                            if (data === 'expired') {
                                badgeClass = 'bg-danger';
                                icon = 'fa-times-circle';
                            } else if (data === 'expiring') {
                                badgeClass = 'bg-warning text-dark';
                                icon = 'fa-exclamation-triangle';
                            } else {
                                badgeClass = 'bg-success';
                                icon = 'fa-check-circle';
                            }
                            return `<span class="badge ${badgeClass} status-badge">
                                <i class="fas ${icon} me-1"></i>
                                ${data.charAt(0).toUpperCase() + data.slice(1)}
                            </span>`;
                        }
                        return data;
                    }
                },
                {
                    data: 'days_left',
                    render: function (data, type, row) {
                        if (type === 'display') {
                            if (row.status === 'expired') {
                                return `<span class="badge bg-danger">
                                    <i class="fas fa-exclamation-circle me-1"></i>
                                    ${Math.abs(data)} days ago
                                </span>`;
                            } else if (row.status === 'expiring') {
                                return `<span class="badge bg-warning text-dark">
                                    <i class="fas fa-clock me-1"></i>
                                    ${data} days left
                                </span>`;
                            } else {
                                return `<span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i>
                                    ${data} days left
                                </span>`;
                            }
                        }
                        return data;
                    }
                },
                {
                    data: 'id',
                    orderable: false,
                    className: 'text-center',
                    render: function (data, type, row) {
                        if (type === 'display') {
                            return `<div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-primary"
                                         onclick="showEditModal(${data})" title="Edit License">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger"
                                         onclick="showDeleteModal(${data}, '${row.name}', '${row.license}')" title="Delete License">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <button type="button" class="btn btn-outline-success"
                                         onclick="showAddPaymentForLicenseModal(${data}, '${row.name}')" title="Add Payment for License">
                                    <i class="fas fa-dollar-sign"></i>
                                </button>
                            </div>`;
                        }
                        return data;
                    }
                }
            ],
            order: [[5, 'desc']],
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            dom: '<"top"Bfl>rt<"bottom"ip>',
            buttons: [
                {
                    extend: 'csv',
                    text: '<i class="fas fa-file-csv me-2"></i>CSV',
                    className: 'btn btn-light btn-sm border mx-1'
                },
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel me-2"></i>Excel',
                    className: 'btn btn-light btn-sm border mx-1'
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf me-2"></i>PDF',
                    className: 'btn btn-light btn-sm border mx-1'
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print me-2"></i>Print',
                    className: 'btn btn-light btn-sm border mx-1'
                }
            ],
            language: {
                processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>',
                emptyTable: '<div class="text-center py-4"><i class="fas fa-inbox fa-3x text-muted mb-3"></i><br><h5 class="text-muted">No licenses found</h5><p class="text-muted">Try adjusting your search criteria</p></div>',
                zeroRecords: '<div class="text-center py-4"><i class="fas fa-search fa-3x text-muted mb-3"></i><br><h5 class="text-muted">No matching records found</h5><p class="text-muted">Try different search terms</p></div>'
            },
            drawCallback: function () {
                updateTableInfo();
            }
        });

        // Load add license form when modal is shown
        $('#addLicenseModal').on('show.bs.modal', function () {
            loadAddLicenseForm();
        });

        // Clean up modal when hidden
        $('#addLicenseModal').on('hidden.bs.modal', function () {
            // Remove any remaining backdrop
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('padding-right', '');

            // Reset modal content
            $('#addLicenseModalBody').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading license form...</p>
            </div>
        `);
        });

        // Status filter handler
        $('.status-filter').on('click', function (e) {
            e.preventDefault();
            const status = $(this).data('status');
            const statusText = $(this).text();

            $('#statusFilterDropdown').html(
                `<i class="fas fa-filter me-1"></i> ${status ? statusText : 'All Statuses'}`
            );

            $('.status-filter').removeClass('active');
            $(this).addClass('active');

            table.ajax.reload(function () {
                showToast(`Filtered by: ${status ? statusText : 'All Statuses'}`, 'info');
            });
        });

        // Confirm delete button handler
        $('#confirmDeleteBtn').on('click', function () {
            deleteLicense(currentLicenseId);
            deleteLicenseModal.hide();
        });

        // Payment for license modal submit
        $(document).on('click', '#submitPaymentForLicense', function (e) {
            e.preventDefault();
            submitPaymentForLicenseForm();
        });

        // Functions
        function loadAddLicenseForm() {
            $('#addLicenseModalBody').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading license form...</p>
            </div>
        `);

            $.get('<?= BASE_URL ?>/activation-codes/create', function (data) {
                $('#addLicenseModalBody').html(data);
                initLicenseForm();
            }).fail(function () {
                $('#addLicenseModalBody').html(`
                <div class="alert alert-danger">
                    Failed to load form. Please try again.
                </div>
            `);
            });
        }

        function initLicenseForm() {
            $('#licenseForm').off('submit').on('submit', function (e) {
                e.preventDefault();
                submitLicenseForm();
            });

            const today = new Date().toISOString().split('T')[0];
            const oneYearLater = new Date();
            oneYearLater.setFullYear(oneYearLater.getFullYear() + 1);
            const oneYearLaterStr = oneYearLater.toISOString().split('T')[0];

            $('#valid_from').val(today);
            $('#valid_to').val(oneYearLaterStr);
        }

        function submitLicenseForm() {
            const $submitBtn = $('#licenseForm button[type="submit"]');
            const originalText = $submitBtn.html();

            $.ajax({
                url: '<?= BASE_URL ?>/activation-codes/create',
                type: 'POST',
                data: $('#licenseForm').serialize(),
                dataType: 'json',
                beforeSend: function () {
                    $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Creating...');
                },
                success: function (response) {
                    if (response.success) {
                        showToast('✅ License created successfully!', 'success');
                        table.ajax.reload(null, false);

                        // Properly close modal and clean up
                        addLicenseModal.hide();

                        // Force cleanup after a short delay
                        setTimeout(function () {
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open').css('padding-right', '');
                        }, 300);

                    } else {
                        if (response.errors) {
                            let errorHtml = '<ul>';
                            response.errors.forEach(function (err) {
                                errorHtml += '<li>' + err + '</li>';
                            });
                            errorHtml += '</ul>';
                            showToast(errorHtml, 'danger');
                        } else {
                            showToast('❌ ' + (response.message || 'Unknown error'), 'danger');
                        }
                    }
                },
                error: function (xhr) {
                    console.error('License creation error:', xhr);
                    showToast('❌ Error creating license. Please try again.', 'danger');
                },
                complete: function () {
                    $submitBtn.prop('disabled', false).html(originalText);
                }
            });
        }

        window.showEditModal = function (id) {
            currentLicenseId = id;
            $('#editLicenseModalBody').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading license details...</p>
            </div>
        `);

            editLicenseModal.show();

            $.get('<?= BASE_URL ?>/activation-codes/edit?id=' + id)
                .done(function (data) {
                    $('#editLicenseModalBody').html(data);
                    attachEditFormHandler();
                })
                .fail(function () {
                    $('#editLicenseModalBody').html(`
                    <div class="alert alert-danger">
                        Failed to load license details. Please try again.
                    </div>
                `);
                });
        };

        function attachEditFormHandler() {
            $('#editLicenseForm').off('submit').on('submit', function (e) {
                e.preventDefault();
                const form = $(this);
                const url = form.attr('action');
                const formData = form.serialize();

                $('.date-error').remove();

                const validFromVal = $('#valid_from').val();
                const validToVal = $('#valid_to').val();
                const validFrom = new Date(validFromVal);
                const validTo = new Date(validToVal);

                if (!validFromVal || isNaN(validFrom.getTime()) || !validToVal || isNaN(validTo.getTime())) {
                    $('#valid_to').after(`
                    <div class="invalid-feedback date-error" style="display: block; color: #dc3545; margin-top: 0.25rem;">
                        Please provide valid dates for both fields.
                    </div>
                `);
                    return;
                }

                if (validTo <= validFrom) {
                    $('#valid_to').after(`
                    <div class="invalid-feedback date-error" style="display: block; color: #dc3545; margin-top: 0.25rem;">
                        End date must be after the start date.
                    </div>
                `);
                    return;
                }

                const submitBtn = form.find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Saving...');

                $.ajax({
                    type: 'POST',
                    url: url,
                    data: formData,
                    success: function (response) {
                        editLicenseModal.hide();
                        table.ajax.reload(null, false);
                        showToast('✅ License updated successfully!', 'success');
                    },
                    error: function (xhr) {
                        if (xhr.responseText) {
                            $('#editLicenseModalBody').html(xhr.responseText);
                            attachEditFormHandler();
                        }
                    },
                    complete: function () {
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });
        }

        window.showDeleteModal = function (id, name, license) {
            currentLicenseId = id;
            $('#deleteLicenseId').text(id);
            $('#deleteLicenseName').text(name);
            $('#deleteLicenseKey').text(license.substring(0, 12) + '...');
            deleteLicenseModal.show();
        };

        window.copyToClipboard = function (text) {
            navigator.clipboard.writeText(text).then(function () {
                showToast('License key copied to clipboard!', 'success');
            });
        };

        function deleteLicense(id) {
            $.ajax({
                url: '<?= BASE_URL ?>/activation-codes/delete?id=' + id,
                type: 'POST',
                beforeSend: function () {
                    showToast('Deleting license...', 'info');
                },
                success: function (response) {
                    table.ajax.reload();
                    showToast('✅ License deleted successfully!', 'success');
                },
                error: function (xhr) {
                    showToast('❌ Error deleting license: ' + xhr.responseText, 'danger');
                }
            });
        }

        window.showAddPaymentForLicenseModal = function (licenseId, licenseName) {
            currentLicenseId = licenseId;
            $('#modalLicenseName').text(`(License ID: ${licenseId})`);
            $('#addPaymentForLicenseModalBody').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading payment form...</p>
            </div>
        `);

            addPaymentForLicenseModal.show();

            $.get(`<?= BASE_URL ?>/payments-manager/create-payment-for-license-form?license_id=${licenseId}&license_name=${encodeURIComponent(licenseName)}`, function (data) {
                $('#addPaymentForLicenseModalBody').html(data);
                initPaymentForLicenseForm();
            }).fail(function () {
                $('#addPaymentForLicenseModalBody').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Failed to load payment form. Please try again.
                </div>
            `);
            });
        };

        function initPaymentForLicenseForm() {
            const today = new Date().toISOString().split('T')[0];
            $('#addPaymentForLicenseForm #payment_date').val(today);

            $('#addPaymentForLicenseForm #method').change(function () {
                if ($(this).val() === 'other') {
                    $('#addPaymentForLicenseForm #customMethod').show().attr('required', true);
                } else {
                    $('#addPaymentForLicenseForm #customMethod').hide().removeAttr('required');
                }
            });
        }

        function submitPaymentForLicenseForm() {
            const form = $('#addPaymentForLicenseForm');
            const formData = {
                license_id: form.find('input[name="license_id"]').val(),
                client_id: form.find('#client_id').val(),
                amount: form.find('#amount').val(),
                method: form.find('#method').val() === 'other' ? form.find('#customMethod').val() : form.find('#method').val(),
                payment_date: form.find('#payment_date').val(),
                note: form.find('#note').val()
            };

            const $button = $('#submitPaymentForLicense');
            const originalText = $button.html();
            $button.html('<i class="fas fa-spinner fa-spin me-2"></i>Saving...').prop('disabled', true);

            $.ajax({
                url: '<?= BASE_URL ?>/payments-manager/create-payment-for-license',
                method: 'POST',
                data: formData,
                dataType: 'json',
                success: function (response) {
                    if (response.error) {
                        showToast(response.error, 'danger');
                    } else {
                        showToast('Payment created successfully!', 'success');
                        addPaymentForLicenseModal.hide();
                        table.ajax.reload(null, false);
                        form[0].reset();
                        form.find('#customMethod').hide().removeAttr('required');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Create payment for license error:', xhr.responseText);
                    let errorMessage = 'An error occurred while creating the payment for license';
                    try {
                        const errorResponse = JSON.parse(xhr.responseText);
                        errorMessage = errorResponse.error || errorMessage;
                    } catch (e) {
                        errorMessage = xhr.responseText || errorMessage;
                    }
                    showToast(errorMessage, 'danger');
                },
                complete: function () {
                    $button.html(originalText).prop('disabled', false);
                }
            });
        }

        function updateTableInfo() {
            const info = table.page.info();
            $('#datatable-info').html(
                `<i class="fas fa-info-circle me-1"></i>Showing ${info.start + 1} to ${info.end} of ${info.recordsTotal} licenses`
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