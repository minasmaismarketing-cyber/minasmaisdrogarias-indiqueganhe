<?php

declare(strict_types=1);

class AdminController extends Controller
{
    private EventLogger $eventLogger;

    public function __construct()
    {
        $this->eventLogger = new EventLogger();
    }

    public function index(): void
    {
        Auth::requireAdmin();

        $campanhaModel = new Campanha();
        $metrics = (new AdminMetricsService())->getDashboardPayload();

        $campanhaAtiva = $campanhaModel->findActive();
        $recentEvents = $this->eventLogger->getRecentEvents(20);

        $this->view('admin.dashboard', [
            'title' => 'Admin Dashboard',
            'totalIndicacoes' => $metrics['totalIndicacoes'],
            'validacaoStats' => $metrics['validacaoStats'],
            'cuponsGerados' => $metrics['cuponsGerados'],
            'taxaConversao' => $metrics['taxaConversao'],
            'campanhaAtiva' => $campanhaAtiva,
            'recentEvents' => $recentEvents,
        ], 'admin');
    }

    public function usuarios(): void
    {
        Auth::requireAdmin();

        $usuarioModel = new Usuario();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $filters = [
            'nome' => trim((string) ($_GET['nome'] ?? '')),
            'cpf' => trim((string) ($_GET['cpf'] ?? '')),
            'email' => trim((string) ($_GET['email'] ?? '')),
            'status' => (string) ($_GET['status'] ?? Usuario::FILTER_TODOS),
        ];

        if (!in_array($filters['status'], [
            Usuario::FILTER_TODOS,
            Usuario::FILTER_ATIVOS,
            Usuario::FILTER_BLOQUEADOS,
            Usuario::FILTER_EXCLUIDOS,
        ], true)) {
            $filters['status'] = Usuario::FILTER_TODOS;
        }

        $total = $usuarioModel->countAllAdmin($filters);
        $usuarios = $usuarioModel->findAllAdmin($filters, $limit, $offset);
        $totalPages = $total > 0 ? (int) ceil($total / $limit) : 1;

        $this->view('admin.usuarios', [
            'title' => 'Usuários',
            'usuarios' => $usuarios,
            'filters' => $filters,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'hasNext' => $page < $totalPages,
            'hasPrev' => $page > 1,
        ], 'admin');
    }

    public function showUsuario(int $id): void
    {
        Auth::requireAdmin();

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->findById($id);

        if ($usuario === null) {
            Session::flash('error', 'Usuário não encontrado.');
            $this->redirect('/admin/usuarios');
        }

        $resumo = (new AdminMetricsService())->getMetricsForUsuario($id);

        $recentEvents = $this->eventLogger->getUserEvents($id, 50);
        $canBlock = empty($usuario['deleted_at']) && (int) ($usuario['ativo'] ?? 1) === 1 && $id !== (int) (Auth::id() ?? 0);
        $canUnblock = empty($usuario['deleted_at']) && (int) ($usuario['ativo'] ?? 1) === 0;

        $this->view('admin.usuario-view', [
            'title' => 'Detalhes do Usuário',
            'usuario' => $usuario,
            'resumo' => $resumo,
            'recentEvents' => $recentEvents,
            'canBlock' => $canBlock,
            'canUnblock' => $canUnblock,
        ], 'admin');
    }

    public function blockUsuario(): void
    {
        Auth::requireAdmin();

        if (!Csrf::validateRequest()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/usuarios');
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id === 0) {
            Session::flash('error', 'ID inválido.');
            $this->redirect('/admin/usuarios');
        }

        if ($id === (int) (Auth::id() ?? 0)) {
            Session::flash('error', 'Você não pode bloquear sua própria conta.');
            $this->redirect('/admin/usuarios/' . $id);
        }

        $usuarioModel = new Usuario();
        if ($usuarioModel->setAtivo($id, false)) {
            Session::flash('success', 'Usuário bloqueado com sucesso.');
        } else {
            Session::flash('error', 'Não foi possível bloquear o usuário.');
        }

        $this->redirect('/admin/usuarios/' . $id);
    }

    public function unblockUsuario(): void
    {
        Auth::requireAdmin();

        if (!Csrf::validateRequest()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/usuarios');
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id === 0) {
            Session::flash('error', 'ID inválido.');
            $this->redirect('/admin/usuarios');
        }

        $usuarioModel = new Usuario();
        if ($usuarioModel->setAtivo($id, true)) {
            Session::flash('success', 'Usuário desbloqueado com sucesso.');
        } else {
            Session::flash('error', 'Não foi possível desbloquear o usuário.');
        }

        $this->redirect('/admin/usuarios/' . $id);
    }

