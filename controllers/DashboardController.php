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
            'csrfToken' => Csrf::token(),
        ], 'app');
    }

    /**
     * Card da Home: prioriza aprovação mais recente ainda não visualizada,
     * depois reprovação não dispensada.
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

        $cupomRepo = new CupomRepository();
        $reprovadoCard = null;

        foreach ($rows as $row) {
            $status = (string) ($row['status'] ?? '');
            $motivoCode = (string) ($row['motivo_bloqueio'] ?? '');
            $id = (int) ($row['id'] ?? 0);
            $indicacaoId = (int) ($row['indicacao_id'] ?? 0);
            $dismissed = !empty($row['status_message_dismissed_at']);
            $friendSeen = !empty($row['friend_benefit_seen_at']);
            $identifier = indicacao_display_identifier($row, (string) ($row['indicacao_created_at'] ?? $row['created_at'] ?? ''));

            $isApproved = in_array($status, [
                ValidacaoIndicacao::STATUS_BENEFICIO_LIBERADO,
                ValidacaoIndicacao::STATUS_APROVADO,
            ], true);

            if ($isApproved && !$friendSeen && !$dismissed) {
                $hasCupomForThis = $indicacaoId > 0 && $cupomRepo->cupomExisteParaIndicacao($indicacaoId);
                $isAdditional = $motivoCode === 'BENEFICIO_JA_LIBERADO' || !$hasCupomForThis;

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

                if ($isAdditional) {
                    return [
                        'type' => 'amigo_aprovado',
                        'title' => 'Mais um amigo foi aprovado! 🎉',
                        'body' => 'Essa indicação foi concluída com sucesso. Seu amigo já pode receber o cupom de 5% OFF para usar na primeira compra.',
                        'identifier' => $identifier,
                        'validacao_id' => $id,
                        'indicacao_id' => $indicacaoId,
                        'cta_url' => url('/meus-cupons?destaque=' . $indicacaoId),
                        'cta_label' => 'Ver benefício do amigo',
                    ];
                }

                return [
                    'type' => 'aprovado',
                    'title' => 'A indicação do ' . $this->identifierAsPossessive($identifier) . ' foi aprovada!',
                    'body' => 'Seu cupom exclusivo de 10% OFF já está disponível.',
                    'identifier' => $identifier,
                    'validacao_id' => $id,
                    'indicacao_id' => $indicacaoId,
                    'cta_url' => url('/meus-cupons?destaque=' . $indicacaoId),
                    'cta_label' => 'Ver meus cupons',
                ];
            }

            if ($status === ValidacaoIndicacao::STATUS_REPROVADO && !$dismissed && $reprovadoCard === null) {
                $reprovadoCard = [
                    'type' => 'reprovado',
                    'title' => 'Uma indicação não foi aprovada',
                    'body' => 'Não foi possível validar esta indicação.',
                    'identifier' => $identifier,
                    'motivo' => indicacao_friendly_reject_reason($motivoCode),
                    'validacao_id' => $id,
                    'indicacao_id' => $indicacaoId,
                ];
            }
        }

        return $reprovadoCard;
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

        $indicacaoModel = new Indicacao();
        $ok = $indicacaoModel->dismissStatusMessage($indicacaoId, $userId);
        $indicacaoModel->markFriendBenefitSeen($indicacaoId, $userId);

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

        $wantsJson = $this->wantsJsonResponse();

        if (!Csrf::validateRequest()) {
            if ($wantsJson) {
                $this->json(['success' => false, 'message' => 'Token de segurança inválido.'], 403);
            }
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/dashboard');
        }

        $user = Auth::user();

        if ($user === null) {
            if ($wantsJson) {
                $this->json(['success' => false, 'message' => 'Não autenticado.'], 401);
            }
            $this->redirect('/login');
        }

        $referral = new ReferralService();
        $inviteLinkService = new InviteLinkService();
        $codigo = (string) $user['codigo_indicador'];
        $result = $referral->logShare((int) $user['id'], $codigo);
        $link = $inviteLinkService->getInviteLink($user);

        if ($wantsJson) {
            $this->json([
                'success' => true,
                'logged' => (bool) ($result['logged'] ?? false),
                'duplicate' => (bool) ($result['duplicate'] ?? false),
                'message' => 'Compartilhamento iniciado',
                'inviteLink' => $link,
            ]);
        }

        Session::flash('success', 'Compartilhamento iniciado. Aguardando o cadastro do amigo.');
        Session::flash('share_link', $link);
        $this->redirect('/dashboard');
    }

    private function wantsJsonResponse(): bool
    {
        $accept = (string) ($_SERVER['HTTP_ACCEPT'] ?? '');
        $requestedWith = (string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');

        return str_contains($accept, 'application/json')
            || strcasecmp($requestedWith, 'XMLHttpRequest') === 0;
    }
}
