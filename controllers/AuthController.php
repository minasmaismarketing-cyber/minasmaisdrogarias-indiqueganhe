<?php

declare(strict_types=1);

class AuthController extends Controller
{
    private EventLogger $eventLogger;

    public function __construct()
    {
        $this->eventLogger = new EventLogger();
    }
    public function registerForm(): void
    {
        AuthMiddleware::requireGuest();

        $this->view('auth.cadastro', [
            'title' => 'Cadastro',
            'errors' => Session::flash('errors') ?? [],
            'success' => Session::flash('success'),
            'referralCode' => (new ReferralService())->getRefFromSession(),
        ]);
    }

    public function register(): void
    {
        AuthMiddleware::requireGuest();

        if (!Csrf::validateRequest()) {
            Session::flash('errors', ['_form' => 'Token de segurança inválido. Tente novamente.']);
            $this->redirect('/cadastro');
        }

        $input = $this->registerInput();
        $errors = $this->validateRegister($input);

        if ($errors !== []) {
            $this->flashOldRegister($input);
            Session::flash('errors', $errors);
            $this->redirect('/cadastro');
        }

        $usuarioModel = new Usuario();
        $codigo = $usuarioModel->generateCodigoIndicador();

        $userId = $usuarioModel->create([
            'nome' => $input['nome'],
            'cpf' => $input['cpf'],
            'telefone' => $input['telefone'],
            'email' => $input['email'],
            'senha_hash' => password_hash($input['senha'], PASSWORD_BCRYPT),
            'aceite_lgpd' => 1,
            'codigo_indicador' => $codigo,
            'whatsapp' => $input['whatsapp'],
        ]);

        $user = $usuarioModel->findById($userId);

        if ($user === null) {
            Session::flash('errors', ['_form' => 'Não foi possível concluir o cadastro.']);
            $this->redirect('/cadastro');
        }

        Auth::login($user);

        (new ReferralService())->attachRegistrationToReferral(
            $userId,
            $input['nome'],
            $input['telefone'],
            $input['cpf']
        );

        $this->eventLogger->logCadastro($userId, $input);
        $this->eventLogger->logLinkGerado($userId, $codigo);

        Session::flash('success', 'Cadastro realizado com sucesso!');
        $this->redirect('/dashboard');
    }

    public function loginForm(): void
    {
        AuthMiddleware::requireGuest();

        $this->view('auth.login', [
            'title' => 'Login — Indique e Ganhe',
            'errors' => Session::flash('errors') ?? [],
            'success' => Session::flash('success'),
        ]);
    }

    public function login(): void
    {
        AuthMiddleware::requireGuest();

        if (!Csrf::validateRequest()) {
            Session::flash('errors', ['_form' => 'Token de segurança inválido. Tente novamente.']);
            $this->redirect('/login');
        }

        $login = Validator::sanitizeString($_POST['login'] ?? '', 180);
        $senha = (string) ($_POST['senha'] ?? '');
        $lembrar = isset($_POST['lembrar']);

        Session::flash('_old_login', $login);

        $rateKey = 'login:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . ':' . strtolower($login);

        if (RateLimiter::tooManyAttempts($rateKey)) {
            $seconds = RateLimiter::remainingSeconds($rateKey);
            $minutes = (int) ceil($seconds / 60);
            Session::flash('errors', [
                '_form' => "Muitas tentativas. Aguarde {$minutes} minuto(s) e tente novamente.",
            ]);
            $this->redirect('/login');
        }

        $errors = [];

        if ($login === '') {
            $errors['login'] = 'Informe CPF ou e-mail.';
        }

        if ($senha === '') {
            $errors['senha'] = 'Informe sua senha.';
        }

        if ($errors !== []) {
            Session::flash('errors', $errors);
            $this->redirect('/login');
        }

        $usuario = (new Usuario())->findByLogin($login);

        if ($usuario === null || !password_verify($senha, (string) $usuario['senha_hash'])) {
            RateLimiter::hit($rateKey);
            Session::flash('errors', ['_form' => 'CPF/e-mail ou senha incorretos.']);
            $this->redirect('/login');
        }

        if (isset($usuario['ativo']) && (int) $usuario['ativo'] === 0) {
            RateLimiter::hit($rateKey);
            Session::flash('errors', ['_form' => 'Conta inativa ou excluída.']);
            $this->redirect('/login');
        }

        RateLimiter::clear($rateKey);
        Auth::login($usuario, $lembrar);
        
        $this->eventLogger->logLogin((int) $usuario['id']);
        
        Session::flash('success', 'Login realizado com sucesso!');
        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !Csrf::validateRequest()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/dashboard');
        }

