<?php

declare(strict_types=1);

/**
 * Ponto único oficial para criação e evolução de indicações.
 *
 * Todo INSERT/UPDATE em `indicacoes` e `validacao_indicacoes` deve ocorrer
 * exclusivamente através desta classe. Controllers e demais camadas não devem
 * gravar diretamente nessas tabelas nem na tabela legada `indicados`.
 */
class ReferralService
{
    public const SESSION_REF = 'indicacao_ref';

    /** Status de resposta da API KOBE (contrato legado). */
    private const API_STATUS_AGUARDANDO_VALIDACAO = 'AGUARDANDO_VALIDACAO';
    private const API_STATUS_EM_ANALISE = 'EM_ANALISE';
    private const API_STATUS_INVALIDADO = 'INVALIDADO';

    public function __construct(
        private Usuario $usuarioModel = new Usuario(),
        private Indicacao $indicacaoModel = new Indicacao(),
        private ValidacaoIndicacao $validacaoModel = new ValidacaoIndicacao()
    ) {
    }

    public function inviteLink(string $codigo): string
    {
        return url('/convite?ref=' . urlencode($codigo));
    }

    /** @return array{valid: bool, user: array<string, mixed>|null, error: string} */
    public function validateCode(string $codigo, ?int $loggedUserId = null): array
    {
        $codigo = strtoupper(trim($codigo));

        if ($codigo === '' || !preg_match('/^MM[A-Z0-9]{6}$/', $codigo)) {
            return ['valid' => false, 'user' => null, 'error' => 'Código de indicação inválido.'];
        }

        $referrer = $this->usuarioModel->findByCodigo($codigo);

        if ($referrer === null) {
            return ['valid' => false, 'user' => null, 'error' => 'Código de indicação inválido.'];
        }

        if ($loggedUserId !== null && (int) $referrer['id'] === $loggedUserId) {
            return ['valid' => false, 'user' => null, 'error' => 'Você não pode usar seu próprio código.'];
        }

        return ['valid' => true, 'user' => $referrer, 'error' => ''];
    }

    public function storeRefInSession(string $codigo): void
    {
        Session::set(self::SESSION_REF, strtoupper(trim($codigo)));
    }

    public function getRefFromSession(): ?string
    {
        $ref = Session::get(self::SESSION_REF);

        return is_string($ref) && $ref !== '' ? $ref : null;
    }

    public function clearRefFromSession(): void
    {
        Session::remove(self::SESSION_REF);
    }

    public function logShare(int $userId, string $codigo): int
    {
        $codigo = strtoupper(trim($codigo));
        $id = $this->recordShare($userId, $codigo);
        Logger::info('Referral share logged', ['user_id' => $userId, 'codigo' => $codigo, 'indicacao_id' => $id]);

        return $id;
    }

    public function handleLinkAccess(string $codigo): array
    {
        $validation = $this->validateCode($codigo, Auth::id());

        if (!$validation['valid'] || $validation['user'] === null) {
            return $validation;
        }

        $this->storeRefInSession($codigo);
        $this->recordLinkAccess((int) $validation['user']['id'], strtoupper(trim($codigo)));

        Logger::info('Referral link accessed', [
            'codigo' => $codigo,
            'referrer_id' => (int) $validation['user']['id'],
        ]);

        return $validation;
    }

    public function attachRegistrationToReferral(
        int $newUserId,
        string $nome,
        string $telefone,
        string $cpf
    ): void {
        $ref = $this->getRefFromSession();

        if ($ref === null) {
            return;
        }

        $this->attachRegistrationWithCode($ref, $newUserId, $nome, $telefone, $cpf);
        $this->clearRefFromSession();
    }

    /**
     * Vincula cadastro concluído a um código de indicação explícito (fluxo /cadastro-indicado).
     *
     * @return bool true quando a indicação foi registrada; false quando rejeitada silenciosamente
     */
    public function attachRegistrationWithCode(
        string $codigo,
        int $newUserId,
        string $nome,
        string $telefone,
        string $cpf
    ): bool {
        $codigo = strtoupper(trim($codigo));
        $telefone = Validator::onlyDigits($telefone);

        $validation = $this->validateCode($codigo);

        if (!$validation['valid'] || $validation['user'] === null) {
            return false;
        }

        $referrer = $validation['user'];

        if ((string) $referrer['cpf'] === $cpf) {
            return false;
        }

        if ($this->indicacaoModel->phoneAlreadyIndicated($telefone)) {
            return false;
        }

        if ($this->indicacaoModel->findActiveByReferrerAndPhone((int) $referrer['id'], $telefone) !== null) {
            return false;
        }

        $this->completeIndicacaoRegistration(
            (int) $referrer['id'],
            $codigo,
            $nome,
            $telefone,
            $newUserId,
            'WEB'
        );

        Logger::info('Referral registration linked', [
            'referrer_id' => (int) $referrer['id'],
            'new_user_id' => $newUserId,
            'codigo' => $codigo,
        ]);

        return true;
    }

