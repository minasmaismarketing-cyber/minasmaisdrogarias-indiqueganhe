<?php

declare(strict_types=1);

/**
 * Webhook público AppsFlyer — Sprint 3.0/3.2.
 */
class AppsFlyerWebhookController extends Controller
{
    private AppsFlyerService $appsFlyerService;
    private ApiLog $apiLogModel;

    public function __construct()
    {
        $this->appsFlyerService = new AppsFlyerService();
        $this->apiLogModel = new ApiLog();
    }

    public function receive(): void
    {
        $startedAt = microtime(true);
        $endpoint = '/api/appsflyer/webhook';
        $ip = $this->getClientIp();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse([
                'success' => false,
                'message' => 'Method not allowed. Use POST.',
            ], 405);

            return;
        }

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (!str_contains($contentType, 'application/json')) {
            $this->sendJsonResponse([
                'success' => false,
                'message' => 'Content-Type must be application/json.',
            ], 400);

            return;
        }

        $rawInput = file_get_contents('php://input') ?: '';
        $payload = json_decode($rawInput, true);

        if (!is_array($payload)) {
            $response = [
                'success' => false,
                'message' => 'Invalid JSON payload.',
            ];
            $this->logWebhookCall($endpoint, ['raw' => $rawInput], $response, $ip, 400, $startedAt);
            $this->sendJsonResponse($response, 400);

            return;
        }

        try {
            $eventData = AppsFlyerEventData::fromWebhookPayload($payload);
            $eventId = $this->appsFlyerService->processWebhookEvent($eventData);

            $response = [
                'success' => true,
                'message' => 'Event received.',
                'event_id' => $eventId,
            ];

            $this->logWebhookCall($endpoint, $payload, $response, $ip, 200, $startedAt);
            $this->sendJsonResponse($response, 200);
        } catch (Throwable $e) {
            AppsFlyerIntegrationLogger::logError('webhook', $e->getMessage(), [
                'ip' => $ip,
                'payload' => $payload,
            ]);

            $response = [
                'success' => false,
                'message' => 'Failed to process event.',
            ];

            $this->logWebhookCall($endpoint, $payload, $response, $ip, 500, $startedAt);
            $this->sendJsonResponse($response, 500);
        }
    }

    /** @param array<string, mixed> $payload
     *  @param array<string, mixed> $response
     */
    private function logWebhookCall(
        string $endpoint,
        array $payload,
        array $response,
        string $ip,
        int $statusCode,
        float $startedAt
    ): void {
        $durationMs = (microtime(true) - $startedAt) * 1000;

        AppsFlyerIntegrationLogger::logWebhookReceived(
            $payload,
            AppsFlyerIntegrationLogger::captureRequestHeaders(),
            $ip,
            $durationMs,
            $statusCode,
            $response
        );

        $this->apiLogModel->create([
            'endpoint' => $endpoint,
            'payload' => $payload,
            'response' => $response,
            'ip' => $ip,
            'status_code' => $statusCode,
        ]);
    }

    private function getClientIp(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', (string) $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } elseif (isset($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = (string) $_SERVER['HTTP_X_REAL_IP'];
        }

        return trim($ip);
    }

    /** @param array<string, mixed> $data */
    private function sendJsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
