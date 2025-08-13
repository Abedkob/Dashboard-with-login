<?php

namespace App\Controllers;

use Exception;
use PDO;
use App\Models\UserAction;

if (!class_exists('App\Controllers\UserActionController')) {
    class UserActionController
    {
        private UserAction $userActionModel;

        private array $allowedActions = [
            'Dashboard' => ['view', 'renew licenses'],
            'License Manager' => ['create', 'view', 'update', 'delete', 'add payment'],
            'Payments' => ['create', 'view', 'update', 'delete'],
            'Activity logs' => ['view'],
            'Roles' => ['create', 'view', 'delete']
        ];

        // Map routes to required permissions
        private array $routePermissions = [
            '/dashboard' => ['page' => 'Dashboard', 'action' => 'view'],
            '/activation-codes' => ['page' => 'License Manager', 'action' => 'view'],
            '/activation-codes/create' => ['page' => 'License Manager', 'action' => 'create'],
            '/activation-codes/edit' => ['page' => 'License Manager', 'action' => 'update'],
            '/activation-codes/delete' => ['page' => 'License Manager', 'action' => 'delete'],
            '/payments-manager' => ['page' => 'Payments', 'action' => 'view'],
            '/payments-manager/create' => ['page' => 'Payments', 'action' => 'create'],
            '/payments-manager/edit' => ['page' => 'Payments', 'action' => 'update'],
            '/payments-manager/delete' => ['page' => 'Payments', 'action' => 'delete'],
            '/logs' => ['page' => 'Activity logs', 'action' => 'view'],
            '/user-actions/activity-logs' => ['page' => 'Roles', 'action' => 'view'],
            '/user-actions/create' => ['page' => 'Roles', 'action' => 'create'],
            '/user-actions/delete' => ['page' => 'Roles', 'action' => 'delete']
        ];

        public function __construct(PDO $db)
        {
            $this->userActionModel = new UserAction($db);
        }

        /**
         * Check if current user has permission for a specific action
         */
        public function hasPermission(string $page, string $action): bool
        {
            // Admin users have all permissions
            if (($_SESSION['user_level'] ?? 0) === 1) {
                return true;
            }

            if (!isset($_SESSION['user_id'])) {
                return false;
            }

            $userId = (int) $_SESSION['user_id'];
            return $this->userActionModel->hasPermission($userId, $page, $action);
        }

        /**
         * Check if current user has permission to access a route
         */
        public function checkRoutePermission(string $route): bool
        {
            // Admin users have all permissions
            if (($_SESSION['user_level'] ?? 0) === 1) {
                return true;
            }

            // Check if user is authenticated
            if (!isset($_SESSION['user_id'])) {
                return false;
            }

            $userId = (int) $_SESSION['user_id'];

            // Check if route requires specific permission
            if (!isset($this->routePermissions[$route])) {
                // If route is not in permission map, allow access (for backward compatibility)
                return true;
            }

            $requiredPermission = $this->routePermissions[$route];

            return $this->userActionModel->hasPermission(
                $userId,
                $requiredPermission['page'],
                $requiredPermission['action']
            );
        }

        /**
         * Require permission for current user - throws exception if not authorized
         */
        public function requirePermission(string $page, string $action): void
        {
            // Admin users have all permissions
            if (($_SESSION['user_level'] ?? 0) === 1) {
                return;
            }

            if (!isset($_SESSION['user_id'])) {
                throw new Exception('Not authenticated');
            }

            $userId = (int) $_SESSION['user_id'];

            if (!$this->userActionModel->hasPermission($userId, $page, $action)) {
                throw new Exception("Access denied. You don't have permission to $action on $page");
            }
        }

        /**
         * Store new user action from form post with detailed error handling
         */
        public function store(): void
        {
            header('Content-Type: application/json');

            try {
                // Check permission to create user actions
                $this->requirePermission('Roles', 'create');

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

                error_log("UserActionController::store() - Received data: user_id=$userId, page='$page', action='$action'");

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
                    echo json_encode(['success' => false, 'error' => "Action '$action' is not allowed for page '$page'. Available actions: " . implode(', ', $this->allowedActions[$page] ?? [])]);
                    exit;
                }

                // Check if user already has this action for this page
                $existingActions = $this->userActionModel->getActionsForUserAndPage($userId, $page);
                error_log("UserActionController::store() - Existing actions for user $userId on page '$page': " . implode(', ', $existingActions));

                if (in_array($action, $existingActions)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'User already has this action for this page']);
                    exit;
                }

                error_log("UserActionController::store() - About to create user action");

                // Attempt to create the user action record
                $result = $this->userActionModel->create($userId, $page, $action);

                if (!$result['success']) {
                    error_log("UserActionController::store() - Create failed: " . $result['error']);
                    http_response_code(500);
                    echo json_encode(['success' => false, 'error' => $result['error']]);
                    exit;
                }

                error_log("UserActionController::store() - Successfully created user action with ID: " . $result['id']);

                // Success response
                echo json_encode([
                    'success' => true,
                    'message' => 'User action created successfully',
                    'id' => $result['id']
                ]);
                exit;

            } catch (Exception $ex) {
                http_response_code(500);
                error_log("UserActionController::store() - Exception: " . $ex->getMessage());
                error_log("UserActionController::store() - Stack trace: " . $ex->getTraceAsString());
                echo json_encode(['success' => false, 'error' => 'An unexpected error occurred: ' . $ex->getMessage()]);
                exit;
            }
        }

        /**
         * Show activity logs with filters and pagination
         */
        public function showActivityLogs(): void
        {
            try {
                // Check permission to view activity logs
                $this->requirePermission('Roles', 'view');

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

                // Permission checks for UI elements
                $canCreate = $this->hasPermission('Roles', 'create');
                $canView = $this->hasPermission('Roles', 'view');
                $canDelete = $this->hasPermission('Roles', 'delete');

                require __DIR__ . '/../../views/user-actions/index.php';
            } catch (Exception $e) {
                error_log("UserActionController::showActivityLogs() - " . $e->getMessage());
                http_response_code(403);

                // Show access denied page instead of plain text
                $errorMessage = "Access denied: " . $e->getMessage();
                require __DIR__ . '/../../views/errors/403.php';
            }
        }

        /**
         * Delete a user action by ID
         */
        public function delete(): void
        {
            header('Content-Type: application/json');

            try {
                // Check permission to delete user actions
                $this->requirePermission('Roles', 'delete');

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
                echo json_encode(['success' => false, 'error' => 'An unexpected error occurred: ' . $ex->getMessage()]);
                exit;
            }
        }

        /**
         * Create form for AJAX loading
         */
        public function createForm(): void
        {
            header('Content-Type: text/html; charset=UTF-8');
            header('Cache-Control: no-cache, must-revalidate');

            try {
                // Check permission to create user actions
                $this->requirePermission('Roles', 'create');

                $availablePages = array_keys($this->allowedActions);
                $availableActions = $this->allowedActions;
                $availableUsers = $this->userActionModel->getUsers();

                // Make BASE_URL available for the form
                if (!defined('BASE_URL')) {
                    define('BASE_URL', '/Practice_php/public');
                }

                error_log("UserActionController::createForm() - Loading form");
                error_log("Available pages: " . print_r($availablePages, true));
                error_log("Available users count: " . count($availableUsers));

                // Include the form template
                require __DIR__ . '/../../views/user-actions/_form.php';

            } catch (Exception $ex) {
                error_log("UserActionController::createForm() - Error: " . $ex->getMessage());
                http_response_code(500);
                echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Error loading form: ' . htmlspecialchars($ex->getMessage()) . '</div>';
            }
        }

        /**
         * Get user permissions (for debugging or API)
         */
        public function getUserPermissions(): void
        {
            header('Content-Type: application/json');

            try {
                if (!isset($_SESSION['user_id'])) {
                    http_response_code(401);
                    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
                    exit;
                }

                $userId = (int) $_SESSION['user_id'];
                $permissions = $this->userActionModel->getUserPermissions($userId);

                echo json_encode([
                    'success' => true,
                    'permissions' => $permissions
                ]);

            } catch (Exception $e) {
                error_log("UserActionController::getUserPermissions() - " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'An unexpected error occurred']);
            }
        }

        // Helper methods
        private function isActionAllowed(string $page, string $action): bool
        {
            return isset($this->allowedActions[$page]) &&
                in_array($action, $this->allowedActions[$page], true);
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
            $result = $this->userActionModel->create((int) $_SESSION['user_id'], $page, $action);
            return $result['success'];
        }
    }
}