    /**
     * Registra indicação recebida via API externa (KOBE).
     *
     * @return array{success: bool, message: string, status_code: int, status?: string, motivo?: string}
     */
    public function registerApiIndication(
        string $codigoIndicador,
        string $cpfIndicado,
        string $emailIndicado,
        string $telefoneIndicado,
        string $tipoEvento
    ): array {
        $codigoIndicador = strtoupper(trim($codigoIndicador));
        $cpfIndicado = Validator::onlyDigits($cpfIndicado);
        $telefoneIndicado = Validator::onlyDigits($telefoneIndicado);
        $tipoEvento = strtoupper(trim($tipoEvento));

        $validation = $this->validateCode($codigoIndicador);

        if (!$validation['valid'] || $validation['user'] === null) {
            return [
                'success' => false,
                'message' => 'Código do indicador não encontrado',
                'status_code' => 404,
            ];
        }

        $referrer = $validation['user'];

        if ($cpfIndicado === (string) $referrer['cpf']) {
            return [
                'success' => false,
                'message' => 'CPF do indicado não pode ser igual ao CPF do indicador',
                'status_code' => 400,
            ];
        }

        if ($this->usuarioModel->cpfExists($cpfIndicado)) {
            return [
                'success' => false,
                'message' => 'CPF já participou do programa',
                'status_code' => 400,
            ];
        }

        if ($this->usuarioModel->emailExists($emailIndicado)) {
            return [
                'success' => false,
                'message' => 'E-mail já cadastrado',
                'status_code' => 400,
            ];
        }

        if ($this->usuarioModel->telefoneExists($telefoneIndicado) || $this->indicacaoModel->phoneAlreadyIndicated($telefoneIndicado)) {
            return [
                'success' => false,
                'message' => 'Telefone já cadastrado',
                'status_code' => 400,
            ];
        }

        if ($tipoEvento === 'REENGAGEMENT') {
            return [
                'success' => false,
                'status' => self::API_STATUS_INVALIDADO,
                'motivo' => 'APP_JA_EXISTENTE',
                'message' => 'Indicação inválida.',
                'status_code' => 400,
            ];
        }

        if ($this->indicacaoModel->findActiveByReferrerAndPhone((int) $referrer['id'], $telefoneIndicado) !== null) {
            return [
                'success' => false,
                'message' => 'Telefone já cadastrado',
                'status_code' => 400,
            ];
        }

        $nomeIndicado = 'Indicado via API';

        $this->completeIndicacaoRegistration(
            (int) $referrer['id'],
            $codigoIndicador,
            $nomeIndicado,
            $telefoneIndicado,
            null,
            'API'
        );

        if ($tipoEvento === 'UNKNOWN') {
            return [
                'success' => true,
                'status' => self::API_STATUS_EM_ANALISE,
                'message' => 'Cadastro recebido e em análise.',
                'status_code' => 200,
            ];
        }

        return [
            'success' => true,
            'status' => self::API_STATUS_AGUARDANDO_VALIDACAO,
            'message' => 'Cadastro recebido com sucesso.',
            'status_code' => 200,
        ];
    }

