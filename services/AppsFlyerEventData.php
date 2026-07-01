<?php

declare(strict_types=1);

/**
 * DTO de evento AppsFlyer — Sprint 3.0 (preparação).
 *
 * Normaliza payloads de webhook/OneLink sem alterar schema de banco.
 */
final class AppsFlyerEventData
{
    /** @param array<string, mixed> $rawPayload */
    public function __construct(
        public readonly ?string $appsflyerId,
        public readonly ?string $mediaSource,
        public readonly ?string $campaign,
        public readonly ?string $campaignId,
        public readonly ?string $platform,
        public readonly ?string $deepLinkValue,
        public readonly ?string $deepLinkSub1,
        public readonly ?string $deepLinkSub2,
        public readonly ?string $installType,
        public readonly array $rawPayload,
    ) {
    }

    /** @param array<string, mixed> $payload */
    public static function fromApiPayload(array $payload): self
    {
        $installType = self::firstString($payload, ['tipoEvento', 'install_type', 'installType']);

        if ($installType !== null) {
            $installType = strtoupper($installType);
        }

        $platform = self::firstString($payload, ['platform', 'plataforma']);

        return new self(
            appsflyerId: self::firstString($payload, ['appsflyerId', 'appsflyer_id']),
            mediaSource: self::firstString($payload, ['mediaSource', 'media_source', 'pid']),
            campaign: self::firstString($payload, ['campaign', 'c']),
            campaignId: self::firstString($payload, ['campaignId', 'campaign_id']),
            platform: self::normalizePlatform($platform),
            deepLinkValue: self::firstString($payload, ['deepLinkValue', 'deep_link_value']),
            deepLinkSub1: self::firstString($payload, ['deepLinkSub1', 'deep_link_sub1']),
            deepLinkSub2: self::firstString($payload, ['deepLinkSub2', 'deep_link_sub2']),
            installType: $installType,
            rawPayload: $payload,
        );
    }

    /** @return array<string, mixed> */
    public function toApiPersistenceArray(?int $indicacaoId = null, ?int $usuarioId = null): array
    {
        $data = $this->toPersistenceArray($indicacaoId);
        $data['usuario_id'] = $usuarioId;
        $data['event_name'] = 'api_confirmar_cadastro';

        return $data;
    }

    /** @param array<string, mixed> $payload */
    public static function hasApiMetadata(array $payload): bool
    {
        $keys = [
            'appsflyerId',
            'appsflyer_id',
            'campaignId',
            'campaign_id',
            'deepLinkSub1',
            'deep_link_sub1',
            'deepLinkSub2',
            'deep_link_sub2',
            'deepLinkValue',
            'deep_link_value',
        ];

        foreach ($keys as $key) {
            if (!empty($payload[$key])) {
                return true;
            }
        }

        return false;
    }

    /** @param array<string, mixed> $payload */
    public static function fromWebhookPayload(array $payload): self
    {
        $installType = self::firstString($payload, [
            'install_type',
            'installType',
            'af_install_type',
        ]);

        if ($installType !== null) {
            $installType = strtoupper($installType);
        }

        return new self(
            appsflyerId: self::firstString($payload, [
                'appsflyer_id',
                'appsflyerId',
                'af_device_id',
                'device_id',
            ]),
            mediaSource: self::firstString($payload, [
                'media_source',
                'mediaSource',
                'pid',
            ]),
            campaign: self::firstString($payload, [
                'campaign',
                'c',
                'campaign_name',
            ]),
            campaignId: self::firstString($payload, [
                'campaign_id',
                'campaignId',
                'af_c_id',
            ]),
            platform: self::normalizePlatform(self::firstString($payload, [
                'platform',
                'os',
                'device_type',
            ])),
            deepLinkValue: self::firstString($payload, [
                'deep_link_value',
                'deepLinkValue',
                'af_dp',
            ]),
            deepLinkSub1: self::firstString($payload, [
                'deep_link_sub1',
                'deepLinkSub1',
                'af_sub1',
            ]),
            deepLinkSub2: self::firstString($payload, [
                'deep_link_sub2',
                'deepLinkSub2',
                'af_sub2',
            ]),
            installType: $installType,
            rawPayload: $payload,
        );
    }

    /** @return array<string, mixed> */
    public function toPersistenceArray(?int $indicacaoId = null): array
    {
        $eventName = self::firstString($this->rawPayload, [
            'event_name',
            'eventName',
            'event_type',
            'eventType',
        ]) ?? 'webhook';

        return [
            'usuario_id' => null,
            'indicacao_id' => $indicacaoId,
            'appsflyer_id' => $this->appsflyerId,
            'event_name' => $eventName,
            'event_value' => null,
            'install_type' => $this->installType ?? InstallType::UNKNOWN->value,
            'media_source' => $this->mediaSource,
            'campaign' => $this->campaign,
            'campaign_id' => $this->campaignId,
            'platform' => $this->platform,
            'raw_payload' => $this->rawPayload,
        ];
    }

    /** @param array<string, mixed> $payload
     *  @param list<string> $keys
     */
    private static function firstString(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $payload)) {
                continue;
            }

            $value = trim((string) $payload[$key]);

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private static function normalizePlatform(?string $platform): ?string
    {
        if ($platform === null) {
            return null;
        }

        return strtolower($platform);
    }
}
