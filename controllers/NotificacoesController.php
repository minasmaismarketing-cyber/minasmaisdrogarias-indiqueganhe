<?php

declare(strict_types=1);

class NotificacoesController extends Controller
{
    public function index(): void
    {
        AuthMiddleware::requireAuth();

        $user = Auth::user();

        if ($user === null) {
            $this->redirect('/login');
        }

        $notificacaoModel = new Notificacao();
        $notificacoes = $notificacaoModel->findByUsuario((int) $user['id']);
        $unreadCount = $notificacaoModel->countUnread((int) $user['id']);

        $this->view('notificacoes.index', [
            'title' => 'Notificações',
            'user' => $user,
            'notificacoes' => $notificacoes,
            'unreadCount' => $unreadCount,
        ], 'app');
    }
}
