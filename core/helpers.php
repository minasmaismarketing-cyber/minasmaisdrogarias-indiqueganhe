<?php

declare(strict_types=1);

function base_path(): string
{
    static $basePath = null;

    if ($basePath !== null) {
        return $basePath;
    }

    if (Env::has('APP_BASE_PATH')) {
        $configured = (string) Env::get('APP_BASE_PATH', '');
        $basePath = $configured !== '' ? '/' . trim($configured, '/') : '';
        return $basePath;
    }

    $scriptName = rawurldecode($_SERVER['SCRIPT_NAME'] ?? '');

    if (str_ends_with($scriptName, '/index.php')) {
        $detected = substr($scriptName, 0, -strlen('/index.php'));
        $basePath = $detected !== '' ? $detected : '';
        return $basePath;
    }

    if (str_ends_with($scriptName, '/server.php')) {
        $detected = substr($scriptName, 0, -strlen('/server.php'));
        $basePath = $detected !== '' ? $detected : '';
        return $basePath;
    }

    $basePath = '';
    return $basePath;
}

function request_path(): string
{
    $uri = resolve_request_uri();
    $uri = rawurldecode($uri);
    $uri = strip_front_script($uri);

    $base = base_path();

    if ($base !== '') {
        if (str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base)) ?: '/';
        } else {
            $encodedBase = str_replace(' ', '%20', $base);
            if (str_starts_with($uri, $encodedBase)) {
                $uri = substr($uri, strlen($encodedBase)) ?: '/';
            }
        }
    }

    $uri = strip_front_script($uri);
    $uri = '/' . trim($uri, '/');

    return $uri === '/' ? '/' : rtrim($uri, '/');
}

function resolve_request_uri(): string
{
    $candidates = [
        $_SERVER['REDIRECT_URL'] ?? null,
        $_SERVER['REQUEST_URI'] ?? null,
        $_SERVER['PATH_INFO'] ?? null,
    ];

    foreach ($candidates as $candidate) {
        if (!is_string($candidate) || $candidate === '') {
            continue;
        }

        $path = parse_url($candidate, PHP_URL_PATH);

        if (is_string($path) && $path !== '') {
            return $path;
        }
    }

    return '/';
}

function strip_front_script(string $path): string
{
    if ($path === '/index.php' || str_ends_with($path, '/index.php')) {
        $path = preg_replace('#/index\.php$#', '', $path) ?? '/';
    }

    if ($path === '/server.php' || str_ends_with($path, '/server.php')) {
        $path = preg_replace('#/server\.php$#', '', $path) ?? '/';
    }

    return $path === '' ? '/' : $path;
}

function url(string $path = ''): string
{
    $appUrl = rtrim((string) Env::get('APP_URL', ''), '/');
    $path = '/' . ltrim($path, '/');

    if ($appUrl !== '') {
        return $appUrl . ($path === '/' ? '' : $path);
    }

    $prefix = base_path() !== '' ? rtrim(base_path(), '/') : '';

    if ($path === '/') {
        return $prefix === '' ? '/' : $prefix . '/';
    }

    return ($prefix === '' ? '' : $prefix) . $path;
}

function asset(string $path): string
{
    $segments = explode('/', ltrim($path, '/'));
    $encoded = implode('/', array_map('rawurlencode', $segments));

    return url('assets/' . $encoded);
}

