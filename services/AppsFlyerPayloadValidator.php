<?php

declare(strict_types=1);

/**
 * Validador de payload AppsFlyer — Status: STUB (Integrations::STATUS_STUB).
 *
 * Desabilitado em runtime; retorna sucesso permissivo até webhook AppsFlyer ser ativado.
 */
class AppsFlyerPayloadValidator
{
    private bool $enabled = false;

    public function validate(array $payload): IntegrationResult
    {
        if (!$this->enabled) {
            return IntegrationResult::ok('Validação de payload AppsFlyer desabilitada.', ['payload' => $payload]);
        }

        return IntegrationResult::falha('Validação de payload AppsFlyer ainda não implementada.');
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
