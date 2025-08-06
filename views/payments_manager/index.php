<?php include __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../public/config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Payments Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" />
    <style>
        .dt-buttons {
            margin-bottom: 10px;
        }

        .total-display {
            background-color: #f8f9fa;
            padding: 8px;
            border-top: 2px solid #dee2e6;
            font-weight: bold;
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h1 class="mb-4">Payments Manager</h1>

        <!-- New Payment Modal -->
        <div class="modal fade" id="newPaymentModal" tabindex="-1" aria-labelledby="newPaymentModalLabel"
            aria-hidden="true">
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
                                    <label for="client_id" class="form-label">Client ID</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="client_id" name="client_id"
                                            required>
                                        <button class="btn btn-outline-secondary" type="button"
                                            id="validateClientBtn">Validate</button>
                                    </div>
                                    <div id="clientInfo" class="mt-2 text-success" style="display: none;">
                                        <span id="clientNameDisplay"></span>
                                    </div>
                                    <div id="clientError" class="mt-2 text-danger" style="display: none;">
                                        Client ID not found in projects list
                                    </div>
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

        <!-- Edit Payment Modal (now properly placed outside the new payment modal) -->
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
                            <div class="row mb-3">
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
                                <div class="col-md-6">
                                    <label for="edit_availableClients" class="form-label">Or select from available
                                        clients</label>
                                    <select class="form-select" id="edit_availableClients">
                                        <option value="">Select a client</option>
                                    </select>
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
                                <button class="btn btn-sm btn-primary edit-payment"
                                    data-id="<?= $payment['id'] ?>">Edit</button>
                                <a href="<?= url('payments-manager/delete?id=' . $payment['id']) ?>"
                                    class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
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
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script>
        $(document).ready(function () {
            var table = $('#paymentsTable').DataTable({
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
                    // Move buttons to the container below the table
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

                    // Calculate total over filtered data
                    var total = api
                        .column(2, { search: 'applied' }) // Amount is now column 2 (index starts from 0)
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    // Update footer with total
                    $('#totalAmount').text(total.toFixed(2));
                },
                drawCallback: function () {
                    // Update total on each draw (when searching/paging)
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

            // Initial total calculation
            calculateTotal();

            function calculateTotal() {
                var total = 0;
                table.column(2, { search: 'applied' }).data().each(function (value) {
                    total += parseFloat(value) || 0;
                });
                $('#totalAmount').text(total.toFixed(2));
            }

            // Recalculate total when search is performed
            table.on('search.dt', function () {
                calculateTotal();
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            // Load available clients on modal show
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

            // Client ID validation
            $('#validateClientBtn').click(function () {
                validateClientId();
            });

            // Client search functionality
            $('#clientSearchBtn').click(function (e) {
                e.preventDefault();
                const term = $('#client_id').val();
                if (term.length < 2) return;

                $.get('<?= url('payments-manager/search-clients') ?>', { term: term }, function (data) {
                    const results = $('#clientSearchResults');
                    results.empty();

                    if (data.length === 0) {
                        results.append('<li><a class="dropdown-item" href="#">No clients found</a></li>');
                    } else {
                        data.forEach(client => {
                            results.append(`<li><a class="dropdown-item client-choice" href="#" 
                                  data-id="${client.id}">${client.id} - ${client.name}</a></li>`);
                        });
                    }

                    // Show dropdown
                    $(this).dropdown('toggle');
                }.bind(this));
            });

            // Handle client selection from search
            $(document).on('click', '.client-choice', function (e) {
                e.preventDefault();
                const clientId = $(this).data('id');
                $('#client_id').val(clientId);
                validateClientId(clientId);
                $('#clientSearchResults').empty();
            });

            // Handle selection from available clients dropdown
            $('#availableClients').change(function () {
                const clientId = $(this).val();
                if (clientId) {
                    $('#client_id').val(clientId);
                    validateClientId(clientId);
                }
            });
            console.log(paymentId);
            // Form submission
            $('#submitPayment').click(function () {

                const formData = {
                    id: paymentId,
                    client_id: $('#edit_client_id').val(),
                    amount: $('#edit_amount').val(),
                    method: $('#edit_method').val() === 'other' ? $('#edit_customMethod').val() : $('#edit_method').val(),
                    payment_date: $('#edit_payment_date').val(),
                    note: $('#edit_note').val()
                };

                if (!formData.client_id) {
                    alert('Please select or validate a client');
                    return;
                }

                $.ajax({
                    url: '<?= url('payments-manager/create') ?>',
                    method: 'POST',
                    data: formData,
                    success: function (response) {
                        const result = JSON.parse(response);
                        if (result.error) {
                            alert(result.error);
                        } else {
                            alert('Payment created successfully!');
                            $('#newPaymentModal').modal('hide');
                            setTimeout(() => location.reload(), 1500);
                        }
                    },
                    error: function (xhr) {
                        const error = JSON.parse(xhr.responseText);
                        alert(error.error || 'An error occurred');
                    }
                });
            });

            // Helper functions
            function loadAvailableClients() {
                $.get('<?= url('payments-manager/available-clients') ?>', function (response) {
                    const select = $('#availableClients');
                    select.empty().append('<option value="">Select a client</option>');

                    // Parse the response if it's a string
                    const data = typeof response === 'string' ? JSON.parse(response) : response;

                    // Check if data is an array
                    if (Array.isArray(data)) {
                        data.forEach(client => {
                            select.append(`<option value="${client.id}">${client.id} - ${client.name}</option>`);
                        });
                    } else {
                        console.error('Expected array but got:', data);
                    }
                }, 'json'); // Explicitly request JSON
            }

            function validateClientId(clientId = null) {
                const idToValidate = clientId || $('#client_id').val();
                if (!idToValidate) return;

                $.get('<?= url('payments-manager/validate-client') ?>',
                    { client_id: idToValidate },
                    function (data) {
                        if (data.valid) {
                            $('#clientError').hide();
                            $('#clientInfo').show();
                            $('#clientNameDisplay').text(`Valid client: ${data.client_name}`);
                        } else {
                            $('#clientInfo').hide();
                            $('#clientError').show();
                        }
                    },
                    'json' // Ensure we're expecting JSON
                ).fail(function () {
                    $('#clientError').text('Error validating client').show();
                    $('#clientInfo').hide();
                });
            }
        });
    </script>




    <script>
        $(document).ready(function () {
            // Edit Payment Modal Handling
            $(document).on('click', '.edit-payment', function () {
                const paymentId = $(this).data('id');
                console.log("Edit button clicked for payment ID:", paymentId);

                // Clear previous validation messages
                $('#edit_clientInfo').hide();
                $('#edit_clientError').hide();

                // Fetch payment details
                $.getJSON('<?= url('payments-manager/get-payment') ?>', { id: paymentId })
                    .done(function (response) {
                        console.log("Payment data loaded:", response);

                        if (response && response.data) {
                            const payment = response.data;

                            // Populate form fields
                            $('#edit_payment_id').val(payment.id);
                            $('#edit_client_id').val(payment.client_id);
                            $('#edit_amount').val(payment.amount);
                            $('#edit_payment_date').val(payment.payment_date);
                            $('#edit_note').val(payment.note || '');

                            // Set payment method
                            const methodSelect = $('#edit_method');
                            methodSelect.val(payment.method);
                            if (payment.method === 'other') {
                                $('#edit_customMethod').show().val(payment.method).attr('required', true);
                            } else {
                                $('#edit_customMethod').hide().removeAttr('required');
                            }

                            // Validate client
                            validateEditClientId(payment.client_id);

                            // Load available clients for dropdown
                            loadEditAvailableClients();

                            // Show modal
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
                    url: '<?= url('payments-manager/update') ?>',
                    method: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function (response) {
                        if (response.error) {
                            alert(response.error);
                        } else {
                            alert('Payment updated successfully!');
                            $('#editPaymentModal').modal('hide');
                            setTimeout(() => location.reload(), 1500);
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
                    $('#edit_client_id').val(clientId);
                    validateEditClientId(clientId);
                }
            });
        });
    </script>

</body>

</html>

<?php include __DIR__ . '/../layouts/footer.php'; ?>