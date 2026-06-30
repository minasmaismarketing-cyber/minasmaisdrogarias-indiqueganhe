<?php

declare(strict_types=1);

class IndicadosController extends Controller
{
    private EventLogger $eventLogger;

    public function __construct()
    {
        $this->eventLogger = new EventLogger();
    }
    private const RATE_LIMIT_ATTEMPTS = 3;
    private const RATE_LIMIT_WINDOW = 3600; // 1 hour

    public function index(): void
    {
        $codigo = $_GET['ref'] ?? '';
        
        if ($codigo === '') {
            $this->renderConviteInvalido();
            return;
        }

        $usuarioModel = new Usuario();
        $indicador = $usuarioModel->findByCodigo($codigo);

        if ($indicador === null) {
            $this->renderConviteInvalido();
            return;
        }

        // Check if already logged in
        if (Auth::check()) {
            $user = Auth::user();
            if ($user !== null && $user['cpf'] === $indicador['cpf']) {
                Session::flash('error', 'Você não pode se auto-indicar.');
                $this->redirect('/dashboard');
            }
        }

        // Save reference in session
        Session::set('referral_code', $codigo);
        Session::set('referral_user_id', $indicador['id']);

        // Create or update indicado record
        $indicadoModel = new Indicado();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        
        // Check if already registered
        $existingIndicado = $indicadoModel->findByCodigo($codigo);
        
        if ($existingIndicado === null) {
            $indicadoModel->create([
                'codigo_indicador' => $codigo,
                'usuario_indicador_id' => $indicador['id'],
                'status' => Indicado::STATUS_LINK_ACESSADO,
                'origem' => $ip,
            ]);
        }

        $this->eventLogger->logConviteAberto($codigo);
        $this->eventLogger->logLinkClicado($indicador['id'], $codigo);

        // Load active campaign data
        $campanhaModel = new Campanha();
        $campanhaAtiva = $campanhaModel->findActive();

        $this->view('indicados.convite', [
            'title' => 'Participe da Campanha',
            'codigo' => $codigo,
            'indicador' => $indicador,
            'campanha' => $campanhaAtiva,
        ], 'main');
    }

    public function participar(): void
    {
        $codigo = $_POST['codigo'] ?? '';
        
        if ($codigo === '') {
            $this->renderConviteInvalido();
            return;
        }

        $usuarioModel = new Usuario();
        $indicador = $usuarioModel->findByCodigo($codigo);

        if ($indicador === null) {
            $this->renderConviteInvalido();
            return;
        }

        // Update indicado status
        $indicadoModel = new Indicado();
        $existingIndicado = $indicadoModel->findByCodigo($codigo);
        
        if ($existingIndicado !== null) {
            $indicadoModel->updateStatus($existingIndicado['id'], Indicado::STATUS_CADASTRO_INICIADO);
            $this->eventLogger->log($indicador['id'], 'CADASTRO_INICIADO', $codigo);
        }

        $this->redirect('/cadastro-indicado?ref=' . $codigo);
    }

