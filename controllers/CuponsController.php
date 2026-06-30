<?php

declare(strict_types=1);

class CuponsController extends Controller
{
    private CupomService $cupomService;
    private CupomRepository $cupomRepository;
    private EventLogger $eventLogger;

    public function __construct()
    {
        $this->cupomService = new CupomService();
        $this->cupomRepository = new CupomRepository();
        $this->eventLogger = new EventLogger();
    }

    public function adminIndex(): void
    {
        Auth::requireAdmin();

        $filters = [
            'codigo' => $_GET['codigo'] ?? '',
            'usuario_id' => $_GET['usuario_id'] ?? '',
            'campanha_id' => $_GET['campanha_id'] ?? '',
            'status' => $_GET['status'] ?? '',
            'data_inicio' => $_GET['data_inicio'] ?? '',
            'data_fim' => $_GET['data_fim'] ?? '',
        ];

        $cupons = $this->cupomRepository->findAll($filters);
        $stats = $this->cupomRepository->getStats();

        $this->view('admin.cupons', [
            'title' => 'Gerenciamento de Cupons',
            'cupons' => $cupons,
            'stats' => $stats,
            'filters' => $filters,
        ], 'admin');
    }

    public function show(int $id): void
    {
        Auth::requireAdmin();

        $cupom = $this->cupomRepository->findById($id);
        if ($cupom === null) {
            Session::flash('error', 'Cupom não encontrado.');
            $this->redirect('/admin/cupons');
        }

        $history = $this->cupomService->getHistory($id);

        $this->view('admin.cupom-view', [
            'title' => 'Detalhes do Cupom',
            'cupom' => $cupom,
            'history' => $history,
        ], 'admin');
    }

    public function cancel(): void
    {
        Auth::requireAdmin();

        if (!Csrf::validateRequest()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/cupons');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $motivo = $_POST['motivo'] ?? null;

        if ($id === 0) {
            Session::flash('error', 'ID inválido.');
            $this->redirect('/admin/cupons');
        }

        $user = Auth::user();
        $adminEmail = $user !== null ? $user['email'] : null;

        if ($this->cupomService->cancel($id, $motivo, $adminEmail)) {
            Session::flash('success', 'Cupom cancelado com sucesso.');
        } else {
            Session::flash('error', 'Erro ao cancelar cupom.');
        }

        $this->redirect('/admin/cupons');
    }

    public function expire(): void
    {
        Auth::requireAdmin();

        if (!Csrf::validateRequest()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/cupons');
        }

        $id = (int) ($_POST['id'] ?? 0);

        if ($id === 0) {
            Session::flash('error', 'ID inválido.');
            $this->redirect('/admin/cupons');
        }

        $user = Auth::user();
        $adminEmail = $user !== null ? $user['email'] : null;

        if ($this->cupomService->expire($id, $adminEmail)) {
            Session::flash('success', 'Cupom expirado com sucesso.');
        } else {
            Session::flash('error', 'Erro ao expirar cupom.');
        }

        $this->redirect('/admin/cupons');
    }

    public function reactivate(): void
    {
        Auth::requireAdmin();

        if (!Csrf::validateRequest()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/cupons');
        }

        $id = (int) ($_POST['id'] ?? 0);

        if ($id === 0) {
            Session::flash('error', 'ID inválido.');
            $this->redirect('/admin/cupons');
        }

        $user = Auth::user();
        $adminEmail = $user !== null ? $user['email'] : null;

        if ($this->cupomService->reactivate($id, $adminEmail)) {
            Session::flash('success', 'Cupom reativado com sucesso.');
        } else {
            Session::flash('error', 'Erro ao reativar cupom.');
        }

        $this->redirect('/admin/cupons');
    }

    public function userIndex(): void
    {
        AuthMiddleware::requireAuth();

        $user = Auth::user();
        if ($user === null) {
            $this->redirect('/login');
        }

        $cupons = $this->cupomRepository->findByUsuario($user['id']);

        $this->view('cupons.index', [
            'title' => 'Meus Cupons',
            'cupons' => $cupons,
        ], 'app');
    }
}
