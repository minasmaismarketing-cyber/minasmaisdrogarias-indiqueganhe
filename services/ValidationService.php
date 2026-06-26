<?php

declare(strict_types=1);

class ValidationService
{
    private Validacao $validacaoModel;
    private HistoricoValidacao $historicoModel;
    private Usuario $usuarioModel;
    private Indicado $indicadoModel;
    private Campanha $campanhaModel;
    private EventLogger $eventLogger;
    private ?ValidationProviderInterface $validationProvider = null;
    private ?CupomService $cupomService = null;

    public function __construct()
    {
        $this->validacaoModel = new Validacao();
        $this->historicoModel = new HistoricoValidacao();
        $this->usuarioModel = new Usuario();
        $this->indicadoModel = new Indicado();
        $this->campanhaModel = new Campanha();
        $this->eventLogger = new EventLogger();
        $this->cupomService = new CupomService();
    }

    /**
     * Set the validation provider (for future integrations)
     */
    public function setValidationProvider(ValidationProviderInterface $provider): void
    {
        $this->validationProvider = $provider;
    }

    /**
     * Create a new validation for an indication
     */
    public function createValidation(int $indicacaoId, int $usuarioId): int
    {
        // Check if validation already exists
        $existing = $this->validacaoModel->findByIndicacao($indicacaoId);
        if ($existing !== null) {
            return $existing['id'];
        }

        $validacaoId = $this->validacaoModel->create([
            'indicacao_id' => $indicacaoId,
            'usuario_id' => $usuarioId,
            'status' => Validacao::STATUS_PENDENTE,
        ]);

        $this->eventLogger->logValidacaoCriada($usuarioId, $validacaoId);

        return $validacaoId;
    }

    /**
     * Validate an indication with all rules
     */
    public function validateIndication(int $indicacaoId, int $usuarioId): array
    {
        $result = [
            'valid' => true,
            'reason' => null,
            'errors' => [],
        ];

        // Get indication data
        $indicado = $this->indicadoModel->findById($indicacaoId);
        if ($indicado === null) {
            $result['valid'] = false;
            $result['errors'][] = 'Indicação não encontrada';
            return $result;
        }

        // Get user data
        $usuario = $this->usuarioModel->findById($usuarioId);
        if ($usuario === null) {
            $result['valid'] = false;
            $result['errors'][] = 'Usuário não encontrado';
            return $result;
        }

        // Get referrer data
        $indicador = $this->usuarioModel->findById($indicado['usuario_indicador_id'] ?? 0);
        if ($indicador === null) {
            $result['valid'] = false;
            $result['errors'][] = 'Indicador não encontrado';
            return $result;
        }

        // Rule: Indicador diferente do indicado
        if ($usuario['id'] === $indicador['id']) {
            $result['valid'] = false;
            $result['errors'][] = 'Autoindicação não permitida';
        }

        // Rule: CPF diferente
        if ($usuario['cpf'] === $indicador['cpf']) {
            $result['valid'] = false;
            $result['errors'][] = 'CPF igual ao do indicador';
        }

        // Rule: Telefone diferente
        if ($usuario['telefone'] === $indicador['telefone']) {
            $result['valid'] = false;
            $result['errors'][] = 'Telefone igual ao do indicador';
        }

        // Rule: Email diferente
        if ($usuario['email'] === $indicador['email']) {
            $result['valid'] = false;
            $result['errors'][] = 'Email igual ao do indicador';
        }

        // Rule: Campanha ativa
        $campanhaAtiva = $this->campanhaModel->findActive();
        if ($campanhaAtiva === null) {
            $result['valid'] = false;
            $result['errors'][] = 'Nenhuma campanha ativa';
        }

        // Rule: Indicação ainda não validada
        $validacaoExistente = $this->validacaoModel->findByIndicacao($indicacaoId);
        if ($validacaoExistente !== null && $validacaoExistente['status'] === Validacao::STATUS_VALIDADO) {
            $result['valid'] = false;
            $result['errors'][] = 'Indicação já validada';
        }

        // Use external provider if available
        if ($this->validationProvider !== null && $this->validationProvider->isAvailable()) {
            $externalResult = $this->validationProvider->validate([
                'cpf' => $usuario['cpf'],
                'email' => $usuario['email'],
                'telefone' => $usuario['telefone'],
            ]);

            if (!$externalResult['valid']) {
                $result['valid'] = false;
                $result['errors'][] = $externalResult['reason'] ?? 'Falha na validação externa';
            }
        }

        if (!$result['valid']) {
            $result['reason'] = implode('; ', $result['errors']);
        }

        return $result;
    }