    private function recordShare(int $usuarioId, string $codigo): int
    {
        $db = Database::getConnection();
        $codigoReferencia = $this->generateCodigoReferencia();
        $stmt = $db->prepare(
            'INSERT INTO indicacoes (usuario_id, codigo_indicador, codigo_referencia, status, origem)
             VALUES (:usuario_id, :codigo, :codigo_ref, :status, :origem)'
        );
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'codigo' => $codigo,
            'codigo_ref' => $codigoReferencia,
            'status' => Indicacao::STATUS_AGUARDANDO,
            'origem' => 'WEB',
        ]);

        return (int) $db->lastInsertId();
    }

    private function recordLinkAccess(int $usuarioId, string $codigo): int
    {
        $db = Database::getConnection();
        $codigoReferencia = $this->generateCodigoReferencia();
        $stmt = $db->prepare(
            'INSERT INTO indicacoes (usuario_id, codigo_indicador, codigo_referencia, status, origem)
             VALUES (:usuario_id, :codigo, :codigo_ref, :status, :origem)'
        );
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'codigo' => $codigo,
            'codigo_ref' => $codigoReferencia,
            'status' => Indicacao::STATUS_LINK_ACESSADO,
            'origem' => 'WEB',
        ]);

        return (int) $db->lastInsertId();
    }

    private function completeIndicacaoRegistration(
        int $referrerUserId,
        string $codigo,
        string $nomeIndicado,
        string $telefoneIndicado,
        ?int $usuarioIndicadoId,
        string $origem
    ): int {
        $existing = $this->indicacaoModel->findActiveByReferrerAndPhone($referrerUserId, $telefoneIndicado);

        if ($existing !== null) {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                'UPDATE indicacoes
                 SET nome_indicado = :nome,
                     telefone_indicado = :telefone,
                     status = :status,
                     updated_at = NOW()
                 WHERE id = :id'
            );
            $stmt->execute([
                'nome' => $nomeIndicado,
                'telefone' => $telefoneIndicado,
                'status' => Indicacao::STATUS_CADASTRO_PENDENTE,
                'id' => $existing['id'],
            ]);

            $indicacaoId = (int) $existing['id'];
            $this->ensureValidacaoForIndicacao($indicacaoId, $referrerUserId, $usuarioIndicadoId);

            return $indicacaoId;
        }

        $db = Database::getConnection();
        $codigoReferencia = $this->generateCodigoReferencia();
        $stmt = $db->prepare(
            'INSERT INTO indicacoes
            (usuario_id, codigo_indicador, codigo_referencia, nome_indicado, telefone_indicado, status, origem)
            VALUES (:usuario_id, :codigo, :codigo_ref, :nome, :telefone, :status, :origem)'
        );
        $stmt->execute([
            'usuario_id' => $referrerUserId,
            'codigo' => $codigo,
            'codigo_ref' => $codigoReferencia,
            'nome' => $nomeIndicado,
            'telefone' => $telefoneIndicado,
            'status' => Indicacao::STATUS_CADASTRO_PENDENTE,
            'origem' => $origem,
        ]);

        $indicacaoId = (int) $db->lastInsertId();
        $this->ensureValidacaoForIndicacao($indicacaoId, $referrerUserId, $usuarioIndicadoId);

        return $indicacaoId;
    }

    private function ensureValidacaoForIndicacao(int $indicacaoId, int $referrerUserId, ?int $usuarioIndicadoId): void
    {
        $existing = $this->validacaoModel->findByIndicacao($indicacaoId);

        if ($existing !== null) {
            if ($usuarioIndicadoId !== null && empty($existing['usuario_indicado_id'])) {
                $db = Database::getConnection();
                $stmt = $db->prepare(
                    'UPDATE validacao_indicacoes
                     SET usuario_indicado_id = :usuario_indicado_id, updated_at = NOW()
                     WHERE id = :id'
                );
                $stmt->execute([
                    'usuario_indicado_id' => $usuarioIndicadoId,
                    'id' => $existing['id'],
                ]);
            }

            return;
        }

        try {
            $this->createValidacaoFromIndicacao($indicacaoId, $referrerUserId, $usuarioIndicadoId);
        } catch (Throwable $e) {
            Logger::warning('Falha ao criar validacao para indicacao', [
                'indicacao_id' => $indicacaoId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function createValidacaoFromIndicacao(int $indicacaoId, int $referrerUserId, ?int $usuarioIndicadoId): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'INSERT INTO validacao_indicacoes (indicacao_id, usuario_indicador_id, usuario_indicado_id, status)
             VALUES (:indicacao_id, :usuario_indicador_id, :usuario_indicado_id, :status)'
        );
        $stmt->execute([
            'indicacao_id' => $indicacaoId,
            'usuario_indicador_id' => $referrerUserId,
            'usuario_indicado_id' => $usuarioIndicadoId,
            'status' => ValidacaoIndicacao::STATUS_PENDENTE,
        ]);

        return (int) $db->lastInsertId();
    }

    private function generateCodigoReferencia(): string
    {
        return strtoupper(bin2hex(random_bytes(10)));
    }
}
