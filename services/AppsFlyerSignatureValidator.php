<?php

declare(strict_types=1);

/**
 * AppsFlyer Signature Validator
 * 
 * Validates signature from AppsFlyer webhook requests.
 * Currently disabled - prepared for future integration.
 */
class AppsFlyerSignatureValidator
{
    private bool $enabled = false;
    private ?string $secret = null;

    public function __construct()
    {
        $this->enabled = false; // Disabled for now
        $this->secret = null;
    }

    /**
     * Validate signature
     */
    public function validate(string $payload, string $signature): bool
    {
        if (!$this->enabled) {
            return true; // Always return true when disabled
        }

        if ($this->secret === null) {
            return false;
        }

        // TODO: Implement actual signature validation
        // This will use HMAC-SHA256 to verify the signature
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
