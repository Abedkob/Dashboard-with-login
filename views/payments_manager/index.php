<?php include __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../public/config.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Payments Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <style>
        :root {
            --primary-color: #6366f1;
            --danger-color: #ef4444;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --dark-color: #1f2937;
            --light-bg: #f8fafc;
            --border-color: #e2e8f0;
        }

        .dt-buttons {
            margin-bottom: 10px;
        }

        .total-display {
            background: linear-gradient(135deg, var(--light-bg) 0%, #e2e8f0 100%);
            padding: 12px 16px;
            border-top: 3px solid var(--primary-color);
            font-weight: 600;
            text-align: right;
            color: var(--dark-color);
        }

        .modal-content {
            border: none;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .modal-header {
            border-bottom: 1px solid var(--border-color);
            padding: 24px 24px 16px;
        }

        .modal-body {
            padding: 24px;
        }

        .modal-footer {
            border-top: 1px solid var(--border-color);
            padding: 16px 24px 24px;
        }

        .btn-modern {
            border-radius: 8px;
            font-weight: 500;
            padding: 8px 16px;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-danger-modern {
            background: linear-gradient(135deg, var(--danger-color) 0%, #dc2626 100%);
            color: white;
            box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.3);
        }

        .btn-danger-modern:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 15px -3px rgba(239, 68, 68, 0.4);
            color: white;
        }

        .btn-secondary-modern {
            background: #6b7280;
            color: white;
            border: 1px solid #6b7280;
        }

        .btn-secondary-modern:hover {
            background: #4b5563;
            border-color: #4b5563;
            color: white;
            transform: translateY(-1px);
        }

        .delete-warning-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .delete-warning-icon i {
            font-size: 28px;
            color: var(--danger-color);
        }

        .delete-modal-title {
            color: var(--dark-color);
            font-weight: 600;
            font-size: 1.25rem;
            text-align: center;
        }

        .delete-modal-text {
            color: #6b7280;
            text-align: center;
            margin-bottom: 0;
            line-height: 1.6;
        }

        .payment-details-card {
            background: var(--light-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 16px;
            margin: 16px 0;
        }

        .payment-detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .payment-detail-row:last-child {
            margin-bottom: 0;
        }

        .payment-detail-label {
            font-weight: 500;
            color: var(--dark-color);
        }

        .payment-detail-value {
            color: #6b7280;
            font-weight: 400;
        }

        .table .btn {
            border-radius: 6px;
            font-size: 0.875rem;
            padding: 4px 12px;
        }

        .btn-outline-danger-modern {
            color: var(--danger-color);
            border: 1px solid var(--danger-color);
            background: transparent;
        }

        .btn-outline-danger-modern:hover {
            background: var(--danger-color);
            border-color: var(--danger-color);
            color: white;
            transform: translateY(-1px);
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h1 class="mb-4">Payments Manager</h1>

        <!-- New Payment Modal -->
        <div class="modal fade" id="newPaymentModal" tabindex="-1" aria-labelledby="newPaymentModalLabel" role="dialog"
            aria-modal="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="newPaymentModalLabel">Add New Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="paymentForm">
                            <div class="row mb-3">

                                <div class="col-md-6">
                                    <label for="availableClients" class="form-label">Or select from available
                                        clients</label>
                                    <select class="form-select" id="availableClients">
                                        <option value="">Select a client</option>
                                        <!-- Clients will be loaded dynamically -->
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="amount" class="form-label">Amount</label>
                                    <input type="number" step="0.01" class="form-control" id="amount" name="amount"
                                        required>
                                    <div class="form-text">Negative values for refunds/credits</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="method" class="form-label">Payment Method</label>
                                    <select class="form-select" id="method" name="method" required>
                                        <option value="">Select method</option>
                                        <option value="cash">Cash</option>
                                        <option value="bank transfer">Bank Transfer</option>
                                        <option value="credit card">Credit Card</option>
                                        <option value="paypal">PayPal</option>
                                        <option value="omt">OMT</option>
                                        <option value="wish">Wish</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <input type="text" class="form-control mt-2" id="customMethod" name="customMethod"
                                        placeholder="Enter custom method" style="display: none;">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="payment_date" class="form-label">Payment Date</label>
                                    <input type="date" class="form-control" id="payment_date" name="payment_date"
                                        required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="note" class="form-label">Note</label>
                                <textarea class="form-control" id="note" name="note" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="submitPayment">Save Payment</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Payment Modal -->
        <div class="modal fade" id="editPaymentModal" tabindex="-1" aria-labelledby="editPaymentModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editPaymentModalLabel">Edit Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editPaymentForm">
                            <input type="hidden" id="edit_payment_id" name="id">
                            <div class="row mb-3" style="display: none;">
                                <div class="col-md-6">
                                    <label for="edit_client_id" class="form-label">Client ID</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="edit_client_id" name="client_id"
                                            required>
                                        <button class="btn btn-outline-secondary" type="button"
                                            id="edit_validateClientBtn">Validate</button>
                                    </div>
                                    <div id="edit_clientInfo" class="mt-2 text-success" style="display: none;">
                                        <span id="edit_clientNameDisplay"></span>
                                    </div>
                                    <div id="edit_clientError" class="mt-2 text-danger" style="display: none;">
                                        Client ID not found in projects list
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="edit_amount" class="form-label">Amount</label>
                                    <input type="number" step="0.01" class="form-control" id="edit_amount" name="amount"
                                        required>
                                </div>
                                <div class="col-md-6">
                                    <label for="edit_method" class="form-label">Payment Method</label>
                                    <select class="form-select" id="edit_method" name="method" required>
                                        <option value="">Select method</option>
                                        <option value="cash">Cash</option>
                                        <option value="bank transfer">Bank Transfer</option>
                                        <option value="credit card">Credit Card</option>
                                        <option value="paypal">PayPal</option>
                                        <option value="omt">OMT</option>
                                        <option value="wish">Wish</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <input type="text" class="form-control mt-2" id="edit_customMethod"
                                        name="customMethod" placeholder="Enter custom method" style="display: none;">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="edit_payment_date" class="form-label">Payment Date</label>
                                    <input type="date" class="form-control" id="edit_payment_date" name="payment_date"
                                        required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="edit_note" class="form-label">Note</label>
                                <textarea class="form-control" id="edit_note" name="note" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="updatePayment">Update Payment</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Payment Modal -->
        <div class="modal fade" id="deletePaymentModal" tabindex="-1" aria-labelledby="deletePaymentModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="delete-warning-icon">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                        <h4 class="delete-modal-title mb-3">Delete Payment</h4>
                        <p class="delete-modal-text mb-4">
                            Are you sure you want to delete this payment? This action cannot be undone.
                        </p>

                        <div class="payment-details-card" id="deletePaymentDetails">
                            <!-- Payment details will be populated here -->
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 justify-content-center gap-3">
                        <button type="button" class="btn btn-secondary-modern btn-modern" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-danger-modern btn-modern" id="confirmDeletePayment">
                            <i class="bi bi-trash3 me-2"></i>Delete Payment
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newPaymentModal">
                Add New Payment
            </button>
        </div>

        <?php if (empty($payments)): ?>
            <div class="alert alert-info">No payments found.</div>
        <?php else: ?>
            <table id="paymentsTable" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Payment Date</th>
                        <th>Note</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?= htmlspecialchars($payment['id']) ?></td>
                            <td>
                                <?= htmlspecialchars($payment['Client'] ?? 'N/A') ?>
                                <span style="display:none"><?= htmlspecialchars($payment['client_id']) ?></span>
                            </td>
                            <td data-order="<?= $payment['amount'] ?>">
                                <?= htmlspecialchars(number_format($payment['amount'], 2)) ?>
                            </td>
                            <td><?= htmlspecialchars($payment['method']) ?></td>
                            <td><?= htmlspecialchars($payment['payment_date']) ?></td>
                            <td><?= nl2br(htmlspecialchars($payment['note'])) ?></td>
                            <td><?= htmlspecialchars($payment['created_at']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary edit-payment me-1" data-id="<?= $payment['id'] ?>">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-outline-danger-modern delete-payment"
                                    data-id="<?= $payment['id'] ?>"
                                    data-client="<?= htmlspecialchars($payment['Client'] ?? 'N/A') ?>"
                                    data-amount="<?= htmlspecialchars(number_format($payment['amount'], 2)) ?>"
                                    data-method="<?= htmlspecialchars($payment['method']) ?>"
                                    data-date="<?= htmlspecialchars($payment['payment_date']) ?>">
                                    <i class="bi bi-trash3"></i> Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="7" class="total-display">Total Amount: <span id="totalAmount">0.00</span></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            <div class="mb-4">
                <div id="exportButtons"></div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <script>
        let table;

        $(document).ready(function () {
            table = $('#paymentsTable').DataTable({
                pageLength: 10,
                order: [[0, 'desc']],
                dom: '<"top"Bf>rt<"bottom"lip>',
                buttons: [
                    {
                        extend: 'copy',
                        text: 'Copy to clipboard',
                        className: 'btn btn-secondary btn-sm'
                    },
                    {
                        extend: 'csv',
                        text: 'Export to CSV',
                        className: 'btn btn-secondary btn-sm'
                    },
                    {
                        extend: 'excel',
                        text: 'Export to Excel',
                        className: 'btn btn-secondary btn-sm'
                    },
                    {
                        extend: 'pdf',
                        text: 'Export to PDF',
                        className: 'btn btn-secondary btn-sm'
                    },
                    {
                        extend: 'print',
                        text: 'Print',
                        className: 'btn btn-secondary btn-sm'
                    }

                ],
                initComplete: function () {
                    this.api().buttons().container().appendTo('#exportButtons');
                },
                footerCallback: function (row, data, start, end, display) {
                    var api = this.api();
                    var intVal = function (i) {
                        return typeof i === 'string' ?
                            parseFloat(i.replace(/[^\d.-]/g, '')) :
                            typeof i === 'number' ?
                                i : 0;
                    };
                    var total = api
                        .column(2, { search: 'applied' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    $('#totalAmount').text(total.toFixed(3));
                },
                drawCallback: function () {
                    var api = this.api();
                    var intVal = function (i) {
                        return typeof i === 'string' ?
                            parseFloat(i.replace(/[^\d.-]/g, '')) :
                            typeof i === 'number' ?
                                i : 0;
                    };
                    var total = api
                        .column(2, { search: 'applied' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    $('#totalAmount').text(total.toFixed(2));
                }
            });

            calculateTotal();

            function calculateTotal() {
                var total = 0;
                table.column(2, { search: 'applied' }).data().each(function (value) {
                    total += parseFloat(value) || 0;
                });
                $('#totalAmount').text(total.toFixed(2));
            }

            table.on('search.dt', function () {
                calculateTotal();
            });
        });
    </script>

    <script>

        $(document).ready(function () {
            // Delete Payment Modal Handling
            let paymentToDelete = null;

            $(document).on('click', '.delete-payment', function () {
                paymentToDelete = $(this).data('id');
                const client = $(this).data('client');
                const amount = $(this).data('amount');
                const method = $(this).data('method');
                const date = $(this).data('date');

                // Populate payment details in modal
                const detailsHtml = `
                    <div class="payment-detail-row">
                        <span class="payment-detail-label">Payment ID:</span>
                        <span class="payment-detail-value">#${paymentToDelete}</span>
                    </div>
                    <div class="payment-detail-row">
                        <span class="payment-detail-label">Client:</span>
                        <span class="payment-detail-value">${client}</span>
                    </div>
                    <div class="payment-detail-row">
                        <span class="payment-detail-label">Amount:</span>
                        <span class="payment-detail-value">$${amount}</span>
                    </div>
                    <div class="payment-detail-row">
                        <span class="payment-detail-label">Method:</span>
                        <span class="payment-detail-value">${method}</span>
                    </div>
                    <div class="payment-detail-row">
                        <span class="payment-detail-label">Date:</span>
                        <span class="payment-detail-value">${date}</span>
                    </div>
                `;

                $('#deletePaymentDetails').html(detailsHtml);
                $('#deletePaymentModal').modal('show');
            });

            // Confirm delete payment
            $('#confirmDeletePayment').click(function () {
                if (!paymentToDelete) return;

                const $button = $(this);
                const originalText = $button.html();

                // Show loading state
                $button.html('<i class="bi bi-hourglass-split me-2"></i>Deleting...').prop('disabled', true);

                $.ajax({
                    url: '<?= url('payments-manager/delete') ?>',
                    method: 'POST',
                    data: { id: paymentToDelete },
                    dataType: 'json',
                    success: function (response) {
                        if (response.error) {
                            alert(response.error);
                        } else {
                            // Show success message
                            const toast = `
                                <div class="toast-container position-fixed top-0 end-0 p-3">
                                    <div class="toast show" role="alert">
                                        <div class="toast-header bg-success text-white">
                                            <i class="bi bi-check-circle-fill me-2"></i>
                                            <strong class="me-auto">Success</strong>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                                        </div>
                                        <div class="toast-body">
                                            Payment deleted successfully!
                                        </div>
                                    </div>
                                </div>
                            `;
                            $('body').append(toast);

                            // Auto-hide toast after 3 seconds
                            setTimeout(() => {
                                $('.toast').toast('hide');
                            }, 3000);

                            $('#deletePaymentModal').modal('hide');

                            // Reload only the DataTable
                            table.ajax.reload(null, false);
                        }
                    },
                    error: function (xhr) {
                        try {
                            const error = xhr.responseJSON ? xhr.responseJSON.error : xhr.responseText;
                            alert(error || 'An error occurred while deleting the payment');
                        } catch (e) {
                            alert('An error occurred while deleting the payment');
                        }
                        console.error("Delete error:", xhr.responseText);
                    },
                    complete: function () {
                        // Reset button state
                        $button.html(originalText).prop('disabled', false);
                        paymentToDelete = null;
                    }
                });
            });

            // Reset modal when closed
            $('#deletePaymentModal').on('hidden.bs.modal', function () {
                paymentToDelete = null;
                $('#confirmDeletePayment').html('<i class="bi bi-trash3 me-2"></i>Delete Payment').prop('disabled', false);
            });
        });
    </script>
    <script>
        $(document).ready(function () {

            // Load available clients when modal is shown
            $('#newPaymentModal').on('show.bs.modal', function () {
                loadAvailableClients();
            });

            // Toggle custom method input
            $('#method').change(function () {
                if ($(this).val() === 'other') {
                    $('#customMethod').show().attr('required', true);
                } else {
                    $('#customMethod').hide().removeAttr('required');
                }
            });

            // Form submission
            $('#submitPayment').click(function () {
                const formData = {
                    client_id: $('#availableClients').val(), // Only dropdown selection
                    amount: $('#amount').val(),
                    method: $('#method').val() === 'other' ? $('#customMethod').val() : $('#method').val(),
                    payment_date: $('#payment_date').val(),
                    note: $('#note').val()
                };

                if (!formData.client_id) {
                    alert('Please select a client from the dropdown');
                    return;
                }

                const $button = $(this);
                const originalText = $button.html();
                $button.html('<i class="bi bi-hourglass-split me-2"></i>Saving...').prop('disabled', true);

                $.ajax({
                    url: '<?= url('payments-manager/create') ?>',
                    method: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function (response) {
                        if (response.error) {
                            alert(response.error);
                        } else {
                            // Show success toast
                            const toast = `
                        <div class="toast-container position-fixed top-0 end-0 p-3">
                            <div class="toast show" role="alert">
                                <div class="toast-header bg-success text-white">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    <strong class="me-auto">Success</strong>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                                </div>
                                <div class="toast-body">
                                    Payment created successfully!
                                </div>
                            </div>
                        </div>
                    `;
                            $('body').append(toast);
                            setTimeout(() => $('.toast').toast('hide'), 3000);

                            $('#newPaymentModal').modal('hide');
                            table.ajax.reload(null, false);
                        }
                    },
                    error: function (xhr) {
                        try {
                            const error = xhr.responseJSON ? xhr.responseJSON.error : xhr.responseText;
                            alert(error || 'An error occurred while creating the payment');
                        } catch (e) {
                            alert('An error occurred while creating the payment');
                        }
                    },
                    complete: function () {
                        $button.html(originalText).prop('disabled', false);
                    }
                });
            });

            // Load available clients into dropdown
            function loadAvailableClients() {
                $.ajax({
                    url: '<?= url('payments-manager/available-clients') ?>',
                    method: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        const select = $('#availableClients');
                        select.empty().append('<option value="">Select a client</option>');

                        if (Array.isArray(data)) {
                            // Store clients data for filtering
                            window.availableClients = data;

                            data.forEach(client => {
                                select.append(`<option value="${client.id}">${client.id} - ${client.name}</option>`);
                            });

                            // Initialize select2 for searchable dropdown
                            select.select2({
                                placeholder: "Search for a client...",
                                allowClear: true,
                                width: '100%',
                                dropdownParent: $('#newPaymentModal')
                            });

                            // Add search box above dropdown
                            $('.select2-container').prepend('<div class="client-search-box mb-2 p-2 border-bottom"><input type="text" class="form-control form-control-sm" id="clientSearchInput" placeholder="Type to filter clients..."></div>');

                            // Filter functionality
                            $('#clientSearchInput').on('input', function () {
                                const searchTerm = $(this).val().toLowerCase();
                                if (searchTerm.length > 0) {
                                    const filteredClients = window.availableClients.filter(client =>
                                        client.name.toLowerCase().includes(searchTerm) ||
                                        client.id.toString().includes(searchTerm)
                                    );

                                    select.empty().append('<option value="">Select a client</option>');
                                    filteredClients.forEach(client => {
                                        select.append(`<option value="${client.id}">${client.id} - ${client.name}</option>`);
                                    });
                                } else {
                                    // Reset to all clients when search is cleared
                                    select.empty().append('<option value="">Select a client</option>');
                                    window.availableClients.forEach(client => {
                                        select.append(`<option value="${client.id}">${client.id} - ${client.name}</option>`);
                                    });
                                }
                            });
                        } else {
                            console.error('Expected array but got:', data);
                        }
                    },
                    error: function (xhr) {
                        console.error('Error loading available clients:', xhr.responseText);
                    }
                });
            }


        });
    </script>
    <script>
        $(document).ready(function () {
            // Initialize Select2 when modal is shown
            $('#newPaymentModal').on('shown.bs.modal', function () {
                $('#availableClients').select2({
                    placeholder: "Search for a client...",
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('#newPaymentModal'),
                    ajax: {
                        url: '<?= url('payments-manager/available-clients') ?>',
                        dataType: 'json',
                        delay: 250,
                        processResults: function (data) {
                            return {
                                results: $.map(data, function (item) {
                                    return {
                                        id: item.id,
                                        text: item.id + ' - ' + item.name
                                    }
                                })
                            };
                        },
                        cache: true
                    }
                });
            });

            // Destroy Select2 when modal is hidden
            $('#newPaymentModal').on('hidden.bs.modal', function () {
                if ($('#availableClients').hasClass('select2-hidden-accessible')) {
                    $('#availableClients').select2('destroy');
                }
            });
    </script>

    <script>
            $(document).ready(function () {
                $(document).on('click', '.edit-payment', function () {
                    const paymentId = $(this).data('id');
                    console.log("Edit button clicked for payment ID:", paymentId);

                    $('#edit_clientInfo').hide();
                    $('#edit_clientError').hide();

                    $.getJSON('<?= url('payments-manager/get-payment') ?>', { id: paymentId })
                        .done(function (response) {
                            if (response && response.data) {
                                const payment = response.data;
                                $('#edit_payment_id').val(payment.id);
                                $('#edit_client_id').val(payment.client_id);
                                $('#edit_amount').val(payment.amount);
                                $('#edit_payment_date').val(payment.payment_date);
                                $('#edit_note').val(payment.note || '');

                                const methodSelect = $('#edit_method');
                                methodSelect.val(payment.method);
                                if (payment.method === 'other') {
                                    $('#edit_customMethod').show().val(payment.method).attr('required', true);
                                } else {
                                    $('#edit_customMethod').hide().removeAttr('required');
                                }

                                validateEditClientId(payment.client_id);
                                loadEditAvailableClients();
                                $('#editPaymentModal').modal('show');
                            } else {
                                console.error("No payment data in response");
                                alert('No payment data found in response');
                            }
                        })
                        .fail(function (xhr, status, error) {
                            console.error("Error loading payment data:", error, xhr.responseText);
                            alert('Error loading payment data: ' + error);
                        });
                });

                // Update payment
                $('#updatePayment').click(function () {
                    const paymentId = $('#edit_payment_id').val();
                    if (!paymentId) {
                        alert('Payment ID is missing');
                        return;
                    }

                    const formData = {
                        id: paymentId,
                        client_id: $('#edit_client_id').val(),
                        amount: $('#edit_amount').val(),
                        method: $('#edit_method').val() === 'other' ? $('#edit_customMethod').val() : $('#edit_method').val(),
                        payment_date: $('#edit_payment_date').val(),
                        note: $('#edit_note').val()
                    };

                    console.log("Submitting update for payment ID:", paymentId, "with data:", formData);

                    $.ajax({
                        url: '<?= url('payments-manager/update?id=') ?>' + paymentId,
                        method: 'POST',
                        data: formData,
                        dataType: 'json',
                        success: function (response) {
                            if (response.error) {
                                alert(response.error);
                            } else {
                                alert('Payment updated successfully!');
                                $('#editPaymentModal').modal('hide');
                                table.ajax.reload(null, false);
                            }
                        },
                        error: function (xhr) {
                            try {
                                const error = xhr.responseJSON ? xhr.responseJSON.error : xhr.responseText;
                                alert(error || 'An error occurred');
                            } catch (e) {
                                alert('An error occurred while processing the request');
                            }
                            console.error("Update error:", xhr.responseText);
                        }
                    });
                });

                // Client validation for edit form
                function validateEditClientId(clientId = null) {
                    const idToValidate = clientId || $('#edit_client_id').val();
                    if (!idToValidate) return;

                    $.getJSON('<?= url('payments-manager/validate-client') ?>', { client_id: idToValidate })
                        .done(function (data) {
                            if (data.valid) {
                                $('#edit_clientError').hide();
                                $('#edit_clientInfo').show();
                                $('#edit_clientNameDisplay').text(`Valid client: ${data.client_name}`);
                            } else {
                                $('#edit_clientInfo').hide();
                                $('#edit_clientError').show();
                            }
                        })
                        .fail(function () {
                            $('#edit_clientError').text('Error validating client').show();
                            $('#edit_clientInfo').hide();
                        });
                }

                // Load available clients for edit form
                function loadEditAvailableClients() {
                    $.getJSON('<?= url('payments-manager/available-clients') ?>')
                        .done(function (data) {
                            const select = $('#edit_availableClients');
                            select.empty().append('<option value="">Select a client</option>');
                            if (Array.isArray(data)) {
                                data.forEach(client => {
                                    select.append(`<option value="${client.id}">${client.id} - ${client.name}</option>`);
                                });
                            }
                        })
                        .fail(function (error) {
                            console.error("Error loading available clients:", error);
                        });
                }

                // Toggle custom method input for edit form
                $('#edit_method').change(function () {
                    if ($(this).val() === 'other') {
                        $('#edit_customMethod').show().attr('required', true);
                    } else {
                        $('#edit_customMethod').hide().removeAttr('required');
                    }
                });

                // Handle selection from edit available clients dropdown
                $('#edit_availableClients').change(function () {
                    const clientId = $(this).val();
                    if (clientId) {
                        $('#edit_availableClients').val(clientId);
                        validateEditClientId(clientId);
                    }
                });
            });
    </script>
</body>

</html>
<?php include __DIR__ . '/../layouts/footer.php'; ?>