    public function cadastro(): void
    {
        $codigo = $_GET['ref'] ?? '';
        
        if ($codigo === '') {
            $this->renderConviteInvalido();
            return;
        }

        $usuarioModel = new Usuario();
        $indicador = $usuarioModel->findByCodigo($codigo);

        if ($indicador === null) {
            $this->renderConviteInvalido();
            return;
        }

        $this->view('indicados.cadastro', [
            'title' => 'Cadastre-se',
            'codigo' => $codigo,
            'indicador' => $indicador,
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

        $usuarioModel = new Usuario();
        $indicador = $usuarioModel->findByCodigo($codigo);

        if ($indicador === null) {
            $this->renderConviteInvalido();
            return;
        }

        // Rate limit check
        $rateLimiter = new RateLimiter();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        
        if (!$rateLimiter->attempt('indicado_cadastro:' . $ip, self::RATE_LIMIT_ATTEMPTS, self::RATE_LIMIT_WINDOW)) {
            Session::flash('error', 'Muitas tentativas. Tente novamente mais tarde.');
            $this->redirect('/cadastro-indicado?ref=' . ($_POST['codigo'] ?? ''));
        }

        $nome = Validator::sanitizeString($_POST['nome'] ?? '', 150);
        $cpf = Validator::onlyDigits($_POST['cpf'] ?? '');
        $telefone = Validator::onlyDigits($_POST['telefone'] ?? '');
        $email = Validator::sanitizeEmail($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $aceiteLgpd = isset($_POST['aceite_lgpd']) ? 1 : 0;

        $errors = [];

        // Validate nome
        if ($nome === '') {
            $errors['nome'] = 'Informe seu nome completo.';
        }

        // Validate CPF
        if (!Validator::validateCpf($cpf)) {
            $errors['cpf'] = 'CPF inválido.';
        }

        // Validate telefone
        if (!Validator::validateTelefone($telefone)) {
            $errors['telefone'] = 'Telefone inválido.';
        }

        // Validate email
        if (!Validator::validateEmail($email)) {
            $errors['email'] = 'Email inválido.';
        }

        // Validate senha
        if (strlen($senha) < 6) {
            $errors['senha'] = 'A senha deve ter no mínimo 6 caracteres.';
        }

        // Validate aceite LGPD
        if ($aceiteLgpd !== 1) {
            $errors['aceite_lgpd'] = 'Você deve aceitar os termos de uso.';
        }

        // Check duplicates
        $indicadoModel = new Indicado();

        if ($indicadoModel->cpfExists($cpf) || $usuarioModel->cpfExists($cpf)) {
            $errors['cpf'] = 'Este CPF já está cadastrado.';
        }

        if ($indicadoModel->emailExists($email) || $usuarioModel->emailExists($email)) {
            $errors['email'] = 'Este email já está cadastrado.';
        }

        if ($indicadoModel->telefoneExists($telefone)) {
            $errors['telefone'] = 'Este telefone já está cadastrado.';
        }

        // Check self-indication
        if ($indicador !== null && $cpf === $indicador['cpf']) {
            $errors['cpf'] = 'Você não pode se auto-indicar.';
        }

        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('old', $_POST);
            $this->redirect('/cadastro-indicado?ref=' . $codigo);
        }

        // Hash password
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        // Update indicado record
        $existingIndicado = $indicadoModel->findByCodigo($codigo);
        
        $indicadoId = null;
        if ($existingIndicado !== null) {
            $indicadoModel->update($existingIndicado['id'], [
                'nome' => $nome,
                'cpf' => $cpf,
                'telefone' => $telefone,
                'email' => $email,
                'senha_hash' => $senhaHash,
                'status' => Indicado::STATUS_AGUARDANDO_VALIDACAO,
                'aceite_lgpd' => $aceiteLgpd,
            ]);
            $indicadoId = $existingIndicado['id'];
        } else {
            $indicadoId = $indicadoModel->create([
                'codigo_indicador' => $codigo,
                'usuario_indicador_id' => $indicador['id'],
                'nome' => $nome,
                'cpf' => $cpf,
                'telefone' => $telefone,
                'email' => $email,
                'senha_hash' => $senhaHash,
                'status' => Indicado::STATUS_AGUARDANDO_VALIDACAO,
                'aceite_lgpd' => $aceiteLgpd,
            ]);
        }

        // Create user in usuarios table
        $usuarioCodigo = $usuarioModel->generateCodigoIndicador();
        $userId = $usuarioModel->create([
            'nome' => $nome,
            'cpf' => $cpf,
            'telefone' => $telefone,
            'email' => $email,
            'senha_hash' => $senhaHash,
            'aceite_lgpd' => $aceiteLgpd,
            'codigo_indicador' => $usuarioCodigo,
            'whatsapp' => $telefone,
        ]);

        // Update indicado with usuario_id if needed
        if ($userId) {
            $indicadoModel->update($indicadoId, ['usuario_indicador_id' => $userId]);
        }

        // Create event
        $eventoModel = new EventoIndicacao();
        $eventoModel->register($indicador['id'], EventoIndicacao::EVENTO_CADASTRO, [
            'nome' => $nome,
            'cpf' => $cpf,
        ]);

        $this->eventLogger->logIndicadoCadastrado((int) $indicador['id'], $nome);
        $this->eventLogger->logCadastro($userId, [
            'nome' => $nome,
            'email' => $email,
        ]);

        // Auto-login the new user
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
