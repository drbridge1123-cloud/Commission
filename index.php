<?php
/**
 * Login Page - Slate Modern Theme
 * Bridge Law & Associates - Commission Calculator
 */
require_once 'includes/auth.php';

// If already logged in, redirect
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin.php');
    } else {
        header('Location: BridgeLaw.php');
    }
    exit;
}

$error = '';
$showTimeout = isset($_GET['timeout']);

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please refresh and try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'Please enter username and password';
        } else {
            $result = loginUser($username, $password);

            if ($result['success']) {
                if (isAdmin()) {
                    header('Location: admin.php');
                } else {
                    header('Location: BridgeLaw.php');
                }
                exit;
            } else {
                $error = $result['error'] ?? 'Invalid username or password';
            }
        }
    }
}

// Generate CSRF token
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bridge Law Commission Calculator</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-wrapper {
            display: flex;
            width: 100%;
            max-width: 900px;
            min-height: 540px;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        }
        
        /* Left Panel */
        .login-left {
            flex: 1;
            background: linear-gradient(135deg, #475569 0%, #334155 100%);
            padding: 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-left::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, transparent 70%);
        }
        
        .login-left::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -30%;
            width: 80%;
            height: 80%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, transparent 70%);
        }
        
        .accent-bar {
            width: 60px;
            height: 4px;
            background: #6366f1;
            border-radius: 2px;
            margin-bottom: 24px;
            position: relative;
            z-index: 1;
        }
        
        .login-left h2 {
            color: white;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 16px;
            line-height: 1.2;
            position: relative;
            z-index: 1;
        }
        
        .login-left p {
            color: #cbd5e1;
            font-size: 16px;
            line-height: 1.7;
            position: relative;
            z-index: 1;
        }
        
        .login-features {
            margin-top: 32px;
            position: relative;
            z-index: 1;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #94a3b8;
            font-size: 14px;
            margin-bottom: 12px;
        }
        
        .feature-icon {
            width: 20px;
            height: 20px;
            background: rgba(99, 102, 241, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .feature-icon svg {
            width: 12px;
            height: 12px;
            color: #6366f1;
        }
        
        /* Right Panel */
        .login-right {
            flex: 1;
            background: white;
            padding: 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-logo {
            width: 48px;
            height: 48px;
            background: #6366f1;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 32px;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.3);
        }
        
        .login-logo span {
            color: white;
            font-size: 22px;
            font-weight: 700;
        }
        
        .login-title {
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 8px;
        }
        
        .login-subtitle {
            color: #64748b;
            margin-bottom: 32px;
            font-size: 15px;
        }
        
        /* Alert Messages */
        .alert {
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .alert-warning {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        
        .alert svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }
        
        /* Form */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #475569;
            margin-bottom: 8px;
        }
        
        .form-input {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 15px;
            color: #1e293b;
            transition: all 0.2s ease;
            background: #f8fafc;
        }
        
        .form-input::placeholder {
            color: #94a3b8;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #6366f1;
            background: white;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: #6366f1;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 8px;
        }
        
        .btn-submit:hover {
            background: #4f46e5;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }
        
        .btn-submit:active {
            transform: translateY(0);
        }
        
        .login-footer {
            text-align: center;
            color: #94a3b8;
            font-size: 13px;
            margin-top: 32px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .login-wrapper {
                flex-direction: column;
                max-width: 420px;
            }
            
            .login-left {
                padding: 32px;
                min-height: auto;
            }
            
            .login-left h2 {
                font-size: 24px;
            }
            
            .login-features {
                display: none;
            }
            
            .login-right {
                padding: 32px;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <!-- Left Panel -->
        <div class="login-left">
            <div class="accent-bar"></div>
            <h2>Welcome Back</h2>
            <p>Access your commission dashboard to track cases, manage payments, and view detailed reports.</p>
            
            <div class="login-features">
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    Track case commissions in real-time
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    Generate detailed reports
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    Secure approval workflow
                </div>
            </div>
        </div>
        
        <!-- Right Panel -->
        <div class="login-right">
            <div class="login-logo">
                <span>B</span>
            </div>
            
            <h1 class="login-title">Sign In</h1>
            <p class="login-subtitle">Bridge Law & Associates</p>
            
            <?php if ($showTimeout): ?>
            <div class="alert alert-warning">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Your session has expired. Please log in again.
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="alert alert-error">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-input" required autocomplete="username"
                        placeholder="Enter your username">
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-input" required autocomplete="current-password"
                        placeholder="Enter your password">
                </div>

                <button type="submit" class="btn-submit">Sign In</button>
            </form>

            <p class="login-footer">© 2025 Bridge Law & Associates</p>
        </div>
    </div>
</body>
</html>
