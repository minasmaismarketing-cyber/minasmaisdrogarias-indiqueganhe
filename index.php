<?php

declare(strict_types=1);

require __DIR__ . '/config/bootstrap.php';

$router = require __DIR__ . '/config/router.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = request_path();

Logger::info('Incoming request', [
    'method' => $method,
    'path' => $path,
    'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
    'redirect_url' => $_SERVER['REDIRECT_URL'] ?? '',
    'script_name' => $_SERVER['SCRIPT_NAME'] ?? '',
]);

$router->dispatch($method, $path);
