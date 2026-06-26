<?php

declare(strict_types=1);

class ErrorController extends Controller
{
    public function notFound(): void
    {
        Logger::info('404 page rendered via route');
        http_response_code(404);
        require BASE_PATH . '/views/errors/404.php';
    }
}
