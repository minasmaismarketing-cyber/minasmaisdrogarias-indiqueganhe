<?php

declare(strict_types=1);

class AppsFlyerService
{
    private AppsFlyerRepository $repository;
    private EventLogger $eventLogger;
    private array $config;

    public function __construct()
    {
        $this->repository = new AppsFlyerRepository();
        $this->eventLogger = new EventLogger();
        $this->config = $this->loadConfig();
    }

    /**
     * Process AppsFlyer event
     */
    public function processEvent(array $payload): int
    {
        // Extract data from payload
        $appsflyerId = $payload['appsflyer_id'] ?? null;
        $eventName = $payload['event_name'] ?? null;
        $eventValue = $payload['event_value'] ?? null;
        $installType = $payload['install_type'] ?? InstallType::UNKNOWN->value;
        $mediaSource = $payload['media_source'] ?? null;
        $campaign = $payload['campaign'] ?? null;
        $campaignId = $payload['campaign_id'] ?? null;
        $platform = $payload['platform'] ?? null;
        $usuarioId = $payload['usuario_id'] ?? null;
        $indicacaoId = $payload['indicacao_id'] ?? null;

        // Create event record
        $eventId = $this->repository->create([
            'usuario_id' => $usuarioId,
            'indicacao_id' => $indicacaoId,
            'appsflyer_id' => $appsflyerId,
            'event_name' => $eventName,
            'event_value' => $eventValue,
            'install_type' => $installType,
            'media_source' => $mediaSource,
            'campaign' => $campaign,
            'campaign_id' => $campaignId,
            'af_status' => AppsFlyerStatus::PENDING->value,
            'platform' => $platform,
            'raw_payload' => $payload,
        ]);

        $this->eventLogger->logAppsflyerEventoRecebido($eventId, $eventName);

        return $eventId;
    }

    /**
     * Validate event
     */
    public function validateEvent(int $eventId): bool
    {
        $event = $this->repository->findById($eventId);
        if ($event === null) {
            return false;
        }

        // Update status to RECEIVED
        $this->repository->updateStatus($eventId, AppsFlyerStatus::RECEIVED->value);
        $this->eventLogger->logAppsflyerEventoProcessado($eventId, $event['event_name']);

        // TODO: Implement actual validation logic when connecting to AppsFlyer API
        // For now, mark as VALIDATED
        $this->repository->updateStatus($eventId, AppsFlyerStatus::VALIDATED->value);
        $this->eventLogger->logAppsflyerEventoValidado($eventId, $event['event_name']);

        return true;
    }

    /**
     * Reject event
     */
    public function rejectEvent(int $eventId, string $reason): bool
    {
        $event = $this->repository->findById($eventId);
        if ($event === null) {
            return false;
        }

        $this->repository->updateStatus($eventId, AppsFlyerStatus::INVALID->value);
        $this->eventLogger->logAppsflyerEventoRejeitado($eventId, $event['event_name'], $reason);

        return true;
    }

    /**
     * Get event by AppsFlyer ID
     */
    public function getEventByAppsflyerId(string $appsflyerId): ?array
    {
        return $this->repository->findByAppsflyerId($appsflyerId);
    }

    /**
     * Get events by user
     */
    public function getEventsByUser(int $usuarioId): array
    {
        return $this->repository->findByUsuario($usuarioId);
    }

    /**
     * Get events by indication
     */
    public function getEventsByIndication(int $indicacaoId): array
    {
        return $this->repository->findByIndicacao($indicacaoId);
    }

    /**
     * Get all events with filters
     */
    public function getAllEvents(array $filters = []): array
    {
        return $this->repository->findAll($filters);
    }

    /**
     * Get statistics
     */
    public function getStats(): array
    {
        return $this->repository->getStats();
    }

    /**
     * Check if AppsFlyer integration is enabled
     */
    public function isEnabled(): bool
    {
        return $this->config['enabled'] ?? false;
    }

    /**
     * Load AppsFlyer configuration
     */
    private function loadConfig(): array
    {
        $configFile = BASE_PATH . '/config/appsflyer.php';
        
        if (!file_exists($configFile)) {
            return [
                'enabled' => false,
                'api_key' => '',
                'dev_key' => '',
                'app_id_android' => '',
                'app_id_ios' => '',
                'onelink_template' => '',
                'endpoint' => '',
            ];
        }

        return require $configFile;
    }
}
