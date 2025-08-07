<?php
// Debug client query
error_log("Fetching client info for client_id: " . $payment['client_id']);
$clientQuery = "SELECT name FROM projects_list WHERE id = ?";
$clientStmt = $GLOBALS['pdo']->prepare($clientQuery);
$clientStmt->execute([$payment['client_id']]);
$clientInfo = $clientStmt->fetch(PDO::FETCH_ASSOC);
error_log("Client query result: " . print_r($clientInfo, true));
$clientName = $clientInfo ? $clientInfo['name'] : 'Unknown Client';
error_log("Client name determined as: " . $clientName);
?>
<form id="editPaymentForm">
    <input type="hidden" name="payment_id" value="<?= htmlspecialchars($payment['id']) ?>">
    <!-- Debug payment ID -->
    <div style="display:none;">DEBUG: Payment ID: <?= htmlspecialchars($payment['id']) ?></div>
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="edit_client_info" class="form-label">Client Information</label>
            <div class="form-control-plaintext bg-light p-3 rounded border">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                        <i class="fas fa-user text-primary"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark"><?= htmlspecialchars($clientName) ?></div>
                        <small class="text-muted">Client ID: <?= htmlspecialchars($payment['client_id']) ?></small>
                    </div>
                </div>
            </div>
            <input type="hidden" name="client_id" value="<?= htmlspecialchars($payment['client_id']) ?>">
            <div class="form-text">Client information cannot be changed during edit</div>
        </div>
        <div class="col-md-6">
            <label for="edit_amount" class="form-label">Amount <span class="text-danger">*</span></label>
            <input type="number" step="0.01" class="form-control" id="edit_amount" name="amount"
                value="<?= htmlspecialchars($payment['amount']) ?>" required>
            <div class="form-text">Negative values for refunds/credits</div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="edit_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
            <select class="form-select" id="edit_method" name="method" required>
                <option value="">Select method</option>
                <option value="cash" <?= $payment['method'] === 'cash' ? 'selected' : '' ?>>Cash</option>
                <option value="bank transfer" <?= $payment['method'] === 'bank transfer' ? 'selected' : '' ?>>Bank Transfer
                </option>
                <option value="credit card" <?= $payment['method'] === 'credit card' ? 'selected' : '' ?>>Credit Card
                </option>
                <option value="paypal" <?= $payment['method'] === 'paypal' ? 'selected' : '' ?>>PayPal</option>
                <option value="omt" <?= $payment['method'] === 'omt' ? 'selected' : '' ?>>OMT</option>
                <option value="wish" <?= $payment['method'] === 'wish' ? 'selected' : '' ?>>Wish</option>
                <option value="other" <?= !in_array($payment['method'], ['cash', 'bank transfer', 'credit card', 'paypal', 'omt', 'wish']) ? 'selected' : '' ?>>Other</option>
            </select>
            <!-- Debug payment method -->
            <div style="display:none;">DEBUG: Current method: <?= htmlspecialchars($payment['method']) ?></div>
            <input type="text" class="form-control mt-2" id="edit_customMethod" name="customMethod"
                placeholder="Enter custom method"
                value="<?= !in_array($payment['method'], ['cash', 'bank transfer', 'credit card', 'paypal', 'omt', 'wish']) ? htmlspecialchars($payment['method']) : '' ?>"
                style="<?= !in_array($payment['method'], ['cash', 'bank transfer', 'credit card', 'paypal', 'omt', 'wish']) ? 'display: block;' : 'display: none;' ?>">
        </div>
        <div class="col-md-6">
            <label for="edit_payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="edit_payment_date" name="payment_date"
                value="<?= htmlspecialchars($payment['payment_date']) ?>" required>
        </div>
    </div>
    <div class="mb-3">
        <label for="edit_note" class="form-label">Note</label>
        <textarea class="form-control" id="edit_note" name="note" rows="3"
            placeholder="Optional notes about this payment..."><?= htmlspecialchars($payment['note'] ?? '') ?></textarea>
    </div>
</form>
<script>
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
                    // Ensure editPaymentModal and paymentsTable are accessible and defined
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
        console.log('Edit payment form script loaded');
        console.log('BASE_URL:', '<?= BASE_URL ?>');
        // Debug initial form state
        console.log('Initial form values:', {
            payment_id: $('input[name="payment_id"]').val(),
            client_id: $('input[name="client_id"]').val(),
            amount: $('#edit_amount').val(),
            method: $('#edit_method').val(),
            payment_date: $('#edit_payment_date').val(),
            note: $('#edit_note').val()
        });

        // Toggle custom method input for edit form
        $('#edit_method').change(function () {
            console.log('Payment method changed to:', $(this).val());
            if ($(this).val() === 'other') {
                $('#edit_customMethod').show().attr('required', true);
                console.log('Showing custom method field');
            } else {
                $('#edit_customMethod').hide().removeAttr('required');
                console.log('Hiding custom method field');
            }
        });

        // Handle form submission
        $('#editPaymentForm').on('submit', function (e) {
            e.preventDefault();
            console.log('Form submission intercepted by editPaymentForm handler');
            window.submitEditPaymentForm();
        });
    });
</script>