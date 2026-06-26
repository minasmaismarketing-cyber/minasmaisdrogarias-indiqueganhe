<?php

declare(strict_types=1);

/**
 * VTEX Validation Provider
 * 
 * Future integration for validating indications through VTEX
 * This provider will check if the user made a qualifying purchase
 * in the VTEX e-commerce platform.
 */
class VTEXValidationProvider implements ValidationProviderInterface
{
    public function validate(array $data): array
    {
        // TODO: Implement VTEX API integration
        // This will:
        // 1. Check if the user made a purchase in VTEX
        // 2. Verify the purchase meets minimum value requirements
        // 3. Validate the order data from VTEX
        
        return [
            'valid' => false,
            'reason' => 'VTEX integration not yet implemented',
        ];
    }

    public function isAvailable(): bool
    {
        // TODO: Check if VTEX API credentials are configured
        return false;
    }
}
