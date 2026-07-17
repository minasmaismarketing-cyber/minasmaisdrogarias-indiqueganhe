<?php

declare(strict_types=1);

class CuponsController extends Controller
{
    private const IMPORT_SESSION_KEY = 'cupom_import_pending';

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

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $filters = [
            'codigo' => trim((string) ($_GET['codigo'] ?? '')),
            'indicador' => trim((string) ($_GET['indicador'] ?? '')),
            'campanha_id' => (string) ($_GET['campanha_id'] ?? ''),
            'status' => (string) ($_GET['status'] ?? ''),
            'data_inicio' => (string) ($_GET['data_inicio'] ?? ''),
            'data_fim' => (string) ($_GET['data_fim'] ?? ''),
        ];

        $total = $this->cupomRepository->countFiltered($filters);
        $cupons = $this->cupomRepository->findAll($filters, $limit, $offset);
        $stats = (new Cupom())->getFilteredStats($filters);
        $campanhaFiltro = $filters['campanha_id'] !== '' ? (int) $filters['campanha_id'] : null;
        $estoqueStats = $this->cupomRepository->getEstoqueStats($campanhaFiltro);
        $campanhas = (new Campanha())->findAll();
        $totalPages = $total > 0 ? (int) ceil($total / $limit) : 1;

        $this->view('admin.cupons', [
            'title' => 'Gerenciamento de Cupons',
            'cupons' => $cupons,
            'stats' => $stats,
            'estoqueStats' => $estoqueStats,
            'filters' => $filters,
            'campanhas' => $campanhas,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'hasNext' => $page < $totalPages,
            'hasPrev' => $page > 1,
        ], 'admin');
    }

    public function importForm(): void
    {
        Auth::requireAdmin();

        $pending = Session::get(self::IMPORT_SESSION_KEY);
        $campanhas = (new Campanha())->findAll();

        $this->view('admin.cupons-import', [
            'title' => 'Importar cupons',
            'campanhas' => $campanhas,
            'pending' => is_array($pending) ? $pending : null,
        ], 'admin');
    }

    public function importValidate(): void
    {
        Auth::requireAdmin();

        if (!Csrf::validateRequest()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/cupons/importar');
        }

        $campanhaId = (int) ($_POST['campanha_id'] ?? 0);
        $file = $_FILES['arquivo'] ?? null;

        if (!is_array($file)) {
            Session::flash('error', 'Selecione um arquivo CSV.');
            $this->redirect('/admin/cupons/importar');
        }

        try {
            $result = $this->cupomService->validateCsvImport($file, $campanhaId);
        } catch (InvalidArgumentException $e) {
            Session::remove(self::IMPORT_SESSION_KEY);
            Session::flash('error', $e->getMessage());
            $this->redirect('/admin/cupons/importar');
        }

        Session::set(self::IMPORT_SESSION_KEY, [
            'campanha_id' => $campanhaId,
            'codigos' => $result['a_importar'],
            'resumo' => $result['resumo'],
            'validated_at' => time(),
        ]);

        Session::flash('success', 'Arquivo validado. Confira o resumo antes de confirmar.');
        $this->redirect('/admin/cupons/importar');
    }

    public function importConfirm(): void
    {
        Auth::requireAdmin();

        if (!Csrf::validateRequest()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/cupons/importar');
        }

        $pending = Session::get(self::IMPORT_SESSION_KEY);
        if (!is_array($pending) || empty($pending['codigos']) || empty($pending['campanha_id'])) {
            Session::flash('error', 'Nenhuma importação validada. Valide o arquivo primeiro.');
            $this->redirect('/admin/cupons/importar');
        }

        $campanhaId = (int) $pending['campanha_id'];
        $codigos = is_array($pending['codigos']) ? $pending['codigos'] : [];
        $user = Auth::user();
        $adminEmail = $user !== null ? (string) $user['email'] : null;

        try {
            $result = $this->cupomService->confirmCsvImport($campanhaId, $codigos, $adminEmail);
            Session::remove(self::IMPORT_SESSION_KEY);
            Session::flash(
                'success',
                sprintf(
                    'Importação concluída: %d cupom(ns) inserido(s), %d ignorado(s).',
                    $result['imported'],
                    $result['skipped']
                )
            );
            $this->redirect('/admin/cupons');
        } catch (Throwable $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect('/admin/cupons/importar');
        }
    }

    public function delete(): void
    {
        Auth::requireAdmin();

        if (!Csrf::validateRequest()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/cupons');
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            Session::flash('error', 'ID inválido.');
            $this->redirect('/admin/cupons');
        }

        if ($this->cupomService->deleteDisponivel($id)) {
            Session::flash('success', 'Cupom disponível excluído.');
        } else {
            Session::flash('error', 'Exclusão bloqueada. Apenas cupons disponíveis (sem atribuição) podem ser apagados.');
        }

        $this->redirect('/admin/cupons');
    }

    public function deleteBatch(): void
    {
        Auth::requireAdmin();

        if (!Csrf::validateRequest()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/cupons');
        }

        $ids = $_POST['ids'] ?? [];
        if (!is_array($ids)) {
            $ids = [];
        }

        $deleted = $this->cupomService->deleteDisponiveisBatch(array_map('intval', $ids));
        if ($deleted > 0) {
            Session::flash('success', $deleted . ' cupom(ns) disponível(is) excluído(s).');
        } else {
            Session::flash('error', 'Nenhum cupom disponível foi excluído.');
        }

        $this->redirect('/admin/cupons');
    }

    public function show(int $id): void
    {
        Auth::requireAdmin();

        $cupom = $this->cupomRepository->findByIdForAdmin($id);
        if ($cupom === null) {
            Session::flash('error', 'Cupom não encontrado.');
            $this->redirect('/admin/cupons');
        }

        $history = $this->cupomService->getHistory($id);
        usort($history, static function (array $a, array $b): int {
            return strtotime((string) $a['created_at']) <=> strtotime((string) $b['created_at']);
        });

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
