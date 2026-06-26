<?php

declare(strict_types=1);

class HomeController extends Controller
{
    public function index(): void
    {
        $config = require BASE_PATH . '/config/app.php';
        $dbTest = Database::connectionTest();
        $dbConnected = $dbTest['connected'];
        $csrfReady = Csrf::token() !== '';

        Logger::info('Home loaded', [
            'db' => $dbConnected ? 'connected' : 'disconnected',
            'db_host' => $dbTest['host'],
            'db_name' => $dbTest['db'],
            'db_user' => $dbTest['user'],
            'pdo_status' => $dbTest['status'],
            'csrf' => $csrfReady ? 'ready' : 'missing',
            'session' => session_status() === PHP_SESSION_ACTIVE ? 'active' : 'inactive',
        ]);

        $this->view('home', [
            'title' => 'Indique e Ganhe',
            'dbConnected' => $dbConnected,
            'csrfReady' => $csrfReady,
            'appEnv' => $config['env'],
            'dbTest' => $dbTest,
        ]);
    }
}