        Auth::logout();
        Session::start();
        Session::flash('success', 'Você saiu da sua conta.');
        $this->redirect('/login');
    }

    public function forgotForm(): void
    {
        AuthMiddleware::requireGuest();

        $this->view('auth.esqueci-senha', [
            'title' => 'Recuperar senha — Indique e Ganhe',
            'errors' => Session::flash('errors') ?? [],
            'success' => Session::flash('success'),
            'resetLink' => Session::flash('reset_link'),
        ]);
    }

    public function forgot(): void
    {
        AuthMiddleware::requireGuest();

        if (!Csrf::validateRequest()) {
            Session::flash('errors', ['_form' => 'Token de segurança inválido. Tente novamente.']);
            $this->redirect('/esqueci-senha');
        }

        $login = Validator::sanitizeString($_POST['login'] ?? '', 180);
        Session::flash('_old_login', $login);

        if ($login === '') {
            Session::flash('errors', ['login' => 'Informe CPF ou e-mail.']);
            $this->redirect('/esqueci-senha');
        }

        $usuario = (new Usuario())->findByLogin($login);

        if ($usuario !== null) {
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 3600);

            (new SenhaRecuperacao())->create(
                (int) $usuario['id'],
                hash('sha256', $token),
                $expiresAt
            );

            $resetLink = url('/redefinir-senha?token=' . $token);

            Logger::info('Password reset token generated', [
                'user_id' => (int) $usuario['id'],
                'reset_link' => $resetLink,
            ]);

            if ((bool) Env::get('APP_DEBUG', false)) {
                Session::flash('reset_link', $resetLink);
            }
        }

        Session::flash(
            'success',
            'Se o cadastro existir, as instruções de recuperação foram registradas.'
        );
        $this->redirect('/esqueci-senha');
    }

    public function resetForm(): void
    {
        AuthMiddleware::requireGuest();

        $token = Validator::sanitizeString($_GET['token'] ?? '', 128);

        if ($token === '' || (new SenhaRecuperacao())->findValidByToken($token) === null) {
            Session::flash('errors', ['_form' => 'Link de recuperação inválido ou expirado.']);
            $this->redirect('/esqueci-senha');
        }

        $this->view('auth.redefinir-senha', [
            'title' => 'Redefinir senha — Indique e Ganhe',
            'token' => $token,
            'errors' => Session::flash('errors') ?? [],
        ]);
    }

    public function reset(): void
    {
        AuthMiddleware::requireGuest();

        if (!Csrf::validateRequest()) {
            Session::flash('errors', ['_form' => 'Token de segurança inválido. Tente novamente.']);
            $this->redirect('/esqueci-senha');
        }

        $token = Validator::sanitizeString($_POST['token'] ?? '', 128);
        $senha = (string) ($_POST['senha'] ?? '');
        $confirmar = (string) ($_POST['confirmar_senha'] ?? '');

        $recuperacao = (new SenhaRecuperacao())->findValidByToken($token);

        if ($recuperacao === null) {
            Session::flash('errors', ['_form' => 'Link de recuperação inválido ou expirado.']);
            $this->redirect('/esqueci-senha');
        }

        $errors = [];

        if (!Validator::senha($senha)) {
            $errors['senha'] = 'Senha com no mínimo 8 caracteres, 1 letra e 1 número.';
        }

        if ($senha !== $confirmar) {
            $errors['confirmar_senha'] = 'As senhas não conferem.';
        }

        if ($errors !== []) {
            Session::flash('errors', $errors);
            $this->redirect('/redefinir-senha?token=' . urlencode($token));
        }

        (new Usuario())->updatePassword(
            (int) $recuperacao['usuario_id'],
            password_hash($senha, PASSWORD_BCRYPT)
        );
        (new SenhaRecuperacao())->markUsed((int) $recuperacao['id']);

        Session::flash('success', 'Senha redefinida com sucesso! Faça login.');
        $this->redirect('/login');
    }

    /** @return array<string, string> */
    private function registerInput(): array
    {
        return [
            'nome' => Validator::sanitizeString($_POST['nome'] ?? '', 150),
            'cpf' => Validator::onlyDigits($_POST['cpf'] ?? ''),
            'telefone' => Validator::onlyDigits($_POST['telefone'] ?? ''),
            'whatsapp' => Validator::onlyDigits($_POST['whatsapp'] ?? ''),
            'email' => strtolower(Validator::sanitizeString($_POST['email'] ?? '', 180)),
            'senha' => (string) ($_POST['senha'] ?? ''),
            'confirmar_senha' => (string) ($_POST['confirmar_senha'] ?? ''),
            'aceite_lgpd' => isset($_POST['aceite_lgpd']) ? '1' : '',
        ];
    }

    /** @param array<string, string> $input
     *  @return array<string, string>
     */
    private function validateRegister(array $input): array
    {
        $errors = [];
        $usuarioModel = new Usuario();

        if ($input['nome'] === '') {
            $errors['nome'] = 'Informe seu nome.';
        }

        if (!Validator::cpf($input['cpf'])) {
            $errors['cpf'] = 'CPF inválido.';
        } elseif ($usuarioModel->cpfExists($input['cpf'])) {
            $errors['cpf'] = 'CPF já cadastrado.';
        }

        if (!Validator::telefone($input['telefone'])) {
            $errors['telefone'] = 'Telefone inválido.';
        } elseif ($usuarioModel->telefoneExists($input['telefone'])) {
            $errors['telefone'] = 'Telefone já cadastrado.';
        }

        if (!Validator::telefone($input['whatsapp'])) {
            $errors['whatsapp'] = 'WhatsApp inválido.';
        }

        if (!Validator::email($input['email'])) {
            $errors['email'] = 'E-mail inválido.';
        } elseif ($usuarioModel->emailExists($input['email'])) {
            $errors['email'] = 'E-mail já cadastrado.';
        }

        if (!Validator::senha($input['senha'])) {
            $errors['senha'] = 'Senha com no mínimo 8 caracteres, 1 letra e 1 número.';
        }

        if ($input['senha'] !== $input['confirmar_senha']) {
            $errors['confirmar_senha'] = 'As senhas não conferem.';
        }

        if ($input['aceite_lgpd'] !== '1') {
            $errors['aceite_lgpd'] = 'É necessário aceitar os termos LGPD.';
        }

        return $errors;
    }

    /** @param array<string, string> $input */
    private function flashOldRegister(array $input): void
    {
        foreach ($input as $key => $value) {
            if ($key !== 'senha' && $key !== 'confirmar_senha') {
                Session::flash('_old_' . $key, $value);
            }
        }
    }
}
