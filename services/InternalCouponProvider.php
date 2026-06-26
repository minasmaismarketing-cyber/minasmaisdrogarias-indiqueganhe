<?php

declare(strict_types=1);

/**
 * Internal Coupon Provider
 * 
 * Generates and validates internal coupons for the Indique e Ganhe system.
 * Coupon format: MM-XXXXXXXX (8 random characters)
 */
class InternalCouponProvider implements CouponProviderInterface
{
    private const PREFIX = 'MM';
    private const CODE_LENGTH = 8;
    private const MAX_ATTEMPTS = 10;

    public function generateCode(array $data): string
    {
        $repository = new CupomRepository();
        $attempts = 0;

        do {
            $code = $this->generateRandomCode();
            $attempts++;

            if (!$repository->codigoExiste($code)) {
                return $code;
            }
        } while ($attempts < self::MAX_ATTEMPTS);

        throw new RuntimeException('Failed to generate unique coupon code after ' . self::MAX_ATTEMPTS . ' attempts');
    }

    public function validate(string $code): array
    {
        $repository = new CupomRepository();
        $cupom = $repository->findByCodigo($code);

        if ($cupom === null) {
            return [
                'valid' => false,
                'reason' => 'Cupom não encontrado',
            ];
        }

        if ($cupom['status'] === Cupom::STATUS_UTILIZADO) {
            return [
                'valid' => false,
                'reason' => 'Cupom já utilizado',
            ];
        }

        if ($cupom['status'] === Cupom::STATUS_EXPIRADO) {
            return [
                'valid' => false,
                'reason' => 'Cupom expirado',
            ];
        }

        if ($cupom['status'] === Cupom::STATUS_CANCELADO) {
            return [
                'valid' => false,
                'reason' => 'Cupom cancelado',
            ];
        }

        if ($cupom['validade'] && strtotime($cupom['validade']) < time()) {
            return [
                'valid' => false,
                'reason' => 'Cupom expirado',
            ];
        }

        return [
            'valid' => true,
            'reason' => null,
            'cupom' => $cupom,
        ];
    }

    public function isAvailable(): bool
    {
        return true; // Internal provider is always available
    }

    private function generateRandomCode(): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $randomString = '';

        for ($i = 0; $i < self::CODE_LENGTH; $i++) {
            $randomString .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return self::PREFIX . '-' . $randomString;
    }
}
