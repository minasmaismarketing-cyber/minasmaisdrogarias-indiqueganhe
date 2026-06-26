<?php

declare(strict_types=1);

class CupomRepository
{
    private Cupom $cupomModel;
    private HistoricoCupom $historicoModel;

    public function __construct()
    {
        $this->cupomModel = new Cupom();
        $this->historicoModel = new HistoricoCupom();
    }

    public function findById(int $id): ?array
    {
        return $this->cupomModel->findById($id);
    }

    public function findByCodigo(string $codigo): ?array
    {
        return $this->cupomModel->findByCodigo($codigo);
    }

    public function findByUsuario(int $usuarioId): array
    {
        return $this->cupomModel->findByUsuario($usuarioId);
    }

    public function findByIndicacao(int $indicacaoId): ?array
    {
        return $this->cupomModel->findByIndicacao($indicacaoId);
    }

    public function findAll(array $filters = []): array
    {
        return $this->cupomModel->findAll($filters);
    }

    public function create(array $data): int
    {
        return $this->cupomModel->create($data);
    }

    public function updateStatus(int $id, string $status): void
    {
        $this->cupomModel->updateStatus($id, $status);
    }

    public function getStats(): array
    {
        return $this->cupomModel->getStats();
    }

    public function checkExpirados(): int
    {
        return $this->cupomModel->checkExpirados();
    }

    public function createHistorico(array $data): int
    {
        return $this->historicoModel->create($data);
    }

    public function getHistorico(int $cupomId): array
    {
        return $this->historicoModel->findByCupom($cupomId);
    }

    public function codigoExiste(string $codigo): bool
    {
        return $this->cupomModel->findByCodigo($codigo) !== null;
    }

    public function cupomExisteParaIndicacao(int $indicacaoId): bool
    {
        return $this->cupomModel->findByIndicacao($indicacaoId) !== null;
    }
}
