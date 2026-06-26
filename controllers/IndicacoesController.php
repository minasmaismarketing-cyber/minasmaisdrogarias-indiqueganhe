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
            Logger::warning('Validacao table not found or error', ['error' => $e->getMessage()]);
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
}