    public function campanhas(): void
    {
        Auth::requireAdmin();

        $campanhaModel = new Campanha();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $filters = [
            'nome' => trim((string) ($_GET['nome'] ?? '')),
            'status' => (string) ($_GET['status'] ?? Campanha::FILTER_TODOS),
            'vigencia' => (string) ($_GET['vigencia'] ?? Campanha::FILTER_TODOS),
        ];

        if (!in_array($filters['status'], Campanha::ADMIN_STATUS_FILTERS, true)) {
            $filters['status'] = Campanha::FILTER_TODOS;
        }

        if (!in_array($filters['vigencia'], Campanha::ADMIN_VIGENCIA_FILTERS, true)) {
            $filters['vigencia'] = Campanha::FILTER_TODOS;
        }

        $total = $campanhaModel->countAllAdmin($filters);
        $campanhas = $campanhaModel->findAllAdmin($filters, $limit, $offset);
        $totalPages = $total > 0 ? (int) ceil($total / $limit) : 1;

        $this->view('admin.campanhas', [
            'title' => 'Campanhas',
            'campanhas' => $campanhas,
            'filters' => $filters,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'hasNext' => $page < $totalPages,
            'hasPrev' => $page > 1,
        ], 'admin');
    }

    public function indicacoes(): void
    {
        Auth::requireAdmin();

        $indicacaoModel = new Indicacao();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $filters = [
            'indicador' => trim((string) ($_GET['indicador'] ?? '')),
            'indicado' => trim((string) ($_GET['indicado'] ?? '')),
            'cpf' => trim((string) ($_GET['cpf'] ?? '')),
            'whatsapp' => trim((string) ($_GET['whatsapp'] ?? '')),
            'codigo' => trim((string) ($_GET['codigo'] ?? '')),
            'status' => (string) ($_GET['status'] ?? Indicacao::FILTER_TODOS),
        ];

        if (!in_array($filters['status'], Indicacao::ADMIN_STATUS_FILTERS, true)) {
            $filters['status'] = Indicacao::FILTER_TODOS;
        }

        $total = $indicacaoModel->countAllAdmin($filters);
        $indicacoes = $indicacaoModel->findAllAdmin($filters, $limit, $offset);
        $totalPages = $total > 0 ? (int) ceil($total / $limit) : 1;

        $this->view('admin.indicacoes', [
            'title' => 'Indicações',
            'indicacoes' => $indicacoes,
            'filters' => $filters,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'hasNext' => $page < $totalPages,
            'hasPrev' => $page > 1,
        ], 'admin');
    }

    public function showIndicacao(int $id): void
    {
        Auth::requireAdmin();

        $indicacaoModel = new Indicacao();
        $indicacao = $indicacaoModel->findByIdAdmin($id);

        if ($indicacao === null) {
            Session::flash('error', 'Indicação não encontrada.');
            $this->redirect('/admin/indicacoes');
        }

        $cupom = null;
        if (!empty($indicacao['cupom_id'])) {
            $cupom = [
                'id' => $indicacao['cupom_id'],
                'codigo' => $indicacao['cupom_codigo'],
                'status' => $indicacao['cupom_status'],
                'created_at' => $indicacao['cupom_created_at'],
            ];
        }

        $validacaoData = null;
        if (!empty($indicacao['validacao_id'])) {
            $validacaoData = [
                'id' => $indicacao['validacao_id'],
                'status' => $indicacao['validacao_status'],
                'elegivel' => (int) ($indicacao['validacao_elegivel'] ?? 0),
                'motivo_bloqueio' => $indicacao['validacao_motivo_bloqueio'],
                'created_at' => $indicacao['validacao_created_at'],
                'updated_at' => $indicacao['validacao_updated_at'],
            ];
        }

        $validadoEm = null;
        if ($validacaoData !== null && in_array(
            (string) $validacaoData['status'],
            [ValidacaoIndicacao::STATUS_APROVADO, ValidacaoIndicacao::STATUS_BENEFICIO_LIBERADO, ValidacaoIndicacao::STATUS_REPROVADO, ValidacaoIndicacao::STATUS_CANCELADO],
            true
        )) {
            $validadoEm = $validacaoData['updated_at'];
        }

        $timeline = $indicacaoModel->getAdminTimeline(
            $id,
            isset($indicacao['validacao_id']) ? (int) $indicacao['validacao_id'] : null,
            $cupom
        );

        $this->view('admin.indicacao-view', [
            'title' => 'Detalhes da Indicação',
            'indicacao' => $indicacao,
            'validacao' => $validacaoData,
            'cupom' => $cupom,
            'validadoEm' => $validadoEm,
            'timeline' => $timeline,
        ], 'admin');
    }

    public function configuracoes(): void
    {
        Auth::requireAdmin();

        $this->view('admin.configuracoes', [
            'title' => 'Configurações',
        ], 'admin');
    }
}
