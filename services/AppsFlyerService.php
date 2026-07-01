<?php

declare(strict_types=1);

/**
 * Integração AppsFlyer — Status: PREPARADA (Integrations::STATUS_PREPARADA).
 *
 * Persiste eventos recebidos e permite validação manual no admin.
 * A validação automática contra a API AppsFlyer permanece desabilitada até configuração completa.
 */
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

    public function processEvent(array $payload): int
    {
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

    public function validateEvent(int $eventId): bool
    {
        $event = $this->repository->findById($eventId);
        if ($event === null) {
            return false;
        }

        $this->repository->updateStatus($eventId, AppsFlyerStatus::RECEIVED->value);
        $this->eventLogger->logAppsflyerEventoProcessado($eventId, $event['event_name']);

        // Validação operacional manual: confirmação admin até integração API AppsFlyer estar ativa.
        $this->repository->updateStatus($eventId, AppsFlyerStatus::VALIDATED->value);
        $this->eventLogger->logAppsflyerEventoValidado($eventId, $event['event_name']);

        return true;
    }

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

    public function getEventByAppsflyerId(string $appsflyerId): ?array
    {
        return $this->repository->findByAppsflyerId($appsflyerId);
    }

    public function getEventsByUser(int $usuarioId): array
    {
        return $this->repository->findByUsuario($usuarioId);
    }

    public function getEventsByIndication(int $indicacaoId): array
    {
        return $this->repository->findByIndicacao($indicacaoId);
    }

    public function getAllEvents(array $filters = []): array
    {
        return $this->repository->findAll($filters);
    }

    public function getStats(): array
    {
        return $this->repository->getStats();
    }

    public function isEnabled(): bool
    {
        return $this->config['enabled'] ?? false;
    }

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
