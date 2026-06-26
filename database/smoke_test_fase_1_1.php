<?php

declare(strict_types=1);

/**
 * FASE 1.1 — Smoke tests estáticos (infraestrutura).
 * Executar no servidor: php database/smoke_test_fase_1_1.php
 */

$basePath = dirname(__DIR__);
$errors = [];
$passed = [];

function pass(string $msg): void
{
    global $passed;
    $passed[] = $msg;
    echo "[OK] {$msg}\n";
}

function fail(string $msg): void
{
    global $errors;
    $errors[] = $msg;
    echo "[FAIL] {$msg}\n";
}

// --- .env ---
$envFile = $basePath . '/.env';
if (!is_file($envFile)) {
    fail('.env não encontrado');
} else {
    pass('.env existe');
    $envContent = file_get_contents($envFile) ?: '';
    if (str_contains($envContent, 'indique.minasmaisdrogarias.com.br')) {
        pass('APP_URL aponta para indique.minasmaisdrogarias.com.br');
    } else {
        fail('APP_URL não contém indique.minasmaisdrogarias.com.br');
    }
    if (str_contains($envContent, 'indique.nexden.com.br')) {
        fail('.env ainda contém indique.nexden.com.br');
    } else {
        pass('.env sem referência a indique.nexden.com.br');
    }
    if (str_contains($envContent, 'u146248277_indicacao')) {
        pass('DB_DATABASE configurado para u146248277_indicacao');
    } else {
        fail('DB_DATABASE não é u146248277_indicacao');
    }
    if (str_contains($envContent, 'u146248277_indiqueganhe')) {
        pass('DB_USERNAME configurado para u146248277_indiqueganhe');
    } else {
        fail('DB_USERNAME incorreto');
    }
}

// --- Bootstrap + helpers ---
require_once $basePath . '/core/Env.php';
Env::load($basePath);
require_once $basePath . '/core/helpers.php';

$expectedUrl = 'https://indique.minasmaisdrogarias.com.br';
if (Env::get('APP_URL') === $expectedUrl) {
    pass('Env::get(APP_URL) = ' . $expectedUrl);
} else {
    fail('Env::get(APP_URL) = ' . (string) Env::get('APP_URL') . ' (esperado: ' . $expectedUrl . ')');
}

$inviteUrl = url('/convite?ref=MM000001');
if (str_starts_with($inviteUrl, $expectedUrl . '/convite')) {
    pass('url(/convite) gera link no domínio Minas Mais');
} else {
    fail('url(/convite) = ' . $inviteUrl);
}

// --- Rotas core ---
$routesFile = $basePath . '/config/routes.php';
$routesContent = file_get_contents($routesFile) ?: '';
$coreRoutes = [
    '/' => 'Home',
    '/login' => 'Login',
    '/cadastro' => 'Cadastro',
    '/dashboard' => 'Dashboard',
    '/perfil' => 'Perfil',
    '/convite' => 'Convite',
    '/indicacoes' => 'Indicações',
];
foreach ($coreRoutes as $path => $label) {
    if (str_contains($routesContent, "'" . $path . "'")) {
        pass("Rota {$label} ({$path}) registrada");
    } else {
        fail("Rota {$label} ({$path}) não encontrada em routes.php");
    }
}

// --- Views ---
$coreViews = [
    'views/home.php' => 'Home',
    'views/auth/login.php' => 'Login',
    'views/auth/cadastro.php' => 'Cadastro',
    'views/dashboard/index.php' => 'Dashboard',
    'views/perfil/index.php' => 'Perfil',
    'views/convite/index.php' => 'Convite',
    'views/indicacoes/index.php' => 'Indicações',
];
foreach ($coreViews as $file => $label) {
    if (is_file($basePath . '/' . $file)) {
        pass("View {$label} existe");
    } else {
        fail("View {$label} ausente: {$file}");
    }
}

// --- Controllers ---
$coreControllers = [
    'HomeController.php',
    'AuthController.php',
    'DashboardController.php',
    'ProfileController.php',
    'ConviteController.php',
    'IndicacoesController.php',
];
foreach ($coreControllers as $file) {
    if (is_file($basePath . '/controllers/' . $file)) {
        pass("Controller {$file} existe");
    } else {
        fail("Controller {$file} ausente");
    }
}

// --- SQL de migração de links ---
if (is_file($basePath . '/database/migration_domain_links.sql')) {
    pass('migration_domain_links.sql gerado para revisão');
} else {
    fail('migration_domain_links.sql não encontrado');
}

// --- DB connection (opcional se senha configurada) ---
if (Env::has('DB_PASSWORD') && (string) Env::get('DB_PASSWORD') !== '') {
    require_once $basePath . '/config/database.php';
    $test = Database::connectionTest();
    if ($test['connected']) {
        pass('Conexão MySQL OK (' . $test['host'] . ' / ' . $test['db'] . ')');
    } else {
        fail('Conexão MySQL: ' . $test['status']);
    }
} else {
    pass('DB_PASSWORD vazio — teste de conexão ignorado (preencher no servidor)');
}

echo "\n--- Resumo ---\n";
echo count($passed) . ' OK, ' . count($errors) . ' FAIL' . "\n";

exit($errors === [] ? 0 : 1);
