<?php

declare(strict_types=1);

return [
    'name' => Env::get('APP_NAME', 'Indique e Ganhe'),
    'env' => Env::get('APP_ENV', 'local'),
    'debug' => (bool) Env::get('APP_DEBUG', false),
    'url' => Env::get('APP_URL', 'http://localhost:8000'),
    'base_path' => Env::get('APP_BASE_PATH', ''),
    'timezone' => Env::get('APP_TIMEZONE', 'America/Sao_Paulo'),
    'session_name' => Env::get('SESSION_NAME', 'indique_session'),
    'session_lifetime' => (int) Env::get('SESSION_LIFETIME', 7200),
    'session_secure' => (bool) Env::get('SESSION_SECURE', false),
];
