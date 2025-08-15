<?php 
// Define BASE_URL if not already defined
if (!defined('BASE_URL')) {
    define('BASE_URL', '/Practice_php/public');
}

include __DIR__ . '/../layouts/header.php'; 
?>

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

    .table-loading {
        position: relative;
    }

    .table-loading::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }

    /* Permission Grid Styles */
    .permission-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .permission-card {
        border: 2px solid #e9ecef;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
    }

    .permission-card.has-selections {
        border-color: #007bff;
        background-color: #f8f9ff;
    }

    .permission-card-header {
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem 0.5rem 0 0;
    }

    .permission-card-body {
        padding: 1rem;
    }

    .permission-checkbox {
        margin-bottom: 0.5rem;
    }

    .permission-checkbox:last-child {
        margin-bottom: 0;
    }

    .page-checkbox {
        font-weight: 600;
        color: #495057;
    }

    .action-checkbox {
        margin-left: 1.5rem;
        font-size: 0.9rem;
    }

    .selection-counter {
        background: #007bff;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 1rem;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .bulk-controls {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    @media (max-width: 768px) {
        .permission-grid {
            grid-template-columns: 1fr;
        }
        
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
        <div>
            <h1 class="h2 mb-0 text-dark fw-bold">User Actions Manager</h1>
            <?php if (!$isAdmin): ?>
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Viewing your permissions only
                </small>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($canView): ?>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <?php if ($canCreate): ?>
                    <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal"
                        data-bs-target="#createActionModal">
                        <i class="fas fa-plus me-2"></i>Create New Actions
                    </button>
                <?php else: ?>
                    <button type="button" class="btn btn-secondary shadow-sm disabled-button"
                        title="<?= $isAdmin ? "You don't have permission to create user actions" : "Contact your administrator to modify permissions" ?>"
                        disabled>
                        <i class="fas fa-lock me-2"></i>
                        <?= $isAdmin ? "Create New Actions" : "Request Permission Change" ?>
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
            <div class="row g-3 align-items-end" id="filtersForm">
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
                    <button type="button" class="btn btn-primary me-2" onclick="UserActionsManager.applyFilters()">
                        <i class="fas fa-filter me-2"></i>Apply Filters
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="UserActionsManager.resetFilters()">
                        <i class="fas fa-undo me-2"></i>Reset
                    </button>
                </div>
            </div>
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
                <small class="text-muted" id="totalActionsCount">
                    <i class="fas fa-info-circle me-1"></i>
                    Total: <?= count($actions) ?> actions
                </small>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" id="tableContainer">
                <table class="table table-hover mb-0" id="userActionsTable">
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
                    <tbody id="tableBody">
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
                                                    onclick="UserActionsManager.deleteAction(<?= (int) $log['id'] ?>)" title="Delete Action">
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
        <nav aria-label="Page navigation" class="mt-4" id="paginationContainer">
            <ul class="pagination justify-content-center">
                <?php
                $totalPages = (int) ceil($totalActions / $perPage);
                $currentPage = $pageNumber;

                // Previous page link
                if ($currentPage > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="javascript:void(0)" onclick="UserActionsManager.loadPage(<?= $currentPage - 1 ?>)" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                        <a class="page-link" href="javascript:void(0)" onclick="UserActionsManager.loadPage(<?= $i ?>)"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="javascript:void(0)" onclick="UserActionsManager.loadPage(<?= $currentPage + 1 ?>)" aria-label="Next">
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
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createActionModalLabel">
                            <i class="fas fa-plus-circle me-2"></i>Create User Actions
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- User Selection -->
                        <div class="mb-4">
                            <label for="user_id" class="form-label fw-semibold">
                                <i class="fas fa-user me-2 text-primary"></i>Select User
                            </label>
                            <?php if ($isAdmin): ?>
                                <select name="user_id" id="user_id" class="form-select shadow-sm" required>
                                    <option value="" disabled selected>-- Choose a user --</option>
                                    <?php if (!empty($availableUsers)): ?>
                                        <?php foreach ($availableUsers as $user): ?>
                                            <option value="<?= (int) $user['user_id'] ?>" data-user-id="<?= (int) $user['user_id'] ?>">
                                                <?= htmlspecialchars($user['username'] ?? 'User #' . $user['user_id']) ?>
                                                (ID: <?= (int) $user['user_id'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>No users available</option>
                                    <?php endif; ?>
                                </select>
                            <?php else: ?>
                                <!-- Non-admin users can only select themselves -->
                                <select name="user_id" id="user_id" class="form-select shadow-sm" required readonly>
                                    <?php if (!empty($availableUsers)): ?>
                                        <?php $currentUser = $availableUsers[0]; ?>
                                        <option value="<?= (int) $currentUser['user_id'] ?>" selected>
                                            <?= htmlspecialchars($currentUser['username']) ?> (You)
                                        </option>
                                    <?php else: ?>
                                        <option value="<?= (int) ($_SESSION['user_id'] ?? 0) ?>" selected>
                                            <?= htmlspecialchars($_SESSION['username'] ?? 'Current User') ?> (You)
                                        </option>
                                    <?php endif; ?>
                                </select>
                            <?php endif; ?>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                <?php if ($isAdmin): ?>
                                    Choose the user who will have these action permissions
                                <?php else: ?>
                                    You can only manage permissions for yourself
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Bulk Controls -->
                        <div class="bulk-controls">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">
                                    <i class="fas fa-tasks me-2"></i>Permission Selection
                                    <span class="selection-counter ms-2" id="selectionCounter">0 selected</span>
                                </h6>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" onclick="UserActionsManager.selectAllPermissions()">
                                        <i class="fas fa-check-square me-1"></i>Check All
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="UserActionsManager.clearAllPermissions()">
                                        <i class="fas fa-square me-1"></i>Uncheck All
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Permission Grid -->
                        <div class="permission-grid" id="permissionGrid">
                            <?php foreach ($availableActions as $page => $actions): ?>
                                <div class="permission-card" data-page="<?= htmlspecialchars($page) ?>">
                                    <div class="permission-card-header">
                                        <div class="form-check permission-checkbox">
                                            <input class="form-check-input page-checkbox" type="checkbox" 
                                                   id="page_<?= htmlspecialchars(str_replace(' ', '_', $page)) ?>"
                                                   data-page="<?= htmlspecialchars($page) ?>"
                                                   onchange="UserActionsManager.togglePageActions(this)">
                                            <label class="form-check-label page-checkbox" 
                                                   for="page_<?= htmlspecialchars(str_replace(' ', '_', $page)) ?>">
                                                <i class="fas fa-file-alt me-2"></i><?= htmlspecialchars($page) ?>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="permission-card-body">
                                        <?php foreach ($actions as $action): ?>
                                            <div class="form-check permission-checkbox action-checkbox">
                                                <input class="form-check-input action-checkbox" type="checkbox" 
                                                       id="action_<?= htmlspecialchars(str_replace(' ', '_', $page . '_' . $action)) ?>"
                                                       data-page="<?= htmlspecialchars($page) ?>"
                                                       data-action="<?= htmlspecialchars($action) ?>"
                                                       onchange="UserActionsManager.updatePageCheckbox(this)">
                                                <label class="form-check-label" 
                                                       for="action_<?= htmlspecialchars(str_replace(' ', '_', $page . '_' . $action)) ?>">
                                                    <i class="fas fa-bolt me-2"></i><?= htmlspecialchars(ucfirst($action)) ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-primary" id="submitActionBtn" onclick="UserActionsManager.submitBatchForm()">
                            <i class="fas fa-save me-2"></i>Create Selected Actions
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
                        <button type="button" class="btn btn-danger" id="confirmDeleteBtn" onclick="UserActionsManager.performDelete()">
                            <i class="fas fa-trash-alt me-2"></i>Delete Action
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script>
        // User Actions Manager - Namespace to avoid global function conflicts
        const UserActionsManager = {
            // Configuration
            config: {
                userPermissions: {
                    canCreate: <?= json_encode($canCreate) ?>,
                    canView: <?= json_encode($canView) ?>,
                    canDelete: <?= json_encode($canDelete) ?>,
                    isAdmin: <?= json_encode($isAdmin) ?>
                },
                baseUrl: '<?= BASE_URL ?>',
                allActions: <?= json_encode($availableActions) ?>
            },

            // State
            state: {
                createActionModal: null,
                deleteActionModal: null,
                currentDeleteId: null
            },

            // Initialize the manager
            init: function() {
                console.log('UserActionsManager initializing...');
                console.log('User permissions:', this.config.userPermissions);
                console.log('Available actions:', this.config.allActions);

                // Initialize modals
                if (this.config.userPermissions.canCreate) {
                    const createModalElement = document.getElementById('createActionModal');
                    if (createModalElement) {
                        this.state.createActionModal = new bootstrap.Modal(createModalElement);
                        console.log('Create modal initialized');
                    }
                }

                if (this.config.userPermissions.canDelete) {
                    const deleteModalElement = document.getElementById('deleteActionModal');
                    if (deleteModalElement) {
                        this.state.deleteActionModal = new bootstrap.Modal(deleteModalElement);
                        console.log('Delete modal initialized');
                    }
                }

                // Initialize filters
                this.initializeFilters();

                // Reset form when modal is hidden
                const createModalElement = document.getElementById('createActionModal');
                if (createModalElement) {
                    createModalElement.addEventListener('hidden.bs.modal', () => {
                        this.clearAllPermissions();
                        const userSelect = document.getElementById('user_id');
                        if (userSelect && !userSelect.hasAttribute('readonly')) {
                            userSelect.selectedIndex = 0;
                        }
                    });
                }

                console.log('UserActionsManager initialization complete');
            },

            // Permission selection functions
            togglePageActions: function(pageCheckbox) {
                const page = pageCheckbox.dataset.page;
                const actionCheckboxes = document.querySelectorAll(`input[data-page="${page}"][data-action]`);
                const isChecked = pageCheckbox.checked;

                actionCheckboxes.forEach(checkbox => {
                    checkbox.checked = isChecked;
                });

                this.updateCardAppearance(page);
                this.updateSelectionCounter();
            },

            updatePageCheckbox: function(actionCheckbox) {
                const page = actionCheckbox.dataset.page;
                const pageCheckbox = document.querySelector(`input[data-page="${page}"]:not([data-action])`);
                const actionCheckboxes = document.querySelectorAll(`input[data-page="${page}"][data-action]`);
                
                const checkedActions = Array.from(actionCheckboxes).filter(cb => cb.checked);
                const totalActions = actionCheckboxes.length;

                if (pageCheckbox) {
                    if (checkedActions.length === 0) {
                        pageCheckbox.checked = false;
                        pageCheckbox.indeterminate = false;
                    } else if (checkedActions.length === totalActions) {
                        pageCheckbox.checked = true;
                        pageCheckbox.indeterminate = false;
                    } else {
                        pageCheckbox.checked = false;
                        pageCheckbox.indeterminate = true;
                    }
                }

                this.updateCardAppearance(page);
                this.updateSelectionCounter();
            },

            updateCardAppearance: function(page) {
                const card = document.querySelector(`[data-page="${page}"]`);
                if (!card) {
                    console.warn('Card not found for page:', page);
                    return;
                }

                const actionCheckboxes = document.querySelectorAll(`input[data-page="${page}"][data-action]`);
                const checkedActions = Array.from(actionCheckboxes).filter(cb => cb.checked);

                if (checkedActions.length > 0) {
                    card.classList.add('has-selections');
                } else {
                    card.classList.remove('has-selections');
                }
            },

            updateSelectionCounter: function() {
                const checkedActions = document.querySelectorAll('input[data-action]:checked');
                const counter = document.getElementById('selectionCounter');
                const count = checkedActions.length;
                
                if (counter) {
                    counter.textContent = `${count} selected`;
                    counter.className = count > 0 ? 'selection-counter' : 'selection-counter bg-secondary';
                }
            },

            selectAllPermissions: function() {
                const allCheckboxes = document.querySelectorAll('#permissionGrid input[type="checkbox"]');
                allCheckboxes.forEach(checkbox => {
                    checkbox.checked = true;
                });

                // Update all page checkboxes
                const pageCheckboxes = document.querySelectorAll('.page-checkbox');
                pageCheckboxes.forEach(checkbox => {
                    if (checkbox.dataset.page) {
                        checkbox.indeterminate = false;
                        this.updateCardAppearance(checkbox.dataset.page);
                    }
                });

                this.updateSelectionCounter();
            },

            clearAllPermissions: function() {
                const allCheckboxes = document.querySelectorAll('#permissionGrid input[type="checkbox"]');
                allCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                    checkbox.indeterminate = false;
                });

                // Update all card appearances
                const cards = document.querySelectorAll('.permission-card');
                cards.forEach(card => {
                    card.classList.remove('has-selections');
                });

                this.updateSelectionCounter();
            },

            // Form submission
            submitBatchForm: function() {
                console.log('submitBatchForm called');
                
                if (!this.config.userPermissions.canCreate) {
                    this.showToast('You do not have permission to create user actions', 'danger');
                    return;
                }

                const userSelect = document.getElementById('user_id');
                const userId = userSelect ? userSelect.value : '';

                if (!userId) {
                    this.showToast('Please select a user', 'warning');
                    return;
                }

                // Get selected permissions
                const selectedPermissions = [];
                const checkedActions = document.querySelectorAll('input[data-action]:checked');

                checkedActions.forEach(checkbox => {
                    selectedPermissions.push({
                        page: checkbox.dataset.page,
                        action: checkbox.dataset.action
                    });
                });

                console.log('Selected permissions:', selectedPermissions);

                if (selectedPermissions.length === 0) {
                    this.showToast('Please select at least one permission', 'warning');
                    return;
                }

                const submitBtn = document.getElementById('submitActionBtn');
                const originalText = submitBtn ? submitBtn.innerHTML : '';

                // Disable button and show loading
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating...';
                }

                // Submit batch request
                fetch(`${this.config.baseUrl}/user-actions/store-batch`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        user_id: parseInt(userId),
                        permissions: selectedPermissions
                    })
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        return response.text().then(text => {
                            console.error('Response text:', text);
                            throw new Error(`HTTP ${response.status}: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.success) {
                        let message = data.message;
                        if (data.errors && data.errors.length > 0) {
                            message += '\n\nWarnings:\n' + data.errors.join('\n');
                        }
                        this.showToast(message, 'success');
                        
                        if (this.state.createActionModal) {
                            this.state.createActionModal.hide();
                        }

                        // Reload table content
                        setTimeout(() => {
                            this.reloadTableContent();
                        }, 1000);
                    } else {
                        this.showToast(data.error || 'Failed to create user actions', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error submitting form:', error);
                    this.showToast('Error: ' + error.message, 'danger');
                })
                .finally(() => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                });
            },

            // Filter functions
            initializeFilters: function() {
                const pageFilter = document.getElementById('pageFilter');
                const actionFilter = document.getElementById('actionFilter');

                if (pageFilter && actionFilter) {
                    // Function to populate actions based on selected page
                    const populateActionFilter = (selectedPage) => {
                        actionFilter.innerHTML = '<option value="">All Actions</option>';

                        if (selectedPage && this.config.allActions[selectedPage]) {
                            this.config.allActions[selectedPage].forEach(action => {
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
                    };

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
            },

            applyFilters: function() {
                const pageFilter = document.getElementById('pageFilter');
                const actionFilter = document.getElementById('actionFilter');
                const userFilter = document.getElementById('userFilter');

                const params = new URLSearchParams();
                
                if (pageFilter && pageFilter.value) {
                    params.set('page', pageFilter.value);
                }
                
                if (actionFilter && actionFilter.value) {
                    params.set('action', actionFilter.value);
                }
                
                if (userFilter && userFilter.value) {
                    params.set('user_id', userFilter.value);
                }

                // Reset to first page when applying filters
                params.set('page_number', '1');

                // Use AJAX to reload table content
                this.reloadTableContentWithParams(params);
            },

            resetFilters: function() {
                const pageFilter = document.getElementById('pageFilter');
                const actionFilter = document.getElementById('actionFilter');
                const userFilter = document.getElementById('userFilter');

                if (pageFilter) pageFilter.value = '';
                if (actionFilter) actionFilter.innerHTML = '<option value="">All Actions</option>';
                if (userFilter) userFilter.value = '';

                // Reload table without filters
                this.reloadTableContentWithParams(new URLSearchParams());
            },

            loadPage: function(pageNumber) {
                const params = new URLSearchParams(window.location.search);
                params.set('page_number', pageNumber);
                this.reloadTableContentWithParams(params);
            },

            reloadTableContent: function() {
                const params = new URLSearchParams(window.location.search);
                this.reloadTableContentWithParams(params);
            },

            reloadTableContentWithParams: function(params) {
                const tableContainer = document.getElementById('tableContainer');
                const totalActionsCount = document.getElementById('totalActionsCount');
                const paginationContainer = document.getElementById('paginationContainer');
                
                if (!tableContainer) {
                    console.error('Table container not found');
                    return;
                }

                // Show loading state
                tableContainer.classList.add('table-loading');
                const loadingOverlay = document.createElement('div');
                loadingOverlay.className = 'position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-white bg-opacity-75';
                loadingOverlay.style.zIndex = '10';
                loadingOverlay.innerHTML = `
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 mb-0">Refreshing data...</p>
                    </div>
                `;
                tableContainer.style.position = 'relative';
                tableContainer.appendChild(loadingOverlay);

                // Build fetch URL
                const fetchUrl = `${this.config.baseUrl}/user-actions/activity-logs?${params.toString()}`;

                fetch(fetchUrl, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.text();
                })
                .then(html => {
                    // Create a temporary container to parse the response
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;
                    
                    // Extract the new table content
                    const newTable = tempDiv.querySelector('#userActionsTable');
                    const newTotalCount = tempDiv.querySelector('#totalActionsCount');
                    const newPagination = tempDiv.querySelector('#paginationContainer');
                    
                    if (newTable) {
                        // Replace entire table
                        const currentTable = document.getElementById('userActionsTable');
                        if (currentTable) {
                            currentTable.parentNode.replaceChild(newTable, currentTable);
                        }
                    }
                    
                    if (newTotalCount && totalActionsCount) {
                        // Update total count
                        totalActionsCount.innerHTML = newTotalCount.innerHTML;
                    }
                    
                    if (newPagination && paginationContainer) {
                        // Update pagination
                        paginationContainer.innerHTML = newPagination.innerHTML;
                        paginationContainer.style.display = 'block';
                    } else if (!newPagination && paginationContainer) {
                        // Hide pagination if not needed
                        paginationContainer.style.display = 'none';
                    }
                    
                    // Update URL without page reload
                    const newUrl = `${window.location.pathname}?${params.toString()}`;
                    window.history.pushState({}, '', newUrl);
                    
                    this.showToast('Table refreshed successfully!', 'success');
                })
                .catch(error => {
                    console.error('Error reloading table:', error);
                    this.showToast('Error refreshing table: ' + error.message, 'danger');
                })
                .finally(() => {
                    // Remove loading state
                    tableContainer.classList.remove('table-loading');
                    if (loadingOverlay && loadingOverlay.parentNode) {
                        loadingOverlay.parentNode.removeChild(loadingOverlay);
                    }
                });
            },

            // Delete functions
            deleteAction: function(id) {
                if (!this.config.userPermissions.canDelete) {
                    this.showToast('You do not have permission to delete user actions', 'danger');
                    return;
                }

                this.state.currentDeleteId = id;
                if (this.state.deleteActionModal) {
                    this.state.deleteActionModal.show();
                }
            },

            performDelete: function() {
                if (!this.config.userPermissions.canDelete) {
                    this.showToast('You do not have permission to delete user actions', 'danger');
                    return;
                }

                const id = this.state.currentDeleteId;
                if (!id) {
                    this.showToast('No action selected for deletion', 'danger');
                    return;
                }

                const submitBtn = document.getElementById('confirmDeleteBtn');
                const originalText = submitBtn ? submitBtn.innerHTML : '';

                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Deleting...';
                }

                fetch(`${this.config.baseUrl}/user-actions/delete`, {
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
                        this.showToast('User action deleted successfully!', 'success');
                        if (this.state.deleteActionModal) {
                            this.state.deleteActionModal.hide();
                        }

                        // Reload table content
                        setTimeout(() => {
                            this.reloadTableContent();
                        }, 1000);
                    } else {
                        this.showToast(data.error || 'Failed to delete user action', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.showToast('Error: ' + error.message, 'danger');
                })
                .finally(() => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                    this.state.currentDeleteId = null;
                });
            },

            // Toast notification function
            showToast: function(message, type) {
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
        };

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            UserActionsManager.init();
        });

        // Legacy global functions for backward compatibility (if needed)
        window.deleteAction = function(id) {
            UserActionsManager.deleteAction(id);
        };

        window.loadPage = function(pageNumber) {
            UserActionsManager.loadPage(pageNumber);
        };

        window.applyFilters = function() {
            UserActionsManager.applyFilters();
        };

        window.resetFilters = function() {
            UserActionsManager.resetFilters();
        };

        window.selectAllPermissions = function() {
            UserActionsManager.selectAllPermissions();
        };

        window.clearAllPermissions = function() {
            UserActionsManager.clearAllPermissions();
        };

        window.togglePageActions = function(checkbox) {
            UserActionsManager.togglePageActions(checkbox);
        };

        window.updatePageCheckbox = function(checkbox) {
            UserActionsManager.updatePageCheckbox(checkbox);
        };
    </script>

<?php endif; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
