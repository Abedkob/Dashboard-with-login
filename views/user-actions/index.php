<?php include __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../../public/config.php'; ?>

<!-- Custom Styles -->
<style>
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

    .action-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
    }

    .user-info {
        background: #f8f9fa;
        border-left: 4px solid #007bff;
        padding: 0.75rem;
        border-radius: 0.25rem;
    }

    .permission-denied {
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        border-radius: 0.5rem;
        padding: 2rem;
        text-align: center;
        color: #6c757d;
    }

    .disabled-button {
        opacity: 0.6;
        cursor: not-allowed;
    }

    @media (max-width: 768px) {
        .btn-toolbar {
            flex-direction: column;
            gap: 0.5rem;
        }
    }
</style>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <div class="d-flex align-items-center">
        <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3">
            <i class="fas fa-user-shield text-primary fs-4"></i>
        </div>
        <h1 class="h2 mb-0 text-dark fw-bold">User Actions Manager</h1>
    </div>
    <?php if ($canView): ?>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <?php if ($canCreate): ?>
                    <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal"
                        data-bs-target="#createActionModal">
                        <i class="fas fa-plus me-2"></i>Create New Action
                    </button>
                <?php else: ?>
                    <button type="button" class="btn btn-secondary shadow-sm disabled-button" 
                        title="You don't have permission to create user actions" disabled>
                        <i class="fas fa-lock me-2"></i>Create New Action
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if (!$canView): ?>
    <!-- Access Denied Message -->
    <div class="permission-denied">
        <div class="mb-3">
            <i class="fas fa-lock fa-3x text-muted"></i>
        </div>
        <h4 class="text-muted">Access Restricted</h4>
        <p class="mb-0">You don't have permission to view user actions. Please contact your administrator for access.</p>
    </div>
<?php else: ?>

