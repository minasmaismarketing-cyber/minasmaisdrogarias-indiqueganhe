<?php

declare(strict_types=1);

/**
 * Validador de assinatura AppsFlyer — Status: STUB (Integrations::STATUS_STUB).
 *
 * Desabilitado em runtime; aceita assinaturas até HMAC-SHA256 ser configurado.
 */
class AppsFlyerSignatureValidator
{
    private bool $enabled = false;

    public function validate(string $payload, string $signature): IntegrationResult
    {
        unset($payload, $signature);

        if (!$this->enabled) {
            return IntegrationResult::ok('Validação de assinatura AppsFlyer desabilitada.');
        }

        return IntegrationResult::falha('Validação de assinatura AppsFlyer ainda não implementada.');
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
