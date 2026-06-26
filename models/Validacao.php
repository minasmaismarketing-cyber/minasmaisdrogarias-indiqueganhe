<?php

declare(strict_types=1);

class Validacao extends Model
{
    public const STATUS_PENDENTE = 'PENDENTE';
    public const STATUS_EM_ANALISE = 'EM_ANALISE';
    public const STATUS_VALIDADO = 'VALIDADO';
    public const STATUS_INVALIDADO = 'INVALIDADO';
    public const STATUS_CANCELADO = 'CANCELADO';

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO validacoes (indicacao_id, usuario_id, status, motivo)
             VALUES (:indicacao_id, :usuario_id, :status, :motivo)'
        );
        $stmt->execute([
            'indicacao_id' => $data['indicacao_id'],
            'usuario_id' => $data['usuario_id'],
            'status' => $data['status'] ?? self::STATUS_PENDENTE,
            'motivo' => $data['motivo'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM validacoes WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByIndicacao(int $indicacaoId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM validacoes WHERE indicacao_id = :indicacao_id LIMIT 1');
        $stmt->execute(['indicacao_id' => $indicacaoId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByUsuario(int $usuarioId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM validacoes WHERE usuario_id = :usuario_id ORDER BY created_at DESC'
        );
        $stmt->execute(['usuario_id' => $usuarioId]);
        return $stmt->fetchAll();
    }

    public function findAll(array $filters = []): array
    {
        $sql = 'SELECT v.*, u.nome as usuario_nome, i.nome_indicado 
                FROM validacoes v
                LEFT JOIN usuarios u ON v.usuario_id = u.id
                LEFT JOIN indicacoes i ON v.indicacao_id = i.id
                WHERE 1=1';
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= ' AND v.status = :status';
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['usuario_id'])) {
            $sql .= ' AND v.usuario_id = :usuario_id';
            $params['usuario_id'] = $filters['usuario_id'];
        }

        if (!empty($filters['cpf'])) {
            $sql .= ' AND i.cpf_indicado = :cpf';
            $params['cpf'] = $filters['cpf'];
        }

        if (!empty($filters['nome'])) {
            $sql .= ' AND (u.nome LIKE :nome OR i.nome_indicado LIKE :nome)';
            $params['nome'] = '%' . $filters['nome'] . '%';
        }

        if (!empty($filters['email'])) {
            $sql .= ' AND (u.email LIKE :email OR i.email_indicado LIKE :email)';
            $params['email'] = '%' . $filters['email'] . '%';
        }

        if (!empty($filters['telefone'])) {
            $sql .= ' AND (u.telefone LIKE :telefone OR i.telefone_indicado LIKE :telefone)';
            $params['telefone'] = '%' . $filters['telefone'] . '%';
        }

        if (!empty($filters['data_inicio'])) {
            $sql .= ' AND v.created_at >= :data_inicio';
            $params['data_inicio'] = $filters['data_inicio'];
        }

        if (!empty($filters['data_fim'])) {
            $sql .= ' AND v.created_at <= :data_fim';
            $params['data_fim'] = $filters['data_fim'];
        }

        $sql .= ' ORDER BY v.created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $status, ?string $motivo = null): void
    {
        $sql = 'UPDATE validacoes SET status = :status, updated_at = NOW()';
        $params = ['id' => $id, 'status' => $status];

        if ($status === self::STATUS_VALIDADO) {
            $sql .= ', validado_em = NOW()';
        }

        if ($motivo !== null) {
            $sql .= ', motivo = :motivo';
            $params['motivo'] = $motivo;
        }

        $sql .= ' WHERE id = :id';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    public function getStats(): array
    {
        $stmt = $this->db->prepare(
            'SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pendentes,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as em_analise,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as validadas,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as invalidadas,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as canceladas
            FROM validacoes'
        );
        $stmt->execute([
            self::STATUS_PENDENTE,
            self::STATUS_EM_ANALISE,
            self::STATUS_VALIDADO,
            self::STATUS_INVALIDADO,
            self::STATUS_CANCELADO,
        ]);
        return $stmt->fetch();
    }

    public function countByStatus(string $status): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) as total FROM validacoes WHERE status = :status'
        );
        $stmt->execute(['status' => $status]);
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_PENDENTE => 'Pendente',
            self::STATUS_EM_ANALISE => 'Em análise',
            self::STATUS_VALIDADO => 'Validado',
            self::STATUS_INVALIDADO => 'Invalidado',
            self::STATUS_CANCELADO => 'Cancelado',
            default => $status,
        };
    }

    public static function statusIcon(string $status): string
    {
        return match ($status) {
            self::STATUS_PENDENTE => '🟡',
            self::STATUS_EM_ANALISE => '�',
            self::STATUS_VALIDADO => '🟢',
            self::STATUS_INVALIDADO => '🔴',
            self::STATUS_CANCELADO => '⚫',
            default => '❓',
        };
    }
}
