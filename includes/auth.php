<?php
/**
 * Authentication and Security Functions
 * Includes session management, CSRF protection, rate limiting, and password policies
 */

require_once __DIR__ . '/../config/database.php';

// ============================================
// HTTPS Enforcement (Production Only)
// ============================================

/**
 * Redirect to HTTPS if not already secure
 * Skips redirect for localhost/development environments
 */
function enforceHTTPS() {
    // Skip for localhost/development
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $localHosts = ['localhost', '127.0.0.1', '::1'];

    // Check if running on localhost
    $isLocalhost = false;
    foreach ($localHosts as $local) {
        if (strpos($host, $local) === 0) {
            $isLocalhost = true;
            break;
        }
    }

    // Skip if localhost or already HTTPS
    if ($isLocalhost) {
        return;
    }

    // Skip if APP_ENV is development
    if (env('APP_ENV', 'production') === 'development') {
        return;
    }

    // Redirect to HTTPS if not secure
    if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
        $redirectUrl = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $redirectUrl);
        exit;
    }
}

// Enforce HTTPS in production
enforceHTTPS();

// ============================================
// Debug Mode Configuration
// ============================================

/**
 * Configure error display based on APP_DEBUG setting
 * In production: hide errors from users, log to file
 * In development: show all errors for debugging
 */
function configureErrorHandling() {
    $debug = env('APP_DEBUG', false);

    if ($debug === true || $debug === 'true' || $debug === '1') {
        // Development mode - show all errors
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
    } else {
        // Production mode - hide errors, log them
        error_reporting(E_ALL);
        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);
        ini_set('log_errors', 1);

        // Set error log path (optional - uses default if not set)
        $logPath = __DIR__ . '/../logs/error.log';
        $logDir = dirname($logPath);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        if (is_writable($logDir)) {
            ini_set('error_log', $logPath);
        }
    }
}

// Configure error handling
configureErrorHandling();

// Configure secure session settings before starting
function configureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Set secure session parameters
        $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';

        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path' => '/',
            'domain' => '',
            'secure' => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        // Use strict mode to prevent session fixation
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_only_cookies', 1);

        session_start();

        // Regenerate session ID periodically
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 300) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
}

// Initialize session
configureSession();

// ============================================
// CSRF Protection Functions
// ============================================

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time']) ||
        (time() - $_SESSION['csrf_token_time']) > CSRF_TOKEN_LIFETIME) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validateCSRFToken($token) {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }

    // Check if token has expired
    if (empty($_SESSION['csrf_token_time']) ||
        (time() - $_SESSION['csrf_token_time']) > CSRF_TOKEN_LIFETIME) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF token from request (header or body)
 */
function getCSRFTokenFromRequest() {
    // Check header first (for AJAX requests)
    // Use case-insensitive header lookup (proxies like ngrok may change case)
    $headers = getallheaders();
    $headersLower = array_change_key_case($headers, CASE_LOWER);
    if (isset($headersLower['x-csrf-token'])) {
        return $headersLower['x-csrf-token'];
    }

    // Check POST body
    if (isset($_POST['csrf_token'])) {
        return $_POST['csrf_token'];
    }

    // Check JSON body
    $input = file_get_contents('php://input');
    if ($input) {
        $data = json_decode($input, true);
        if (isset($data['csrf_token'])) {
            return $data['csrf_token'];
        }
    }

    return null;
}

/**
 * Require valid CSRF token for state-changing requests
 */
function requireCSRFToken() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' ||
        $_SERVER['REQUEST_METHOD'] === 'PUT' ||
        $_SERVER['REQUEST_METHOD'] === 'DELETE') {

        $token = getCSRFTokenFromRequest();
        if (!validateCSRFToken($token)) {
            jsonResponse(['error' => 'Invalid or missing CSRF token'], 403);
        }
    }
}

// ============================================
// Rate Limiting Functions
// ============================================

/**
 * Check rate limit for an action
 */
