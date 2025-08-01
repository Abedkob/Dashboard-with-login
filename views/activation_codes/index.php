<?php include __DIR__ . '/../layouts/header.php'; ?>

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
            <a href="/Practice_php/public/activation-codes/create" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus me-2"></i>Add New License
            </a>
            <div class="dropdown">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle shadow-sm"
                    data-bs-toggle="dropdown">
                    <i class="fas fa-download me-2"></i>Export
                </button>
                <ul class="dropdown-menu shadow">
                    <li><a class="dropdown-item" href="#" onclick="exportData('csv')">
                            <i class="fas fa-file-csv me-2 text-success"></i>Export as CSV
                        </a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportData('pdf')">
                            <i class="fas fa-file-pdf me-2 text-danger"></i>Export as PDF
                        </a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportData('excel')">
                            <i class="fas fa-file-excel me-2 text-success"></i>Export as Excel
                        </a></li>
                </ul>
            </div>
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

<!-- Search and Filter Controls -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
    </div>
    <div class="card-body">
        <div class="row g-3">

            <div class="col-md-2">
                <label for="datatable-status" class="form-label fw-semibold">
                    <i class="fas fa-filter me-1"></i>Status
                </label>
                <select class="form-select shadow-sm" id="datatable-status">
                    <option value="">All Status</option>
                    <option value="active">✅ Active</option>
                    <option value="expired">❌ Expired</option>
                    <option value="expiring">⚠️ Expiring Soon</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="datatable-length" class="form-label fw-semibold">
                    <i class="fas fa-list-ol me-1"></i>Show
                </label>
                <select class="form-select shadow-sm" id="datatable-length">
                    <option value="10">10 entries</option>
                    <option value="25">25 entries</option>
                    <option value="50">50 entries</option>
                    <option value="100">100 entries</option>
                    <option value="-1">All entries</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <div class="btn-group w-100" role="group">
                    <button type="button" class="btn btn-outline-secondary shadow-sm" id="datatable-reset">
                        <i class="fas fa-sync-alt me-2"></i>Reset Filters
                    </button>
                    <button type="button" class="btn btn-primary shadow-sm" id="datatable-apply">
                        <i class="fas fa-filter me-2"></i>Apply Filters
                    </button>
                </div>
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
            <small class="text-muted" id="datatable-info">
                <i class="fas fa-info-circle me-1"></i>
                Loading licenses...
            </small>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="licenses-table" class="table table-hover mb-0" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th width="40" class="text-center">
                            <input type="checkbox" id="select-all" onchange="toggleSelectAll()"
                                class="form-check-input">
                        </th>
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

