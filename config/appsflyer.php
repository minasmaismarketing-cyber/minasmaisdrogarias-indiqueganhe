<?php

declare(strict_types=1);

/**
 * Configuração AppsFlyer — Status: PREPARADA (Integrations::STATUS_PREPARADA).
 *
 * Campos vazios até credenciais e webhook serem configurados em produção.
 */

return [
    /**
     * Enable or disable AppsFlyer integration
     */
    'enabled' => false,

    /**
     * AppsFlyer API Key
     * Used for API authentication
     */
    'api_key' => '',

    /**
     * AppsFlyer Dev Key
     * Used for development/testing
     */
    'dev_key' => '',

    /**
     * App ID for Android
     */
    'app_id_android' => '',

    /**
     * App ID for iOS
     */
    'app_id_ios' => '',

    /**
     * OneLink Template
     * Used for deep linking
     */
    'onelink_template' => '',

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