function checkRateLimit($action = 'default', $maxRequests = null, $windowSeconds = null) {
    $maxRequests = $maxRequests ?? RATE_LIMIT_REQUESTS;
    $windowSeconds = $windowSeconds ?? RATE_LIMIT_WINDOW;

    $key = 'rate_limit_' . $action;
    $now = time();

    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [
            'count' => 0,
            'window_start' => $now
        ];
    }

    $data = &$_SESSION[$key];

    // Reset if window has passed
    if ($now - $data['window_start'] > $windowSeconds) {
        $data['count'] = 0;
        $data['window_start'] = $now;
    }

    $data['count']++;

    if ($data['count'] > $maxRequests) {
        return false;
    }

    return true;
}

/**
 * Require rate limit check, respond with error if exceeded
 */
function requireRateLimit($action = 'default', $maxRequests = null, $windowSeconds = null) {
    if (!checkRateLimit($action, $maxRequests, $windowSeconds)) {
        jsonResponse([
            'error' => 'Too many requests. Please try again later.',
            'retry_after' => RATE_LIMIT_WINDOW
        ], 429);
    }
}

// ============================================
// Password Policy Functions
// ============================================

/**
 * Validate password against policy
 * Returns array of validation errors, empty if valid
 */
function validatePassword($password) {
    $errors = [];

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }

    if (strlen($password) > 128) {
        $errors[] = 'Password must not exceed 128 characters';
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    }

    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    }

    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number';
    }

    return $errors;
}

// ============================================
// Input Validation Functions
// ============================================

/**
 * Sanitize string input
 */
function sanitizeString($input, $maxLength = 255) {
    if (!is_string($input)) {
        return '';
    }
    $input = trim($input);
    $input = substr($input, 0, $maxLength);
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

/**
 * Validate and sanitize numeric input
 */
function sanitizeNumber($input, $min = null, $max = null, $decimals = 2) {
    $number = filter_var($input, FILTER_VALIDATE_FLOAT);
    if ($number === false) {
        return 0;
    }

    if ($min !== null && $number < $min) {
        $number = $min;
    }

    if ($max !== null && $number > $max) {
        $number = $max;
    }

    return round($number, $decimals);
}

/**
 * Validate email
 */
function sanitizeEmail($input) {
    $email = filter_var(trim($input), FILTER_VALIDATE_EMAIL);
    return $email ?: '';
}

// ============================================
// Authentication Functions
// ============================================

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if the current user has a specific permission
 * Admins always have all permissions
 */
function hasPermission($permission) {
    if (isAdmin()) return true;
    $permissions = $_SESSION['permissions'] ?? [];
    return !empty($permissions[$permission]);
}

/**
 * Get current user info
 */
function getCurrentUser() {
    if (!isLoggedIn()) return null;

    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? '',
        'display_name' => $_SESSION['display_name'] ?? '',
        'role' => $_SESSION['role'] ?? 'employee',
        'commission_rate' => $_SESSION['commission_rate'] ?? 10.00,
        'uses_presuit_offer' => $_SESSION['uses_presuit_offer'] ?? 1,
        'permissions' => $_SESSION['permissions'] ?? []
    ];
}

/**
 * Require login - redirect if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        if (isAjaxRequest()) {
            jsonResponse(['error' => 'Unauthorized'], 401);
        }
        header('Location: index.php');
        exit;
    }
}

/**
 * Require admin - redirect if not admin
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        if (isAjaxRequest()) {
            jsonResponse(['error' => 'Forbidden - Admin access required'], 403);
        }
        header('Location: BridgeLaw.php');
        exit;
    }
}

/**
 * Check if request is AJAX
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Login user with rate limiting
 */
