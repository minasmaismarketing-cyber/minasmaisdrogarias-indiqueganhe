<?php

declare(strict_types=1);

class IndicacoesController extends Controller
{
    public function index(): void
    {
        AuthMiddleware::requireAuth();

        $user = Auth::user();

        if ($user === null) {
            $this->redirect('/login');
        }

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $userId = (int) $user['id'];

        $historicoValidacoes = [];
        $validacaoStats = ['total' => 0, 'aprovados' => 0, 'reprovados' => 0, 'beneficios_liberados' => 0];
        $total = 0;

        try {
            $validacaoModel = new ValidacaoIndicacao();
            $validacaoStats = $validacaoModel->statsUsuarioIndicador($userId);
            $total = $validacaoModel->countByUsuarioIndicador($userId);
            $historicoValidacoes = $validacaoModel->listByUsuarioIndicador($userId, $limit, $offset);
        } catch (Throwable $e) {
            Logger::warning('Falha ao carregar validacao_indicacoes', ['error' => $e->getMessage()]);
        }

        $totalPages = $total > 0 ? (int) ceil($total / $limit) : 1;

        $this->view('indicacoes.index', [
            'title' => 'Minhas Indicações',
            'user' => $user,
            'historicoValidacoes' => $historicoValidacoes,
            'validacaoStats' => $validacaoStats,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'hasNext' => $page < $totalPages,
            'hasPrev' => $page > 1,
        ], 'app');
    }

    /**
     * Abre o WhatsApp com mensagem do benefício 5% (MINAS-5) para o indicado.
     * POST /indicacoes/{id}/compartilhar-beneficio
     */
    public function compartilharBeneficio(string $id): void
    {
        AuthMiddleware::requireAuth();

        $user = Auth::user();
        if ($user === null) {
            $this->redirect('/login');
        }

        $referrer = $_SERVER['HTTP_REFERER'] ?? '';
        $backPath = str_contains($referrer, '/meus-cupons') ? '/meus-cupons' : '/indicacoes';

        if (!Csrf::validateRequest()) {
            Session::flash('error', 'Não foi possível realizar esta ação.');
            $this->redirect($backPath);
        }

        $indicacaoId = (int) $id;
        $userId = (int) $user['id'];

        if ($indicacaoId <= 0) {
            Session::flash('error', 'Não foi possível realizar esta ação.');
            $this->redirect($backPath);
        }

        $indicacaoModel = new Indicacao();
        $indicacao = $indicacaoModel->findById($indicacaoId);

        if ($indicacao === null || (int) ($indicacao['usuario_id'] ?? 0) !== $userId) {
            Logger::warning('Tentativa de compartilhar benefício sem autorização', [
                'usuario_id' => $userId,
                'indicacao_id' => $indicacaoId,
            ]);
            Session::flash('error', 'Não foi possível realizar esta ação.');
            $this->redirect($backPath);
        }

        $validacao = (new ValidacaoIndicacao())->findByIndicacao($indicacaoId);
        $vStatus = (string) ($validacao['status'] ?? '');
        $iStatus = (string) ($indicacao['status'] ?? '');

        if (!is_indicacao_aprovada_para_beneficio($vStatus, $iStatus)) {
            Session::flash('error', 'Não foi possível realizar esta ação.');
            $this->redirect($backPath);
        }

        $telefoneRaw = (string) ($indicacao['telefone_indicado'] ?? '');
        $whatsapp = normalize_brazilian_whatsapp_number($telefoneRaw);

        if ($whatsapp === null) {
            Logger::warning('Telefone inválido para compartilhar benefício', [
                'usuario_id' => $userId,
                'indicacao_id' => $indicacaoId,
                'telefone' => mask_whatsapp_phone_tail($telefoneRaw),
            ]);
            Session::flash('error', 'Não foi possível abrir o WhatsApp porque o telefone informado não é válido.');
            $this->redirect($backPath);
        }

        $eventoModel = new Evento();
        $alreadyRecent = $eventoModel->hasRecentBeneficioWhatsappOpen($userId, $indicacaoId, 15);
        if (!$alreadyRecent) {
            (new EventLogger())->logBeneficioWhatsappOpened($userId, $indicacaoId);
        }

        $message = beneficio_indicado_whatsapp_message();
        $url = 'https://wa.me/' . $whatsapp . '?text=' . rawurlencode($message);

        header('Location: ' . $url, true, 302);
        exit;
    }
}
