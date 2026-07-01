<?php

declare(strict_types=1);

/**
 * Integração API KOBE — Status: ATIVA (Integrations::STATUS_ATIVA).
 *
 * Endpoint: POST /api/indicacao/confirmar-cadastro
 * Controller: ApiIndicacaoController (sem alteração nesta sprint)
 * Persistência de indicação: ReferralService::registerApiIndication() (sem alteração nesta sprint)
 *
 * Esta classe documenta o contrato; a implementação permanece nos pontos acima.
 */
final class KobeApiIntegration
{
    public const ENDPOINT = '/api/indicacao/confirmar-cadastro';

    private function __construct()
    {
    }
}
