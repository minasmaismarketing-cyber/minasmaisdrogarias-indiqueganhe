<?php

declare(strict_types=1);

/**
 * Provider de cupom interno — Status: ATIVA (Integrations::STATUS_ATIVA).
 *
 * Gera e valida cupons no banco local (formato MM-XXXXXXXX).
 */
class InternalCouponProvider implements CouponProviderInterface
{
    private const PREFIX = 'MM';
    private const CODE_LENGTH = 8;
    private const MAX_ATTEMPTS = 10;

    public function generateCode(array $data): IntegrationResult
    {
        unset($data);

        try {
            $repository = new CupomRepository();
            $attempts = 0;

            do {
                $code = $this->generateRandomCode();
                $attempts++;

                if (!$repository->codigoExiste($code)) {
                    return IntegrationResult::ok('Código de cupom gerado.', ['codigo' => $code]);
                }
            } while ($attempts < self::MAX_ATTEMPTS);

            return IntegrationResult::falha(
                'Não foi possível gerar código único após ' . self::MAX_ATTEMPTS . ' tentativas.'
            );
        } catch (Throwable $e) {
            return IntegrationResult::falha('Falha ao gerar código de cupom: ' . $e->getMessage());
        }
    }

    public function validate(string $code): IntegrationResult
    {
        $repository = new CupomRepository();
        $cupom = $repository->findByCodigo($code);

        if ($cupom === null) {
            return IntegrationResult::falha('Cupom não encontrado');
        }

        if ($cupom['status'] === Cupom::STATUS_UTILIZADO) {
            return IntegrationResult::falha('Cupom já utilizado');
        }

        if ($cupom['status'] === Cupom::STATUS_EXPIRADO) {
            return IntegrationResult::falha('Cupom expirado');
        }

        if ($cupom['status'] === Cupom::STATUS_CANCELADO) {
            return IntegrationResult::falha('Cupom cancelado');
        }

        if ($cupom['validade'] && strtotime($cupom['validade']) < time()) {
            return IntegrationResult::falha('Cupom expirado');
        }

        return IntegrationResult::ok('Cupom válido.', ['cupom' => $cupom]);
    }

    public function isAvailable(): IntegrationResult
    {
        return IntegrationResult::ok('Provider interno de cupons disponível.');
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
