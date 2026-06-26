<?php

declare(strict_types=1);

/**
 * Entrada da API — rotas serão implementadas nas próximas etapas.
 */

require dirname(__DIR__) . '/config/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'success' => false,
    'message' => 'API em construção.',
], JSON_UNESCAPED_UNICODE);
