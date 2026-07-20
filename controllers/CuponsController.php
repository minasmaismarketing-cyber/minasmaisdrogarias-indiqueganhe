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
            $token = $this->storePendingImportCodes($result['a_importar']);
        } catch (InvalidArgumentException $e) {
            $this->clearPendingImport();
            Session::flash('error', $e->getMessage());
            $this->redirect('/admin/cupons/importar');
        } catch (Throwable $e) {
            $this->clearPendingImport();
            Logger::error('Cupom import validate failed', [
                'endpoint' => '/admin/cupons/importar/validar',
                'campanha_id' => $campanhaId,
                'exception' => $e->getMessage(),
            ]);
            Session::flash('error', 'Não foi possível preparar a importação. Tente novamente.');
            $this->redirect('/admin/cupons/importar');
        }

        Session::set(self::IMPORT_SESSION_KEY, [
            'campanha_id' => $campanhaId,
            'token' => $token,
            'count' => count($result['a_importar']),
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
        if (!is_array($pending) || empty($pending['campanha_id']) || empty($pending['token'])) {
            Session::flash('error', 'Nenhuma importação validada. Valide o arquivo primeiro.');
            $this->redirect('/admin/cupons/importar');
        }

        $campanhaId = (int) $pending['campanha_id'];
        $token = (string) $pending['token'];
        $codigos = $this->loadPendingImportCodes($token);

        if ($codigos === []) {
            Logger::error('Cupom import confirm sem códigos', [
                'endpoint' => '/admin/cupons/importar/confirmar',
                'campanha_id' => $campanhaId,
                'quantidade_recebida' => 0,
                'quantidade_inserida' => 0,
                'quantidade_ignorada' => 0,
                'exception' => 'Arquivo temporário de importação ausente ou vazio',
            ]);
            Session::flash('error', 'Dados da validação expiraram. Valide o arquivo novamente.');
            $this->clearPendingImport();
            $this->redirect('/admin/cupons/importar');
        }

        $user = Auth::user();
        $adminEmail = $user !== null ? (string) $user['email'] : null;

        try {
            $result = $this->cupomService->confirmCsvImport($campanhaId, $codigos, $adminEmail);
            $this->clearPendingImport();

            $imported = (int) $result['imported'];
            $skipped = (int) $result['skipped'];
            if ($imported > 0 && $skipped === 0) {
                Session::flash(
                    'success',
                    number_format($imported, 0, ',', '.') . ' cupons importados com sucesso.'
                );
            } else {
                Session::flash(
                    'success',
                    sprintf(
                        'Importação concluída: %s cupom(ns) inserido(s), %s ignorado(s).',
                        number_format($imported, 0, ',', '.'),
                        number_format($skipped, 0, ',', '.')
                    )
                );
            }
            $this->redirect('/admin/cupons');
        } catch (Throwable $e) {
            Logger::error('Cupom import confirm failed', [
                'endpoint' => '/admin/cupons/importar/confirmar',
                'campanha_id' => $campanhaId,
                'quantidade_recebida' => count($codigos),
                'quantidade_inserida' => 0,
                'quantidade_ignorada' => 0,
                'exception' => $e->getMessage(),
            ]);
            Session::flash('error', $e->getMessage());
            $this->redirect('/admin/cupons/importar');
        }
    }

    /** @param list<string> $codigos */
    private function storePendingImportCodes(array $codigos): string
    {
        $dir = $this->pendingImportDir();
        if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) {
            throw new RuntimeException('Não foi possível preparar o armazenamento temporário da importação.');
        }

        $token = bin2hex(random_bytes(16));
        $path = $dir . DIRECTORY_SEPARATOR . $token . '.json';
        $json = json_encode(['codigos' => array_values($codigos)], JSON_UNESCAPED_UNICODE);
        if ($json === false || file_put_contents($path, $json) === false) {
            throw new RuntimeException('Não foi possível salvar os códigos validados para confirmação.');
        }

        return $token;
    }

    /** @return list<string> */
    private function loadPendingImportCodes(string $token): array
    {
        if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
            return [];
        }

        $path = $this->pendingImportDir() . DIRECTORY_SEPARATOR . $token . '.json';
        if (!is_file($path)) {
            return [];
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            return [];
        }

        $data = json_decode($raw, true);
        if (!is_array($data) || !isset($data['codigos']) || !is_array($data['codigos'])) {
            return [];
        }

        $codigos = [];
        foreach ($data['codigos'] as $codigo) {
            $codigo = trim((string) $codigo);
            if ($codigo !== '') {
                $codigos[] = $codigo;
            }
        }

        return $codigos;
    }

    private function clearPendingImport(): void
    {
        $pending = Session::get(self::IMPORT_SESSION_KEY);
        if (is_array($pending) && !empty($pending['token']) && is_string($pending['token'])) {
            $token = $pending['token'];
            if (preg_match('/^[a-f0-9]{32}$/', $token)) {
                $path = $this->pendingImportDir() . DIRECTORY_SEPARATOR . $token . '.json';
                if (is_file($path)) {
                    @unlink($path);
                }
            }
        }

        Session::remove(self::IMPORT_SESSION_KEY);
    }

    private function pendingImportDir(): string
    {
        return BASE_PATH . '/storage/cupom_imports';
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
