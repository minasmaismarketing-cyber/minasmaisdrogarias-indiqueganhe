<?php

declare(strict_types=1);

class PremiosController extends Controller
{
    public function index(): void
    {
        AuthMiddleware::requireAuth();

        $user = Auth::user();

        if ($user === null) {
            $this->redirect('/login');
        }

        $indicacaoModel = new Indicacao();
        $userId = (int) $user['id'];

        try {
            $stats = $indicacaoModel->statsByUsuario($userId);
            $liberadas = $stats['liberadas'];
        } catch (PDOException $e) {
            $liberadas = 0;
        }

        // Determine premio state
        if ($liberadas === 0) {
            $premioState = 'sem_beneficio';
            $premioLabel = 'Sem benefício disponível';
        } elseif ($liberadas > 0) {
            $premioState = 'disponivel';
            $premioLabel = 'Benefício disponível';
        } else {
            $premioState = 'resgatado';
            $premioLabel = 'Benefício resgatado';
        }

        $this->view('premios.index', [
            'title' => 'Meus Prêmios',
            'user' => $user,
            'premioState' => $premioState,
            'premioLabel' => $premioLabel,
            'liberadas' => $liberadas,
        ], 'app');
    }
}
