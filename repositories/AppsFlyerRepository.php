<?php

declare(strict_types=1);

class AppsFlyerRepository
{
    private AppsFlyerEvent $appsFlyerEventModel;

    public function __construct()
    {
        $this->appsFlyerEventModel = new AppsFlyerEvent();
    }

    public function findById(int $id): ?array
    {
        return $this->appsFlyerEventModel->findById($id);
    }

    public function findByUsuario(int $usuarioId): array
    {
        return $this->appsFlyerEventModel->findByUsuario($usuarioId);
    }

    public function findByIndicacao(int $indicacaoId): array
    {
        return $this->appsFlyerEventModel->findByIndicacao($indicacaoId);
    }

    public function findByAppsflyerId(string $appsflyerId): ?array
    {
        return $this->appsFlyerEventModel->findByAppsflyerId($appsflyerId);
    }

    public function findAll(array $filters = []): array
    {
        return $this->appsFlyerEventModel->findAll($filters);
    }

    public function create(array $data): int
    {
        return $this->appsFlyerEventModel->create($data);
    }

    public function updateStatus(int $id, string $status): void
    {
        $this->appsFlyerEventModel->updateStatus($id, $status);
    }

    public function getStats(array $filters = []): array
    {
        return $this->appsFlyerEventModel->getStats($filters);
    }

    public function countByStatus(string $status): int
    {
        return $this->appsFlyerEventModel->countByStatus($status);
    }

    public function listRecent(int $limit = 50): array
    {
        return $this->appsFlyerEventModel->listRecent($limit);
    }

    public function existsByAppsflyerId(string $appsflyerId): bool
    {
        return $this->appsFlyerEventModel->findByAppsflyerId($appsflyerId) !== null;
    }
}
