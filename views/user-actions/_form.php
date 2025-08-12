<form id="userActionForm" method="post" action="<?= BASE_URL ?>/user-actions/store" class="needs-validation" novalidate>
    <!-- User Selection -->
    <div class="mb-4">
        <label for="user_id" class="form-label fw-semibold">
            <i class="fas fa-user me-2 text-primary"></i>Select User
        </label>
        <select name="user_id" id="user_id" class="form-select shadow-sm" required>
            <option value="" disabled selected>-- Choose a user --</option>
            <?php foreach ($availableUsers as $user): ?>
                    <option value="<?= (int) $user['id'] ?>" data-user-id="<?= (int) $user['id'] ?>">
                        <?= htmlspecialchars($user['username'] ?? 'User #' . $user['id']) ?>
                        <small class="text-muted">(ID: <?= (int) $user['id'] ?>)</small>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pageSelect = document.getElementById('page');
    const actionSelect = document.getElementById('action');
    const actionDescription = document.getElementById('actionDescription');
    const actionDescriptionText = document.getElementById('actionDescriptionText');
    
    const allActions = <?= json_encode($availableActions) ?>;
    
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

    function populateActions(selectedPage) {
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

    // Handle page selection change
    if (pageSelect) {
        pageSelect.addEventListener('change', function() {
            const selectedPage = this.value;
            populateActions(selectedPage);
        });
    }
    
    // Handle action selection change
    if (actionSelect) {
        actionSelect.addEventListener('change', function() {
            const selectedAction = this.value;
            if (selectedAction && actionDescriptions[selectedAction] && actionDescription && actionDescriptionText) {
                actionDescriptionText.textContent = actionDescriptions[selectedAction];
                actionDescription.style.display = 'block';
            } else if (actionDescription) {
                actionDescription.style.display = 'none';
            }
        });
    }

    // Initialize on page load
    if (pageSelect && pageSelect.value) {
        populateActions(pageSelect.value);
    }

    // Form validation
    const form = document.getElementById('userActionForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission
            
            // The actual submission will be handled by the parent page's JavaScript
            console.log('Form submit prevented - will be handled by modal JavaScript');
        });
    }
});
</script>
