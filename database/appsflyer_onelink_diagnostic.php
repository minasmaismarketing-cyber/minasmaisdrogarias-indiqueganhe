<?php

declare(strict_types=1);

/**
 * Comando de diagnóstico OneLink — Sprint 3.2.1
 *
 * Exibe a URL final que o Dashboard geraria para um usuário de exemplo.
 *
 * Uso:
 *   php database/appsflyer_onelink_diagnostic.php
 *   php database/appsflyer_onelink_diagnostic.php MM123456 42
 */

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/core/Env.php';
Env::load(BASE_PATH);
require_once BASE_PATH . '/core/helpers.php';
require_once BASE_PATH . '/services/AppsFlyerConfig.php';
require_once BASE_PATH . '/services/OneLinkBuilder.php';
require_once BASE_PATH . '/services/InviteLinkService.php';
require_once BASE_PATH . '/services/AppsFlyerDiagnosticService.php';

$codigo = strtoupper(trim($argv[1] ?? 'MM000001'));
$usuarioId = trim($argv[2] ?? '1');

$usuario = [
    'id' => $usuarioId,
    'codigo_indicador' => $codigo,
    'nome' => 'Usuário Homologação',
];

$report = (new AppsFlyerDiagnosticService())->getGeneratedInviteLinkReport($usuario);

$labels = [
    'generated_invite_link' => 'Generated Invite Link',
    'onelink_template' => 'OneLink Template',
    'enabled' => 'Enabled',
    'media_source' => 'Media Source',
    'campaign' => 'Campaign',
    'deep_link_value' => 'Deep Link Value',
    'deep_link_sub1' => 'Deep Link Sub1',
    'deep_link_sub2' => 'Deep Link Sub2',
    'deep_link_sub3' => 'Deep Link Sub3',
    'deep_link_sub4' => 'Deep Link Sub4',
    'pid' => 'pid',
    'c' => 'c',
    'link_type' => 'Link Type',
];

echo PHP_EOL . '=== AppsFlyer OneLink Diagnostic ===' . PHP_EOL . PHP_EOL;

foreach ($labels as $key => $label) {
    $value = $report[$key] ?? '';

    if (is_bool($value)) {
        $value = $value ? 'true' : 'false';
    }

    echo str_pad($label . ':', 22) . $value . PHP_EOL;
}

echo PHP_EOL;