<script>
    $(document).ready(function () {
        // Initialize DataTable with improved styling
        var table = $('#licenses-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '/Practice_php/public/activation-codes/datatable',
                type: 'POST',
                data: function (d) {
                    d.status = $('#datatable-status').val();
                    d.search = $('#datatable-search').val();
                }
            },
            columns: [
                {
                    data: null,
                    orderable: false,
                    className: 'text-center',
                    defaultContent: '<input type="checkbox" class="license-checkbox form-check-input">'
                },
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
                                    <a href="/Practice_php/public/activation-codes/edit?id=${data}" 
                                       class="btn btn-outline-primary" title="Edit License">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="deleteLicense(${data})" title="Delete License">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>`;
                        }
                        return data;
                    }
                }
            ],
            select: {
                style: 'multi',
                selector: 'td:first-child'
            },
            order: [[5, 'desc']],
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            dom: '<"top"lf>rt<"bottom"ip>',
            language: {
                processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>',
                emptyTable: '<div class="text-center py-4"><i class="fas fa-inbox fa-3x text-muted mb-3"></i><br><h5 class="text-muted">No licenses found</h5><p class="text-muted">Try adjusting your search criteria</p></div>',
                zeroRecords: '<div class="text-center py-4"><i class="fas fa-search fa-3x text-muted mb-3"></i><br><h5 class="text-muted">No matching records found</h5><p class="text-muted">Try different search terms</p></div>'
            },
            drawCallback: function () {
                updateSelectedCount();
                updateTableInfo();
            }
        });

        // Apply filters with loading state
        $('#datatable-apply').on('click', function () {
            $(this).html('<i class="fas fa-spinner fa-spin me-2"></i>Applying...');
            table.ajax.reload(function () {
                $('#datatable-apply').html('<i class="fas fa-filter me-2"></i>Apply Filters');
            });
        });

        // Reset filters
        $('#datatable-reset').on('click', function () {
            $('#datatable-search').val('');
            $('#datatable-status').val('');
            $('#datatable-length').val('10');
            table.search('').columns().search('').draw();
            showToast('Filters reset successfully', 'info');
        });

        // Change page length
        $('#datatable-length').on('change', function () {
            table.page.len($(this).val()).draw();
        });

        // Update table info
        function updateTableInfo() {
            var info = table.page.info();
            $('#datatable-info').html(
                `<i class="fas fa-info-circle me-1"></i>Showing ${info.start + 1} to ${info.end} of ${info.recordsTotal} licenses`
            );
        }

        // Update selected count
        function updateSelectedCount() {
            var selectedCount = table.rows({ selected: true }).count();
            $('#selected-count').text(selectedCount);
            if (selectedCount > 0) {
                $('.bulk-actions').slideDown();
            } else {
                $('.bulk-actions').slideUp();
            }
        }

        // Toggle select all
        window.toggleSelectAll = function () {
            if ($('#select-all').is(':checked')) {
                table.rows().select();
            } else {
                table.rows().deselect();
            }
            updateSelectedCount();
        }

        // Row selection event
        table.on('select deselect', function () {
            updateSelectedCount();
        });
    });

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function () {
            showToast('License key copied to clipboard!', 'success');
        });
    }

    function deleteLicense(id) {
        if (confirm('⚠️ Are you sure you want to delete this license?\n\nThis action cannot be undone.')) {
            $.ajax({
                url: `/Practice_php/public/activation-codes/delete?id=${id}`,
                type: 'POST',
                beforeSend: function () {
                    showToast('Deleting license...', 'info');
                },
                success: function (response) {
                    $('#licenses-table').DataTable().ajax.reload();
                    showToast('✅ License deleted successfully!', 'success');
                },
                error: function (xhr) {
                    showToast('❌ Error deleting license: ' + xhr.responseText, 'danger');
                }
            });
        }
    }

    function bulkUpdate() {
        const selectedIds = $('#licenses-table').DataTable().rows({ selected: true }).ids().toArray();
        const newDate = $('#bulk-valid-to').val();

        if (selectedIds.length === 0) {
            showToast('⚠️ Please select at least one license', 'warning');
            return;
        }
        if (!newDate) {
            showToast('⚠️ Please select a new expiry date', 'warning');
            return;
        }

        if (confirm(`🔄 Are you sure you want to update ${selectedIds.length} license(s)?\n\nNew expiry date: ${newDate}`)) {
            $.ajax({
                url: '/Practice_php/public/activation-codes/bulk-update',
                type: 'POST',
                data: {
                    ids: selectedIds,
                    valid_to: newDate
                },
                beforeSend: function () {
                    showToast('Updating licenses...', 'info');
                },
                success: function (response) {
                    $('#licenses-table').DataTable().ajax.reload();
                    showToast(`✅ ${selectedIds.length} license(s) updated successfully!`, 'success');
                    $('#bulk-valid-to').val('');
                },
                error: function (xhr) {
                    showToast('❌ Error updating licenses: ' + xhr.responseText, 'danger');
                }
            });
        }
    }

    function bulkDelete() {
        const selectedIds = $('#licenses-table').DataTable().rows({ selected: true }).ids().toArray();

        if (selectedIds.length === 0) {
            showToast('⚠️ Please select at least one license', 'warning');
            return;
        }

        if (confirm(`🗑️ Are you sure you want to delete ${selectedIds.length} license(s)?\n\n⚠️ This action cannot be undone!`)) {
            $.ajax({
                url: '/Practice_php/public/activation-codes/bulk-delete',
                type: 'POST',
                data: {
                    ids: selectedIds
                },
                beforeSend: function () {
                    showToast('Deleting licenses...', 'info');
                },
                success: function (response) {
                    $('#licenses-table').DataTable().ajax.reload();
                    showToast(`✅ ${selectedIds.length} license(s) deleted successfully!`, 'success');
                },
                error: function (xhr) {
                    showToast('❌ Error deleting licenses: ' + xhr.responseText, 'danger');
                }
            });
        }
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

    function exportData(format) {
        showToast(`📊 Exporting data as ${format.toUpperCase()}...`, 'info');
        // Add your export logic here
    }

</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>