<?php
declare(strict_types=1);
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

try {
    getDB()->query('SELECT 1');
    echo json_encode(['status' => 'ok', 'message' => 'Database connected']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
}