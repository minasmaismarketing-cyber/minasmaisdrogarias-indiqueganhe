<?php

declare(strict_types=1);

class AdminController extends Controller
{
    public const ADMIN_EMAIL = 'admin@minasmais.com.br';
    private EventLogger $eventLogger;

    public function __construct()
    {
        $this->eventLogger = new EventLogger();
    }

    public function index(): void
    {
        $this->requireAdmin();

        $usuarioModel = new Usuario();
        $indicacaoModel = new Indicacao();
        $campanhaModel = new Campanha();
        $indicadoModel = new Indicado();
        $validacaoModel = new Validacao();

        $totalUsuarios = $usuarioModel->countAll();
        $totalIndicacoes = $indicacaoModel->countAll();
        $campanhaAtiva = $campanhaModel->findActive();
        $campanhaStats = $campanhaModel->getStats();
        $validacaoStats = $validacaoModel->getStats();
        $recentEvents = $this->eventLogger->getRecentEvents(20);

        // Get campaign-specific stats
        $usuariosParticipantes = 0;
        $totalIndicacoesCampanha = 0;
        $indicacoesValidadas = 0;
        $cuponsLiberados = 0;

        if ($campanhaAtiva !== null) {
            $indicados = $indicadoModel->listByIndicador($campanhaAtiva['slug']);
            $usuariosParticipantes = count($indicados);
            $totalIndicacoesCampanha = $usuariosParticipantes;
            
            foreach ($indicados as $indicado) {
                if ($indicado['status'] === Indicado::STATUS_VALIDADO) {
                    $indicacoesValidadas++;
                    $cuponsLiberados++;
                }
            }
        }

        // Calculate conversion rate
        $taxaConversao = $totalIndicacoesCampanha > 0 
            ? round(($indicacoesValidadas / $totalIndicacoesCampanha) * 100, 2) 
            : 0;

        // Calculate approval rate
        $taxaAprovacao = ($validacaoStats['total'] ?? 0) > 0 
            ? round(($validacaoStats['validadas'] ?? 0) / ($validacaoStats['total'] ?? 1) * 100, 2) 
            : 0;

        $this->view('admin.dashboard', [
            'title' => 'Admin Dashboard',
            'totalUsuarios' => $totalUsuarios,
            'totalIndicacoes' => $totalIndicacoes,
            'campanhaAtiva' => $campanhaAtiva,
            'campanhaStats' => $campanhaStats,
            'usuariosParticipantes' => $usuariosParticipantes,
            'totalIndicacoesCampanha' => $totalIndicacoesCampanha,
            'indicacoesValidadas' => $indicacoesValidadas,
            'cuponsLiberados' => $cuponsLiberados,
            'taxaConversao' => $taxaConversao,
            'validacaoStats' => $validacaoStats,
            'taxaAprovacao' => $taxaAprovacao,
            'recentEvents' => $recentEvents,
        ], 'admin');
    }

    public function usuarios(): void
    {
        $this->requireAdmin();

        $usuarioModel = new Usuario();
        $usuarios = $usuarioModel->listAll();

        $this->view('admin.usuarios', [
            'title' => 'Usuários',
            'usuarios' => $usuarios,
        ], 'admin');
    }

    public function campanhas(): void
    {
        $this->requireAdmin();

        $campanhaModel = new Campanha();
        $campanhas = $campanhaModel->findAll();

        $this->view('admin.campanhas', [
            'title' => 'Campanhas',
            'campanhas' => $campanhas,
        ], 'admin');
    }

    public function indicacoes(): void
    {
        $this->requireAdmin();

        $indicacaoModel = new Indicacao();
        $indicacoes = $indicacaoModel->listAll();

        $this->view('admin.indicacoes', [
            'title' => 'Indicações',
            'indicacoes' => $indicacoes,
        ], 'admin');
    }

    public function configuracoes(): void
    {
        $this->requireAdmin();

        $this->view('admin.configuracoes', [
            'title' => 'Configurações',
        ], 'admin');
    }

    private function requireAdmin(): void
    {
        AuthMiddleware::requireAuth();

        $user = Auth::user();

        if ($user === null || $user['email'] !== self::ADMIN_EMAIL) {
            Session::flash('error', 'Acesso negado. Apenas administradores podem acessar esta página.');
            $this->redirect('/dashboard');
        }
    }
}
