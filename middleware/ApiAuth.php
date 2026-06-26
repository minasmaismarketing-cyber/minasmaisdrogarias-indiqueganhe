<?php

declare(strict_types=1);

class ApiAuth
{
    public function handle(): void
    {
        // Only accept POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse([
                'success' => false,
                'message' => 'Method not allowed. Use POST.',
            ], 405);
            exit;
        }

        // Validate Content-Type
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (!str_contains($contentType, 'application/json')) {
            $this->sendJsonResponse([
                'success' => false,
                'message' => 'Content-Type must be application/json.',
            ], 400);
            exit;
        }

        // Validate Authorization header
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (empty($authHeader)) {
            $this->sendJsonResponse([
                'success' => false,
                'message' => 'Authorization header required.',
            ], 401);
            exit;
        }

        // Check Bearer token format
        if (!preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            $this->sendJsonResponse([
                'success' => false,
                'message' => 'Invalid authorization format. Use: Bearer {token}',
            ], 401);
            exit;
        }

        $providedToken = $matches[1];
        $expectedToken = $_ENV['API_TOKEN'] ?? '';

        if (empty($expectedToken)) {
            $this->sendJsonResponse([
                'success' => false,
                'message' => 'API token not configured on server.',
            ], 500);
            exit;
        }

        if (!hash_equals($expectedToken, $providedToken)) {
            $this->sendJsonResponse([
                'success' => false,
                'message' => 'Invalid API token.',
            ], 401);
            exit;
        }
    }

    private function sendJsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
