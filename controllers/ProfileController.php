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

        $historicoValidacoes = [];
        $validacaoStats = ['total' => 0, 'aprovados' => 0, 'reprovados' => 0, 'beneficios_liberados' => 0];

        try {
            $validacaoModel = new ValidacaoIndicacao();
            $validacaoStats = $validacaoModel->statsUsuarioIndicador((int) $user['id']);
            $historicoValidacoes = $validacaoModel->listByUsuarioIndicador((int) $user['id'], 20);
        } catch (Throwable $e) {
            Logger::warning('Validacao table not found or error', ['error' => $e->getMessage()]);
        }

        $this->view('perfil.index', [
            'title' => 'Perfil — Indique e Ganhe',
            'user' => $user,
            'errors' => Session::flash('errors') ?? [],
            'success' => Session::flash('success'),
            'historicoValidacoes' => $historicoValidacoes,
            'validacaoStats' => $validacaoStats,
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
        $telefone = Validator::onlyDigits($_POST['telefone'] ?? '');
        $whatsapp = Validator::onlyDigits($_POST['whatsapp'] ?? '');

        $errors = [];

        if ($nome === '') {
            $errors['nome'] = 'Informe seu nome.';
        }

        if (!Validator::telefone($telefone)) {
            $errors['telefone'] = 'Telefone inválido.';
        }

        if (!Validator::telefone($whatsapp)) {
            $errors['whatsapp'] = 'WhatsApp inválido.';
        }

        $usuarioModel = new Usuario();

        if ($telefone !== (string) $user['telefone'] && $usuarioModel->telefoneExists($telefone)) {
            $errors['telefone'] = 'Telefone já cadastrado.';
        }

        if ($errors !== []) {
            Session::flash('errors', $errors);
            $this->redirect('/perfil');
        }

        $usuarioModel->updateProfile((int) $user['id'], [
            'nome' => $nome,
            'telefone' => $telefone,
            'whatsapp' => $whatsapp,
        ]);

        $this->eventLogger->logPerfilEditado((int) $user['id'], [
            'nome' => $nome,
            'telefone' => $telefone,
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
        (new Usuario())->softDelete($userId);

        $this->eventLogger->logPerfilEditado($userId, ['action' => 'account_deleted']);

        Auth::logout();

        Session::start();
        Session::flash('success', 'Sua conta foi excluída com sucesso.');
        $this->redirect('/login');
    }
}
