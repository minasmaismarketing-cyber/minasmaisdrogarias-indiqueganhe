<?php

declare(strict_types=1);

class ConfiguracoesController extends Controller
{
    public function index(): void
    {
        AuthMiddleware::requireAuth();

        $user = Auth::user();

        if ($user === null) {
            $this->redirect('/login');
        }

        $this->view('configuracoes.index', [
            'title' => 'Configurações',
            'user' => $user,
            'errors' => Session::flash('errors') ?? [],
            'success' => Session::flash('success'),
        ], 'app');
    }

    public function update(): void
    {
        AuthMiddleware::requireAuth();

        if (!Csrf::validateRequest()) {
            Session::flash('errors', ['_form' => 'Token de segurança inválido.']);
            $this->redirect('/configuracoes');
        }

        $user = Auth::user();

        if ($user === null) {
            $this->redirect('/login');
        }

        $telefone = Validator::onlyDigits($_POST['telefone'] ?? '');
        $email = strtolower(Validator::sanitizeString($_POST['email'] ?? '', 180));
        $whatsapp = Validator::onlyDigits($_POST['whatsapp'] ?? '');

        $errors = [];

        if (!Validator::telefone($telefone)) {
            $errors['telefone'] = 'Telefone inválido.';
        }

        if (!Validator::email($email)) {
            $errors['email'] = 'E-mail inválido.';
        }

        if (!Validator::telefone($whatsapp)) {
            $errors['whatsapp'] = 'WhatsApp inválido.';
        }

        $usuarioModel = new Usuario();

        if ($telefone !== (string) $user['telefone'] && $usuarioModel->telefoneExists($telefone)) {
            $errors['telefone'] = 'Telefone já cadastrado.';
        }

        if ($email !== (string) $user['email'] && $usuarioModel->emailExists($email)) {
            $errors['email'] = 'E-mail já cadastrado.';
        }

        if ($errors !== []) {
            Session::flash('errors', $errors);
            $this->redirect('/configuracoes');
        }

        $usuarioModel->updateProfile((int) $user['id'], [
            'nome' => (string) $user['nome'],
            'telefone' => $telefone,
            'whatsapp' => $whatsapp,
        ]);

        // Update email separately if changed
        if ($email !== (string) $user['email']) {
            $stmt = $usuarioModel->db->prepare(
                'UPDATE usuarios SET email = :email, updated_at = NOW() WHERE id = :id'
            );
            $stmt->execute(['email' => $email, 'id' => (int) $user['id']]);
        }

        Session::flash('success', 'Configurações atualizadas com sucesso!');
        $this->redirect('/configuracoes');
    }
}
