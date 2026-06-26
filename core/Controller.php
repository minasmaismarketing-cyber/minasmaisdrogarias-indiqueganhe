<?php

declare(strict_types=1);

abstract class Controller
{
    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        extract($data, EXTR_SKIP);

        $viewFile = BASE_PATH . '/views/' . str_replace('.', '/', $view) . '.php';

        if (!is_file($viewFile)) {
            throw new RuntimeException("View não encontrada: {$view}");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $layoutFile = BASE_PATH . '/views/layouts/' . $layout . '.php';

        if (!is_file($layoutFile)) {
            echo $content;
            return;
        }

        require $layoutFile;
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }
}
