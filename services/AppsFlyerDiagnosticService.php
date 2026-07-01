<?php

declare(strict_types=1);

/**
 * Agrega dados de diagnóstico AppsFlyer para homologação (somente leitura).
 */
final class AppsFlyerDiagnosticService
{
    private ?ApiLog $apiLogModel = null;
    private ?AppsFlyerRepository $appsFlyerRepository = null;

    /** @return array<string, mixed> */
    public function getSnapshot(): array
    {
        $config = AppsFlyerConfig::all();

        return [
            'oneLinkActive' => AppsFlyerConfig::isOneLinkActive(),
            'enabled' => AppsFlyerConfig::isEnabled(),
            'homologation' => AppsFlyerConfig::isHomologation(),
            'template' => (string) ($config['onelink_template'] ?? ''),
            'default_media_source' => (string) ($config['default_media_source'] ?? ''),
            'default_campaign' => (string) ($config['default_campaign'] ?? ''),
            'lastWebhook' => $this->apiLog()->findLatestByEndpoint('/api/appsflyer/webhook'),
            'lastTestWebhook' => $this->apiLog()->findLatestByEndpoint('/api/appsflyer/test'),
            'lastApiKobe' => $this->apiLog()->findLatestByEndpoint('/api/indicacao/confirmar-cadastro'),
            'lastEvent' => $this->getLastEvent(),
            'lastOneLinkLog' => AppsFlyerIntegrationLogger::readLastEntry('onelink'),
            'lastWebhookLog' => AppsFlyerIntegrationLogger::readLastEntry('webhook'),
            'lastApiKobeLog' => AppsFlyerIntegrationLogger::readLastEntry('api_kobe'),
            'lastError' => AppsFlyerIntegrationLogger::readLastEntry('errors'),
        ];
    }

    /**
     * Prévia da URL exata que o Dashboard geraria para um usuário (Sprint 3.2.1).
     *
     * @param array<string, mixed> $usuario Registro do usuário (modelo Usuario)
     * @return array{
     *     generated_invite_link: string,
     *     link_type: string,
     *     onelink_template: string,
     *     enabled: bool,
     *     media_source: string,
     *     campaign: string,
     *     deep_link_value: string,
     *     deep_link_sub1: string,
     *     deep_link_sub2: string,
     *     deep_link_sub3: string,
     *     deep_link_sub4: string,
     *     deep_link_sub5: string,
     *     pid: string,
     *     c: string
     * }
     */
    public function getGeneratedInviteLinkReport(array $usuario): array
    {
        $config = AppsFlyerConfig::all();
        $inviteLinkService = new InviteLinkService();
        $details = $inviteLinkService->buildInviteLinkDetails($usuario);
        $params = $details['params'] ?? [];

        return [
            'generated_invite_link' => $details['url'],
            'link_type' => $details['type'],
            'onelink_template' => (string) ($config['onelink_template'] ?? ''),
            'enabled' => AppsFlyerConfig::isEnabled(),
            'media_source' => (string) ($config['default_media_source'] ?? ''),
            'campaign' => (string) ($config['default_campaign'] ?? ''),
            'deep_link_value' => (string) ($params['deep_link_value'] ?? ''),
            'deep_link_sub1' => (string) ($params['deep_link_sub1'] ?? ''),
            'deep_link_sub2' => (string) ($params['deep_link_sub2'] ?? ''),
            'deep_link_sub3' => (string) ($params['deep_link_sub3'] ?? ''),
            'deep_link_sub4' => (string) ($params['deep_link_sub4'] ?? ''),
            'deep_link_sub5' => (string) ($params['deep_link_sub5'] ?? ''),
            'pid' => (string) ($params['pid'] ?? ''),
            'c' => (string) ($params['c'] ?? ''),
        ];
    }

    /** @return array<string, mixed>|null */
    private function getLastEvent(): ?array
    {
        $events = $this->appsFlyerRepository()->listRecent(1);

        return $events[0] ?? null;
    }

    private function apiLog(): ApiLog
    {
        return $this->apiLogModel ??= new ApiLog();
    }

    private function appsFlyerRepository(): AppsFlyerRepository
    {
        return $this->appsFlyerRepository ??= new AppsFlyerRepository();
    }
}
