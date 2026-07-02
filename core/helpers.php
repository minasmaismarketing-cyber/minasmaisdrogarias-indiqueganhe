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

function first_name_from_full(?string $nome, string $fallback = 'Alguém'): string
{
    $nome = trim((string) $nome);

    if ($nome === '') {
        return $fallback;
    }

    $parts = preg_split('/\s+/u', $nome) ?: [];

    return $parts[0] !== '' ? $parts[0] : $fallback;
}

function app_download_url(): string
{
    static $url = null;

    if ($url !== null) {
        return $url;
    }

    $configured = trim((string) Env::get('APP_DOWNLOAD_URL', ''));

    $url = $configured !== '' ? $configured : '#';

    return $url;
}

/**
 * URL OneLink base para Smart Script (template + redirection profile).
 */
function convite_smart_script_one_link_url(): string
{
    $config = AppsFlyerConfig::all();
    $smart = $config['smart_script'] ?? [];

    $base = rtrim(trim((string) ($smart['one_link_url'] ?? '')), '/');
    $profile = trim((string) ($smart['redirection_profile'] ?? ''));

    if ($base !== '' && $profile !== '') {
        return $base . '/' . $profile;
    }

    if ($base !== '') {
        return $base . '/';
    }

    return trim((string) ($config['onelink_template'] ?? ''));
}

/** @return array<string, mixed> */
function convite_smart_script_payload(string $ref = ''): array
{
    $config = AppsFlyerConfig::all();
    $smart = $config['smart_script'] ?? [];

    return [
        'enabled' => (bool) ($smart['enabled'] ?? false),
        'scriptUrl' => (string) ($smart['script_url'] ?? ''),
        'oneLinkURL' => convite_smart_script_one_link_url(),
        'mediaSource' => (string) ($config['default_media_source'] ?? 'User_invite'),
        'campaign' => (string) ($config['default_campaign'] ?? 'Indique e Ganhe Minas Mais'),
        'afSub2' => (string) ($smart['af_sub2'] ?? '1'),
        'afSub4' => (string) ($smart['af_sub4'] ?? 'indique_ganhe'),
        'afSub5' => (string) ($smart['af_sub5'] ?? 'homolog'),
        'fallbackUrl' => app_download_url(),
    ];
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
        ['label' => 'Diagnóstico', 'path' => '/admin/diagnostico'],
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
        '/admin/diagnostico' => 'Diagnóstico',
        '/admin/configuracoes' => 'Configurações',
        '/admin/campanhas/criar' => 'Nova campanha',
    ];

    if (isset($static[$path])) {
        $crumbs[] = ['label' => $static[$path], 'url' => null];
        return $crumbs;
    }

    if (preg_match('#^/admin/indicacoes/(\d+)$#', $path) === 1) {
        $crumbs[] = ['label' => 'Indicações', 'url' => url('/admin/indicacoes')];
        $crumbs[] = ['label' => 'Detalhes', 'url' => null];
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

    if (preg_match('#^/admin/campanhas/(\d+)$#', $path) === 1) {
        $crumbs[] = ['label' => 'Campanhas', 'url' => url('/admin/campanhas')];
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

/**
 * @param array<string, mixed> $filters
 * @param list<string> $ignoreKeys
 */
function admin_has_active_filters(array $filters, array $ignoreKeys = ['page']): bool
{
    foreach ($filters as $key => $value) {
        if (in_array((string) $key, $ignoreKeys, true)) {
            continue;
        }

        if (is_string($value)) {
            $value = trim($value);
        }

        if ($value === '' || $value === null) {
            continue;
        }

        if (in_array($value, ['todos', 'TODOS'], true)) {
            continue;
        }

        return true;
    }

    return false;
}

/**
 * @param array<string, mixed> $filters
 */
function admin_filter_panel(string $id, array $filters, callable $renderForm): void
{
    $filterPanelId = $id;
    $filterPanelOpen = admin_has_active_filters($filters);
    require BASE_PATH . '/views/partials/admin-filter-panel.php';
}

/**
 * @param array{
 *   title: string,
 *   meta?: ?string,
 *   headerActions?: ?string,
 *   emptyMessage: string,
 *   columns: list<array{label: string, class?: string, align?: string}>,
 *   rows: list<mixed>,
 *   zebra?: bool,
 *   class?: string,
 * } $config
 * @param callable(mixed, int): void $renderRow
 */
function admin_table(array $config, callable $renderRow): void
{
    $adminTableTitle = $config['title'];
    $adminTableMeta = $config['meta'] ?? null;
    $adminTableHeaderActions = $config['headerActions'] ?? null;
    $adminTableEmptyMessage = $config['emptyMessage'];
    $adminTableColumns = $config['columns'];
    $adminTableRows = $config['rows'];
    $adminTableZebra = $config['zebra'] ?? true;
    $adminTableClass = trim('admin-table' . (isset($config['class']) ? ' ' . $config['class'] : ''));
    $adminTableRenderRow = $renderRow;
    require BASE_PATH . '/views/partials/admin-table.php';
}

function admin_empty_state(string $message, string $icon = '📭'): void
{
    $adminEmptyMessage = $message;
    $adminEmptyIcon = $icon;
    require BASE_PATH . '/views/partials/admin-empty-state.php';
}

/**
 * @param list<array{label: string, value: string, html?: bool, highlight?: bool}> $lines
 */
function admin_info_lines(array $lines): void
{
    $adminInfoLines = $lines;
    require BASE_PATH . '/views/partials/admin-info-lines.php';
}

/**
 * @param list<array{label: string, value: string, html?: bool, highlight?: bool}> $lines
 */
function admin_detail_card(string $title, array $lines, ?string $headerBadgeHtml = null): void
{
    $adminDetailTitle = $title;
    $adminDetailHeaderBadge = $headerBadgeHtml;
    $adminInfoLines = $lines;
    require BASE_PATH . '/views/partials/admin-detail-card.php';
}
