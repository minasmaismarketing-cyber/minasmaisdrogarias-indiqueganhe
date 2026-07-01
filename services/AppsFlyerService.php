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
    private Usuario $usuarioModel;
    private array $config;

    public function __construct()
    {
        $this->repository = new AppsFlyerRepository();
        $this->eventLogger = new EventLogger();
        $this->usuarioModel = new Usuario();
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
            'raw_payload' => $payload['raw_payload'] ?? $payload,
        ]);

        $this->eventLogger->logAppsflyerEventoRecebido($eventId, $eventName);

        return $eventId;
    }

    /**
     * Entrada oficial do webhook AppsFlyer (Sprint 3.0).
     */
    public function processWebhookEvent(AppsFlyerEventData $eventData): int
    {
        $indicacao = $this->findIndicacaoByAppsFlyerId(
            $eventData->appsflyerId,
            $eventData->deepLinkSub1
        );
        $indicacaoId = $indicacao['id'] ?? null;

        return $this->processEvent($eventData->toPersistenceArray(
            $indicacaoId !== null ? (int) $indicacaoId : null
        ));
    }

    /**
     * Metadados AppsFlyer enviados pela API KOBE após confirmação de cadastro.
     */
    public function processApiEvent(
        AppsFlyerEventData $eventData,
        ?int $indicacaoId = null,
        ?int $usuarioId = null
    ): int {
        if ($indicacaoId === null) {
            $this->findIndicacaoByAppsFlyerId($eventData->appsflyerId, $eventData->deepLinkSub1);
        }

        return $this->processEvent($eventData->toApiPersistenceArray($indicacaoId, $usuarioId));
    }

    /**
     * Preparação Sprint 3.1 — correlação futura entre appsflyer_id e indicação.
     *
     * Quando `deepLinkSub1` estiver presente, valida o código do indicador.
     * A associação completa com `indicacoes` será implementada em sprint futura.
     *
     * @return array<string, mixed>|null
     */
    public function findIndicacaoByAppsFlyerId(?string $appsflyerId, ?string $deepLinkSub1 = null): ?array
    {
        $codigoIndicador = $this->resolveCodigoIndicadorFromDeepLink($deepLinkSub1);

        if ($codigoIndicador !== null) {
            Logger::info('AppsFlyer correlation prepared via deepLinkSub1', [
                'appsflyer_id' => $appsflyerId,
                'codigo_indicador' => $codigoIndicador,
            ]);
        }

        unset($appsflyerId, $codigoIndicador);

        return null;
    }

    /**
     * Localiza código do indicador a partir de deep_link_sub1 / deepLinkSub1.
     */
    public function resolveCodigoIndicadorFromDeepLink(?string $deepLinkSub1): ?string
    {
        if ($deepLinkSub1 === null || trim($deepLinkSub1) === '') {
            return null;
        }

        $codigo = strtoupper(trim($deepLinkSub1));

        if (!preg_match('/^MM[A-Z0-9]{6}$/', $codigo)) {
            return null;
        }

        $usuario = $this->usuarioModel->findByCodigo($codigo);

        return $usuario !== null ? $codigo : null;
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
        return AppsFlyerConfig::isEnabled();
    }

    public function isHomologation(): bool
    {
        return AppsFlyerConfig::isHomologation();
    }

    private function loadConfig(): array
    {
        return AppsFlyerConfig::all();
    }
}
