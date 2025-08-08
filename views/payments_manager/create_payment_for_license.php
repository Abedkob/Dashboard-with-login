<?php
// This file is loaded into a modal via AJAX.
// Required variables: $licenseId, $licenseName, and database access via $GLOBALS['pdo'].

// Ensure PDO is available
if (!isset($GLOBALS['pdo'])) {
    require_once __DIR__ . '/../../public/config.php';
}

// Fetch clients for the dropdown
$stmt = $GLOBALS['pdo']->prepare("SELECT id, name FROM projects_list ORDER BY name ASC");
$stmt->execute();
$availableClients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get license info passed via GET
$licenseId = $_GET['license_id'] ?? null;
$licenseName = $_GET['license_name'] ?? 'N/A';

// Preselect client_id using licenseId
$preselectedClientId = $licenseId;
?>

<form id="addPaymentForLicenseForm">
    <!-- Hidden field to link this payment to a license/project -->
    <input type="hidden" name="license_id" value="<?= htmlspecialchars($licenseId) ?>">

    <div class="row mb-3">
        <!-- License Display -->
        <div class="col-md-6">
            <label class="form-label">License Information</label>
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
            <div class="form-text">Payment will be linked to this license (via Client ID).</div>
        </div>

        <!-- Client Dropdown -->
        <div class="col-md-6 d-none"> <!-- Hidden but still in the DOM and functional -->
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

        <!-- Payment Amount and Method -->
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
                <div class="form-text">Use negative numbers for refunds or credits.</div>
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

        <!-- Payment Date -->
        <div class="mb-3">
            <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="payment_date" name="payment_date" required>
        </div>

        <!-- Optional Note -->
        <div class="mb-3">
            <label for="note" class="form-label">Note</label>
            <textarea class="form-control" id="note" name="note" rows="3"
                placeholder="Optional notes about this payment..."></textarea>
        </div>
</form>