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
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $filtro = $_GET['filtro'] ?? 'todas';
        $busca = $_GET['busca'] ?? '';

        $indicacaoModel = new Indicacao();
        $userId = (int) $user['id'];

        try {
            $stats = $indicacaoModel->statsByUsuario($userId);
            
            // Apply filter and search
            $indicacoes = $indicacaoModel->listByUsuario($userId, $limit);
            
            if ($filtro !== 'todas') {
                $indicacoes = array_filter($indicacoes, function($item) use ($filtro) {
                    return match ($filtro) {
                        'pendentes' => in_array($item['status'], [Indicacao::STATUS_AGUARDANDO, Indicacao::STATUS_LINK_ACESSADO, Indicacao::STATUS_CADASTRO_PENDENTE]),
                        'validadas' => $item['status'] === Indicacao::STATUS_VALIDADO,
                        'premiadas' => $item['status'] === Indicacao::STATUS_PREMIO_LIBERADO,
                        default => true,
                    };
                });
            }
            
            if ($busca !== '') {
                $buscaLower = strtolower($busca);
                $indicacoes = array_filter($indicacoes, function($item) use ($buscaLower) {
                    $nome = strtolower($item['nome_indicado'] ?? '');
                    $telefone = strtolower($item['telefone_indicado'] ?? '');
                    return str_contains($nome, $buscaLower) || str_contains($telefone, $buscaLower);
                });
            }
            
            $total = count($indicacoes);
        } catch (PDOException $e) {
            $stats = ['total' => 0, 'validadas' => 0, 'pendentes' => 0, 'liberadas' => 0];
            $indicacoes = [];
            $total = 0;
        }

        // Add timeline data to each indication
        foreach ($indicacoes as &$indicacao) {
            $indicacao['timeline'] = $indicacaoModel->getTimeline((int) $indicacao['id']);
        }

        $totalPages = (int) ceil($total / $limit);

        $this->view('indicacoes.index', [
            'title' => 'Minhas Indicações',
            'user' => $user,
            'stats' => $stats,
            'indicacoes' => $indicacoes,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'hasNext' => $page < $totalPages,
            'hasPrev' => $page > 1,
            'filtro' => $filtro,
            'busca' => $busca,
        ], 'app');
    }
}