function loginUser($username, $password) {
    // Apply strict rate limiting for login attempts
    $rateLimitKey = 'login_' . md5($_SERVER['REMOTE_ADDR'] ?? 'unknown');

    if (!checkRateLimit($rateLimitKey, 5, 300)) { // 5 attempts per 5 minutes
        return ['success' => false, 'error' => 'Too many login attempts. Please try again in 5 minutes.'];
    }

    $pdo = getDB();

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
    $stmt->execute([sanitizeString($username, 50)]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Regenerate session ID on login to prevent session fixation
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'] ?? '';
        $_SESSION['display_name'] = $user['display_name'] ?? '';
        $_SESSION['role'] = $user['role'] ?? 'employee';
        $_SESSION['commission_rate'] = $user['commission_rate'] ?? 10.00;
        $_SESSION['uses_presuit_offer'] = $user['uses_presuit_offer'] ?? 1;
        $_SESSION['permissions'] = json_decode($user['permissions'] ?? '{}', true) ?: [];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();

        // Reset rate limit on successful login
        unset($_SESSION[$rateLimitKey]);

        // Log successful login
        logAudit('login', 'users', $user['id'], null, ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']);

        return ['success' => true];
    }

    // Log failed login attempt
    logAudit('login_failed', 'users', null, null, [
        'username' => $username,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);

    return ['success' => false, 'error' => 'Invalid username or password'];
}

/**
 * Logout user
 */
function logoutUser() {
    // Log logout
    if (isLoggedIn()) {
        logAudit('logout', 'users', $_SESSION['user_id'], null, null);
    }

    // Clear session data
    $_SESSION = [];

    // Delete session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    // Destroy session
    session_destroy();
}

/**
 * Check session timeout
 */
function checkSessionTimeout() {
    if (isLoggedIn()) {
        $lastActivity = $_SESSION['last_activity'] ?? 0;
        if (time() - $lastActivity > SESSION_LIFETIME) {
            logoutUser();
            if (isAjaxRequest()) {
                jsonResponse(['error' => 'Session expired'], 401);
            }
            header('Location: index.php?timeout=1');
            exit;
        }
        $_SESSION['last_activity'] = time();
    }
}

// ============================================
// Audit Logging Functions
// ============================================

/**
 * Log an audit event
 */
function logAudit($action, $tableName = null, $recordId = null, $oldData = null, $newData = null) {
    try {
        $pdo = getDB();

        // Check if audit_logs table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'audit_logs'");
        if ($stmt->rowCount() === 0) {
            return; // Table doesn't exist yet
        }

        $userId = $_SESSION['user_id'] ?? null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        $stmt = $pdo->prepare("
            INSERT INTO audit_logs (user_id, action, table_name, record_id, old_data, new_data, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $userId,
            $action,
            $tableName,
            $recordId,
            $oldData ? json_encode($oldData) : null,
            $newData ? json_encode($newData) : null,
            $ipAddress,
            substr($userAgent, 0, 255)
        ]);
    } catch (Exception $e) {
        // Silently fail - audit logging should not break the application
        error_log("Audit log error: " . $e->getMessage());
    }
}

// ============================================
// Response Helpers
// ============================================

/**
 * JSON response helper
 */
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Add security headers
 */
function addSecurityHeaders() {
    // Basic security headers
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');

    // HSTS for HTTPS connections
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }

    // Content Security Policy
    // Allows: self, Tailwind CSS, Chart.js, SheetJS, inline styles
    $csp = [
        "default-src 'self'",
        "script-src 'self' 'unsafe-inline' 'unsafe-eval' cdn.tailwindcss.com cdn.jsdelivr.net cdnjs.cloudflare.com",
        "style-src 'self' 'unsafe-inline' cdn.tailwindcss.com fonts.googleapis.com",
        "font-src 'self' fonts.gstatic.com",
        "img-src 'self' data: blob:",
        "connect-src 'self' cdn.jsdelivr.net cdnjs.cloudflare.com",
        "frame-ancestors 'self'",
        "form-action 'self'",
        "base-uri 'self'"
    ];
    header('Content-Security-Policy: ' . implode('; ', $csp));
}

// Add security headers
addSecurityHeaders();

// Check session timeout
checkSessionTimeout();
?>
