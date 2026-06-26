<?php

declare(strict_types=1);

class AppsFlyerController extends Controller
{
    private AppsFlyerService $appsFlyerService;
    private AppsFlyerRepository $appsFlyerRepository;

    public function __construct()
    {
        $this->appsFlyerService = new AppsFlyerService();
        $this->appsFlyerRepository = new AppsFlyerRepository();
    }

    public function adminIndex(): void
    {
        $this->requireAdmin();

        $filters = [
            'af_status' => $_GET['status'] ?? '',
            'platform' => $_GET['platform'] ?? '',
            'install_type' => $_GET['install_type'] ?? '',
            'data_inicio' => $_GET['data_inicio'] ?? '',
            'data_fim' => $_GET['data_fim'] ?? '',
        ];

        $events = $this->appsFlyerRepository->findAll($filters);
        $stats = $this->appsFlyerRepository->getStats();
        $recentEvents = $this->appsFlyerRepository->listRecent(10);
        $integrationEnabled = $this->appsFlyerService->isEnabled();

        $this->view('admin.appsflyer', [
            'title' => 'AppsFlyer Integration',
            'events' => $events,
            'stats' => $stats,
            'recentEvents' => $recentEvents,
            'integrationEnabled' => $integrationEnabled,
            'filters' => $filters,
        ], 'admin');
    }

    public function view(int $id): void
    {
        $this->requireAdmin();

        $event = $this->appsFlyerRepository->findById($id);
        if ($event === null) {
            Session::flash('error', 'Evento não encontrado.');
            $this->redirect('/admin/appsflyer');
        }

        $this->view('admin.appsflyer-view', [
            'title' => 'Detalhes do Evento AppsFlyer',
            'event' => $event,
        ], 'admin');
    }

    public function validate(): void
    {
        $this->requireAdmin();

        if (!Csrf::validateRequest()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/appsflyer');
        }

        $id = (int) ($_POST['id'] ?? 0);

        if ($id === 0) {
            Session::flash('error', 'ID inválido.');
            $this->redirect('/admin/appsflyer');
        }

        if ($this->appsFlyerService->validateEvent($id)) {
            Session::flash('success', 'Evento validado com sucesso.');
        } else {
            Session::flash('error', 'Erro ao validar evento.');
        }

        $this->redirect('/admin/appsflyer');
    }

    public function reject(): void
    {
        $this->requireAdmin();

        if (!Csrf::validateRequest()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/appsflyer');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $reason = $_POST['reason'] ?? '';

        if ($id === 0) {
            Session::flash('error', 'ID inválido.');
            $this->redirect('/admin/appsflyer');
        }

        if ($this->appsFlyerService->rejectEvent($id, $reason)) {
            Session::flash('success', 'Evento rejeitado com sucesso.');
        } else {
            Session::flash('error', 'Erro ao rejeitar evento.');
        }

        $this->redirect('/admin/appsflyer');
    }

    private function requireAdmin(): void
    {
        AuthMiddleware::requireAuth();

        $user = Auth::user();

        if ($user === null || $user['email'] !== AdminController::ADMIN_EMAIL) {
            Session::flash('error', 'Acesso negado. Apenas administradores podem acessar esta página.');
            $this->redirect('/dashboard');
        }
    }
}
