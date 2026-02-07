<?php
/**
 * Messages API
 * Secure messaging between admin and employees
 */
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

// Rate limiting
requireRateLimit('api_messages', 30, 60); // 30 requests per minute

$pdo = getDB();
$user = getCurrentUser();
$method = $_SERVER['REQUEST_METHOD'];

// GET - Fetch messages for current user (both sent and received)
if ($method === 'GET') {
    // Get received messages (with both from_name and to_name)
    $sql = "SELECT m.*,
                   uf.display_name as from_name,
                   ut.display_name as to_name,
                   'received' as direction
            FROM messages m
            JOIN users uf ON m.from_user_id = uf.id
            JOIN users ut ON m.to_user_id = ut.id
            WHERE m.to_user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user['id']]);
    $received = $stmt->fetchAll();

    // Get sent messages (with both from_name and to_name)
    $sql = "SELECT m.*,
                   uf.display_name as from_name,
                   ut.display_name as to_name,
                   'sent' as direction
            FROM messages m
            JOIN users uf ON m.from_user_id = uf.id
            JOIN users ut ON m.to_user_id = ut.id
            WHERE m.from_user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user['id']]);
    $sent = $stmt->fetchAll();

    // Merge and sort by date
    $messages = array_merge($received, $sent);
    usort($messages, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

    // Get unread count (only for received messages)
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM messages WHERE to_user_id = ? AND is_read = 0");
    $stmt->execute([$user['id']]);
    $unreadCount = $stmt->fetch()['count'];

    jsonResponse([
        'messages' => $messages,
        'unread_count' => $unreadCount,
        'csrf_token' => generateCSRFToken()
    ]);
}

// Require CSRF for state-changing operations
requireCSRFToken();

// POST - Send new message (admin to employee or employee to admin)
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $toUserId = intval($data['to_user_id'] ?? 0);
    $subject = sanitizeString($data['subject'] ?? '', 200);
    $message = sanitizeString($data['message'] ?? '', 5000);

    if ($toUserId <= 0 || empty($subject) || empty($message)) {
        jsonResponse(['error' => 'Recipient, subject and message are required'], 400);
    }

    // Check if recipient exists
    $stmt = $pdo->prepare("SELECT id, role, display_name FROM users WHERE id = ? AND is_active = 1");
    $stmt->execute([$toUserId]);
    $recipient = $stmt->fetch();

    if (!$recipient) {
        jsonResponse(['error' => 'Invalid recipient'], 404);
    }

    // Validate: Admin can send to employees, employees can send to admin
    if (isAdmin()) {
        if ($recipient['role'] !== 'employee') {
            jsonResponse(['error' => 'Admins can only send messages to employees'], 403);
        }
    } else {
        if ($recipient['role'] !== 'admin') {
            jsonResponse(['error' => 'Employees can only send messages to admins'], 403);
        }
    }

    $stmt = $pdo->prepare("
        INSERT INTO messages (from_user_id, to_user_id, subject, message)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([
        $user['id'],
        $toUserId,
        $subject,
        $message
    ]);

    $newId = $pdo->lastInsertId();

    // Audit log
    logAudit('send_message', 'messages', $newId, null, [
        'to_user' => $recipient['display_name'],
        'subject' => $subject
    ]);

    jsonResponse(['success' => true, 'id' => $newId]);
}

// PUT - Mark message(s) as read
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $messageId = intval($data['id'] ?? 0);
    $markAll = !empty($data['mark_all']);

    if ($markAll) {
        $stmt = $pdo->prepare("
            UPDATE messages
            SET is_read = 1, read_at = NOW()
            WHERE to_user_id = ? AND is_read = 0
        ");
        $stmt->execute([$user['id']]);

        logAudit('mark_all_read', 'messages', null, null, ['count' => $stmt->rowCount()]);

        jsonResponse(['success' => true, 'marked' => $stmt->rowCount()]);
    } else {
        if ($messageId <= 0) {
            jsonResponse(['error' => 'Invalid message ID'], 400);
        }

        // Check if message belongs to user
        $stmt = $pdo->prepare("SELECT id FROM messages WHERE id = ? AND to_user_id = ?");
        $stmt->execute([$messageId, $user['id']]);
        if (!$stmt->fetch()) {
            jsonResponse(['error' => 'Message not found'], 404);
        }

        $stmt = $pdo->prepare("
            UPDATE messages
            SET is_read = 1, read_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$messageId]);

        jsonResponse(['success' => true]);
    }
}

// DELETE - Delete message (recipient only)
if ($method === 'DELETE') {
    $messageId = intval($_GET['id'] ?? 0);

    if ($messageId <= 0) {
        jsonResponse(['error' => 'Invalid message ID'], 400);
    }

    // Check if message belongs to user
    $stmt = $pdo->prepare("SELECT id, subject FROM messages WHERE id = ? AND to_user_id = ?");
    $stmt->execute([$messageId, $user['id']]);
    $message = $stmt->fetch();

    if (!$message) {
        jsonResponse(['error' => 'Message not found'], 404);
    }

    $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->execute([$messageId]);

    logAudit('delete_message', 'messages', $messageId, ['subject' => $message['subject']], null);

    jsonResponse(['success' => true]);
}
?>
