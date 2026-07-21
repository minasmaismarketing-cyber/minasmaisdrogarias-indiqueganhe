<?php

declare(strict_types=1);

class ProfileController extends Controller
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

        $this->view('perfil.index', [
            'title' => 'Perfil — Indique e Ganhe',
            'user' => $user,
            'errors' => Session::flash('errors') ?? [],
            'old' => Session::flash('old') ?? [],
            'success' => Session::flash('success'),
        ], 'app');
    }

    public function update(): void
    {
        AuthMiddleware::requireAuth();

        if (!Csrf::validateRequest()) {
            Session::flash('errors', ['_form' => 'Token de segurança inválido.']);
            $this->redirect('/perfil');
        }

        $user = Auth::user();

        if ($user === null) {
            $this->redirect('/login');
        }

        $nome = Validator::sanitizeString($_POST['nome'] ?? '', 150);
        $whatsapp = Validator::onlyDigits($_POST['whatsapp'] ?? '');

        $errors = [];

        if ($nome === '') {
            $errors['nome'] = 'Informe seu nome.';
        }

        if (!Validator::telefone($whatsapp)) {
            $errors['whatsapp'] = 'WhatsApp inválido.';
        }

        $usuarioModel = new Usuario();

        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('old', [
                'nome' => $nome,
                'whatsapp' => (string) ($_POST['whatsapp'] ?? ''),
            ]);
            $this->redirect('/perfil');
        }

        $usuarioModel->updateProfile((int) $user['id'], [
            'nome' => $nome,
            'whatsapp' => $whatsapp,
        ]);

        $this->eventLogger->logPerfilEditado((int) $user['id'], [
            'nome' => $nome,
            'whatsapp' => $whatsapp,
        ]);

        Session::flash('success', 'Perfil atualizado com sucesso!');
        $this->redirect('/perfil');
    }

    public function changePassword(): void
    {
        AuthMiddleware::requireAuth();

        if (!Csrf::validateRequest()) {
            Session::flash('errors', ['_form' => 'Token de segurança inválido.']);
            $this->redirect('/perfil');
        }

        $user = Auth::user();

        if ($user === null) {
            $this->redirect('/login');
        }

        $senhaAtual = (string) ($_POST['senha_atual'] ?? '');
        $novaSenha = (string) ($_POST['nova_senha'] ?? '');
        $confirmarSenha = (string) ($_POST['confirmar_senha'] ?? '');

        $errors = [];

        if ($senhaAtual === '') {
            $errors['senha_atual'] = 'Informe sua senha atual.';
        }

        if (!Validator::senha($novaSenha)) {
            $errors['nova_senha'] = 'Senha com no mínimo 8 caracteres, 1 letra e 1 número.';
        }

        if ($novaSenha !== $confirmarSenha) {
            $errors['confirmar_senha'] = 'As senhas não conferem.';
        }

        if (!password_verify($senhaAtual, (string) $user['senha_hash'])) {
            $errors['senha_atual'] = 'Senha atual incorreta.';
        }

        if ($errors !== []) {
            Session::flash('errors', $errors);
            $this->redirect('/perfil');
        }

        (new Usuario())->updatePassword((int) $user['id'], password_hash($novaSenha, PASSWORD_BCRYPT));

        $this->eventLogger->logSenhaAlterada((int) $user['id']);

        Session::flash('success', 'Senha alterada com sucesso!');
        $this->redirect('/perfil');
    }

    public function delete(): void
    {
        AuthMiddleware::requireAuth();

        if (!Csrf::validateRequest()) {
            Session::flash('errors', ['_form' => 'Token de segurança inválido.']);
            $this->redirect('/perfil');
        }

        $user = Auth::user();

        if ($user === null) {
            $this->redirect('/login');
        }

        $senha = (string) ($_POST['senha'] ?? '');
        $errors = [];

        if ($senha === '') {
            $errors['senha'] = 'Informe sua senha para confirmar a exclusão.';
        }

        if (!password_verify($senha, (string) $user['senha_hash'])) {
            $errors['senha'] = 'Senha incorreta.';
        }

        if ($errors !== []) {
            Session::flash('errors', $errors);
            $this->redirect('/perfil');
        }

        $userId = (int) $user['id'];

        try {
            (new Usuario())->excluirConta($userId);
        } catch (PDOException $e) {
            Logger::error('Account delete failed', ['user_id' => $userId, 'error' => $e->getMessage()]);
            Session::flash('errors', ['senha' => 'Não foi possível excluir a conta. Tente novamente.']);
            $this->redirect('/perfil');
        }

        $this->eventLogger->logPerfilEditado($userId, ['action' => 'account_deleted']);

        Auth::logout();

        Session::start();
        Session::flash('success', 'Sua conta foi excluída com sucesso.');
        $this->redirect('/login');
    }
}
