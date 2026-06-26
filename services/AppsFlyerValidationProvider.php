<?php

declare(strict_types=1);

/**
 * AppsFlyer Validation Provider
 * 
 * Future integration for validating indications through AppsFlyer
 * This provider will check if the user completed the required actions
 * in the mobile app tracked by AppsFlyer.
 */
class AppsFlyerValidationProvider implements ValidationProviderInterface
{
    public function validate(array $data): array
    {
        // TODO: Implement AppsFlyer API integration
        // This will:
        // 1. Check if the user installed the app via the referral link
        // 2. Verify the user completed required in-app actions
        // 3. Validate the attribution data from AppsFlyer
        
        return [
            'valid' => false,
            'reason' => 'AppsFlyer integration not yet implemented',
        ];
    }

    public function isAvailable(): bool
    {
        // TODO: Check if AppsFlyer API credentials are configured
        return false;
    }
}
