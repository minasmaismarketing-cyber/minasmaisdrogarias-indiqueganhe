<?php

declare(strict_types=1);

/**
 * Validador de webhook AppsFlyer — Status: STUB (Integrations::STATUS_STUB).
 *
 * Desabilitado em runtime; aceita requisições até verificação de assinatura ser implementada.
 */
class AppsFlyerWebhookValidator
{
    private bool $enabled = false;

    public function validate(array $payload, string $signature): IntegrationResult
    {
        unset($payload, $signature);

        if (!$this->enabled) {
            return IntegrationResult::ok('Validação de webhook AppsFlyer desabilitada.');
        }

        return IntegrationResult::falha('Validação de webhook AppsFlyer ainda não implementada.');
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
