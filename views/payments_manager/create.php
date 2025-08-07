<?php
// Load available clients for the add form
require_once __DIR__ . '/../../public/config.php';

// Get available clients
$clientsQuery = "SELECT id, name FROM projects_list ORDER BY name ASC";
$clientsStmt = $GLOBALS['pdo']->prepare($clientsQuery);
$clientsStmt->execute();
$availableClients = $clientsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<form id="addPaymentForm">
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="client_id" class="form-label">Client <span class="text-danger">*</span></label>
            <select class="form-select" id="client_id" name="client_id" required>
                <option value="">Select a client...</option>
                <?php foreach ($availableClients as $client): ?>
                    <option value="<?= $client['id'] ?>">
                        <?= htmlspecialchars($client['name']) ?> (ID: <?= $client['id'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">Choose a client from the available list</div>
        </div>
        <div class="col-md-6">
            <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
            <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
            <div class="form-text">Negative values for refunds/credits</div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label for="method" class="form-label">Payment Method <span class="text-danger">*</span></label>
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
        <div class="col-md-6">
            <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="payment_date" name="payment_date" required>
        </div>
    </div>

    <div class="mb-3">
        <label for="note" class="form-label">Note</label>
        <textarea class="form-control" id="note" name="note" rows="3"
            placeholder="Optional notes about this payment..."></textarea>
    </div>
</form>

<script>
    $(document).ready(function () {
        // Set today's date as default
        const today = new Date().toISOString().split('T')[0];
        $('#payment_date').val(today);

        // Toggle custom method input
        $('#method').change(function () {
            if ($(this).val() === 'other') {
                $('#customMethod').show().attr('required', true);
            } else {
                $('#customMethod').hide().removeAttr('required');
            }
        });

        // Handle form submission
        $('#addPaymentForm').on('submit', function (e) {
            e.preventDefault();

            const formData = {
                client_id: $('#client_id').val(),
                amount: $('#amount').val(),
                method: $('#method').val() === 'other' ? $('#customMethod').val() : $('#method').val(),
                payment_date: $('#payment_date').val(),
                note: $('#note').val()
            };

            const $button = $(this).find('button[type="submit"]');
            const originalText = $button.html();
            $button.html('<i class="fas fa-spinner fa-spin me-2"></i>Saving...').prop('disabled', true);

            $.ajax({
                url: '<?= BASE_URL ?>/payments-manager/create',
                method: 'POST',
                data: formData,
                dataType: 'json',
                success: function (response) {
                    if (response.error) {
                        showToast(response.error, 'danger');
                    } else {
                        showToast('Payment created successfully!', 'success');
                        addPaymentModal.hide();
                        paymentsTable.ajax.reload();
                        $('#addPaymentForm')[0].reset();
                        $('#customMethod').hide().removeAttr('required');
                        // Reset date to today
                        $('#payment_date').val(new Date().toISOString().split('T')[0]);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Create error:', xhr.responseText);
                    let errorMessage = 'An error occurred while creating the payment';

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
        });
    });
</script>