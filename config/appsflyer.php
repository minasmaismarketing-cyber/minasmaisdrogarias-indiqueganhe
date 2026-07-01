<?php

declare(strict_types=1);

/**
 * Configuração AppsFlyer — Sprint 3.2.1 (homologação OneLink).
 *
 * OneLink Minas Mais conectado ao backend. Credenciais de API/webhook
 * permanecem vazias até obtenção no painel AppsFlyer.
 */

return [
    /**
     * Enable or disable AppsFlyer integration
     */
    'enabled' => true,

    /**
     * Modo homologação — logs detalhados e endpoint de teste
     */
    'homologation' => true,

    /**
     * OneLink template — URL base criada no painel AppsFlyer
     */
    'onelink_template' => 'https://drogariasminasmais.onelink.me/zjoY/indiqueganhe',

    /**
     * AppsFlyer Dev Key
     * Preencher com a Dev Key do app no painel AppsFlyer:
     * App Settings → App Details → Dev Key
     */
    'dev_key' => '',

    /**
     * App ID Android (package name / ID do app na AppsFlyer)
     * Preencher em: App Settings → Android app
     */
    'app_id_android' => '',

    /**
     * App ID iOS (App Store ID / ID do app na AppsFlyer)
     * Preencher em: App Settings → iOS app
     */
    'app_id_ios' => '',

    /**
     * Secret para validação de assinatura do webhook (futuro)
     * Preencher quando configurar postbacks em: Integration → Webhooks
     */
    'webhook_secret' => '',

    /**
     * Media source (pid) — parâmetro de atribuição OneLink
     */
    'default_media_source' => 'User_invite',

    /**
     * Campanha (c) — parâmetro de atribuição OneLink
     */
    'default_campaign' => 'Indique e Ganhe Minas Mais',

    /**
     * AppsFlyer API Key
     * Preencher em: Integration → API Access
     */
    'api_key' => '',

    /**
     * AppsFlyer API Endpoint
     */
    'endpoint' => '',

    /**
     * Webhook Configuration
     */
    'webhook' => [
        'enabled' => false,
        'secret' => '',
        'validate_signature' => false,
    ],

    /**
     * Event Configuration
     */
    'events' => [
        'install' => 'install',
        'first_open' => 'first_open',
        'purchase' => 'purchase',
        'custom_event' => 'custom_event',
    ],

    /**
     * Validation Rules
     */
    'validation' => [
        'require_media_source' => true,
        'require_campaign' => false,
        'allow_duplicate_events' => false,
        'event_ttl_hours' => 24,
    ],

    /**
     * Retry Configuration
     */
    'retry' => [
        'max_attempts' => 3,
        'delay_seconds' => 5,
    ],

    /**
     * Logging Configuration
     */
    'logging' => [
        'log_all_events' => true,
        'log_payloads' => true,
        'log_responses' => true,
    ],
];
