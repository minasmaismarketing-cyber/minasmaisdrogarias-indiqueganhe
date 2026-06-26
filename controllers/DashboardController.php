<?php

declare(strict_types=1);

use PDOException;

class DashboardController extends Controller
{
    private EventLogger $eventLogger;

    public function __construct()
    {
        $this->eventLogger = new EventLogger();
    }
    public function index(): void
    {
        AuthMiddleware::requireAuth();

        $user = Auth::user();

        if ($user === null) {
            $this->redirect('/login');
        }

        $referral = new ReferralService();
        $userId = (int) $user['id'];
        $codigo = (string) $user['codigo_indicador'];

        // Check if indicacoes table exists
        $stats = ['total' => 0, 'validadas' => 0, 'pendentes' => 0, 'liberadas' => 0];
        $indicacoes = [];
        $indicados = [];
        $validacoes = [];
        $validacaoStats = ['total' => 0, 'aprovados' => 0, 'reprovados' => 0, 'beneficios_liberados' => 0];

        // Get real stats from indicados table
        try {
            $indicadoModel = new Indicado();
            $indicados = $indicadoModel->listByIndicador($codigo);
            
            // Calculate real stats
            $stats['total'] = count($indicados);
            $stats['pendentes'] = 0;
            $stats['validadas'] = 0;
            $stats['liberadas'] = 0;
            
            foreach ($indicados as $indicado) {
                if ($indicado['status'] === Indicado::STATUS_AGUARDANDO_VALIDACAO) {
                    $stats['pendentes']++;
                } elseif ($indicado['status'] === Indicado::STATUS_VALIDADO) {
                    $stats['validadas']++;
                    $stats['liberadas']++;
                }
            }
            
            // Add timeline data to each indicado
            foreach ($indicados as &$indicado) {
                $indicado['timeline'] = $indicadoModel->getTimeline((int) $indicado['id']);
            }
        } catch (PDOException $e) {
            Logger::warning('Indicados table not found', ['error' => $e->getMessage()]);
        }

        // Legacy indicacoes table (for backward compatibility)
        try {
            $indicacaoModel = new Indicacao();
            $legacyIndicacoes = $indicacaoModel->listByUsuario($userId);
            $indicacoes = $legacyIndicacoes;
        } catch (PDOException $e) {
            // Table doesn't exist yet
            Logger::warning('Indicacoes table not found', ['error' => $e->getMessage()]);
        }

        // Get or create user's link
        $linkModel = new LinkIndicacao();
        $link = $linkModel->findByUsuario($userId);

        if ($link === null) {
            $linkModel->createForUser($userId, $codigo);
            $link = $linkModel->findByUsuario($userId);
        }

        $linkStats = $linkModel->getStatsByUsuario($userId);
        $linkUrl = $link ? (string) $link['url'] : $referral->inviteLink($codigo);

        $this->view('dashboard.index', [
            'title' => 'Meu painel',
            'user' => $user,
            'success' => Session::flash('success'),
            'inviteLink' => $linkUrl,
            'codigo' => $codigo,
            'stats' => $stats,
            'indicacoes' => $indicacoes,
            'indicados' => $indicados,
            'linkStats' => $linkStats,
            'validacoes' => $validacoes,
            'validacaoStats' => $validacaoStats,
        ], 'app');
    }

    public function share(): void
    {
        AuthMiddleware::requireAuth();

        if (!Csrf::validateRequest()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/dashboard');
        }

        $user = Auth::user();

        if ($user === null) {
            $this->redirect('/login');
        }

        $referral = new ReferralService();
        $codigo = (string) $user['codigo_indicador'];
        $referral->logShare((int) $user['id'], $codigo);

        $this->eventLogger->logLinkCompartilhado((int) $user['id'], $codigo);

        Session::flash('success', 'Link de indicação registrado!');
        Session::flash('share_link', $referral->inviteLink($codigo));
        $this->redirect('/dashboard');
    }
}
