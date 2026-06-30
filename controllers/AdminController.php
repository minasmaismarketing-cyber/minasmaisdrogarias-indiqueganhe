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
        $usuarios = $usuarioModel->listAll();

        $this->view('admin.usuarios', [
            'title' => 'Usuários',
            'usuarios' => $usuarios,
        ], 'admin');
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
