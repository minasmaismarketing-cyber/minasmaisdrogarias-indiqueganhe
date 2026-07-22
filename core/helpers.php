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

    $url = url('assets/' . $encoded);
    $file = BASE_PATH . '/assets/' . ltrim($path, '/');

    if (is_file($file)) {
        $url .= '?v=' . filemtime($file);
    }

    return $url;
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
function convite_smart_script_payload(string $ref = '', string $indicadorId = ''): array
{
    $config = AppsFlyerConfig::all();
    $smart = $config['smart_script'] ?? [];

    return [
        'enabled' => (bool) ($smart['enabled'] ?? false),
        'scriptUrl' => (string) ($smart['script_url'] ?? ''),
        'oneLinkURL' => convite_smart_script_one_link_url(),
        'mediaSource' => (string) ($config['default_media_source'] ?? 'User_invite'),
        'campaign' => (string) ($config['default_campaign'] ?? 'Indique e Ganhe Minas Mais'),
        'deepLinkValue' => (string) ($smart['deep_link_value'] ?? 'indique'),
        'deepLinkSub2' => $indicadorId !== ''
            ? $indicadorId
            : (string) ($smart['deep_link_sub2'] ?? ''),
        'deepLinkSub4' => (string) ($smart['deep_link_sub4'] ?? 'indique_ganhe'),
        'deepLinkSub5' => (string) ($smart['deep_link_sub5'] ?? 'homolog'),
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
    $digits = Validator::onlyDigits($phone);

    if (strlen($digits) === 11) {
        return sprintf(
            '(%s) %s-%s',
            substr($digits, 0, 2),
            substr($digits, 2, 5),
            substr($digits, 7)
        );
    }

    if (strlen($digits) === 10) {
        return sprintf(
            '(%s) %s-%s',
            substr($digits, 0, 2),
            substr($digits, 2, 4),
            substr($digits, 6)
        );
    }

    return $phone;
}

/**
 * Normaliza telefone brasileiro para wa.me (apenas dígitos com DDI 55).
 * Não altera o valor persistido — uso exclusivo para montar o link.
 */
function normalize_brazilian_whatsapp_number(string $phone): ?string
{
    $digits = preg_replace('/\D+/', '', $phone) ?? '';
    if ($digits === '') {
        return null;
    }

    // Remove prefixo 0 de operadora / longa distância repetido
    while (str_starts_with($digits, '0')) {
        $digits = substr($digits, 1);
    }

    if (str_starts_with($digits, '55')) {
        $national = substr($digits, 2);
    } else {
        $national = $digits;
    }

    // DDD (2) + número 8 ou 9 dígitos
    if (!preg_match('/^\d{10,11}$/', $national)) {
        return null;
    }

    $ddd = (int) substr($national, 0, 2);
    if ($ddd < 11 || $ddd > 99) {
        return null;
    }

    return '55' . $national;
}

/** Máscara segura para UI: apenas os 2 últimos dígitos (ex.: ••89). */
function mask_whatsapp_phone_tail(string $phone): string
{
    $digits = preg_replace('/\D+/', '', $phone) ?? '';
    if (strlen($digits) < 2) {
        return '••';
    }

    return '••' . substr($digits, -2);
}

/**
 * Telefone mascarado para exibição (ex.: ••5296).
 * Nunca retorna o valor completo.
 */
function mask_phone_for_display(?string $phone): string
{
    $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';
    if ($digits === '') {
        return '••••';
    }
    if (strlen($digits) <= 4) {
        return '••' . $digits;
    }

    return '••' . substr($digits, -4);
}

/**
 * E-mail mascarado para exibição (ex.: d***@gmail.com).
 * Nunca retorna o valor completo.
 */
function mask_email_for_display(?string $email): string
{
    $email = strtolower(trim((string) $email));
    if ($email === '' || !str_contains($email, '@')) {
        return '***@***';
    }

    [$local, $domain] = explode('@', $email, 2);
    $local = trim($local);
    $domain = trim($domain);
    if ($local === '' || $domain === '') {
        return '***@***';
    }

    $first = substr($local, 0, 1);

    return $first . '***@' . $domain;
}

/**
 * CPF mascarado para exibição (ex.: ***.***.***-78).
 * Nunca retorna o valor completo.
 */
function mask_cpf_for_display(?string $cpf): string
{
    $digits = preg_replace('/\D+/', '', (string) $cpf) ?? '';
    if ($digits === '') {
        return '***.***.***-**';
    }
    $tail = substr($digits, -2);
    if (strlen($tail) < 2) {
        $tail = str_pad($tail, 2, '*', STR_PAD_LEFT);
    }

    return '***.***.***-' . $tail;
}

/**
 * Identificador amigável da indicação (telefone → e-mail → CPF → data).
 *
 * @param array<string, mixed> $indicacao
 */
function indicacao_display_identifier(array $indicacao, ?string $fallbackDate = null): string
{
    $telefone = trim((string) ($indicacao['telefone_indicado'] ?? ''));
    if ($telefone !== '') {
        return 'Telefone final ' . mask_phone_for_display($telefone);
    }

    $email = trim((string) ($indicacao['email_indicado'] ?? ''));
    if ($email !== '') {
        return 'E-mail ' . mask_email_for_display($email);
    }

    $cpfDigits = preg_replace('/\D+/', '', (string) ($indicacao['cpf_indicado'] ?? '')) ?? '';
    if ($cpfDigits !== '') {
        $tail = substr($cpfDigits, -2);
        if (strlen($tail) < 2) {
            $tail = str_pad($tail, 2, '*', STR_PAD_LEFT);
        }

        return 'CPF final ••' . $tail;
    }

    $rawDate = $fallbackDate
        ?? (string) ($indicacao['created_at'] ?? $indicacao['indicacao_created_at'] ?? '');
    if ($rawDate !== '') {
        $ts = strtotime($rawDate);
        if ($ts !== false) {
            return 'Indicação de ' . date('d/m/Y', $ts) . ' às ' . date('H:i', $ts);
        }
    }

    return 'Indicação registrada';
}

/** Mensagem amigável de reprovação (códigos internos → texto ao usuário). */
function indicacao_friendly_reject_reason(?string $motivo): string
{
    $motivo = trim((string) $motivo);

    return match ($motivo) {
        'TELEFONE_JA_CADASTRADO' => 'Não foi possível validar esta indicação.',
        'CPF_JA_CADASTRADO', 'CPF_EXISTENTE', 'USUARIO_JA_CADASTRADO' => 'Este CPF já possui cadastro.',
        'CPF_JA_PARTICIPOU', 'JA_PARTICIPOU' => 'Este CPF já participou da campanha.',
        'EMAIL_JA_CADASTRADO' => 'Este e-mail já possui cadastro.',
        'CADASTRO_INCOMPLETO' => 'O cadastro no aplicativo não foi concluído.',
        'DADOS_INVALIDOS' => 'Não foi possível validar os dados informados.',
        'CAMPANHA_ENCERRADA', 'CAMPANHA_INATIVA', 'CAMPANHA_EXPIRADA' => 'A campanha já foi encerrada.',
        'AUTOINDICACAO', 'AUTO_INDICACAO' => 'Não é permitido indicar a si mesmo.',
        'APP_JA_EXISTENTE' => 'Não foi possível validar esta indicação.',
        '' => 'Não foi possível validar esta indicação.',
        default => (preg_match('/^[A-Z0-9_]+$/', $motivo) === 1)
            ? 'Não foi possível validar esta indicação.'
            : $motivo,
    };
}

/** Últimos 4 caracteres do código de cupom (para logs/filtros). */
function cupom_codigo_sufixo(string $codigo): string
{
    $codigo = trim($codigo);
    if ($codigo === '') {
        return '';
    }

    return substr($codigo, -4);
}

/** Máscara administrativa de cupom: ••••ABCD */
function mask_cupom_codigo(?string $codigo): string
{
    $codigo = trim((string) $codigo);
    if ($codigo === '') {
        return '••••';
    }

    return '••••' . cupom_codigo_sufixo($codigo);
}

/**
 * Indicação elegível para avisar o amigo (benefício 5% / WhatsApp).
 * Combina status de validacao_indicacoes e indicacoes.
 */
function is_indicacao_aprovada_para_beneficio(string $validacaoStatus, string $indicacaoStatus = ''): bool
{
    $validacaoOk = in_array($validacaoStatus, [
        ValidacaoIndicacao::STATUS_APROVADO,
        ValidacaoIndicacao::STATUS_BENEFICIO_LIBERADO,
    ], true);

    $indicacaoOk = in_array($indicacaoStatus, [
        Indicacao::STATUS_VALIDADO,
        Indicacao::STATUS_PREMIO_LIBERADO,
    ], true);

    return $validacaoOk || $indicacaoOk;
}

/** Mensagem oficial do WhatsApp (benefício do indicado MINAS-5). */
function beneficio_indicado_whatsapp_message(): string
{
    return "🎉 Sua indicação na Minas Mais foi aprovada!\n\n"
        . "Você ganhou 5% OFF na sua primeira compra pelo App Minas Mais.\n\n"
        . "Use o cupom:\n\n"
        . "🎁 MINAS-5\n\n"
        . "E tem mais: agora você também pode ganhar 10% OFF.\n\n"
        . "Acesse nosso sistema de Indique e Ganhe, faça seu cadastro e compartilhe seu link exclusivo com um novo amigo. Assim que a indicação for aprovada, seu cupom de 10% será liberado.\n\n"
        . "👉 Participe agora:\n"
        . "https://indique.minasmaisdrogarias.com.br";
}

/** Fuso horário oficial da aplicação (tempo real). */
function app_timezone(): DateTimeZone
{
    static $tz = null;

    if ($tz instanceof DateTimeZone) {
        return $tz;
    }

    $name = (string) Env::get('APP_TIMEZONE', 'America/Sao_Paulo');
    try {
        $tz = new DateTimeZone($name);
    } catch (Exception $e) {
        $tz = new DateTimeZone('America/Sao_Paulo');
    }

    return $tz;
}

/**
 * Formata data/hora no fuso da aplicação.
 * Aceita strings do MySQL (Y-m-d H:i:s) já alinhadas pela sessão PDO.
 */
function format_datetime(mixed $value, string $format = 'd/m/Y H:i'): string
{
    if ($value === null) {
        return '—';
    }

    $raw = trim((string) $value);
    if ($raw === '' || $raw === '0000-00-00' || $raw === '0000-00-00 00:00:00') {
        return '—';
    }

    try {
        $dt = new DateTimeImmutable($raw, app_timezone());

        return $dt->setTimezone(app_timezone())->format($format);
    } catch (Exception $e) {
        $ts = strtotime($raw);

        return $ts !== false ? date($format, $ts) : '—';
    }
}

function format_date(mixed $value, string $format = 'd/m/Y'): string
{
    return format_datetime($value, $format);
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
