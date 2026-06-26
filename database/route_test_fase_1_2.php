<?php

declare(strict_types=1);

/**
 * FASE 1.2 — Testes de resolução de rotas (sem executar controllers).
 * Executar: php database/route_test_fase_1_2.php
 */

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/core/Env.php';
Env::load(BASE_PATH);
require_once BASE_PATH . '/core/helpers.php';
require_once BASE_PATH . '/core/Logger.php';
require_once BASE_PATH . '/core/RoutePattern.php';
require_once BASE_PATH . '/core/Router.php';

/** @var Router $router */
$router = require BASE_PATH . '/config/router.php';

/** @var list<array{method: string, path: string, expect: string|null, middleware: ?string, params: array<string, string>}> */
$tests = [
    // Core — compatibilidade
    ['method' => 'GET', 'path' => '/', 'expect' => 'HomeController@index', 'middleware' => null, 'params' => []],
    ['method' => 'GET', 'path' => '/login', 'expect' => 'AuthController@loginForm', 'middleware' => null, 'params' => []],
    ['method' => 'POST', 'path' => '/login', 'expect' => 'AuthController@login', 'middleware' => null, 'params' => []],
    ['method' => 'GET', 'path' => '/cadastro', 'expect' => 'AuthController@registerForm', 'middleware' => null, 'params' => []],
    ['method' => 'POST', 'path' => '/cadastro', 'expect' => 'AuthController@register', 'middleware' => null, 'params' => []],
    ['method' => 'GET', 'path' => '/dashboard', 'expect' => 'DashboardController@index', 'middleware' => null, 'params' => []],
    ['method' => 'GET', 'path' => '/perfil', 'expect' => 'ProfileController@index', 'middleware' => null, 'params' => []],
    ['method' => 'GET', 'path' => '/indicacoes', 'expect' => 'IndicacoesController@index', 'middleware' => null, 'params' => []],
    ['method' => 'GET', 'path' => '/convite', 'expect' => 'ConviteController@index', 'middleware' => null, 'params' => []],
    ['method' => 'POST', 'path' => '/convite/participar', 'expect' => 'ConviteController@participar', 'middleware' => null, 'params' => []],

    // Admin estático
    ['method' => 'GET', 'path' => '/admin', 'expect' => 'AdminController@index', 'middleware' => null, 'params' => []],
    ['method' => 'GET', 'path' => '/admin/usuarios', 'expect' => 'AdminController@usuarios', 'middleware' => null, 'params' => []],
    ['method' => 'GET', 'path' => '/admin/campanhas', 'expect' => 'AdminController@campanhas', 'middleware' => null, 'params' => []],
    ['method' => 'GET', 'path' => '/admin/campanhas/criar', 'expect' => 'CampanhasController@create', 'middleware' => null, 'params' => []],
    ['method' => 'GET', 'path' => '/admin/validacoes', 'expect' => 'ValidacaoController@adminIndex', 'middleware' => null, 'params' => []],
    ['method' => 'GET', 'path' => '/admin/cupons', 'expect' => 'CuponsController@adminIndex', 'middleware' => null, 'params' => []],
    ['method' => 'GET', 'path' => '/admin/appsflyer', 'expect' => 'AppsFlyerController@adminIndex', 'middleware' => null, 'params' => []],

    // Admin dinâmico — {id}
    ['method' => 'GET', 'path' => '/admin/validacoes/12', 'expect' => 'ValidacaoController@view', 'middleware' => null, 'params' => ['id' => '12']],
    ['method' => 'GET', 'path' => '/admin/cupons/7', 'expect' => 'CuponsController@view', 'middleware' => null, 'params' => ['id' => '7']],
    ['method' => 'GET', 'path' => '/admin/appsflyer/3', 'expect' => 'AppsFlyerController@view', 'middleware' => null, 'params' => ['id' => '3']],
    ['method' => 'GET', 'path' => '/admin/campanhas/editar/5', 'expect' => 'CampanhasController@edit', 'middleware' => null, 'params' => ['id' => '5']],
    ['method' => 'POST', 'path' => '/admin/campanhas/editar/5', 'expect' => 'CampanhasController@edit', 'middleware' => null, 'params' => ['id' => '5']],
    ['method' => 'POST', 'path' => '/admin/campanhas/ativar/1', 'expect' => 'CampanhasController@activate', 'middleware' => null, 'params' => ['id' => '1']],
    ['method' => 'POST', 'path' => '/admin/campanhas/desativar/2', 'expect' => 'CampanhasController@deactivate', 'middleware' => null, 'params' => ['id' => '2']],
    ['method' => 'POST', 'path' => '/admin/campanhas/duplicar/4', 'expect' => 'CampanhasController@duplicate', 'middleware' => null, 'params' => ['id' => '4']],
    ['method' => 'POST', 'path' => '/admin/campanhas/excluir/9', 'expect' => 'CampanhasController@delete', 'middleware' => null, 'params' => ['id' => '9']],

    // API + middleware
    ['method' => 'POST', 'path' => '/api/indicacao/confirmar-cadastro', 'expect' => 'ApiIndicacaoController@confirmarCadastro', 'middleware' => 'ApiAuth', 'params' => []],

    // PUT/DELETE — suporte do router (rota de teste via resolve manual)
    ['method' => 'GET', 'path' => '/notificacoes', 'expect' => 'NotificacoesController@index', 'middleware' => null, 'params' => []],
    ['method' => 'GET', 'path' => '/cadastro-indicado', 'expect' => 'IndicadosController@cadastro', 'middleware' => null, 'params' => []],
    ['method' => 'GET', 'path' => '/finalizado', 'expect' => 'IndicadosController@finalizado', 'middleware' => null, 'params' => []],

    // Deve falhar (id inválido)
    ['method' => 'GET', 'path' => '/admin/cupons/abc', 'expect' => null, 'middleware' => null, 'params' => []],
    ['method' => 'GET', 'path' => '/rota-inexistente', 'expect' => null, 'middleware' => null, 'params' => []],
];

