<?php

declare(strict_types=1);

/**
 * AppsFlyer Payload Validator
 * 
 * Validates payload structure from AppsFlyer webhook requests.
 * Currently disabled - prepared for future integration.
 */
class AppsFlyerPayloadValidator
{
    private bool $enabled = false;

    public function __construct()
    {
        $this->enabled = false; // Disabled for now
    }

    /**
     * Validate payload structure
     */
    public function validate(array $payload): array
    {
        if (!$this->enabled) {
            return ['valid' => true, 'errors' => []];
        }

        $errors = [];

        // TODO: Implement actual payload validation
        // This will check required fields and data types

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Check if validator is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
