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
        try {
            $stats = (new Indicacao())->statsByUsuario($userId);
        } catch (PDOException $e) {
            Logger::warning('Indicacoes table not found', ['error' => $e->getMessage()]);
        }

        $linkModel = new LinkIndicacao();
        $link = $linkModel->findByUsuario($userId);

        if ($link === null) {
            $linkModel->createForUser($userId, $codigo);
        }

        $linkUrl = $inviteLinkService->getInviteLink($user);
        $statusCard = $this->resolveStatusCard($userId);
        $showRetryCta = $statusCard === null && (
            Session::flash('show_retry_cta') === '1'
            || $this->hasDismissedReprovacao($userId)
        );

        $this->view('dashboard.index', [
            'title' => 'Meu painel',
            'user' => $user,
            'success' => Session::flash('success'),
            'inviteLink' => $linkUrl,
            'codigo' => $codigo,
            'stats' => $stats,
            'statusCard' => $statusCard,
            'showRetryCta' => $showRetryCta,
        ], 'app');
    }

    /**
     * Último resultado definitivo da indicação (para card da Home).
     * Pendente / em análise → null (não exibe card).
     * Reprovações com "Entendi" dispensadas são ignoradas.
     *
     * @return array<string, mixed>|null
     */
    private function resolveStatusCard(int $usuarioId): ?array
    {
        try {
            $rows = (new ValidacaoIndicacao())->listByUsuarioIndicador($usuarioId, 50, 0);
        } catch (Throwable) {
            return null;
        }

        foreach ($rows as $row) {
            $status = (string) ($row['status'] ?? '');
            $motivoCode = (string) ($row['motivo_bloqueio'] ?? '');
            $id = (int) ($row['id'] ?? 0);
            $indicacaoId = (int) ($row['indicacao_id'] ?? 0);
            $dismissed = !empty($row['status_message_dismissed_at']);
            $identifier = indicacao_display_identifier($row, (string) ($row['indicacao_created_at'] ?? $row['created_at'] ?? ''));

            if ($status === ValidacaoIndicacao::STATUS_BENEFICIO_LIBERADO) {
                if ($dismissed) {
                    continue;
                }

                return [
                    'type' => 'aprovado',
                    'title' => 'A indicação do ' . $this->identifierAsPossessive($identifier) . ' foi aprovada!',
                    'body' => 'Seu cupom exclusivo de 10% OFF já está disponível.',
                    'identifier' => $identifier,
                    'validacao_id' => $id,
                    'indicacao_id' => $indicacaoId,
                ];
            }

            if ($status === ValidacaoIndicacao::STATUS_APROVADO) {
                if ($dismissed) {
                    continue;
                }

                if ($motivoCode === 'BENEFICIO_PENDENTE_SEM_ESTOQUE') {
                    return [
                        'type' => 'beneficio_pendente',
                        'title' => 'A indicação do ' . $this->identifierAsPossessive($identifier) . ' foi aprovada.',
                        'body' => 'Estamos preparando seu benefício. Seu cupom será liberado em breve.',
                        'identifier' => $identifier,
                        'validacao_id' => $id,
                        'indicacao_id' => $indicacaoId,
                    ];
                }

                return [
                    'type' => 'aprovado',
                    'title' => 'A indicação do ' . $this->identifierAsPossessive($identifier) . ' foi aprovada!',
                    'body' => 'Seu cupom exclusivo de 10% OFF já está disponível.',
                    'identifier' => $identifier,
                    'validacao_id' => $id,
                    'indicacao_id' => $indicacaoId,
                ];
            }

            if ($status === ValidacaoIndicacao::STATUS_REPROVADO) {
                if ($dismissed) {
                    continue;
                }

                return [
                    'type' => 'reprovado',
                    'title' => 'Não foi possível validar esta indicação',
                    'body' => 'Não foi possível validar a indicação do ' . $this->identifierAsPossessive($identifier) . '.',
                    'identifier' => $identifier,
                    'motivo' => indicacao_friendly_reject_reason($motivoCode),
                    'validacao_id' => $id,
                    'indicacao_id' => $indicacaoId,
                ];
            }

            if (in_array($status, [
                ValidacaoIndicacao::STATUS_PENDENTE,
                ValidacaoIndicacao::STATUS_AGUARDANDO_CADASTRO,
                ValidacaoIndicacao::STATUS_AGUARDANDO_VALIDACAO,
                ValidacaoIndicacao::STATUS_EM_ANALISE,
            ], true)) {
                // Pendente não gera card hero; lista na página de indicações.
                continue;
            }
        }

        return null;
    }

    private function identifierAsPossessive(string $identifier): string
    {
        if (str_starts_with($identifier, 'Telefone final ')) {
            return 'telefone final ' . substr($identifier, strlen('Telefone final '));
        }
        if (str_starts_with($identifier, 'E-mail ')) {
            return 'e-mail ' . substr($identifier, strlen('E-mail '));
        }
        if (str_starts_with($identifier, 'CPF final ')) {
            return 'CPF final ' . substr($identifier, strlen('CPF final '));
        }

        return mb_strtolower($identifier);
    }

    private function hasDismissedReprovacao(int $usuarioId): bool
    {
        try {
            $rows = (new ValidacaoIndicacao())->listByUsuarioIndicador($usuarioId, 20, 0);
        } catch (Throwable) {
            return false;
        }

        foreach ($rows as $row) {
            if ((string) ($row['status'] ?? '') === ValidacaoIndicacao::STATUS_REPROVADO
                && !empty($row['status_message_dismissed_at'])
            ) {
                return true;
            }
        }

        return false;
    }

    public function dismissStatusCard(): void
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

        $userId = (int) $user['id'];
        $indicacaoId = (int) ($_POST['indicacao_id'] ?? 0);
        $validacaoId = (int) ($_POST['validacao_id'] ?? 0);

        if ($indicacaoId <= 0 && $validacaoId > 0) {
            $validacao = (new ValidacaoIndicacao())->findById($validacaoId);
            if ($validacao !== null && (int) ($validacao['usuario_indicador_id'] ?? 0) === $userId) {
                $indicacaoId = (int) ($validacao['indicacao_id'] ?? 0);
            }
        }

        if ($indicacaoId <= 0) {
            Session::flash('error', 'Não foi possível atualizar este aviso.');
            $this->redirect('/dashboard');
        }

        $ok = (new Indicacao())->dismissStatusMessage($indicacaoId, $userId);
        if (!$ok) {
            Logger::warning('Falha ao dispensar card de indicação', [
                'usuario_id' => $userId,
                'indicacao_id' => $indicacaoId,
            ]);
            Session::flash('error', 'Não foi possível atualizar este aviso. Execute a migration do campo de ciência se ainda não foi aplicada.');
            $this->redirect('/dashboard');
        }

        Session::flash('show_retry_cta', '1');
        $this->redirect('/dashboard');
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
