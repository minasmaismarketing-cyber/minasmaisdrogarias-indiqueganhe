<?php

declare(strict_types=1);

interface ValidationProviderInterface
{
    /**
     * Validate an indication using external provider
     * 
     * @param array $data Indication data (cpf, email, telefone, etc.)
     * @return array Result with 'valid' (bool) and 'reason' (string|null)
     */
    public function validate(array $data): array;

    /**
     * Check if provider is available/configured
     * 
     * @return bool
     */
    public function isAvailable(): bool;
}
