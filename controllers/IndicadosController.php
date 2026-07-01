<?php

declare(strict_types=1);

class IndicadosController extends Controller
{
    private EventLogger $eventLogger;
    private ReferralService $referralService;

    public function __construct()
    {
        $this->eventLogger = new EventLogger();
        $this->referralService = new ReferralService();
    }

    private const RATE_LIMIT_ATTEMPTS = 3;
    private const RATE_LIMIT_WINDOW = 3600; // 1 hour

    public function cadastro(): void
    {
        $codigo = $_GET['ref'] ?? '';

        if ($codigo === '') {
            $this->renderConviteInvalido();
            return;
        }

        $result = $this->referralService->validateCode($codigo, Auth::id());

        if (!$result['valid'] || $result['user'] === null) {
            $this->renderConviteInvalido();
            return;
        }

        $this->referralService->storeRefInSession($codigo);

        $this->view('indicados.cadastro', [
            'title' => 'Cadastre-se',
            'codigo' => $codigo,
            'indicador' => $result['user'],
        ], 'app');
    }

    public function salvar(): void
    {
        if (!Csrf::validateRequest()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/cadastro-indicado?ref=' . ($_POST['codigo'] ?? ''));
        }

        $codigo = $_POST['codigo'] ?? '';

        if ($codigo === '') {
            $this->renderConviteInvalido();
            return;
        }

        $result = $this->referralService->validateCode($codigo, Auth::id());

        if (!$result['valid'] || $result['user'] === null) {
            $this->renderConviteInvalido();
            return;
        }

        $indicador = $result['user'];

        $rateLimiter = new RateLimiter();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';

        if (!$rateLimiter->attempt('indicado_cadastro:' . $ip, self::RATE_LIMIT_ATTEMPTS, self::RATE_LIMIT_WINDOW)) {
            Session::flash('error', 'Muitas tentativas. Tente novamente mais tarde.');
            $this->redirect('/cadastro-indicado?ref=' . ($_POST['codigo'] ?? ''));
        }

        $nome = Validator::sanitizeString($_POST['nome'] ?? '', 150);
        $cpf = Validator::onlyDigits($_POST['cpf'] ?? '');
        $whatsapp = Validator::onlyDigits($_POST['whatsapp'] ?? '');
        $email = Validator::sanitizeEmail($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $aceiteLgpd = isset($_POST['aceite_lgpd']) ? 1 : 0;

        $errors = [];
        $usuarioModel = new Usuario();
        $indicacaoModel = new Indicacao();

        if ($nome === '') {
            $errors['nome'] = 'Informe seu nome completo.';
        }

        if (!Validator::validateCpf($cpf)) {
            $errors['cpf'] = 'CPF inválido.';
        }

        if (!Validator::validateTelefone($whatsapp)) {
            $errors['whatsapp'] = 'WhatsApp inválido.';
        }

        if (!Validator::validateEmail($email)) {
            $errors['email'] = 'Email inválido.';
        }

        if (strlen($senha) < 6) {
            $errors['senha'] = 'A senha deve ter no mínimo 6 caracteres.';
        }

        if ($aceiteLgpd !== 1) {
            $errors['aceite_lgpd'] = 'Você deve aceitar os termos de uso.';
        }

        if ($usuarioModel->cpfExists($cpf)) {
            $errors['cpf'] = 'Este CPF já está cadastrado.';
        }

        if ($usuarioModel->emailExists($email)) {
            $errors['email'] = 'Este email já está cadastrado.';
        }

        if ($usuarioModel->telefoneExists($whatsapp) || $indicacaoModel->phoneAlreadyIndicated($whatsapp)) {
            $errors['whatsapp'] = 'Este WhatsApp já está cadastrado.';
        }

        if ($cpf === $indicador['cpf']) {
            $errors['cpf'] = 'Você não pode se auto-indicar.';
        }

        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('old', $_POST);
            $this->redirect('/cadastro-indicado?ref=' . $codigo);
        }

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $usuarioCodigo = $usuarioModel->generateCodigoIndicador();
        $userId = $usuarioModel->create([
            'nome' => $nome,
            'cpf' => $cpf,
            'email' => $email,
            'senha_hash' => $senhaHash,
            'aceite_lgpd' => $aceiteLgpd,
            'codigo_indicador' => $usuarioCodigo,
            'whatsapp' => $whatsapp,
        ]);

        $this->referralService->attachRegistrationWithCode($codigo, $userId, $nome, $whatsapp, $cpf);

        $this->eventLogger->logIndicadoCadastrado((int) $indicador['id'], $nome);
        $this->eventLogger->logCadastro($userId, [
            'nome' => $nome,
            'email' => $email,
        ]);

        $newUser = $usuarioModel->findById($userId);
        if ($newUser !== null) {
            Auth::login($newUser);
        }

        $this->redirect('/finalizado');
    }

    public function finalizado(): void
    {
        $this->view('indicados.finalizado', [
            'title' => 'Cadastro Realizado',
        ], 'app');
    }

    private function renderConviteInvalido(): void
    {
        $this->view('indicados.convite-invalido', [
            'title' => 'Convite Inválido',
        ], 'app');
    }
}
