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

    /** Status de resposta da API KOBE (contrato). */
    private const API_STATUS_AGUARDANDO_VALIDACAO = 'AGUARDANDO_VALIDACAO';
    private const API_STATUS_EM_ANALISE = 'EM_ANALISE';
    private const API_STATUS_INVALIDADO = 'INVALIDADO';
    private const API_STATUS_BENEFICIO_LIBERADO = 'BENEFICIO_LIBERADO';
    private const API_STATUS_BENEFICIO_PENDENTE = 'BENEFICIO_PENDENTE';

    private const FALLBACK_NOME_API = 'Indicado via API';
    private const IDEMPOTENCY_ENDPOINT = '/api/indicacao/confirmar-cadastro';

    public function __construct(
        private Usuario $usuarioModel = new Usuario(),
        private Indicacao $indicacaoModel = new Indicacao(),
        private ValidacaoIndicacao $validacaoModel = new ValidacaoIndicacao()
    ) {
    }

    public function inviteLink(string $codigo): string
    {
        return (new InviteLinkService())->getInviteLinkByCodigo($codigo);
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
     * Registra indicação recebida via API externa (KOBE) com validação automática.
     *
     * @return array{success: bool, message: string, status_code: int, status?: string, motivo?: string}
     */
    public function registerApiIndication(
        string $codigoIndicador,
        string $cpfIndicado,
        string $emailIndicado,
        string $telefoneIndicado,
        string $tipoEvento,
        ?string $nomeIndicado = null,
        ?string $idempotencyKey = null,
        ?string $plataforma = null
    ): array {
        $codigoIndicador = strtoupper(trim($codigoIndicador));
        $cpfIndicado = Validator::onlyDigits($cpfIndicado);
        $emailIndicado = strtolower(trim($emailIndicado));
        $telefoneIndicado = Validator::onlyDigits($telefoneIndicado);
        $tipoEvento = strtoupper(trim($tipoEvento));
        $plataforma = $plataforma !== null ? strtoupper(trim($plataforma)) : null;
        $idempotencyKey = $idempotencyKey !== null ? trim($idempotencyKey) : null;
        if ($idempotencyKey === '') {
            $idempotencyKey = null;
        }
        $nomePayload = $nomeIndicado !== null ? trim($nomeIndicado) : '';

        if ($idempotencyKey !== null) {
            $cached = $this->findIdempotentResponse($idempotencyKey);
            if ($cached !== null) {
                Logger::info('API KOBE reenvio idempotente', [
                    'idempotency_key' => $idempotencyKey,
                    'resultado' => $cached['status'] ?? null,
                ]);
                return $cached;
            }
        }

        $validation = $this->validateCode($codigoIndicador);

        if (!$validation['valid'] || $validation['user'] === null) {
            return $this->storeAndReturnIdempotent($idempotencyKey, [
                'success' => false,
                'message' => 'Código do indicador não encontrado',
                'status_code' => 404,
            ]);
        }

        $referrer = $validation['user'];
        $referrerId = (int) $referrer['id'];

        $existingLogical = $this->indicacaoModel->findApiProcessedByReferrerAndCpf($referrerId, $cpfIndicado);
        if ($existingLogical !== null) {
            $replay = $this->buildReplayResponse($existingLogical);
            Logger::info('API KOBE cadastro já processado (lógico)', [
                'indicacao_id' => $existingLogical['id'] ?? null,
                'cpf' => $this->maskCpf($cpfIndicado),
            ]);
            return $this->storeAndReturnIdempotent($idempotencyKey, $replay);
        }

        $matchedUser = $this->resolveIndicadoUsuario($cpfIndicado, $emailIndicado, $telefoneIndicado);
        $displayName = $this->resolveNomeIndicado($nomePayload, $matchedUser);

        if ($cpfIndicado === (string) $referrer['cpf']) {
            return $this->finalizeRejectedApiIndication(
                $referrerId,
                $codigoIndicador,
                $displayName,
                $cpfIndicado,
                $emailIndicado,
                $telefoneIndicado,
                $matchedUser,
                $tipoEvento,
                $plataforma,
                $idempotencyKey,
                'AUTOINDICACAO',
                'CPF do indicado não pode ser igual ao CPF do indicador'
            );
        }

        if ($tipoEvento === 'REENGAGEMENT') {
            return $this->finalizeRejectedApiIndication(
                $referrerId,
                $codigoIndicador,
                $displayName,
                $cpfIndicado,
                $emailIndicado,
                $telefoneIndicado,
                $matchedUser,
                $tipoEvento,
                $plataforma,
                $idempotencyKey,
                'APP_JA_EXISTENTE',
                'Indicação inválida.',
                self::API_STATUS_INVALIDADO
            );
        }

        if ($this->indicacaoModel->cpfAlreadyParticipated($cpfIndicado)) {
            return $this->finalizeRejectedApiIndication(
                $referrerId,
                $codigoIndicador,
                $displayName,
                $cpfIndicado,
                $emailIndicado,
                $telefoneIndicado,
                $matchedUser,
                $tipoEvento,
                $plataforma,
                $idempotencyKey,
                'CPF_JA_PARTICIPOU',
                'CPF já participou do programa'
            );
        }

        if ($matchedUser !== null || $this->usuarioModel->cpfExists($cpfIndicado)) {
            $motivo = 'CPF_JA_CADASTRADO';
            if ($matchedUser !== null && Validator::onlyDigits((string) ($matchedUser['cpf'] ?? '')) !== $cpfIndicado) {
                if (strtolower((string) ($matchedUser['email'] ?? '')) === $emailIndicado) {
                    $motivo = 'EMAIL_JA_CADASTRADO';
                } else {
                    $motivo = 'TELEFONE_JA_CADASTRADO';
                }
            }

            return $this->finalizeRejectedApiIndication(
                $referrerId,
                $codigoIndicador,
                $displayName,
                $cpfIndicado,
                $emailIndicado,
                $telefoneIndicado,
                $matchedUser,
                $tipoEvento,
                $plataforma,
                $idempotencyKey,
                $motivo,
                $motivo === 'EMAIL_JA_CADASTRADO' ? 'E-mail já cadastrado'
                    : ($motivo === 'TELEFONE_JA_CADASTRADO' ? 'Telefone já cadastrado' : 'CPF já participou do programa')
            );
        }

        if ($this->usuarioModel->emailExists($emailIndicado)) {
            return $this->finalizeRejectedApiIndication(
                $referrerId,
                $codigoIndicador,
                $displayName,
                $cpfIndicado,
                $emailIndicado,
                $telefoneIndicado,
                null,
                $tipoEvento,
                $plataforma,
                $idempotencyKey,
                'EMAIL_JA_CADASTRADO',
                'E-mail já cadastrado'
            );
        }

        if ($this->indicacaoModel->emailAlreadyParticipated($emailIndicado)) {
            return $this->finalizeRejectedApiIndication(
                $referrerId,
                $codigoIndicador,
                $displayName,
                $cpfIndicado,
                $emailIndicado,
                $telefoneIndicado,
                null,
                $tipoEvento,
                $plataforma,
                $idempotencyKey,
                'EMAIL_JA_CADASTRADO',
                'E-mail já cadastrado'
            );
        }

        if ($this->indicacaoModel->phoneAlreadyIndicated($telefoneIndicado)
            || $this->indicacaoModel->findActiveByReferrerAndPhone($referrerId, $telefoneIndicado) !== null
        ) {
            return $this->finalizeRejectedApiIndication(
                $referrerId,
                $codigoIndicador,
                $displayName,
                $cpfIndicado,
                $emailIndicado,
                $telefoneIndicado,
                null,
                $tipoEvento,
                $plataforma,
                $idempotencyKey,
                'TELEFONE_JA_CADASTRADO',
                'Telefone já cadastrado'
            );
        }

        $db = Database::getConnection();
        $db->beginTransaction();

        try {
            $indicacaoId = $this->completeIndicacaoRegistration(
                $referrerId,
                $codigoIndicador,
                $displayName,
                $telefoneIndicado,
                null,
                'API',
                $cpfIndicado,
                $emailIndicado,
                $tipoEvento,
                $plataforma,
                $idempotencyKey
            );

            $validacao = $this->validacaoModel->findByIndicacao($indicacaoId);
            if ($validacao === null) {
                throw new RuntimeException('Validação não criada para indicação API');
            }

            $validacaoId = (int) $validacao['id'];
            $this->validacaoModel->recordApiHistory(
                $validacaoId,
                (string) $validacao['status'],
                (string) $validacao['status'],
                'Cadastro recebido via API'
            );

            if ($tipoEvento === 'UNKNOWN') {
                $this->validacaoModel->updateStatus($validacaoId, ValidacaoIndicacao::STATUS_EM_ANALISE);
                $this->validacaoModel->recordApiHistory(
                    $validacaoId,
                    ValidacaoIndicacao::STATUS_PENDENTE,
                    ValidacaoIndicacao::STATUS_EM_ANALISE,
                    'Evento UNKNOWN — análise manual necessária'
                );

                $db->commit();

                $response = [
                    'success' => true,
                    'status' => self::API_STATUS_EM_ANALISE,
                    'message' => 'Cadastro recebido e em análise.',
                    'status_code' => 200,
                ];

                Logger::info('API KOBE UNKNOWN em análise', [
                    'indicacao_id' => $indicacaoId,
                    'cpf' => $this->maskCpf($cpfIndicado),
                ]);

                return $this->storeAndReturnIdempotent($idempotencyKey, $response);
            }

            // INSTALL (e demais elegíveis): aprovação automática + cupom
            $approval = $this->validacaoModel->approveAutomatically($validacaoId);

            $db->commit();

            if (!($approval['ok'] ?? false)) {
                $response = [
                    'success' => true,
                    'status' => self::API_STATUS_AGUARDANDO_VALIDACAO,
                    'message' => 'Cadastro recebido com sucesso.',
                    'status_code' => 200,
                ];
                return $this->storeAndReturnIdempotent($idempotencyKey, $response);
            }

            if (!empty($approval['cupom_assigned'])) {
                $response = [
                    'success' => true,
                    'status' => self::API_STATUS_BENEFICIO_LIBERADO,
                    'message' => 'Indicação aprovada e benefício liberado.',
                    'status_code' => 200,
                ];
            } else {
                $response = [
                    'success' => true,
                    'status' => self::API_STATUS_BENEFICIO_PENDENTE,
                    'message' => 'Indicação aprovada. O benefício será liberado em breve.',
                    'status_code' => 200,
                ];
            }

            Logger::info('API KOBE resultado automático', [
                'indicacao_id' => $indicacaoId,
                'resultado' => $response['status'],
                'motivo' => $approval['motivo'] ?? null,
                'cpf' => $this->maskCpf($cpfIndicado),
            ]);

            return $this->storeAndReturnIdempotent($idempotencyKey, $response);
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            Logger::error('API KOBE falha ao processar cadastro', [
                'error' => $e->getMessage(),
                'cpf' => $this->maskCpf($cpfIndicado),
            ]);
            throw $e;
        }
    }

    /**
     * @param array<string, mixed>|null $matchedUser
     * @return array{success: bool, message: string, status_code: int, status?: string, motivo?: string}
     */
    private function finalizeRejectedApiIndication(
        int $referrerId,
        string $codigoIndicador,
        string $displayName,
        string $cpfIndicado,
        string $emailIndicado,
        string $telefoneIndicado,
        ?array $matchedUser,
        string $tipoEvento,
        ?string $plataforma,
        ?string $idempotencyKey,
        string $motivo,
        string $message,
        string $apiStatus = self::API_STATUS_INVALIDADO
    ): array {
        $db = Database::getConnection();
        $db->beginTransaction();

        try {
            $indicacaoId = $this->completeIndicacaoRegistration(
                $referrerId,
                $codigoIndicador,
                $displayName,
                $telefoneIndicado,
                $matchedUser !== null ? (int) $matchedUser['id'] : null,
                'API',
                $cpfIndicado,
                $emailIndicado,
                $tipoEvento,
                $plataforma,
                $idempotencyKey
            );

            $validacao = $this->validacaoModel->findByIndicacao($indicacaoId);
            if ($validacao !== null) {
                $this->validacaoModel->rejectAutomatically((int) $validacao['id'], $motivo);
            }

            $db->commit();
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            Logger::warning('API KOBE falha ao persistir reprovação', [
                'motivo' => $motivo,
                'error' => $e->getMessage(),
                'cpf' => $this->maskCpf($cpfIndicado),
            ]);
        }

        $response = [
            'success' => false,
            'status' => $apiStatus,
            'motivo' => $motivo,
            'message' => $message,
            'status_code' => 400,
        ];

        Logger::info('API KOBE reprovação automática', [
            'motivo' => $motivo,
            'cpf' => $this->maskCpf($cpfIndicado),
        ]);

        return $this->storeAndReturnIdempotent($idempotencyKey, $response);
    }

    /** @param array<string, mixed>|null $matchedUser */
    private function resolveNomeIndicado(string $nomePayload, ?array $matchedUser): string
    {
        if ($nomePayload !== '') {
            return $nomePayload;
        }

        if ($matchedUser !== null) {
            $nome = trim((string) ($matchedUser['nome'] ?? ''));
            if ($nome !== '') {
                return $nome;
            }
        }

        return self::FALLBACK_NOME_API;
    }

    /** @return array<string, mixed>|null */
    private function resolveIndicadoUsuario(string $cpf, string $email, string $telefone): ?array
    {
        $byCpf = $this->usuarioModel->findByCpf($cpf);
        if ($byCpf !== null) {
            return $byCpf;
        }

        $byEmail = $this->usuarioModel->findByEmail($email);
        if ($byEmail !== null) {
            return $byEmail;
        }

        return $this->usuarioModel->findByTelefone($telefone);
    }

    /** @param array<string, mixed> $indicacao */
    private function buildReplayResponse(array $indicacao): array
    {
        $validacao = $this->validacaoModel->findByIndicacao((int) $indicacao['id']);
        $vStatus = (string) ($validacao['status'] ?? '');
        $motivo = (string) ($validacao['motivo_bloqueio'] ?? '');

        if ($vStatus === ValidacaoIndicacao::STATUS_BENEFICIO_LIBERADO
            || (string) ($indicacao['status'] ?? '') === Indicacao::STATUS_PREMIO_LIBERADO
        ) {
            return [
                'success' => true,
                'status' => self::API_STATUS_BENEFICIO_LIBERADO,
                'message' => 'Cadastro já processado anteriormente.',
                'status_code' => 200,
            ];
        }

        if ($vStatus === ValidacaoIndicacao::STATUS_APROVADO) {
            $status = $motivo === 'BENEFICIO_PENDENTE_SEM_ESTOQUE'
                ? self::API_STATUS_BENEFICIO_PENDENTE
                : self::API_STATUS_BENEFICIO_LIBERADO;

            return [
                'success' => true,
                'status' => $status,
                'message' => 'Cadastro já processado anteriormente.',
                'status_code' => 200,
            ];
        }

        if ($vStatus === ValidacaoIndicacao::STATUS_EM_ANALISE) {
            return [
                'success' => true,
                'status' => self::API_STATUS_EM_ANALISE,
                'message' => 'Cadastro já processado anteriormente.',
                'status_code' => 200,
            ];
        }

        if ($vStatus === ValidacaoIndicacao::STATUS_REPROVADO
            || (string) ($indicacao['status'] ?? '') === Indicacao::STATUS_INVALIDO
        ) {
            return [
                'success' => false,
                'status' => self::API_STATUS_INVALIDADO,
                'motivo' => $motivo !== '' ? $motivo : 'INVALIDO',
                'message' => 'Cadastro já processado anteriormente.',
                'status_code' => 400,
            ];
        }

        return [
            'success' => true,
            'status' => $vStatus !== '' ? $vStatus : self::API_STATUS_AGUARDANDO_VALIDACAO,
            'message' => 'Cadastro já processado anteriormente.',
            'status_code' => 200,
        ];
    }

    /** @return array{success: bool, message: string, status_code: int, status?: string, motivo?: string}|null */
    private function findIdempotentResponse(string $key): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                'SELECT status_code, response_json FROM api_idempotency
                 WHERE idempotency_key = :key AND endpoint = :endpoint LIMIT 1'
            );
            $stmt->execute(['key' => $key, 'endpoint' => self::IDEMPOTENCY_ENDPOINT]);
            $row = $stmt->fetch();
            if ($row === false) {
                $byIndicacao = $this->indicacaoModel->findByIdempotencyKey($key);
                if ($byIndicacao !== null) {
                    return $this->buildReplayResponse($byIndicacao);
                }
                return null;
            }

            $decoded = json_decode((string) $row['response_json'], true);
            if (!is_array($decoded)) {
                return null;
            }
            $decoded['status_code'] = (int) $row['status_code'];
            return $decoded;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param array{success: bool, message: string, status_code: int, status?: string, motivo?: string} $response
     * @return array{success: bool, message: string, status_code: int, status?: string, motivo?: string}
     */
    private function storeAndReturnIdempotent(?string $key, array $response): array
    {
        if ($key === null || $key === '') {
            return $response;
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                'INSERT INTO api_idempotency (idempotency_key, endpoint, status_code, response_json)
                 VALUES (:key, :endpoint, :status_code, :response)
                 ON DUPLICATE KEY UPDATE status_code = VALUES(status_code), response_json = VALUES(response_json)'
            );
            $toStore = $response;
            unset($toStore['status_code']);
            $stmt->execute([
                'key' => $key,
                'endpoint' => self::IDEMPOTENCY_ENDPOINT,
                'status_code' => (int) ($response['status_code'] ?? 200),
                'response' => json_encode($toStore, JSON_UNESCAPED_UNICODE),
            ]);
        } catch (Throwable $e) {
            Logger::warning('Falha ao gravar idempotency key', ['error' => $e->getMessage()]);
        }

        return $response;
    }

    private function maskCpf(string $cpf): string
    {
        $digits = Validator::onlyDigits($cpf);
        if (strlen($digits) < 5) {
            return '***';
        }

        return substr($digits, 0, 3) . '.***.***-' . substr($digits, -2);
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

        $indicacaoId = (int) $db->lastInsertId();
        $this->ensureValidacaoForIndicacao($indicacaoId, $usuarioId, null);

        return $indicacaoId;
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

        $indicacaoId = (int) $db->lastInsertId();
        $this->ensureValidacaoForIndicacao($indicacaoId, $usuarioId, null);

        return $indicacaoId;
    }

    private function completeIndicacaoRegistration(
        int $referrerUserId,
        string $codigo,
        string $nomeIndicado,
        string $telefoneIndicado,
        ?int $usuarioIndicadoId,
        string $origem,
        ?string $cpfIndicado = null,
        ?string $emailIndicado = null,
        ?string $tipoEvento = null,
        ?string $plataforma = null,
        ?string $idempotencyKey = null
    ): int {
        $existing = $this->indicacaoModel->findActiveByReferrerAndPhone($referrerUserId, $telefoneIndicado);

        if ($existing !== null) {
            $existingStatus = (string) ($existing['status'] ?? '');
            $reusable = in_array($existingStatus, [
                Indicacao::STATUS_AGUARDANDO,
                Indicacao::STATUS_LINK_ACESSADO,
                Indicacao::STATUS_CADASTRO_PENDENTE,
            ], true);
            if (!$reusable) {
                $existing = null;
            }
        }

        if ($existing !== null) {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                'UPDATE indicacoes
                 SET nome_indicado = :nome,
                     telefone_indicado = :telefone,
                     cpf_indicado = COALESCE(:cpf, cpf_indicado),
                     email_indicado = COALESCE(:email, email_indicado),
                     tipo_evento = COALESCE(:tipo_evento, tipo_evento),
                     plataforma = COALESCE(:plataforma, plataforma),
                     idempotency_key = COALESCE(:idempotency_key, idempotency_key),
                     status = :status,
                     updated_at = NOW()
                 WHERE id = :id'
            );
            $stmt->execute([
                'nome' => $nomeIndicado,
                'telefone' => $telefoneIndicado,
                'cpf' => $cpfIndicado,
                'email' => $emailIndicado,
                'tipo_evento' => $tipoEvento,
                'plataforma' => $plataforma,
                'idempotency_key' => $idempotencyKey,
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
            (usuario_id, codigo_indicador, codigo_referencia, nome_indicado, telefone_indicado,
             cpf_indicado, email_indicado, status, origem, tipo_evento, plataforma, idempotency_key)
            VALUES (:usuario_id, :codigo, :codigo_ref, :nome, :telefone,
                    :cpf, :email, :status, :origem, :tipo_evento, :plataforma, :idempotency_key)'
        );
        $stmt->execute([
            'usuario_id' => $referrerUserId,
            'codigo' => $codigo,
            'codigo_ref' => $codigoReferencia,
            'nome' => $nomeIndicado,
            'telefone' => $telefoneIndicado,
            'cpf' => $cpfIndicado,
            'email' => $emailIndicado,
            'status' => Indicacao::STATUS_CADASTRO_PENDENTE,
            'origem' => $origem,
            'tipo_evento' => $tipoEvento,
            'plataforma' => $plataforma,
            'idempotency_key' => $idempotencyKey,
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
