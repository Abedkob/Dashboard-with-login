<?php
namespace App\Controllers;
use Exception;
use PDO;
use App\Models\UserAction;

class UserActionController
{
    private UserAction $userActionModel;

    private array $allowedActions = [
        'Dashboard' => ['view', 'renew licenses'],
        'License Manager' => ['create', 'read', 'update', 'delete', 'view', 'add payment'],
        'Payments' => ['create', 'read', 'update', 'delete'],
        'Activity logs' => ['view', 'create', 'update', 'delete'],
        'Roles' => ['create', 'read', 'update', 'delete', 'manage permissions']
    ];

    public function __construct(PDO $db)
    {
        $this->userActionModel = new UserAction($db);
    }

    // Track page view action for current user
    public function trackPageView(string $pageName): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->userActionModel->create((int) $_SESSION['user_id'], $pageName, 'view');
        }
    }

    // Track a specific action for current user if allowed
    public function trackAction(string $page, string $action): bool
    {
        if (!isset($_SESSION['user_id']) || !$this->isActionAllowed($page, $action)) {
            return false;
        }
        return $this->userActionModel->create((int) $_SESSION['user_id'], $page, $action);
    }

    // Show activity logs with filters and pagination
    public function showActivityLogs(): void
    {
        $isAdmin = ($_SESSION['user_level'] ?? 0) === 1;

        // Fix: Properly handle user_id parameter conversion
        $userIdParam = $_GET['user_id'] ?? null;
        if ($isAdmin) {
            // For admin users, convert empty string to null, otherwise convert to int
            $userId = ($userIdParam === '' || $userIdParam === null) ? null : (int) $userIdParam;
        } else {
            // For non-admin users, always use their own user_id
            $userId = (int) ($_SESSION['user_id'] ?? 0);
        }

        $page = $_GET['page'] ?? null;
        $action = $_GET['action'] ?? null;
        $pageNumber = max(1, (int) ($_GET['page_number'] ?? 1));
        $perPage = 20;

        // Convert empty strings to null for proper filtering
        $page = ($page === '') ? null : $page;
        $action = ($action === '') ? null : $action;

        $actions = $this->userActionModel->getFilteredActions(
            $userId,
            $page,
            $action,
            $perPage,
            ($pageNumber - 1) * $perPage
        );

        $totalActions = $this->userActionModel->countFilteredActions(
            $userId,
            $page,
            $action
        );

        $availablePages = array_keys($this->allowedActions);
        $availableActions = $this->allowedActions;

        $availableUsers = $isAdmin ? $this->userActionModel->getUsers() : [];

        $canEdit = $isAdmin;    // Customize as needed
        $canDelete = $isAdmin;  // Customize as needed

        require __DIR__ . '/../../views/user-actions/index.php';
    }

    // Show form to create a new user action (AJAX)
    public function createForm(): void
    {
        // Set proper headers
        header('Content-Type: text/html; charset=UTF-8');
        header('Cache-Control: no-cache, must-revalidate');

        try {
            $this->requirePermission('Activity logs', 'create');

            $availablePages = array_keys($this->allowedActions);
            $availableActions = $this->allowedActions;
            $availableUsers = $this->userActionModel->getUsers();

            // Debug logging
            error_log("UserActionController::createForm() - Loading form");
            error_log("Available pages: " . print_r($availablePages, true));
            error_log("Available users count: " . count($availableUsers));

            // Start output buffering
            ob_start();

            // Include the form template
            include __DIR__ . '/../../views/user-actions/_form.php';

            // Get the buffered content
            $formHtml = ob_get_clean();

            // Debug the output
            error_log("Form HTML length: " . strlen($formHtml));

            if (empty(trim($formHtml))) {
                throw new Exception("Form template produced empty output");
            }

            // Output the form
            echo $formHtml;
            exit;

        } catch (Exception $ex) {
            error_log("UserActionController::createForm() - Error: " . $ex->getMessage());
            http_response_code(500);
            echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Error loading form: ' . htmlspecialchars($ex->getMessage()) . '</div>';
            exit;
        }
    }

    // Store new user action from form post
    public function store(): void
    {
        header('Content-Type: application/json');

        try {
            $this->requirePermission('Activity logs', 'create');

            // Validate request method
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
                exit;
            }

            // Read POST data safely
            $userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
            $page = isset($_POST['page']) ? trim($_POST['page']) : '';
            $action = isset($_POST['action']) ? trim($_POST['action']) : '';

            // Validate required fields
            if ($userId === 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Please select a valid user']);
                exit;
            }

            if ($page === '') {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Please select a page/module']);
                exit;
            }

            if ($action === '') {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Please select an action']);
                exit;
            }

            // Check if the action is allowed on the given page
            if (!$this->isActionAllowed($page, $action)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'This action is not allowed for the selected page']);
                exit;
            }

            // Check if user already has this action for this page
            $existingActions = $this->getActionsForUserAndPage($userId, $page);
            if (in_array($action, $existingActions)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'User already has this action for this page']);
                exit;
            }

            // Attempt to create the user action record
            $success = $this->userActionModel->create($userId, $page, $action);

            if (!$success) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to create user action. Please try again.']);
                exit;
            }

            // Success response
            echo json_encode([
                'success' => true,
                'message' => 'User action created successfully'
            ]);
            exit;

        } catch (Exception $ex) {
            http_response_code(500);
            error_log("UserActionController::store() - " . $ex->getMessage());
            echo json_encode(['success' => false, 'error' => 'An unexpected error occurred. Please try again.']);
            exit;
        }
    }

    // Delete a user action by ID
    public function delete(): void
    {
        header('Content-Type: application/json');

        try {
            $this->requirePermission('Activity logs', 'delete');

            // Validate request method
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
                exit;
            }

            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            $id = isset($input['id']) ? (int) $input['id'] : 0;

            if ($id === 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid action ID']);
                exit;
            }

            $success = $this->userActionModel->delete($id);

            if (!$success) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to delete user action']);
                exit;
            }

            echo json_encode([
                'success' => true,
                'message' => 'User action deleted successfully'
            ]);
            exit;

        } catch (Exception $ex) {
            http_response_code(500);
            error_log("UserActionController::delete() - " . $ex->getMessage());
            echo json_encode(['success' => false, 'error' => 'An unexpected error occurred']);
            exit;
        }
    }

    // Get assigned actions for a user and page (AJAX)
    public function getUserActions(): void
    {
        header('Content-Type: application/json');

        $userIdParam = $_GET['user_id'] ?? null;
        $userId = ($userIdParam === '' || $userIdParam === null) ? null : (int) $userIdParam;
        $page = $_GET['page'] ?? null;

        if (!$userId || !$page) {
            echo json_encode(['success' => false, 'error' => 'Missing parameters']);
            return;
        }

        $userActions = $this->getActionsForUserAndPage($userId, $page);

        echo json_encode([
            'success' => true,
            'userActions' => $userActions,
        ]);
    }

    // Update user actions for a page (AJAX)
    public function updateUserActions(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
            return;
        }

        $userIdParam = $_POST['user_id'] ?? null;
        $userId = ($userIdParam === '' || $userIdParam === null) ? null : (int) $userIdParam;
        $page = $_POST['page'] ?? null;
        $actions = $_POST['actions'] ?? [];

        if (!$userId || !$page) {
            echo json_encode(['success' => false, 'error' => 'Missing parameters']);
            return;
        }

        if (!isset($this->allowedActions[$page])) {
            echo json_encode(['success' => false, 'error' => 'Invalid page']);
            return;
        }

        $allowed = $this->allowedActions[$page];
        $actions = array_filter(array_map('trim', (array) $actions));

        foreach ($actions as $action) {
            if (!in_array($action, $allowed, true)) {
                echo json_encode(['success' => false, 'error' => "Invalid action: $action"]);
                return;
            }
        }

        $success = $this->updateActionsForUserAndPage($userId, $page, $actions);

        echo json_encode(['success' => $success]);
    }

    // API method to track an action (POST JSON)
    public function apiTrackAction(): void
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $page = $data['page'] ?? '';
        $action = $data['action'] ?? '';

        $success = $this->trackAction($page, $action);

        echo json_encode(['success' => $success]);
    }

    // --- Helper methods ---

    private function requirePermission(string $page, string $action): void
    {
        if (!isset($_SESSION['user_id'])) {
            throw new Exception('Not authenticated');
        }

        if (!$this->isActionAllowed($page, $action)) {
            throw new Exception('Permission denied');
        }
    }

    private function isActionAllowed(string $page, string $action): bool
    {
        return isset($this->allowedActions[$page]) &&
            in_array($action, $this->allowedActions[$page], true);
    }

    private function getActionsForUserAndPage(int $userId, string $page): array
    {
        // Fetch assigned actions from DB
        return $this->userActionModel->getActionsForUserAndPage($userId, $page);
    }

    public function hasPermission(string $page, string $action): bool
    {
        // Admin shortcut
        if (($_SESSION['user_level'] ?? 0) === 1) {
            return true; // admins have all permissions
        }

        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        $userId = (int) $_SESSION['user_id'];

        // Get the user's assigned actions for this page
        $userActions = $this->getActionsForUserAndPage($userId, $page);

        return in_array($action, $userActions, true);
    }

    private function updateActionsForUserAndPage(int $userId, string $page, array $actions): bool
    {
        return $this->userActionModel->updateActionsForUserAndPage($userId, $page, $actions);
    }
}
