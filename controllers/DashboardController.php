<?php

declare(strict_types=1);

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

        $inviteLinkService = new InviteLinkService();
        $userId = (int) $user['id'];
        $codigo = (string) $user['codigo_indicador'];

        $stats = ['total' => 0, 'validadas' => 0, 'pendentes' => 0, 'liberadas' => 0];
        $validacaoStats = ['total' => 0, 'aprovados' => 0, 'reprovados' => 0, 'beneficios_liberados' => 0];

        try {
            $indicacaoModel = new Indicacao();
            $stats = $indicacaoModel->statsByUsuario($userId);
        } catch (PDOException $e) {
            Logger::warning('Indicacoes table not found', ['error' => $e->getMessage()]);
        }

        try {
            $indicadoModel = new Indicado();
            foreach ($indicadoModel->listByIndicador($codigo) as $indicado) {
                if (($indicado['nome'] ?? '') === '' && ($indicado['cpf'] ?? '') === '') {
                    continue;
                }

                $stats['total']++;

                if ($indicado['status'] === Indicado::STATUS_VALIDADO) {
                    $stats['validadas']++;
                    $stats['liberadas']++;
                } elseif ($indicado['status'] === Indicado::STATUS_AGUARDANDO_VALIDACAO) {
                    $stats['pendentes']++;
                } elseif ($indicado['status'] !== Indicado::STATUS_INVALIDADO) {
                    $stats['pendentes']++;
                }
            }
        } catch (PDOException $e) {
            Logger::warning('Indicados table not found', ['error' => $e->getMessage()]);
        }

        // Get or create user's link
        $linkModel = new LinkIndicacao();
        $link = $linkModel->findByUsuario($userId);

        if ($link === null) {
            $linkModel->createForUser($userId, $codigo);
            $link = $linkModel->findByUsuario($userId);
        }

        $linkStats = $linkModel->getStatsByUsuario($userId);
        $linkUrl = $inviteLinkService->getInviteLink($user);

        $this->view('dashboard.index', [
            'title' => 'Meu painel',
            'user' => $user,
            'success' => Session::flash('success'),
            'inviteLink' => $linkUrl,
            'codigo' => $codigo,
            'stats' => $stats,
            'linkStats' => $linkStats,
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
        $inviteLinkService = new InviteLinkService();
        $codigo = (string) $user['codigo_indicador'];
        $referral->logShare((int) $user['id'], $codigo);

        $this->eventLogger->logLinkCompartilhado((int) $user['id'], $codigo);

        Session::flash('success', 'Link de indicação registrado!');
        Session::flash('share_link', $inviteLinkService->getInviteLink($user));
        $this->redirect('/dashboard');
    }
}
