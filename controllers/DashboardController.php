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

        $linkModel = new LinkIndicacao();
        $link = $linkModel->findByUsuario($userId);

        if ($link === null) {
            $linkModel->createForUser($userId, $codigo);
        }

        $linkUrl = $inviteLinkService->getInviteLink($user);
        $statusCard = $this->resolveStatusCard($userId);

        $this->view('dashboard.index', [
            'title' => 'Meu painel',
            'user' => $user,
            'success' => Session::flash('success'),
            'inviteLink' => $linkUrl,
            'codigo' => $codigo,
            'statusCard' => $statusCard,
        ], 'app');
    }

    /**
     * Último resultado definitivo da indicação (para card da Home).
     * Pendente / em análise → null (não exibe card).
     *
     * @return array{type: string, title: string, body: string, motivo?: string, validacao_id: int}|null
     */
    private function resolveStatusCard(int $usuarioId): ?array
    {
        try {
            $rows = (new ValidacaoIndicacao())->listByUsuarioIndicador($usuarioId, 30, 0);
        } catch (Throwable) {
            return null;
        }

        foreach ($rows as $row) {
            $status = (string) ($row['status'] ?? '');
            $motivoCode = (string) ($row['motivo_bloqueio'] ?? '');
            $id = (int) ($row['id'] ?? 0);

            if ($status === ValidacaoIndicacao::STATUS_BENEFICIO_LIBERADO) {
                return [
                    'type' => 'aprovado',
                    'title' => 'Sua indicação foi aprovada!',
                    'body' => 'Seu cupom exclusivo de 10% OFF já está disponível.',
                    'validacao_id' => $id,
                ];
            }

            if ($status === ValidacaoIndicacao::STATUS_APROVADO) {
                if ($motivoCode === 'BENEFICIO_PENDENTE_SEM_ESTOQUE') {
                    return [
                        'type' => 'beneficio_pendente',
                        'title' => 'Sua indicação foi aprovada.',
                        'body' => 'Estamos preparando seu benefício. Seu cupom será liberado em breve.',
                        'validacao_id' => $id,
                    ];
                }

                if ($motivoCode === 'BENEFICIO_JA_LIBERADO') {
                    return [
                        'type' => 'aprovado',
                        'title' => 'Sua indicação foi aprovada!',
                        'body' => 'Seu benefício já havia sido liberado anteriormente. Confira em Meus Cupons.',
                        'validacao_id' => $id,
                    ];
                }

                return [
                    'type' => 'aprovado',
                    'title' => 'Sua indicação foi aprovada!',
                    'body' => 'Seu cupom exclusivo de 10% OFF já está disponível.',
                    'validacao_id' => $id,
                ];
            }

            if ($status === ValidacaoIndicacao::STATUS_REPROVADO) {
                return [
                    'type' => 'reprovado',
                    'title' => 'Sua indicação não foi aprovada.',
                    'body' => '',
                    'motivo' => $this->friendlyMotivo($motivoCode),
                    'validacao_id' => $id,
                ];
            }
        }

        return null;
    }

    private function friendlyMotivo(string $motivo): string
    {
        return match ($motivo) {
            'CPF_JA_PARTICIPOU', 'JA_PARTICIPOU' => 'CPF já utilizado anteriormente.',
            'CPF_JA_CADASTRADO', 'CPF_EXISTENTE', 'USUARIO_JA_CADASTRADO' => 'Usuário já participou da campanha.',
            'EMAIL_JA_CADASTRADO' => 'E-mail já utilizado nesta campanha.',
            'TELEFONE_JA_CADASTRADO' => 'Telefone já utilizado nesta campanha.',
            'AUTOINDICACAO', 'AUTO_INDICACAO' => 'Não é permitido indicar a si mesmo.',
            'CAMPANHA_INATIVA' => 'Campanha inativa no momento da indicação.',
            'CAMPANHA_EXPIRADA' => 'Campanha expirada no momento da indicação.',
            '' => 'Motivo não informado.',
            default => ValidacaoIndicacao::motivoLabel($motivo),
        };
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
