<?php

declare(strict_types=1);

/**
 * Provider de cupom VTEX — Status: STUB (Integrations::STATUS_STUB).
 *
 * Preparado para sincronizar geração e validação de cupons com a VTEX.
 * Ativação futura: configurar credenciais VTEX e implementar chamadas HTTP.
 */
class VTEXCouponProvider implements CouponProviderInterface
{
    public function generateCode(array $data): IntegrationResult
    {
        unset($data);

        return IntegrationResult::falha('Integração VTEX ainda não implementada.');
    }

    public function validate(string $code): IntegrationResult
    {
        unset($code);

        return IntegrationResult::falha('Integração VTEX ainda não implementada.');
    }

    public function isAvailable(): IntegrationResult
    {
        return IntegrationResult::falha('Integração VTEX não configurada.');
    }
}
