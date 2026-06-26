<?php

declare(strict_types=1);

class AppsFlyerValidationService
{
    /**
     * Validate AppsFlyer payload
     */
    public function validatePayload(array $payload): array
    {
        $errors = [];

        // Validate required fields
        if (empty($payload['appsflyer_id'])) {
            $errors[] = 'appsflyer_id is required';
        }

        if (empty($payload['event_name'])) {
            $errors[] = 'event_name is required';
        }

        // Validate install_type
        if (!empty($payload['install_type'])) {
            $validInstallTypes = array_map(fn($type) => $type->value, InstallType::cases());
            if (!in_array($payload['install_type'], $validInstallTypes)) {
                $errors[] = 'Invalid install_type. Valid values: ' . implode(', ', $validInstallTypes);
            }
        }

        // Validate platform
        if (!empty($payload['platform'])) {
            $validPlatforms = ['android', 'ios', 'web'];
            if (!in_array(strtolower($payload['platform']), $validPlatforms)) {
                $errors[] = 'Invalid platform. Valid values: ' . implode(', ', $validPlatforms);
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Validate installation
     */
    public function validateInstallation(array $payload): array
    {
        $errors = [];

        // Check if install_type is valid
        if (empty($payload['install_type'])) {
            $errors[] = 'install_type is required for installation validation';
        }

        // Check for FIRST_INSTALL
        if (!empty($payload['install_type']) && $payload['install_type'] === InstallType::FIRST_INSTALL->value) {
            // Additional validations for first install
            if (empty($payload['media_source'])) {
                $errors[] = 'media_source is required for FIRST_INSTALL';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Validate platform
     */
    public function validatePlatform(string $platform): bool
    {
        $validPlatforms = ['android', 'ios', 'web'];
        return in_array(strtolower($platform), $validPlatforms);
    }

    /**
     * Validate campaign
     */
    public function validateCampaign(?string $campaign): bool
    {
        // Campaign validation logic
        // For now, just check if it's not empty when expected
        return true; // TODO: Implement actual campaign validation
    }

    /**
     * Validate origin (media source)
     */
    public function validateOrigin(?string $mediaSource): bool
    {
        // Media source validation logic
        // For now, just check if it's not empty when expected
        return true; // TODO: Implement actual origin validation
    }

    /**
     * Validate event value
     */
    public function validateEventValue(?string $eventValue): bool
    {
        if ($eventValue === null) {
            return true;
        }

        // Check if it's a valid number
        return is_numeric($eventValue);
    }

    /**
     * Check if event is duplicate
     */
    public function isDuplicateEvent(string $appsflyerId, string $eventName): bool
    {
        $repository = new AppsFlyerRepository();
        $existingEvent = $repository->findByAppsflyerId($appsflyerId);

        if ($existingEvent === null) {
            return false;
        }

        return $existingEvent['event_name'] === $eventName;
    }

    /**
     * Validate event timing
     */
    public function validateEventTiming(array $payload): bool
    {
        // Check if event timestamp is within acceptable range
        // For now, always return true
        return true; // TODO: Implement actual timing validation
    }

    /**
     * Get validation summary
     */
    public function getValidationSummary(array $payload): array
    {
        $payloadValidation = $this->validatePayload($payload);
        $installationValidation = $this->validateInstallation($payload);
        $platformValid = empty($payload['platform']) || $this->validatePlatform($payload['platform']);
        $campaignValid = $this->validateCampaign($payload['campaign'] ?? null);
        $originValid = $this->validateOrigin($payload['media_source'] ?? null);
        $eventValueValid = $this->validateEventValue($payload['event_value'] ?? null);

        return [
            'payload_valid' => $payloadValidation['valid'],
            'installation_valid' => $installationValidation['valid'],
            'platform_valid' => $platformValid,
            'campaign_valid' => $campaignValid,
            'origin_valid' => $originValid,
            'event_value_valid' => $eventValueValid,
            'all_valid' => $payloadValidation['valid'] && 
                          $installationValidation['valid'] && 
                          $platformValid && 
                          $campaignValid && 
                          $originValid && 
                          $eventValueValid,
        ];
    }
}