<!-- Success/Error Messages -->
<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <strong>Success!</strong> <?= htmlspecialchars($_SESSION['flash_message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <strong>Error!</strong> <?= htmlspecialchars($_SESSION['flash_error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- Filters Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="mb-0 d-flex align-items-center">
            <i class="fas fa-filter me-2 text-primary"></i>
            Filters
        </h5>
    </div>
    <div class="card-body">
        <form method="get" class="row g-3 align-items-end" id="filtersForm">
            <div class="col-md-3">
                <label for="pageFilter" class="form-label">Page/Module</label>
                <select id="pageFilter" name="page" class="form-select">
                    <option value="">All Pages</option>
                    <?php foreach ($availablePages as $page): ?>
                        <option value="<?= htmlspecialchars($page) ?>" <?= ($page === ($_GET['page'] ?? '')) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($page) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label for="actionFilter" class="form-label">Action</label>
                <select id="actionFilter" name="action" class="form-select">
                    <option value="">All Actions</option>
                    <!-- Actions will be populated dynamically -->
                </select>
            </div>

            <?php if ($isAdmin): ?>
                <div class="col-md-3">
                    <label for="userFilter" class="form-label">User</label>
                    <select id="userFilter" name="user_id" class="form-select">
                        <option value="">All Users</option>
                        <?php foreach ($availableUsers as $user): ?>
                            <option value="<?= (int) $user['user_id'] ?>" <?= ((string) $user['user_id'] === ($_GET['user_id'] ?? '')) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user['username']) ?> (ID: <?= (int) $user['user_id'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="col-md-3">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-filter me-2"></i>Apply Filters
                </button>
                <a href="<?= BASE_URL ?>/user-actions/activity-logs" class="btn btn-outline-secondary">
                    <i class="fas fa-undo me-2"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Activity Logs Table -->
<div class="card shadow-sm">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0 d-flex align-items-center">
                <i class="fas fa-table me-2 text-primary"></i>
                User Actions Database
            </h5>
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                Total: <?= count($actions) ?> actions
            </small>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <?php if ($isAdmin): ?>
                            <th><i class="fas fa-user me-1"></i>User</th>
                        <?php endif; ?>
                        <th><i class="fas fa-file-alt me-1"></i>Page</th>
                        <th><i class="fas fa-bolt me-1"></i>Action</th>
                        <th width="120" class="text-center"><i class="fas fa-cogs me-1"></i>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($actions)): ?>
                        <tr>
                            <td colspan="<?= $isAdmin ? 4 : 3 ?>" class="text-center py-4">
                                <div class="text-center">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No user actions found</h5>
                                    <p class="text-muted">Try adjusting your filters or create a new action</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($actions as $log): ?>
                            <tr>
                                <?php if ($isAdmin): ?>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                                                <i class="fas fa-user text-primary"></i>
                                            </div>
                                            <div>
                                                <span class="fw-semibold"><?= htmlspecialchars($log['username']) ?></span>
                                                <br><small class="text-muted">ID: <?= (int) $log['user_id'] ?></small>
                                            </div>
                                        </div>
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="fas fa-file-alt me-1"></i><?= htmlspecialchars($log['page']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-primary action-badge">
                                        <i class="fas fa-bolt me-1"></i><?= htmlspecialchars($log['action']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($canDelete): ?>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-danger"
                                                onclick="deleteAction(<?= (int) $log['id'] ?>)" title="Delete Action">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">
                                            <i class="fas fa-lock me-1"></i>No actions available
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
<?php if ($totalActions > $perPage): ?>
    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
            <?php
            $totalPages = (int) ceil($totalActions / $perPage);
            $currentPage = $pageNumber;
            $queryParams = $_GET;

            // Previous page link
            if ($currentPage > 1):
                $queryParams['page_number'] = $currentPage - 1;
                ?>
                <li class="page-item">
                    <a class="page-link" href="?<?= http_build_query($queryParams) ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++):
                $queryParams['page_number'] = $i;
                ?>
                <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query($queryParams) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($currentPage < $totalPages):
                $queryParams['page_number'] = $currentPage + 1;
                ?>
                <li class="page-item">
                    <a class="page-link" href="?<?= http_build_query($queryParams) ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>

<?php if ($canCreate): ?>
<!-- Create Action Modal -->
<div class="modal fade" id="createActionModal" tabindex="-1" aria-labelledby="createActionModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createActionModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Create New User Action
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="userActionForm" method="post" action="<?= BASE_URL ?>/user-actions/store"
                    class="needs-validation" novalidate>
                    <!-- User Selection -->
                    <div class="mb-4">
                        <label for="user_id" class="form-label fw-semibold">
                            <i class="fas fa-user me-2 text-primary"></i>Select User
                        </label>
                        <select name="user_id" id="user_id" class="form-select shadow-sm" required>
                            <option value="" disabled selected>-- Choose a user --</option>
                            <?php foreach ($availableUsers as $user): ?>
                                <option value="<?= (int) $user['user_id'] ?>" data-user-id="<?= (int) $user['user_id'] ?>">
                                    <?= htmlspecialchars($user['username'] ?? 'User #' . $user['user_id']) ?>
                                    <small class="text-muted">(ID: <?= (int) $user['user_id'] ?>)</small>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">
                            <i class="fas fa-exclamation-circle me-1"></i>Please select a valid user
                        </div>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>Choose the user who will have this action permission
                        </div>
                    </div>

                    <!-- Page Selection -->
                    <div class="mb-4">
                        <label for="page" class="form-label fw-semibold">
                            <i class="fas fa-file-alt me-2 text-primary"></i>Page/Module
                        </label>
                        <select name="page" id="page" class="form-select shadow-sm" required>
                            <option value="" disabled selected>-- Select a page --</option>
                            <?php foreach ($availablePages as $page): ?>
                                <option value="<?= htmlspecialchars($page) ?>">
                                    <?= htmlspecialchars($page) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">
                            <i class="fas fa-exclamation-circle me-1"></i>Please select a page/module
                        </div>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>Select the page or module for this permission
                        </div>
                    </div>

                    <!-- Action Selection -->
                    <div class="mb-4">
                        <label for="action" class="form-label fw-semibold">
                            <i class="fas fa-bolt me-2 text-primary"></i>Action
                        </label>
                        <select name="action" id="action" class="form-select shadow-sm" required disabled>
                            <option value="" disabled selected>-- Select an action --</option>
                        </select>
                        <div class="invalid-feedback">
                            <i class="fas fa-exclamation-circle me-1"></i>Please select an action
                        </div>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>Choose what action the user can perform on this page
                        </div>
                    </div>

                    <!-- Action Description -->
                    <div class="mb-4" id="actionDescription" style="display: none;">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Action Details:</strong>
                            <span id="actionDescriptionText"></span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" id="submitActionBtn">
                    <i class="fas fa-save me-2"></i>Create Action
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canDelete): ?>
<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteActionModal" tabindex="-1" aria-labelledby="deleteActionModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteActionModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3 fs-1 text-danger">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <h5 class="text-dark">Are you sure you want to delete this user action?</h5>
                <p class="text-muted">This action cannot be undone. The user will lose this permission immediately.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash-alt me-2"></i>Delete Action
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
    // Pass permission data to JavaScript
    const userPermissions = {
        canCreate: <?= json_encode($canCreate) ?>,
        canView: <?= json_encode($canView) ?>,
        canDelete: <?= json_encode($canDelete) ?>
    };

    document.addEventListener('DOMContentLoaded', function () {
        // Only initialize if user has view permission
        if (!userPermissions.canView) {
            return;
        }

        const allActions = <?= json_encode($availableActions) ?>;
        let createActionModal, deleteActionModal;
        let currentDeleteId = null;

        // Initialize modals only if user has permissions
        if (userPermissions.canCreate) {
            const createModalElement = document.getElementById('createActionModal');
            if (createModalElement) {
                createActionModal = new bootstrap.Modal(createModalElement);
            }
        }

        if (userPermissions.canDelete) {
            const deleteModalElement = document.getElementById('deleteActionModal');
            if (deleteModalElement) {
                deleteActionModal = new bootstrap.Modal(deleteModalElement);
            }
        }

        // Initialize filters
        initializeFilters();

        // Submit action button handler
        if (userPermissions.canCreate) {
            const submitBtn = document.getElementById('submitActionBtn');
            if (submitBtn) {
                submitBtn.addEventListener('click', function () {
                    if (!userPermissions.canCreate) {
                        showToast('You do not have permission to create user actions', 'danger');
                        return;
                    }
                    submitCreateForm();
                });
            }
        }

        // Confirm delete button handler
        if (userPermissions.canDelete) {
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            if (confirmDeleteBtn) {
                confirmDeleteBtn.addEventListener('click', function () {
                    if (!userPermissions.canDelete) {
                        showToast('You do not have permission to delete user actions', 'danger');
                        return;
                    }
                    if (currentDeleteId) {
                        performDelete(currentDeleteId);
                    }
                });
            }
        }

        // Form elements for dynamic behavior
        const pageSelect = document.getElementById('page');
        const actionSelect = document.getElementById('action');
        const actionDescription = document.getElementById('actionDescription');
        const actionDescriptionText = document.getElementById('actionDescriptionText');

        // Action descriptions for better UX
        const actionDescriptions = {
            'view': 'Allows the user to view and access this page',
            'create': 'Allows the user to create new records',
            'read': 'Allows the user to read and view existing records',
            'update': 'Allows the user to edit and modify existing records',
            'delete': 'Allows the user to delete existing records',
            'manage permissions': 'Allows the user to manage permissions for other users',
            'add payment': 'Allows the user to add payment records',
            'renew licenses': 'Allows the user to renew license records'
        };

        // Initialize form dynamic behavior if elements exist and user has create permission
        if (userPermissions.canCreate && pageSelect && actionSelect) {
            // Handle page selection change
            pageSelect.addEventListener('change', function () {
                const selectedPage = this.value;
                populateActions(selectedPage);
            });

            // Handle action selection change
            actionSelect.addEventListener('change', function () {
                const selectedAction = this.value;
                if (selectedAction && actionDescriptions[selectedAction] && actionDescription && actionDescriptionText) {
                    actionDescriptionText.textContent = actionDescriptions[selectedAction];
                    actionDescription.style.display = 'block';
                } else if (actionDescription) {
                    actionDescription.style.display = 'none';
                }
            });

            // Reset form when modal is hidden
            const createModalElement = document.getElementById('createActionModal');
            if (createModalElement) {
                createModalElement.addEventListener('hidden.bs.modal', function () {
                    const form = document.getElementById('userActionForm');
                    if (form) {
                        form.reset();
                        form.classList.remove('was-validated');

                        // Reset action select
                        actionSelect.innerHTML = '<option value="" disabled selected>-- Select an action --</option>';
                        actionSelect.disabled = true;

                        // Hide description
                        if (actionDescription) {
                            actionDescription.style.display = 'none';
                        }
                    }
                });
            }
        }

        function populateActions(selectedPage) {
            if (!userPermissions.canCreate) return;

            const actions = allActions[selectedPage] || [];

            // Clear and reset action select
            actionSelect.innerHTML = '<option value="" disabled selected>-- Select an action --</option>';
            actionSelect.disabled = actions.length === 0;

            // Hide description initially
            if (actionDescription) {
                actionDescription.style.display = 'none';
            }

            // Populate actions
            actions.forEach(action => {
                const option = document.createElement('option');
                option.value = action;
                option.textContent = action.charAt(0).toUpperCase() + action.slice(1);
                actionSelect.appendChild(option);
            });

            // Enable action select if we have actions
            if (actions.length > 0) {
                actionSelect.disabled = false;
            }
        }

        function initializeFilters() {
            const pageFilter = document.getElementById('pageFilter');
            const actionFilter = document.getElementById('actionFilter');

            if (pageFilter && actionFilter) {
                // Function to populate actions based on selected page
                function populateActionFilter(selectedPage) {
                    actionFilter.innerHTML = '<option value="">All Actions</option>';

                    if (selectedPage && allActions[selectedPage]) {
                        allActions[selectedPage].forEach(action => {
                            const option = document.createElement('option');
                            option.value = action;
                            option.textContent = action.charAt(0).toUpperCase() + action.slice(1);

                            // Check if this action should be selected based on URL params
                            const urlParams = new URLSearchParams(window.location.search);
                            if (urlParams.get('action') === action) {
                                option.selected = true;
                            }

                            actionFilter.appendChild(option);
                        });
                    }
                }

                // Handle page filter change
                pageFilter.addEventListener('change', function () {
                    const selectedPage = this.value;
                    populateActionFilter(selectedPage);
                });

                // Initialize action filter on page load
                const currentPage = pageFilter.value;
                if (currentPage) {
                    populateActionFilter(currentPage);
                }
            }
        }

        function submitCreateForm() {
            if (!userPermissions.canCreate) {
                showToast('You do not have permission to create user actions', 'danger');
                return;
            }

            const form = document.getElementById('userActionForm');
            if (!form) {
                showToast('Form not found', 'danger');
                return;
            }

            // Validate form
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                showToast('Please fill in all required fields correctly', 'warning');
                return;
            }

            // Get form data properly
            const formData = new FormData();
            formData.append('user_id', document.getElementById('user_id').value);
            formData.append('page', document.getElementById('page').value);
            formData.append('action', document.getElementById('action').value);

            const submitBtn = document.getElementById('submitActionBtn');
            const originalText = submitBtn.innerHTML;

            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating...';

            // Use the correct URL
            const submitUrl = '<?= BASE_URL ?>/user-actions/store';

            fetch(submitUrl, {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (response.status === 403) {
                        throw new Error('You do not have permission to create user actions');
                    }
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`HTTP ${response.status}: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showToast('User action created successfully!', 'success');
                        if (createActionModal) {
                            createActionModal.hide();
                        }

                        // Reload only the table content instead of the whole page
                        setTimeout(() => {
                            reloadTableContent();
                        }, 1000);
                    } else {
                        showToast(data.error || 'Failed to create user action', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error submitting form:', error);
                    showToast('Error: ' + error.message, 'danger');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
        }

        // Function to reload only the table content
        function reloadTableContent() {
            const currentUrl = new URL(window.location);

            // Show loading indicator
            const tableContainer = document.querySelector('.table-responsive');
            if (tableContainer) {
                tableContainer.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Reloading...</span>
                    </div>
                    <p class="mt-2">Refreshing data...</p>
                </div>
                `;
            }

            // Fetch updated content
            fetch(currentUrl.toString())
                .then(response => response.text())
                .then(html => {
                    // Parse the response and extract the table content
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newTableContainer = doc.querySelector('.table-responsive');
                    const newPagination = doc.querySelector('nav[aria-label="Page navigation"]');

                    if (newTableContainer && tableContainer) {
                        tableContainer.innerHTML = newTableContainer.innerHTML;
                    }

                    // Update pagination if it exists
                    const currentPagination = document.querySelector('nav[aria-label="Page navigation"]');
                    if (newPagination && currentPagination) {
                        currentPagination.innerHTML = newPagination.innerHTML;
                    }

                    showToast('Table refreshed successfully!', 'success');
                })
                .catch(error => {
                    console.error('Error reloading table:', error);
                    showToast('Failed to refresh table', 'danger');
                    // Fallback to page reload if AJAX fails
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                });
        }

        // Global delete function
        window.deleteAction = function (id) {
            if (!userPermissions.canDelete) {
                showToast('You do not have permission to delete user actions', 'danger');
                return;
            }

            currentDeleteId = id;
            if (deleteActionModal) {
                deleteActionModal.show();
            }
        };

        function performDelete(id) {
            if (!userPermissions.canDelete) {
                showToast('You do not have permission to delete user actions', 'danger');
                return;
            }

            const submitBtn = document.getElementById('confirmDeleteBtn');
            const originalText = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Deleting...';

            fetch('<?= BASE_URL ?>/user-actions/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            })
                .then(response => {
                    if (response.status === 403) {
                        throw new Error('You do not have permission to delete user actions');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showToast('User action deleted successfully!', 'success');
                        if (deleteActionModal) {
                            deleteActionModal.hide();
                        }

                        // Reload only the table content instead of the whole page
                        setTimeout(() => {
                            reloadTableContent();
                        }, 1000);
                    } else {
                        showToast(data.error || 'Failed to delete user action', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error: ' + error.message, 'danger');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    currentDeleteId = null;
                });
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

<?php endif; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
