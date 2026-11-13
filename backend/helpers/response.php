<?php
declare(strict_types=1);

function sendJson(int $status, array $data): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function sendError(int $status, string $message): never
{
    sendJson($status, ['status' => 'error', 'message' => $message]);
}

function sendSuccess(int $status, string $message, array $data = []): never
{
    sendJson($status, ['status' => 'success', 'message' => $message, 'data' => $data]);
}

function getJsonInput(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '', true);
    return is_array($data) ? $data : [];
}