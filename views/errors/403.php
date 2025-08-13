<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-danger">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-ban fa-4x text-danger"></i>
                    </div>
                    <h1 class="display-4 text-danger mb-3">403</h1>
                    <h3 class="text-danger mb-3">Access Denied</h3>
                    <p class="text-muted mb-4">
                        <?= htmlspecialchars($errorMessage ?? 'You do not have permission to access this resource.') ?>
                    </p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Need Access?</strong> Contact your administrator to request the necessary permissions.
                    </div>
                    <div class="mt-4">
                        <a href="<?= BASE_URL ?>/dashboard" class="btn btn-primary me-2">
                            <i class="fas fa-home me-2"></i>Go to Dashboard
                        </a>
                        <button onclick="history.back()" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Go Back
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>