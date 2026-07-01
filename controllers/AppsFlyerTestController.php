<?php

declare(strict_types=1);

/**
 * Endpoint de teste AppsFlyer — disponível apenas em modo homologação.
 *
 * POST /api/appsflyer/test
 */
class AppsFlyerTestController extends Controller
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
        $endpoint = '/api/appsflyer/test';
        $ip = $this->getClientIp();

        if (!AppsFlyerConfig::isHomologation()) {
            $this->sendJsonResponse([
                'success' => false,
                'message' => 'Endpoint disponível apenas em modo homologação.',
            ], 403);

            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse([
                'success' => false,
                'message' => 'Method not allowed. Use POST.',
            ], 405);

            return;
        }

        $rawInput = file_get_contents('php://input') ?: '';
        $payload = json_decode($rawInput, true);

        if (!is_array($payload)) {
            $response = [
                'success' => false,
                'message' => 'Invalid JSON payload.',
            ];
            $this->persistCall($endpoint, ['raw' => $rawInput], $response, $ip, 400);
            $this->sendJsonResponse($response, 400);

            return;
        }

        try {
            $eventData = AppsFlyerEventData::fromWebhookPayload($payload);
            $eventId = $this->appsFlyerService->processWebhookEvent($eventData);

            $response = [
                'success' => true,
                'message' => 'Test event processed.',
                'event_id' => $eventId,
                'homologation' => true,
            ];

            $durationMs = (microtime(true) - $startedAt) * 1000;
            AppsFlyerIntegrationLogger::logWebhookReceived(
                $payload,
                AppsFlyerIntegrationLogger::captureRequestHeaders(),
                $ip,
                $durationMs,
                200,
                $response
            );

            $this->persistCall($endpoint, $payload, $response, $ip, 200);
            $this->sendJsonResponse($response, 200);
        } catch (Throwable $e) {
            AppsFlyerIntegrationLogger::logError('test', $e->getMessage(), [
                'ip' => $ip,
                'payload' => $payload,
            ]);

            $response = [
                'success' => false,
                'message' => 'Failed to process test event.',
                'error' => $e->getMessage(),
            ];

            $durationMs = (microtime(true) - $startedAt) * 1000;
            AppsFlyerIntegrationLogger::logWebhookReceived(
                $payload,
                AppsFlyerIntegrationLogger::captureRequestHeaders(),
                $ip,
                $durationMs,
                500,
                $response
            );

            $this->persistCall($endpoint, $payload, $response, $ip, 500);
            $this->sendJsonResponse($response, 500);
        }
    }

    /** @param array<string, mixed> $payload
     *  @param array<string, mixed> $response
     */
    private function persistCall(
        string $endpoint,
        array $payload,
        array $response,
        string $ip,
        int $statusCode
    ): void {
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
