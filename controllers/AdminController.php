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

        $indicacaoModel = new Indicacao();
        $validacaoModel = new ValidacaoIndicacao();
        $cupomModel = new Cupom();
        $campanhaModel = new Campanha();

        $totalIndicacoes = $indicacaoModel->countAll();
        $validacaoStats = $validacaoModel->getStats();
        $cupomStats = $cupomModel->getStats();
        $cuponsGerados = (int) ($cupomStats['total'] ?? 0);

        $aprovadas = (int) ($validacaoStats['validadas'] ?? 0);
        $taxaConversao = $totalIndicacoes > 0
            ? round(($aprovadas / $totalIndicacoes) * 100, 2)
            : 0;

        $campanhaAtiva = $campanhaModel->findActive();
        $recentEvents = $this->eventLogger->getRecentEvents(20);

        $this->view('admin.dashboard', [
            'title' => 'Admin Dashboard',
            'totalIndicacoes' => $totalIndicacoes,
            'validacaoStats' => $validacaoStats,
            'cuponsGerados' => $cuponsGerados,
            'taxaConversao' => $taxaConversao,
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

        $indicacaoModel = new Indicacao();
        $validacaoModel = new ValidacaoIndicacao();
        $cupomModel = new Cupom();

        $resumo = [
            'total_indicacoes' => $indicacaoModel->countByUsuario($id),
            'cupons_gerados' => $cupomModel->countByUsuario($id),
        ];
        $resumo = array_merge($resumo, $validacaoModel->statsAdminUsuario($id));

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
        $campanhas = $campanhaModel->findAll();

        $this->view('admin.campanhas', [
            'title' => 'Campanhas',
            'campanhas' => $campanhas,
        ], 'admin');
    }

    public function indicacoes(): void
    {
        Auth::requireAdmin();

        $indicacaoModel = new Indicacao();
        $indicacoes = $indicacaoModel->listAll();

        $this->view('admin.indicacoes', [
            'title' => 'Indicações',
            'indicacoes' => $indicacoes,
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
