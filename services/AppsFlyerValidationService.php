<?php

declare(strict_types=1);

/**
 * Regras de validação AppsFlyer — Status: PREPARADA (Integrations::STATUS_PREPARADA).
 *
 * Valida estrutura de payload localmente. Regras avançadas de campanha/origem/timing
 * permanecem permissivas até dados externos estarem disponíveis.
 */
class AppsFlyerValidationService
{
    public function validatePayload(array $payload): IntegrationResult
    {
        $errors = [];

        if (empty($payload['appsflyer_id'])) {
            $errors[] = 'appsflyer_id is required';
        }

        if (empty($payload['event_name'])) {
            $errors[] = 'event_name is required';
        }

        if (!empty($payload['install_type'])) {
            $validInstallTypes = array_map(static fn ($type) => $type->value, InstallType::cases());
            if (!in_array($payload['install_type'], $validInstallTypes, true)) {
                $errors[] = 'Invalid install_type. Valid values: ' . implode(', ', $validInstallTypes);
            }
        }

        if (!empty($payload['platform'])) {
            $validPlatforms = ['android', 'ios', 'web'];
            if (!in_array(strtolower((string) $payload['platform']), $validPlatforms, true)) {
                $errors[] = 'Invalid platform. Valid values: ' . implode(', ', $validPlatforms);
            }
        }

        if ($errors !== []) {
            return IntegrationResult::falha(implode('; ', $errors), ['errors' => $errors]);
        }

        return IntegrationResult::ok('Payload AppsFlyer válido.');
    }

    public function validateInstallation(array $payload): IntegrationResult
    {
        $errors = [];

        if (empty($payload['install_type'])) {
            $errors[] = 'install_type is required for installation validation';
        }

        if (!empty($payload['install_type']) && $payload['install_type'] === InstallType::FIRST_INSTALL->value) {
            if (empty($payload['media_source'])) {
                $errors[] = 'media_source is required for FIRST_INSTALL';
            }
        }

        if ($errors !== []) {
            return IntegrationResult::falha(implode('; ', $errors), ['errors' => $errors]);
        }

        return IntegrationResult::ok('Instalação AppsFlyer válida.');
    }

    public function validatePlatform(string $platform): IntegrationResult
    {
        $validPlatforms = ['android', 'ios', 'web'];

        if (in_array(strtolower($platform), $validPlatforms, true)) {
            return IntegrationResult::ok('Plataforma válida.');
        }

        return IntegrationResult::falha('Plataforma inválida.');
    }

    public function validateCampaign(?string $campaign): IntegrationResult
    {
        unset($campaign);

        return IntegrationResult::ok('Validação de campanha permissiva até integração externa.');
    }

    public function validateOrigin(?string $mediaSource): IntegrationResult
    {
        unset($mediaSource);

        return IntegrationResult::ok('Validação de origem permissiva até integração externa.');
    }

    public function validateEventValue(?string $eventValue): IntegrationResult
    {
        if ($eventValue === null || is_numeric($eventValue)) {
            return IntegrationResult::ok('Valor de evento válido.');
        }

        return IntegrationResult::falha('Valor de evento deve ser numérico.');
    }

    public function isDuplicateEvent(string $appsflyerId, string $eventName): bool
    {
        $repository = new AppsFlyerRepository();
        $existingEvent = $repository->findByAppsflyerId($appsflyerId);

        if ($existingEvent === null) {
            return false;
        }

        return $existingEvent['event_name'] === $eventName;
    }

    public function validateEventTiming(array $payload): IntegrationResult
    {
        unset($payload);

        return IntegrationResult::ok('Validação de timing permissiva até integração externa.');
    }

    public function getValidationSummary(array $payload): array
    {
        $payloadValidation = $this->validatePayload($payload);
        $installationValidation = $this->validateInstallation($payload);
        $platformValid = empty($payload['platform']) || $this->validatePlatform((string) $payload['platform'])->sucesso;
        $campaignValid = $this->validateCampaign($payload['campaign'] ?? null)->sucesso;
        $originValid = $this->validateOrigin($payload['media_source'] ?? null)->sucesso;
        $eventValueValid = $this->validateEventValue($payload['event_value'] ?? null)->sucesso;

        return [
            'payload_valid' => $payloadValidation->sucesso,
            'installation_valid' => $installationValidation->sucesso,
            'platform_valid' => $platformValid,
            'campaign_valid' => $campaignValid,
            'origin_valid' => $originValid,
            'event_value_valid' => $eventValueValid,
            'all_valid' => $payloadValidation->sucesso
                && $installationValidation->sucesso
                && $platformValid
                && $campaignValid
                && $originValid
                && $eventValueValid,
        ];
    }
}