    /**
     * Start validation process
     */
    public function startValidation(int $validacaoId, ?string $adminEmail = null): bool
    {
        $validacao = $this->validacaoModel->findById($validacaoId);
        if ($validacao === null) {
            return false;
        }

        $statusAnterior = $validacao['status'];
        $this->validacaoModel->updateStatus($validacaoId, Validacao::STATUS_EM_ANALISE);

        // Record history
        $this->historicoModel->create([
            'validacao_id' => $validacaoId,
            'status_anterior' => $statusAnterior,
            'status_novo' => Validacao::STATUS_EM_ANALISE,
            'descricao' => 'Início da análise',
            'usuario_admin' => $adminEmail,
        ]);

        $this->eventLogger->logValidacaoIniciada($validacao['usuario_id'], $validacaoId);

        return true;
    }

    /**
     * Approve validation
     */
    public function approveValidation(int $validacaoId, ?string $observacao = null, ?string $adminEmail = null): bool
    {
        $validacao = $this->validacaoModel->findById($validacaoId);
        if ($validacao === null) {
            return false;
        }

        $statusAnterior = $validacao['status'];
        $this->validacaoModel->updateStatus($validacaoId, Validacao::STATUS_VALIDADO);

        // Update indicado status
        $indicado = $this->indicadoModel->findById($validacao['indicacao_id']);
        if ($indicado !== null) {
            $this->indicadoModel->updateStatus($indicado['id'], Indicado::STATUS_VALIDADO);
        }

        // Record history
        $this->historicoModel->create([
            'validacao_id' => $validacaoId,
            'status_anterior' => $statusAnterior,
            'status_novo' => Validacao::STATUS_VALIDADO,
            'descricao' => $observacao ?? 'Validação aprovada',
            'usuario_admin' => $adminEmail,
        ]);

        $this->eventLogger->logValidacaoAprovada($validacao['usuario_id'], $validacaoId);

        // Generate coupon automatically
        try {
            $this->cupomService->generateForIndicacao($validacao['indicacao_id'], $validacao['usuario_id']);
        } catch (Exception $e) {
            Logger::error('Failed to generate coupon', [
                'validacao_id' => $validacaoId,
                'error' => $e->getMessage(),
            ]);
        }

        return true;
    }

    /**
     * Reject validation
     */
    public function rejectValidation(int $validacaoId, string $motivo, ?string $adminEmail = null): bool
    {
        $validacao = $this->validacaoModel->findById($validacaoId);
        if ($validacao === null) {
            return false;
        }

        $statusAnterior = $validacao['status'];
        $this->validacaoModel->updateStatus($validacaoId, Validacao::STATUS_INVALIDADO, $motivo);

        // Update indicado status
        $indicado = $this->indicadoModel->findById($validacao['indicacao_id']);
        if ($indicado !== null) {
            $this->indicadoModel->updateStatus($indicado['id'], Indicado::STATUS_INVALIDADO);
        }

        // Record history
        $this->historicoModel->create([
            'validacao_id' => $validacaoId,
            'status_anterior' => $statusAnterior,
            'status_novo' => Validacao::STATUS_INVALIDADO,
            'descricao' => $motivo,
            'usuario_admin' => $adminEmail,
        ]);

        $this->eventLogger->logValidacaoInvalidada($validacao['usuario_id'], $validacaoId, $motivo);

        return true;
    }

    /**
     * Cancel validation
     */
    public function cancelValidation(int $validacaoId, ?string $motivo = null, ?string $adminEmail = null): bool
    {
        $validacao = $this->validacaoModel->findById($validacaoId);
        if ($validacao === null) {
            return false;
        }

        $statusAnterior = $validacao['status'];
        $this->validacaoModel->updateStatus($validacaoId, Validacao::STATUS_CANCELADO, $motivo);

        // Record history
        $this->historicoModel->create([
            'validacao_id' => $validacaoId,
            'status_anterior' => $statusAnterior,
            'status_novo' => Validacao::STATUS_CANCELADO,
            'descricao' => $motivo ?? 'Validação cancelada',
            'usuario_admin' => $adminEmail,
        ]);

        $this->eventLogger->logValidacaoCancelada($validacao['usuario_id'], $validacaoId);

        return true;
    }

    /**
     * Get validation history
     */
    public function getHistory(int $validacaoId): array
    {
        return $this->historicoModel->findByValidacao($validacaoId);
    }
}
