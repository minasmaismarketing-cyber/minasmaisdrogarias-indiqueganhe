<?php

declare(strict_types=1);

interface CouponProviderInterface
{
    /**
     * Generate a unique coupon code
     * 
     * @param array $data Coupon data (usuario_id, indicacao_id, campanha_id, tipo, valor)
     * @return string Generated coupon code
     */
    public function generateCode(array $data): string;

    /**
     * Validate a coupon code
     * 
     * @param string $code Coupon code to validate
     * @return array Result with 'valid' (bool) and 'reason' (string|null)
     */
    public function validate(string $code): array;

    /**
     * Check if provider is available/configured
     * 
     * @return bool
     */
    public function isAvailable(): bool;
}
