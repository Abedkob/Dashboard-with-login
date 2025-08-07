<?php
// This file is loaded via AJAX into the modal
// It expects $licenseId and $licenseName to be available from the controller
// It also needs $availableClients for the client_id dropdown

// Ensure $GLOBALS['pdo'] is available (should be set by the controller)
if (!isset($GLOBALS['pdo'])) {
    // Fallback/debug: if PDO is not globally available, try to include config
    // In a proper MVC setup, the controller should pass the PDO object or ensure its availability.
    require_once __DIR__ . '/../../public/config.php';
}

// Get available clients for the dropdown
$clientsQuery = "SELECT id, name FROM projects_list ORDER BY name ASC";
$clientsStmt = $GLOBALS['pdo']->prepare($clientsQuery);
$clientsStmt->execute();
$availableClients = $clientsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get license_id (which is projects_list.id) and license_name passed from the controller via GET parameters
$licenseId = $_GET['license_id'] ?? null;
$licenseName = $_GET['license_name'] ?? 'N/A';

// Pre-select the client_id dropdown with the licenseId (which is projects_list.id)
$preselectedClientId = $licenseId;
?>
<form id="addPaymentForLicenseForm">
    <!-- Hidden input to pass the license ID (which is projects_list.id) -->
    <input type="hidden" name="license_id" value="<?= htmlspecialchars($licenseId) ?>">
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="license_info" class="form-label">License Information</label>
            <div class="form-control-plaintext bg-light p-3 rounded border">
                <div class="d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3">
                        <i class="fas fa-key text-success"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark">License Name: <?= htmlspecialchars($licenseName) ?></div>
                        <small class="text-muted">License ID: <?= htmlspecialchars($licenseId) ?></small>
                    </div>
                </div>
            </div>
            <div class="form-text">This payment will be linked to this license (via Client ID).</div>
        </div>
        <div class="col-md-6">
            <label for="client_id" class="form-label">Client <span class="text-danger">*</span></label>
            <select class="form-select" id="client_id" name="client_id" required>
                <option value="">Select a client...</option>
                <?php foreach ($availableClients as $client): ?>
                    <option value="<?= $client['id'] ?>" <?= ($preselectedClientId == $client['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($client['name']) ?> (ID: <?= htmlspecialchars($client['id']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">This client ID will be the license ID from the table.</div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
            <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
            <div class="form-text">Negative values for refunds/credits</div>
        </div>
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
    </div>
    <div class="mb-3">
        <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
        <input type="date" class="form-control" id="payment_date" name="payment_date" required>
    </div>
    <div class="mb-3">
        <label for="note" class="form-label">Note</label>
        <textarea class="form-control" id="note" name="note" rows="3"
            placeholder="Optional notes about this payment..."></textarea>
    </div>
</form>