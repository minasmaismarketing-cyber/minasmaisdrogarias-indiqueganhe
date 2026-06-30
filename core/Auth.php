<?php

declare(strict_types=1);

class Auth
{
    private const SESSION_KEY = 'auth_user_id';
    private const REMEMBER_COOKIE = 'indique_remember';
    private const REMEMBER_DAYS = 30;

    public static function login(array $user, bool $remember = false): void
    {
        Session::regenerate();
        Session::set(self::SESSION_KEY, (int) $user['id']);

        if ($remember) {
            self::setRememberToken((int) $user['id']);
        } else {
            self::clearRememberToken((int) $user['id']);
        }

        Logger::info('User logged in', ['user_id' => (int) $user['id']]);
    }

    public static function logout(): void
    {
        $userId = self::id();

        if ($userId !== null) {
            self::clearRememberToken($userId);
        }

        Session::remove(self::SESSION_KEY);
        Session::destroy();
        self::forgetRememberCookie();

        Logger::info('User logged out', ['user_id' => $userId]);
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        $id = Session::get(self::SESSION_KEY);

        return is_int($id) ? $id : (is_numeric($id) ? (int) $id : null);
    }

    /** @return array<string, mixed>|null */
    public static function user(): ?array
    {
        $id = self::id();

        if ($id === null) {
            $id = self::loginFromRememberCookie();
        }

        if ($id === null) {
            return null;
        }

        $usuario = (new Usuario())->findById($id);

        if ($usuario === null) {
            self::invalidateAuthState();
            return null;
        }

        if (self::isAccountExcluded($usuario)) {
            self::invalidateAuthState();
            return null;
        }

        return $usuario;
    }

    public static function isAdmin(): bool
    {
        $user = self::user();

        return $user !== null && (new Usuario())->isAdmin($user);
    }

    public static function requireAdmin(): void
    {
        AuthMiddleware::requireAuth();

        $user = self::user();

        if ($user === null || !(new Usuario())->isAdmin($user)) {
            Session::flash('error', 'Acesso negado. Apenas administradores podem acessar esta página.');
            header('Location: ' . url('/dashboard'));
            exit;
        }
    }

    public static function attemptFromRememberCookie(): void
    {
        self::loginFromRememberCookie();
    }

    private static function loginFromRememberCookie(): ?int
    {
        $cookie = $_COOKIE[self::REMEMBER_COOKIE] ?? null;

        if (!is_string($cookie) || !str_contains($cookie, '|')) {
            return null;
        }

        [$userId, $token] = explode('|', $cookie, 2);
        $userId = (int) $userId;

        if ($userId <= 0 || $token === '') {
            return null;
        }

        $usuario = (new Usuario())->findById($userId);

        if ($usuario === null || empty($usuario['remember_token'])) {
            return null;
        }

        if (!hash_equals((string) $usuario['remember_token'], hash('sha256', $token))) {
            return null;
        }

        if (!self::canRestoreRememberSession($usuario)) {
            self::forgetRememberCookie();
            return null;
        }

        Session::set(self::SESSION_KEY, $userId);

        return $userId;
    }

    /** @param array<string, mixed> $usuario */
    private static function isAccountExcluded(array $usuario): bool
    {
        if (isset($usuario['deleted_at']) && $usuario['deleted_at'] !== null && $usuario['deleted_at'] !== '') {
            return true;
        }

        if (isset($usuario['ativo']) && (int) $usuario['ativo'] === 0) {
            return true;
        }

        return false;
    }

    /** @param array<string, mixed> $usuario */
    private static function canRestoreRememberSession(array $usuario): bool
    {
        if (!isset($usuario['ativo']) || (int) $usuario['ativo'] !== 1) {
            return false;
        }

        if (isset($usuario['deleted_at']) && $usuario['deleted_at'] !== null && $usuario['deleted_at'] !== '') {
            return false;
        }

        return true;
    }

    private static function invalidateAuthState(): void
    {
        Session::remove(self::SESSION_KEY);
        self::forgetRememberCookie();
    }

    private static function setRememberToken(int $userId): void
    {
        $token = bin2hex(random_bytes(32));
        (new Usuario())->updateRememberToken($userId, hash('sha256', $token));

        $secure = (bool) Env::get('SESSION_SECURE', false);
        $cookiePath = base_path() !== '' ? base_path() . '/' : '/';

        setcookie(
            self::REMEMBER_COOKIE,
            $userId . '|' . $token,
            [
                'expires' => time() + (self::REMEMBER_DAYS * 86400),
                'path' => $cookiePath,
                'secure' => $secure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]
        );
    }

    private static function clearRememberToken(int $userId): void
    {
        (new Usuario())->updateRememberToken($userId, null);
        self::forgetRememberCookie();
    }

    private static function forgetRememberCookie(): void
    {
        $cookiePath = base_path() !== '' ? base_path() . '/' : '/';
        $secure = (bool) Env::get('SESSION_SECURE', false);

        setcookie(self::REMEMBER_COOKIE, '', [
            'expires' => time() - 3600,
            'path' => $cookiePath,
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}
