<?php

declare(strict_types=1);

/**
 * Status de integração AppsFlyer + KOBE para homologação (Admin > Configurações).
 */
final class HomologacaoIntegrationStatus
{
    /** @return list<array{name: string, emoji: string, label: string, detail: string}> */
    public static function getIntegrationRows(): array
    {
        return [
            self::appsFlyerRow(),
            self::oneLinkRow(),
            self::smartScriptRow(),
            self::landingRow(),
            self::apiKobeRow(),
            self::sdkRow(),
        ];
    }

    /** @return array{name: string, emoji: string, label: string, detail: string} */
    private static function appsFlyerRow(): array
    {
        $config = AppsFlyerConfig::all();
        $mediaSource = trim((string) ($config['default_media_source'] ?? ''));
        $campaign = trim((string) ($config['default_campaign'] ?? ''));
        $configured = $mediaSource !== ''
            && $campaign !== ''
            && AppsFlyerConfig::isOneLinkConfigured();

        return [
            'name' => 'AppsFlyer',
            'emoji' => $configured ? '🟢' : '🟡',
            'label' => $configured ? 'Configurada' : 'Parcialmente configurada',
            'detail' => $configured
                ? $mediaSource . ' · ' . $campaign
                : 'Verifique config/appsflyer.php',
        ];
    }

    /** @return array{name: string, emoji: string, label: string, detail: string} */
    private static function oneLinkRow(): array
    {
        $config = AppsFlyerConfig::all();
        $template = trim((string) ($config['onelink_template'] ?? ''));
        $smart = $config['smart_script'] ?? [];
        $smartBase = rtrim(trim((string) ($smart['one_link_url'] ?? '')), '/');
        $profile = trim((string) ($smart['redirection_profile'] ?? ''));
        $smartUrl = $smartBase !== '' && $profile !== ''
            ? $smartBase . '/' . $profile
            : $smartBase;

        $configured = $template !== '' || $smartUrl !== '';

        return [
            'name' => 'OneLink',
            'emoji' => $configured ? '🟢' : '🟡',
            'label' => $configured ? 'Configurado' : 'Não configurado',
            'detail' => $template !== '' ? $template : ($smartUrl !== '' ? $smartUrl : ''),
        ];
    }

    /** @return array{name: string, emoji: string, label: string, detail: string} */
    private static function smartScriptRow(): array
    {
        $config = AppsFlyerConfig::all();
        $smart = $config['smart_script'] ?? [];
        $enabled = (bool) ($smart['enabled'] ?? false);
        $scriptUrl = trim((string) ($smart['script_url'] ?? ''));
        $oneLinkUrl = trim((string) ($smart['one_link_url'] ?? ''));
        $active = $enabled && $scriptUrl !== '' && $oneLinkUrl !== '';

        return [
            'name' => 'Smart Script',
            'emoji' => $active ? '🟢' : '🟡',
            'label' => $active ? 'Ativo' : 'Inativo',
            'detail' => $active ? $oneLinkUrl : 'smart_script.enabled = false',
        ];
    }

    /** @return array{name: string, emoji: string, label: string, detail: string} */
    private static function landingRow(): array
    {
        $config = AppsFlyerConfig::all();
        $smart = $config['smart_script'] ?? [];
        $smartEnabled = (bool) ($smart['enabled'] ?? false);
        $fallbackUrl = app_download_url();
        $active = $smartEnabled || ($fallbackUrl !== '' && $fallbackUrl !== '#');

        return [
            'name' => 'Landing de Convite',
            'emoji' => $active ? '🟢' : '🟡',
            'label' => $active ? 'Ativa' : 'Indisponível',
            'detail' => '/convite?ref={codigo}',
        ];
    }

    /** @return array{name: string, emoji: string, label: string, detail: string} */
    private static function apiKobeRow(): array
    {
        $token = trim((string) Env::get('KOBE_API_TOKEN', ''));
        $configured = $token !== '';

        return [
            'name' => 'API KOBE',
            'emoji' => $configured ? '🟢' : '🟡',
            'label' => $configured ? 'Configurada' : 'Token pendente',
            'detail' => KobeApiIntegration::ENDPOINT,
        ];
    }

    /** @return array{name: string, emoji: string, label: string, detail: string} */
    private static function sdkRow(): array
    {
        $config = AppsFlyerConfig::all();
        $androidId = trim((string) ($config['app_id_android'] ?? ''));
        $iosId = trim((string) ($config['app_id_ios'] ?? ''));
        $homologation = AppsFlyerConfig::isHomologation();
        $sdkReady = $androidId !== '' && $iosId !== '';

        if ($homologation && !$sdkReady) {
            return [
                'name' => 'Aplicativo (SDK)',
                'emoji' => '🟡',
                'label' => 'Aguardando homologação',
                'detail' => self::sdkDetail($androidId, $iosId),
            ];
        }

        return [
            'name' => 'Aplicativo (SDK)',
            'emoji' => $sdkReady ? '🟢' : '🟡',
            'label' => $sdkReady ? 'Configurado' : 'Aguardando homologação',
            'detail' => self::sdkDetail($androidId, $iosId),
        ];
    }

    private static function sdkDetail(string $androidId, string $iosId): string
    {
        $parts = [];

        if ($androidId !== '') {
            $parts[] = 'Android: ' . $androidId;
        }

        if ($iosId !== '') {
            $parts[] = 'iOS: ' . $iosId;
        }

        if ($parts === []) {
            return 'app_id_android / app_id_ios pendentes em config/appsflyer.php';
        }

        return implode(' · ', $parts);
    }
}
