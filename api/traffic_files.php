<?php
/**
 * Traffic Case Files API
 * Upload, list, download, and delete file attachments for traffic cases
 */
error_reporting(0);
ini_set('display_errors', 0);

require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Don't set JSON header for download requests
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($action !== 'download') {
    header('Content-Type: application/json');
}

if (!isLoggedIn()) {
    if ($action === 'download') {
        http_response_code(401);
        exit('Unauthorized');
    }
    jsonResponse(['error' => 'Unauthorized'], 401);
}

$pdo = getDB();
$user = getCurrentUser();

// Allowed file types
$allowedTypes = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'image/jpeg',
    'image/png',
    'image/gif'
];

$allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif'];
$maxFileSize = 20 * 1024 * 1024; // 20MB
$uploadDir = __DIR__ . '/../uploads/traffic/';

// GET - List files for a case OR download a file
if ($method === 'GET') {
    if ($action === 'download') {
        // Download a specific file
        $fileId = intval($_GET['id'] ?? 0);
        if ($fileId <= 0) {
            http_response_code(400);
            exit('Invalid file ID');
        }

        $stmt = $pdo->prepare("SELECT * FROM traffic_case_files WHERE id = ?");
        $stmt->execute([$fileId]);
        $file = $stmt->fetch();

        if (!$file) {
            http_response_code(404);
            exit('File not found');
        }

        $filePath = $uploadDir . $file['case_id'] . '/' . $file['filename'];
        if (!file_exists($filePath)) {
            http_response_code(404);
            exit('File not found on disk');
        }

        // Serve the file
        header('Content-Type: ' . ($file['file_type'] ?: 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache');
        readfile($filePath);
        exit;
    }

    // List files for a case
    $caseId = intval($_GET['case_id'] ?? 0);
    if ($caseId <= 0) {
        jsonResponse(['error' => 'Invalid case ID'], 400);
    }

    $stmt = $pdo->prepare("
        SELECT f.*, u.display_name as uploader_name
        FROM traffic_case_files f
        JOIN users u ON f.uploaded_by = u.id
        WHERE f.case_id = ?
        ORDER BY f.uploaded_at DESC
    ");
    $stmt->execute([$caseId]);
    $files = $stmt->fetchAll();

    jsonResponse(['files' => $files, 'csrf_token' => generateCSRFToken()]);
}

// POST - Upload file
if ($method === 'POST') {
    requireCSRFToken();

    $caseId = intval($_POST['case_id'] ?? 0);
    if ($caseId <= 0) {
        jsonResponse(['error' => 'Invalid case ID'], 400);
    }

    // Verify the case exists
    $stmt = $pdo->prepare("SELECT id FROM traffic_cases WHERE id = ?");
    $stmt->execute([$caseId]);
    if (!$stmt->fetch()) {
        jsonResponse(['error' => 'Traffic case not found'], 404);
    }

    // Check if file was uploaded
    if (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
        jsonResponse(['error' => 'No file selected'], 400);
    }

    $file = $_FILES['file'];

    // Check upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds server limit',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form limit',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write to disk',
        ];
        $msg = $errors[$file['error']] ?? 'Upload error';
        jsonResponse(['error' => $msg], 400);
    }

    // Validate file size
    if ($file['size'] > $maxFileSize) {
        jsonResponse(['error' => 'File too large. Maximum size is 20MB'], 400);
    }

    // Validate file extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExtensions)) {
        jsonResponse(['error' => 'File type not allowed. Allowed: ' . implode(', ', $allowedExtensions)], 400);
    }

    // Validate MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        jsonResponse(['error' => 'File type not allowed'], 400);
    }

    // Create case-specific directory
    $caseDir = $uploadDir . $caseId . '/';
    if (!is_dir($caseDir)) {
        mkdir($caseDir, 0755, true);
    }

    // Generate unique filename
    $storedName = uniqid('file_', true) . '.' . $ext;
    $destPath = $caseDir . $storedName;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        jsonResponse(['error' => 'Failed to save file'], 500);
    }

    // Insert record
    $stmt = $pdo->prepare("
        INSERT INTO traffic_case_files (case_id, filename, original_name, file_type, file_size, uploaded_by)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $caseId,
        $storedName,
        $file['name'],
        $mimeType,
        $file['size'],
        $user['id']
    ]);

    $fileId = $pdo->lastInsertId();

    jsonResponse([
        'success' => true,
        'file' => [
            'id' => $fileId,
            'original_name' => $file['name'],
            'file_size' => $file['size'],
            'file_type' => $mimeType,
            'uploaded_at' => date('Y-m-d H:i:s')
        ]
    ]);
}

// DELETE - Remove file
if ($method === 'DELETE') {
    requireCSRFToken();

    $data = json_decode(file_get_contents('php://input'), true);
    $fileId = intval($data['id'] ?? 0);

    if ($fileId <= 0) {
        jsonResponse(['error' => 'Invalid file ID'], 400);
    }

    // Get file info
    $stmt = $pdo->prepare("SELECT * FROM traffic_case_files WHERE id = ?");
    $stmt->execute([$fileId]);
    $file = $stmt->fetch();

    if (!$file) {
        jsonResponse(['error' => 'File not found'], 404);
    }

    // Delete physical file
    $filePath = $uploadDir . $file['case_id'] . '/' . $file['filename'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    // Delete database record
    $stmt = $pdo->prepare("DELETE FROM traffic_case_files WHERE id = ?");
    $stmt->execute([$fileId]);

    jsonResponse(['success' => true]);
}
