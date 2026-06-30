<?php

declare(strict_types=1);

class ValidacaoController extends Controller
{
    private ValidationService $validationService;
    private Validacao $validacaoModel;
    private EventLogger $eventLogger;

    public function __construct()
    {
        $this->validationService = new ValidationService();
        $this->validacaoModel = new Validacao();
        $this->eventLogger = new EventLogger();
    }

    public function adminIndex(): void
    {
        Auth::requireAdmin();

        $filters = [
            'status' => $_GET['status'] ?? '',
            'cpf' => $_GET['cpf'] ?? '',
            'nome' => $_GET['nome'] ?? '',
            'email' => $_GET['email'] ?? '',
            'telefone' => $_GET['telefone'] ?? '',
            'data_inicio' => $_GET['data_inicio'] ?? '',
            'data_fim' => $_GET['data_fim'] ?? '',
        ];

        $validacoes = $this->validacaoModel->findAll($filters);
        $stats = $this->validacaoModel->getStats();

        $this->view('admin.validacoes', [
            'title' => 'Validação de Indicações',
            'validacoes' => $validacoes,
            'stats' => $stats,
            'filters' => $filters,
        ], 'admin');
    }

    public function view(int $id): void
    {
        Auth::requireAdmin();

        $validacao = $this->validacaoModel->findById($id);
        if ($validacao === null) {
            Session::flash('error', 'Validação não encontrada.');
            $this->redirect('/admin/validacoes');
        }

        $history = $this->validationService->getHistory($id);

        $this->view('admin.validacao-view', [
            'title' => 'Detalhes da Validação',
            'validacao' => $validacao,
            'history' => $history,
        ], 'admin');
    }

    public function start(): void
    {
        Auth::requireAdmin();

        if (!Csrf::validateRequest()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/validacoes');
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id === 0) {
            Session::flash('error', 'ID inválido.');
            $this->redirect('/admin/validacoes');
        }

        $user = Auth::user();
        $adminEmail = $user !== null ? $user['email'] : null;

        if ($this->validationService->startValidation($id, $adminEmail)) {
            Session::flash('success', 'Validação iniciada com sucesso.');
        } else {
            Session::flash('error', 'Erro ao iniciar validação.');
        }

        $this->redirect('/admin/validacoes');
    }

    public function approve(): void
    {
        Auth::requireAdmin();

        if (!Csrf::validateRequest()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/validacoes');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $observacao = $_POST['observacao'] ?? null;

        if ($id === 0) {
            Session::flash('error', 'ID inválido.');
            $this->redirect('/admin/validacoes');
        }

        $user = Auth::user();
        $adminEmail = $user !== null ? $user['email'] : null;

        if ($this->validationService->approveValidation($id, $observacao, $adminEmail)) {
            Session::flash('success', 'Validação aprovada com sucesso.');
        } else {
            Session::flash('error', 'Erro ao aprovar validação.');
        }

        $this->redirect('/admin/validacoes');
    }

    public function reject(): void
    {
        Auth::requireAdmin();

        if (!Csrf::validateRequest()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/validacoes');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? '');

        if ($id === 0) {
            Session::flash('error', 'ID inválido.');
            $this->redirect('/admin/validacoes');
        }

        if ($motivo === '') {
            Session::flash('error', 'Informe o motivo da rejeição.');
            $this->redirect('/admin/validacoes');
        }

        $user = Auth::user();
        $adminEmail = $user !== null ? $user['email'] : null;

        if ($this->validationService->rejectValidation($id, $motivo, $adminEmail)) {
            Session::flash('success', 'Validação rejeitada com sucesso.');
        } else {
            Session::flash('error', 'Erro ao rejeitar validação.');
        }

        $this->redirect('/admin/validacoes');
    }

    public function cancel(): void
    {
        Auth::requireAdmin();

        if (!Csrf::validateRequest()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/validacoes');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $motivo = $_POST['motivo'] ?? null;

        if ($id === 0) {
            Session::flash('error', 'ID inválido.');
            $this->redirect('/admin/validacoes');
        }

        $user = Auth::user();
        $adminEmail = $user !== null ? $user['email'] : null;

        if ($this->validationService->cancelValidation($id, $motivo, $adminEmail)) {
            Session::flash('success', 'Validação cancelada com sucesso.');
        } else {
            Session::flash('error', 'Erro ao cancelar validação.');
        }

        $this->redirect('/admin/validacoes');
    }
}