function brand_logo_url(): string
{
    $file = 'Logo - Drogaria e Perfumaria.png';

    if (is_file(BASE_PATH . '/assets/images/' . $file)) {
        return asset('images/' . $file);
    }

    return asset('images/logo-placeholder.svg');
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrf_field(): string
{
    return Csrf::field();
}

function old(string $key, mixed $default = ''): mixed
{
    return Session::flash('_old_' . $key) ?? $default;
}

function flash(string $key, mixed $default = null): mixed
{
    return Session::flash($key) ?? $default;
}

function auth_check(): bool
{
    return Auth::check();
}

function format_cpf(string $cpf): string
{
    $cpf = Validator::onlyDigits($cpf);

    if (strlen($cpf) !== 11) {
        return $cpf;
    }

    return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.'
        . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
}

function format_phone(string $phone): string
{
    $phone = Validator::onlyDigits($phone);

    if (strlen($phone) === 11) {
        return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 5) . '-' . substr($phone, 7);
    }

    if (strlen($phone) === 10) {
        return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 4) . '-' . substr($phone, 6);
    }

    return $phone;
}

/** @return list<array{label: string, path: string}> */
function admin_nav_items(): array
{
    return [
        ['label' => 'Dashboard', 'path' => '/admin'],
        ['label' => 'Usuários', 'path' => '/admin/usuarios'],
        ['label' => 'Indicações', 'path' => '/admin/indicacoes'],
        ['label' => 'Validações', 'path' => '/admin/validacoes'],
        ['label' => 'Cupons', 'path' => '/admin/cupons'],
        ['label' => 'Campanhas', 'path' => '/admin/campanhas'],
        ['label' => 'AppsFlyer', 'path' => '/admin/appsflyer'],
        ['label' => 'Configurações', 'path' => '/admin/configuracoes'],
    ];
}

function admin_nav_is_active(string $path): bool
{
    $current = request_path();

    if ($path === '/admin') {
        return $current === '/admin';
    }

    return str_starts_with($current, $path);
}

/** @return list<array{label: string, url: ?string}> */
function admin_breadcrumbs(): array
{
    $path = request_path();
    $crumbs = [
        ['label' => 'Admin', 'url' => url('/admin')],
    ];

    $static = [
        '/admin/usuarios' => 'Usuários',
        '/admin/indicacoes' => 'Indicações',
        '/admin/validacoes' => 'Validações',
        '/admin/cupons' => 'Cupons',
        '/admin/campanhas' => 'Campanhas',
        '/admin/appsflyer' => 'AppsFlyer',
        '/admin/configuracoes' => 'Configurações',
        '/admin/campanhas/criar' => 'Nova campanha',
    ];

    if (isset($static[$path])) {
        $crumbs[] = ['label' => $static[$path], 'url' => null];
        return $crumbs;
    }

    if (preg_match('#^/admin/usuarios/(\d+)$#', $path) === 1) {
        $crumbs[] = ['label' => 'Usuários', 'url' => url('/admin/usuarios')];
        $crumbs[] = ['label' => 'Detalhes', 'url' => null];
        return $crumbs;
    }

    if (preg_match('#^/admin/cupons/(\d+)$#', $path) === 1) {
        $crumbs[] = ['label' => 'Cupons', 'url' => url('/admin/cupons')];
        $crumbs[] = ['label' => 'Detalhes', 'url' => null];
        return $crumbs;
    }

    if (preg_match('#^/admin/validacoes/(\d+)$#', $path) === 1) {
        $crumbs[] = ['label' => 'Validações', 'url' => url('/admin/validacoes')];
        $crumbs[] = ['label' => 'Detalhes', 'url' => null];
        return $crumbs;
    }

    if (preg_match('#^/admin/appsflyer/(\d+)$#', $path) === 1) {
        $crumbs[] = ['label' => 'AppsFlyer', 'url' => url('/admin/appsflyer')];
        $crumbs[] = ['label' => 'Detalhes', 'url' => null];
        return $crumbs;
    }

    if (preg_match('#^/admin/campanhas/editar/(\d+)$#', $path) === 1) {
        $crumbs[] = ['label' => 'Campanhas', 'url' => url('/admin/campanhas')];
        $crumbs[] = ['label' => 'Editar', 'url' => null];
        return $crumbs;
    }

    if ($path === '/admin') {
        $crumbs[count($crumbs) - 1]['url'] = null;
    }

    return $crumbs;
}