// Teste de placeholders adicionais (RoutePattern isolado)
$patternTests = [
    ['pattern' => '/campanha/{slug}', 'path' => '/campanha/minhas-mais', 'params' => ['slug' => 'minhas-mais']],
    ['pattern' => '/ref/{codigo}', 'path' => '/ref/MM48271', 'params' => ['codigo' => 'MM48271']],
    ['pattern' => '/evt/{uuid}', 'path' => '/evt/550e8400-e29b-41d4-a716-446655440000', 'params' => ['uuid' => '550e8400-e29b-41d4-a716-446655440000']],
];

$results = [];
$passed = 0;
$failed = 0;

foreach ($tests as $test) {
    $resolved = $router->resolve($test['method'], $test['path']);
    $actual = $resolved !== null ? $resolved['controller'] . '@' . $resolved['action'] : null;
    $ok = $actual === $test['expect']
        && ($resolved['middleware'] ?? null) === $test['middleware']
        && ($resolved['params'] ?? []) === $test['params'];

    if ($ok) {
        $passed++;
        $status = 'PASS';
    } else {
        $failed++;
        $status = 'FAIL';
    }

    $results[] = [
        'method' => $test['method'],
        'path' => $test['path'],
        'status' => $status,
        'controller' => $resolved['controller'] ?? '-',
        'action' => $resolved['action'] ?? '-',
        'middleware' => $resolved['middleware'] ?? '-',
        'params' => $resolved['params'] ?? [],
        'expected' => $test['expect'],
    ];
}

foreach ($patternTests as $pt) {
    $compiled = RoutePattern::compile($pt['pattern']);
    $matched = $compiled?->match($pt['path']);
    $ok = $matched === $pt['params'];
    if ($ok) {
        $passed++;
        $status = 'PASS';
    } else {
        $failed++;
        $status = 'FAIL';
    }
    $results[] = [
        'method' => 'PATTERN',
        'path' => $pt['path'],
        'status' => $status,
        'controller' => 'RoutePattern',
        'action' => $pt['pattern'],
        'middleware' => '-',
        'params' => $matched ?? [],
        'expected' => json_encode($pt['params']),
    ];
}

// Teste PUT/DELETE no router
$putDeleteRouter = new Router();
$putDeleteRouter->put('/recurso/{id}', 'HomeController', 'index');
$putDeleteRouter->delete('/recurso/{id}', 'HomeController', 'index');
$putOk = $putDeleteRouter->resolve('PUT', '/recurso/10') !== null;
$deleteOk = $putDeleteRouter->resolve('DELETE', '/recurso/10') !== null;
$passed += ($putOk ? 1 : 0) + ($deleteOk ? 1 : 0);
$failed += ($putOk ? 0 : 1) + ($deleteOk ? 0 : 1);
$results[] = ['method' => 'PUT', 'path' => '/recurso/10', 'status' => $putOk ? 'PASS' : 'FAIL', 'controller' => 'HomeController', 'action' => 'index', 'middleware' => '-', 'params' => ['id' => '10'], 'expected' => 'HomeController@index'];
$results[] = ['method' => 'DELETE', 'path' => '/recurso/10', 'status' => $deleteOk ? 'PASS' : 'FAIL', 'controller' => 'HomeController', 'action' => 'index', 'middleware' => '-', 'params' => ['id' => '10'], 'expected' => 'HomeController@index'];

// Gerar ROTAS_TESTADAS.md
$md = "# ROTAS_TESTADAS.md — Fase 1.2\n\n";
$md .= "**Data:** " . date('d/m/Y H:i') . "\n\n";
$md .= "**Resumo:** {$passed} PASS, {$failed} FAIL\n\n";
$md .= "| Método | Rota | Status | Controller | Action | Middleware | Params | Esperado |\n";
$md .= "|--------|------|--------|------------|--------|------------|--------|----------|\n";

foreach ($results as $row) {
    $params = $row['params'] === [] ? '-' : json_encode($row['params'], JSON_UNESCAPED_UNICODE);
    $md .= sprintf(
        "| %s | `%s` | %s | %s | %s | %s | %s | %s |\n",
        $row['method'],
        $row['path'],
        $row['status'],
        $row['controller'],
        $row['action'],
        $row['middleware'],
        $params,
        $row['expected'] ?? '-'
    );
}

file_put_contents(BASE_PATH . '/ROTAS_TESTADAS.md', $md);

echo "Testes: {$passed} PASS, {$failed} FAIL\n";
echo "Relatório: ROTAS_TESTADAS.md\n";

exit($failed > 0 ? 1 : 0);
