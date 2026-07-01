<?php

declare(strict_types=1);

class CupomService
{
    private CupomRepository $repository;
    private HistoricoCupom $historicoModel;
    private Campanha $campanhaModel;
    private EventLogger $eventLogger;
    private ?CouponProviderInterface $couponProvider = null;

    public function __construct()
    {
        $this->repository = new CupomRepository();
        $this->historicoModel = new HistoricoCupom();
        $this->campanhaModel = new Campanha();
        $this->eventLogger = new EventLogger();
    }

    /**
     * Set the coupon provider (for future integrations)
     */
    public function setCouponProvider(CouponProviderInterface $provider): void
    {
        $this->couponProvider = $provider;
    }

    /**
     * Generate a coupon for a validated indication
     */
    public function generateForIndicacao(int $indicacaoId, int $usuarioId): ?int
    {
        // Check if coupon already exists for this indication
        if ($this->repository->cupomExisteParaIndicacao($indicacaoId)) {
            return null;
        }

        // Get active campaign
        $campanhaAtiva = $this->campanhaModel->findActive();
        if ($campanhaAtiva === null) {
            throw new RuntimeException('Nenhuma campanha ativa encontrada');
        }

        // Calculate validity (30 days from now)
        $validade = date('Y-m-d', strtotime('+30 days'));

        // Generate coupon code
        $provider = $this->couponProvider ?? new InternalCouponProvider();
        $generation = $provider->generateCode([
            'usuario_id' => $usuarioId,
            'indicacao_id' => $indicacaoId,
            'campanha_id' => $campanhaAtiva['id'],
        ]);

        if (!$generation->sucesso) {
            throw new RuntimeException($generation->mensagem);
        }

        $codigo = (string) ($generation->dados['codigo'] ?? '');
        if ($codigo === '') {
            throw new RuntimeException('Provider de cupom não retornou código válido.');
        }

        // Create coupon
        $cupomId = $this->repository->create([
            'codigo' => $codigo,
            'usuario_id' => $usuarioId,
            'indicacao_id' => $indicacaoId,
            'campanha_id' => $campanhaAtiva['id'],
            'tipo' => $campanhaAtiva['tipo_desconto'],
            'valor' => $campanhaAtiva['desconto'],
            'status' => Cupom::STATUS_DISPONIVEL,
            'origem' => 'INDICACAO_VALIDADA',
            'validade' => $validade,
        ]);

        $this->eventLogger->logCupomCriado($usuarioId, $cupomId, $codigo);

        return $cupomId;
    }

    /**
     * Reserve a coupon
     */
    public function reserve(int $cupomId, ?string $adminEmail = null): bool
    {
        $cupom = $this->repository->findById($cupomId);
        if ($cupom === null) {
            return false;
        }

        if ($cupom['status'] !== Cupom::STATUS_DISPONIVEL) {
            return false;
        }

        $statusAnterior = $cupom['status'];
        $this->repository->updateStatus($cupomId, Cupom::STATUS_RESERVADO);

        // Record history
        $this->historicoModel->create([
            'cupom_id' => $cupomId,
            'status_anterior' => $statusAnterior,
            'status_novo' => Cupom::STATUS_RESERVADO,
            'descricao' => 'Cupom reservado',
            'usuario_admin' => $adminEmail,
        ]);

        $this->eventLogger->logCupomReservado($cupom['usuario_id'], $cupomId, $cupom['codigo']);

        return true;
    }

    /**
     * Mark coupon as used
     */
    public function use(int $cupomId, ?string $adminEmail = null): bool
    {
        $cupom = $this->repository->findById($cupomId);
        if ($cupom === null) {
            return false;
        }

        if (!in_array($cupom['status'], [Cupom::STATUS_DISPONIVEL, Cupom::STATUS_RESERVADO])) {
            return false;
        }

        $statusAnterior = $cupom['status'];
        $this->repository->updateStatus($cupomId, Cupom::STATUS_UTILIZADO);

        // Record history
        $this->historicoModel->create([
            'cupom_id' => $cupomId,
            'status_anterior' => $statusAnterior,
            'status_novo' => Cupom::STATUS_UTILIZADO,
            'descricao' => 'Cupom utilizado',
            'usuario_admin' => $adminEmail,
        ]);

        $this->eventLogger->logCupomUtilizado($cupom['usuario_id'], $cupomId, $cupom['codigo']);

        return true;
    }

    /**
     * Cancel a coupon
     */
    public function cancel(int $cupomId, ?string $motivo = null, ?string $adminEmail = null): bool
    {
        $cupom = $this->repository->findById($cupomId);
        if ($cupom === null) {
            return false;
        }

        if ($cupom['status'] === Cupom::STATUS_UTILIZADO) {
            return false;
        }

        $statusAnterior = $cupom['status'];
        $this->repository->updateStatus($cupomId, Cupom::STATUS_CANCELADO);

        // Record history
        $this->historicoModel->create([
            'cupom_id' => $cupomId,
            'status_anterior' => $statusAnterior,
            'status_novo' => Cupom::STATUS_CANCELADO,
            'descricao' => $motivo ?? 'Cupom cancelado',
            'usuario_admin' => $adminEmail,
        ]);

        $this->eventLogger->logCupomCancelado($cupom['usuario_id'], $cupomId, $cupom['codigo']);

        return true;
    }

    /**
     * Expire a coupon
     */
    public function expire(int $cupomId, ?string $adminEmail = null): bool
    {
        $cupom = $this->repository->findById($cupomId);
        if ($cupom === null) {
            return false;
        }

        if ($cupom['status'] !== Cupom::STATUS_DISPONIVEL) {
            return false;
        }

        $statusAnterior = $cupom['status'];
        $this->repository->updateStatus($cupomId, Cupom::STATUS_EXPIRADO);

        // Record history
        $this->historicoModel->create([
            'cupom_id' => $cupomId,
            'status_anterior' => $statusAnterior,
            'status_novo' => Cupom::STATUS_EXPIRADO,
            'descricao' => 'Cupom expirado',
            'usuario_admin' => $adminEmail,
        ]);

        $this->eventLogger->logCupomExpirado($cupom['usuario_id'], $cupomId, $cupom['codigo']);

        return true;
    }

    /**
     * Reactivate a cancelled or expired coupon
     */
    public function reactivate(int $cupomId, ?string $adminEmail = null): bool
    {
        $cupom = $this->repository->findById($cupomId);
        if ($cupom === null) {
            return false;
        }

        if (!in_array($cupom['status'], [Cupom::STATUS_CANCELADO, Cupom::STATUS_EXPIRADO])) {
            return false;
        }

        $statusAnterior = $cupom['status'];
        $this->repository->updateStatus($cupomId, Cupom::STATUS_DISPONIVEL);

        // Record history
        $this->historicoModel->create([
            'cupom_id' => $cupomId,
            'status_anterior' => $statusAnterior,
            'status_novo' => Cupom::STATUS_DISPONIVEL,
            'descricao' => 'Cupom reativado',
            'usuario_admin' => $adminEmail,
        ]);

        return true;
    }

    /**
     * Check and expire all expired coupons
     */
    public function checkExpiredCoupons(): int
    {
        return $this->repository->checkExpirados();
    }

    /**
     * Get coupon history
     */
    public function getHistory(int $cupomId): array
    {
        return $this->repository->getHistorico($cupomId);
    }
}
