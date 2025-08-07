<?php include __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../public/config.php'; ?>
<!-- Custom Styles -->
<style>
    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
    }

    .payment-amount {
        font-family: 'Courier New', monospace;
        background: #f8f9fa;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.875rem;
        font-weight: 600;
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
            <i class="fas fa-credit-card text-primary fs-4"></i>
        </div>
        <h1 class="h2 mb-0 text-dark fw-bold">Payments Manager</h1>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal"
                data-bs-target="#addPaymentModal">
                <i class="fas fa-plus me-2"></i>Add New Payment
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
<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPaymentModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Add New Payment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="addPaymentModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading payment form...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="submitPayment">Save Payment</button>
            </div>
        </div>
    </div>
</div>
<!-- Edit Payment Modal -->
<div class="modal fade" id="editPaymentModal" tabindex="-1" aria-labelledby="editPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPaymentModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Payment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="editPaymentModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading payment details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="submitEditPayment">Update Payment</button>
            </div>
        </div>
    </div>
</div>
<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deletePaymentModal" tabindex="-1" aria-labelledby="deletePaymentModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deletePaymentModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="delete-modal-icon mb-3 fs-1 text-danger">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <h5 class="text-dark">Are you sure you want to delete this payment?</h5>
                <p class="text-muted">This action cannot be undone. All data associated with this payment will be
                    permanently removed.</p>
                <div class="payment-info bg-white border p-3 rounded mt-3 mb-3 shadow-sm">
                    <p class="mb-1"><strong>Payment ID:</strong> <span id="deletePaymentId"></span></p>
                    <p class="mb-1"><strong>Client:</strong> <span id="deletePaymentClient"></span></p>
                    <p class="mb-0"><strong>Amount:</strong> <span id="deletePaymentAmount"></span></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash-alt me-2"></i>Delete Payment
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Payments Table -->
<div class="card shadow-sm">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0 d-flex align-items-center">
                <i class="fas fa-table me-2 text-primary"></i>
                Payments Database
            </h5>
            <div class="d-flex">
                <!-- Client Filter Dropdown -->
                <div class="dropdown me-2">
                    <button class="btn btn-light dropdown-toggle" type="button" id="clientFilterDropdown"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-filter me-1"></i> Client
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="clientFilterDropdown" id="clientFilterMenu">
                        <li><a class="dropdown-item client-filter" href="#" data-client="">All Clients</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <!-- Client options will be populated dynamically -->
                    </ul>
                </div>
                <small class="text-muted" id="datatable-info">
                    <i class="fas fa-info-circle me-1"></i>
                    Loading payments...
                </small>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="payments-table" class="table table-hover mb-0" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th><i class="fas fa-hashtag me-1"></i>ID</th>
                        <th><i class="fas fa-user me-1"></i>Client</th>
                        <th><i class="fas fa-dollar-sign me-1"></i>Amount</th>
                        <th><i class="fas fa-credit-card me-1"></i>Method</th>
                        <th><i class="fas fa-calendar me-1"></i>Payment Date</th>
                        <th><i class="fas fa-sticky-note me-1"></i>Note</th>
                        <th><i class="fas fa-clock me-1"></i>Created At</th>
                        <th width="150" class="text-center"><i class="fas fa-cogs me-1"></i>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr>
                        <td colspan="7" class="total-display">
                            <strong>Total Amount (Current Page): $<span id="totalAmount">0.00</span></strong>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script>
    // Global variables
    let paymentsTable;
    let currentPaymentId = null;
    let addPaymentModal, editPaymentModal, deletePaymentModal;

    // Define the submission function in global scope
    window.submitEditPaymentForm = function () {
        const formData = $('#editPaymentForm').serialize();
        const paymentId = $('input[name="payment_id"]').val();
        const $button = $('#submitEditPayment');
        const originalText = $button.html();
        $button.html('<i class="fas fa-spinner fa-spin me-2"></i>Updating...').prop('disabled', true);
        $.ajax({
            url: '<?= BASE_URL ?>/payments-manager/update?id=' + paymentId,
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.error) {
                    showToast(response.error, 'danger');
                } else {
                    showToast('Payment updated successfully!', 'success');
                    if (typeof editPaymentModal !== 'undefined' && editPaymentModal.hide) {
                        editPaymentModal.hide();
                    } else {
                        console.error('editPaymentModal not defined or missing hide method');
                        $('#editPaymentModal').modal('hide'); // Fallback
                    }
                    if (typeof paymentsTable !== 'undefined' && paymentsTable.ajax.reload) {
                        paymentsTable.ajax.reload();
                    } else {
                        console.error('paymentsTable not defined or missing ajax.reload method');
                    }
                }
            },
            error: function (xhr) {
                let errorMessage = 'Error updating payment';
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMessage = response.error || errorMessage;
                } catch (e) {
                    errorMessage = xhr.responseText || errorMessage;
                }
                showToast(errorMessage, 'danger');
            },
            complete: function () {
                $button.html(originalText).prop('disabled', false);
            }
        });
    };

    $(document).ready(function () {
        // Initialize modals
        addPaymentModal = new bootstrap.Modal(document.getElementById('addPaymentModal'));
        editPaymentModal = new bootstrap.Modal(document.getElementById('editPaymentModal'));
        deletePaymentModal = new bootstrap.Modal(document.getElementById('deletePaymentModal'));
        // Initialize DataTable
        initializeDataTable();
        // Load client filter options
        loadClientFilterOptions();
        // Bind events
        bindEvents();
    });

    function initializeDataTable() {
        paymentsTable = $('#payments-table').DataTable({
            serverSide: true,
            ajax: {
                url: '<?= BASE_URL ?>/payments-manager/datatable',
                type: 'POST',
                data: function (d) {
                    const selectedClient = $('.client-filter.active').data('client') || '';
                    d.client_filter = selectedClient;
                }
            },
            columns: [
                {
                    data: 'id',
                    className: 'fw-bold text-primary'
                },
                {
                    data: 'client_name',
                    render: function (data, type, row) {
                        return `<div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                            <i class="fas fa-user text-primary"></i>
                        </div>
                        <div>
                            <span class="fw-semibold">${data || 'N/A'}</span>
                            <br><small class="text-muted">ID: ${row.client_id}</small>
                        </div>
                    </div>`;
                    }
                },
                {
                    data: 'amount',
                    render: function (data, type, row) {
                        const amount = parseFloat(data);
                        const colorClass = amount >= 0 ? 'text-success' : 'text-danger';
                        const sign = amount >= 0 ? '+' : '';
                        return `<span class="payment-amount ${colorClass}">
                        ${sign}$${Math.abs(amount).toFixed(2)}
                    </span>`;
                    }
                },
                {
                    data: 'method',
                    render: function (data, type, row) {
                        return `<span class="badge bg-light text-dark border">
                        <i class="fas fa-credit-card me-1"></i>${data}
                    </span>`;
                    }
                },
                {
                    data: 'payment_date',
                    render: function (data, type, row) {
                        return `<span class="badge bg-light text-dark border">
                        <i class="fas fa-calendar me-1"></i>${data}
                    </span>`;
                    }
                },
                {
                    data: 'note',
                    render: function (data, type, row) {
                        if (!data) return '<span class="text-muted">-</span>';
                        const truncated = data.length > 50 ? data.substring(0, 50) + '...' : data;
                        return `<span title="${data}">${truncated}</span>`;
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
                            onclick="showEditModal(${data})" title="Edit Payment">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger"
                            onclick="showDeleteModal(${data}, '${row.client_name || 'N/A'}', '${row.amount}')" title="Delete Payment">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>`;
                    }
                }
            ],
            order: [[0, 'desc']],
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            dom: '<"top"Bfl>rt<"bottom"ip>',
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
                emptyTable: '<div class="text-center py-4"><i class="fas fa-inbox fa-3x text-muted mb-3"></i><br><h5 class="text-muted">No payments found</h5><p class="text-muted">Try adjusting your search criteria</p></div>',
                zeroRecords: '<div class="text-center py-4"><i class="fas fa-search fa-3x text-muted mb-3"></i><br><h5 class="text-muted">No matching records found</h5><p class="text-muted">Try different search terms</p></div>'
            },
            drawCallback: function () {
                updateTableInfo();
                calculateVisibleTotal();
            }
        });
    }

    function loadAddPaymentForm() {
        $('#addPaymentModalBody').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading payment form...</p>
        </div>
        `);
        // Corrected AJAX call to the controller route
        $.get('<?= BASE_URL ?>/payments-manager/create', function (data) {
            $('#addPaymentModalBody').html(data);
            // Set today's date as default for the new form
            const today = new Date().toISOString().split('T')[0];
            $('#addPaymentModalBody').find('#payment_date').val(today);
        }).fail(function () {
            $('#addPaymentModalBody').html(`
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Failed to load form. Please try again.
            </div>
            `);
        });
    }

    function bindEvents() {
        // Form submission for add (delegated)
        $(document).on('click', '#submitPayment', function (e) {
            e.preventDefault();
            $('#addPaymentForm').trigger('submit');
        });

        // Edit Payment Form Submit Trigger (delegated)
        $(document).on('click', '#submitEditPayment', function (e) {
            e.preventDefault();
            // Trigger the form's submit event, which will be caught by the delegated handler below
            $('#editPaymentForm').trigger('submit');
        });

        // Delegated form submission handler for editPaymentForm
        $(document).on('submit', '#editPaymentForm', function (e) {
            e.preventDefault();
            console.log('Form submission intercepted by delegated handler for editPaymentForm');
            window.submitEditPaymentForm();
        });

        // Delegated change handler for edit_method (for dynamically loaded forms)
        $(document).on('change', '#edit_method', function () {
            console.log('Payment method changed to:', $(this).val());
            if ($(this).val() === 'other') {
                $('#edit_customMethod').show().attr('required', true);
                console.log('Showing custom method field');
            } else {
                $('#edit_customMethod').hide().removeAttr('required');
                console.log('Hiding custom method field');
            }
        });

        // Client filter handler
        $(document).on('click', '.client-filter', function (e) {
            e.preventDefault();
            const client = $(this).data('client');
            const clientText = $(this).text();
            // Update the dropdown button text
            $('#clientFilterDropdown').html(
                `<i class="fas fa-filter me-1"></i> ${client ? clientText : 'All Clients'}`
            );
            // Remove active class from all filters
            $('.client-filter').removeClass('active');
            // Add active class to clicked filter
            $(this).addClass('active');
            // Reload the table with the new filter
            paymentsTable.ajax.reload(function () {
                showToast(`Filtered by: ${client ? clientText : 'All Clients'}`, 'info');
            });
        });

        // Confirm delete button handler
        $('#confirmDeleteBtn').on('click', function () {
            deletePayment(currentPaymentId);
            deletePaymentModal.hide();
        });

        // Reset form when add modal is hidden
        $('#addPaymentModal').on('hidden.bs.modal', function () {
            // Reset the form content to the loading spinner for next time
            $('#addPaymentModalBody').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading payment form...</p>
            </div>
            `);
        });
        $('#addPaymentModal').on('show.bs.modal', loadAddPaymentForm);
    }

    function loadClientFilterOptions() {
        $.get('<?= BASE_URL ?>/payments-manager/get-clients', function (data) {
            const menu = $('#clientFilterMenu');
            // Keep the "All Clients" option and divider
            const staticItems = menu.find('li:first, li:nth-child(2)');
            menu.empty().append(staticItems);
            if (data && data.length > 0) {
                data.forEach(client => {
                    menu.append(`
                    <li><a class="dropdown-item client-filter" href="#" data-client="${client.client_id}">
                        ${client.client_name} (ID: ${client.client_id})
                    </a></li>
                `);
                });
            }
        }).fail(function () {
            console.error('Failed to load client filter options');
        });
    }

    function showEditModal(id) {
        currentPaymentId = id;
        $('#editPaymentModalBody').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading payment details...</p>
        </div>
        `);
        editPaymentModal.show();
        // Corrected AJAX call to the controller route
        $.get(`<?= BASE_URL ?>/payments-manager/edit?id=${id}`, function (data) {
            $('#editPaymentModalBody').html(data);
            // After content is loaded, trigger change to set custom method visibility
            $('#editPaymentModalBody').find('#edit_method').trigger('change');
            // Also log initial values after content is loaded
            console.log('Initial form values after load:', {
                payment_id: $('#editPaymentModalBody').find('input[name="payment_id"]').val(),
                client_id: $('#editPaymentModalBody').find('input[name="client_id"]').val(),
                amount: $('#editPaymentModalBody').find('#edit_amount').val(),
                method: $('#editPaymentModalBody').find('#edit_method').val(),
                payment_date: $('#editPaymentModalBody').find('#edit_payment_date').val(),
                note: $('#editPaymentModalBody').find('#edit_note').val()
            });
        }).fail(function () {
            $('#editPaymentModalBody').html(`
            <div class="alert alert-danger">
                Failed to load payment details. Please try again.
            </div>
            `);
        });
    }

    function showDeleteModal(id, client, amount) {
        currentPaymentId = id;
        $('#deletePaymentId').text(id);
        $('#deletePaymentClient').text(client);
        $('#deletePaymentAmount').text('$' + parseFloat(amount).toFixed(2));
        deletePaymentModal.show();
    }

    function deletePayment(id) {
        $.ajax({
            url: `<?= BASE_URL ?>/payments-manager/delete`,
            type: 'POST',
            data: { id: id },
            beforeSend: function () {
                showToast('Deleting payment...', 'info');
            },
            success: function (response) {
                paymentsTable.ajax.reload();
                showToast('Payment deleted successfully!', 'success');
            },
            error: function (xhr) {
                const error = xhr.responseJSON ? xhr.responseJSON.error : 'Error deleting payment';
                showToast(error, 'danger');
            }
        });
    }

    function calculateVisibleTotal() {
        let total = 0;
        // Get visible rows data
        paymentsTable.rows({ page: 'current' }).data().each(function (row) {
            total += parseFloat(row.amount) || 0;
        });
        $('#totalAmount').text(total.toFixed(2));
    }

    function updateTableInfo() {
        const info = paymentsTable.page.info();
        $('#datatable-info').html(
            `<i class="fas fa-info-circle me-1"></i>Showing ${info.start + 1} to ${info.end} of ${info.recordsTotal} payments`
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
</script>
<?php include __DIR__ . '/../layouts/footer.php'; ?>