<?php

declare(strict_types=1);

/**
 * AppsFlyer Webhook Validator
 * 
 * Validates webhook requests from AppsFlyer.
 * Currently disabled - prepared for future integration.
 */
class AppsFlyerWebhookValidator
{
    private bool $enabled = false;

    public function __construct()
    {
        $this->enabled = false; // Disabled for now
    }

    /**
     * Validate webhook request
     */
    public function validate(array $payload, string $signature): bool
    {
        if (!$this->enabled) {
            return true; // Always return true when disabled
        }

        // TODO: Implement actual webhook validation
        // This will verify the signature from AppsFlyer
        return false;
    }

    /**
     * Check if validator is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
