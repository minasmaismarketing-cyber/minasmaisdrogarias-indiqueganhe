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

    public function findAll(array $filters = [], int $limit = 0, int $offset = 0): array
    {
        return $this->cupomModel->findAll($filters, $limit, $offset);
    }

    public function countFiltered(array $filters = []): int
    {
        return $this->cupomModel->countFiltered($filters);
    }

    public function findByIdForAdmin(int $id): ?array
    {
        return $this->cupomModel->findByIdForAdmin($id);
    }

    public function create(array $data): int
    {
        return $this->cupomModel->create($data);
    }

    /** @param list<string> $codigos */
    public function bulkInsertEstoque(
        int $campanhaId,
        array $codigos,
        string $tipo,
        float $valor,
        string $origem = 'IMPORT_CSV'
    ): int {
        return $this->cupomModel->bulkInsertEstoque($campanhaId, $codigos, $tipo, $valor, $origem);
    }

    public function findDisponivelForUpdate(int $campanhaId): ?array
    {
        return $this->cupomModel->findDisponivelForUpdate($campanhaId);
    }

    public function assignToIndicacao(int $cupomId, int $usuarioId, int $indicacaoId): void
    {
        $this->cupomModel->assignToIndicacao($cupomId, $usuarioId, $indicacaoId);
    }

    public function deleteDisponivel(int $id): bool
    {
        return $this->cupomModel->deleteDisponivel($id);
    }

    /** @param list<int> $ids */
    public function deleteDisponiveisBatch(array $ids): int
    {
        return $this->cupomModel->deleteDisponiveisBatch($ids);
    }

    /** @param list<string> $codigos
     *  @return list<string>
     */
    public function filterExistingCodigos(array $codigos): array
    {
        return $this->cupomModel->filterExistingCodigos($codigos);
    }

    public function getEstoqueStats(?int $campanhaId = null): array
    {
        return $this->cupomModel->getEstoqueStats($campanhaId);
    }

    public function updateStatus(int $id, string $status): void
    {
        $this->cupomModel->updateStatus($id, $status);
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
