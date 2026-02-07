<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once dirname(__DIR__) . '/includes/auth.php';

if (!isLoggedIn()) {
    jsonResponse(['error' => 'Unauthorized', 'cases' => [], 'stats' => []], 401);
}

requireRateLimit('api_litigation', 60, 60);

$pdo = getDB();
$user = getCurrentUser();
$method = $_SERVER['REQUEST_METHOD'];

// Auto-create/update table schema
try {
    $check = $pdo->query("SHOW TABLES LIKE 'litigation_cases'");
    if ($check->rowCount() === 0) {
        $pdo->exec("CREATE TABLE litigation_cases (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            case_number VARCHAR(100) NOT NULL,
            client_name VARCHAR(255) NOT NULL,
            injury_type VARCHAR(100),
            opposing_insurance VARCHAR(255),
            litigation_stage VARCHAR(50) DEFAULT 'filed',
            next_deadline DATE,
            settlement_amount DECIMAL(12,2),
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id)
        )");
    } else {
        // Add missing columns if they don't exist
        $columns = $pdo->query("SHOW COLUMNS FROM litigation_cases")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('injury_type', $columns)) {
            $pdo->exec("ALTER TABLE litigation_cases ADD COLUMN injury_type VARCHAR(100) AFTER client_name");
        }
        if (!in_array('opposing_insurance', $columns)) {
            $pdo->exec("ALTER TABLE litigation_cases ADD COLUMN opposing_insurance VARCHAR(255) AFTER injury_type");
        }
        if (!in_array('notes', $columns)) {
            $pdo->exec("ALTER TABLE litigation_cases ADD COLUMN notes TEXT");
        }
        if (!in_array('next_deadline', $columns)) {
            $pdo->exec("ALTER TABLE litigation_cases ADD COLUMN next_deadline DATE");
        }
        if (!in_array('settlement_amount', $columns)) {
            $pdo->exec("ALTER TABLE litigation_cases ADD COLUMN settlement_amount DECIMAL(12,2)");
        }
    }
} catch (Exception $e) {}

if ($method === 'GET') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM litigation_cases WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user['id']]);
        $cases = $stmt->fetchAll();

        foreach ($cases as &$c) {
            $c['days_open'] = (new DateTime($c['created_at']))->diff(new DateTime())->days;
        }

        jsonResponse(['success' => true, 'cases' => $cases, 'stats' => [], 'csrf_token' => generateCSRFToken()]);
    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage(), 'cases' => [], 'stats' => []], 500);
    }
}

requireCSRFToken();

if ($method === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("INSERT INTO litigation_cases (user_id, case_number, client_name, injury_type, opposing_insurance, litigation_stage, next_deadline, settlement_amount, notes) VALUES (?, ?, ?, ?, ?, 'filed', ?, ?, ?)");
        $stmt->execute([$user['id'], $data['case_number'] ?? '', $data['client_name'] ?? '', $data['injury_type'] ?? '', $data['opposing_insurance'] ?? '', $data['next_deadline'] ?? null, $data['settlement_amount'] ?? null, $data['notes'] ?? '']);
        jsonResponse(['success' => true, 'case_id' => $pdo->lastInsertId()]);
    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()], 500);
    }
}

if ($method === 'PUT') {
    try {
        $id = intval($_GET['id'] ?? 0);
        $data = json_decode(file_get_contents('php://input'), true);
        $updates = [];
        $params = [];
        foreach (['case_number', 'client_name', 'injury_type', 'opposing_insurance', 'litigation_stage', 'next_deadline', 'settlement_amount', 'notes'] as $f) {
            if (isset($data[$f])) { $updates[] = "$f = ?"; $params[] = $data[$f]; }
        }
        if ($updates) {
            $params[] = $id;
            $params[] = $user['id'];
            $pdo->prepare("UPDATE litigation_cases SET " . implode(',', $updates) . " WHERE id = ? AND user_id = ?")->execute($params);
        }
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()], 500);
    }
}

if ($method === 'DELETE') {
    try {
        $id = intval($_GET['id'] ?? 0);
        $pdo->prepare("DELETE FROM litigation_cases WHERE id = ? AND user_id = ?")->execute([$id, $user['id']]);
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()], 500);
    }
}

jsonResponse(['error' => 'Method not allowed'], 405);
