<?php

declare(strict_types=1);

class Cupom extends Model
{
    public const STATUS_DISPONIVEL = 'DISPONIVEL';
    public const STATUS_RESERVADO = 'RESERVADO';
    public const STATUS_UTILIZADO = 'UTILIZADO';
    public const STATUS_EXPIRADO = 'EXPIRADO';
    public const STATUS_CANCELADO = 'CANCELADO';

    public const TIPO_PERCENTUAL = 'PERCENTUAL';
    public const TIPO_VALOR_FIXO = 'VALOR_FIXO';

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO cupons (codigo, usuario_id, indicacao_id, campanha_id, tipo, valor, status, origem, validade)
             VALUES (:codigo, :usuario_id, :indicacao_id, :campanha_id, :tipo, :valor, :status, :origem, :validade)'
        );
        $stmt->execute([
            'codigo' => $data['codigo'],
            'usuario_id' => $data['usuario_id'],
            'indicacao_id' => $data['indicacao_id'],
            'campanha_id' => $data['campanha_id'],
            'tipo' => $data['tipo'],
            'valor' => $data['valor'],
            'status' => $data['status'] ?? self::STATUS_DISPONIVEL,
            'origem' => $data['origem'] ?? null,
            'validade' => $data['validade'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM cupons WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByCodigo(string $codigo): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM cupons WHERE codigo = :codigo LIMIT 1');
        $stmt->execute(['codigo' => $codigo]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByUsuario(int $usuarioId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM cupons WHERE usuario_id = :usuario_id ORDER BY created_at DESC'
        );
        $stmt->execute(['usuario_id' => $usuarioId]);
        return $stmt->fetchAll();
    }

    public function findByIndicacao(int $indicacaoId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM cupons WHERE indicacao_id = :indicacao_id LIMIT 1');
        $stmt->execute(['indicacao_id' => $indicacaoId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findAll(array $filters = []): array
    {
        $sql = 'SELECT c.*, u.nome as usuario_nome, i.nome_indicado, cam.nome as campanha_nome
                FROM cupons c
                LEFT JOIN usuarios u ON c.usuario_id = u.id
                LEFT JOIN indicacoes i ON c.indicacao_id = i.id
                LEFT JOIN campanhas cam ON c.campanha_id = cam.id
                WHERE 1=1';
        $params = [];

        if (!empty($filters['codigo'])) {
            $sql .= ' AND c.codigo LIKE :codigo';
            $params['codigo'] = '%' . $filters['codigo'] . '%';
        }

        if (!empty($filters['usuario_id'])) {
            $sql .= ' AND c.usuario_id = :usuario_id';
            $params['usuario_id'] = $filters['usuario_id'];
        }

        if (!empty($filters['campanha_id'])) {
            $sql .= ' AND c.campanha_id = :campanha_id';
            $params['campanha_id'] = $filters['campanha_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= ' AND c.status = :status';
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['data_inicio'])) {
            $sql .= ' AND c.created_at >= :data_inicio';
            $params['data_inicio'] = $filters['data_inicio'];
        }

        if (!empty($filters['data_fim'])) {
            $sql .= ' AND c.created_at <= :data_fim';
            $params['data_fim'] = $filters['data_fim'];
        }

        $sql .= ' ORDER BY c.created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $status, ?string $motivo = null): void
    {
        $sql = 'UPDATE cupons SET status = :status, updated_at = NOW()';
        $params = ['id' => $id, 'status' => $status];

        if ($status === self::STATUS_UTILIZADO) {
            $sql .= ', utilizado_em = NOW()';
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
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as disponiveis,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as reservados,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as utilizados,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as expirados,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelados
            FROM cupons'
        );
        $stmt->execute([
            self::STATUS_DISPONIVEL,
            self::STATUS_RESERVADO,
            self::STATUS_UTILIZADO,
            self::STATUS_EXPIRADO,
            self::STATUS_CANCELADO,
        ]);
        return $stmt->fetch();
    }

    public function countByStatus(string $status): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) as total FROM cupons WHERE status = :status'
        );
        $stmt->execute(['status' => $status]);
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function checkExpirados(): int
    {
        $stmt = $this->db->prepare(
            'UPDATE cupons 
             SET status = :status_expirado, updated_at = NOW() 
             WHERE status = :status_disponivel AND validade < CURDATE()'
        );
        $stmt->execute([
            'status_expirado' => self::STATUS_EXPIRADO,
            'status_disponivel' => self::STATUS_DISPONIVEL,
        ]);
        return $stmt->rowCount();
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_DISPONIVEL => 'Disponível',
            self::STATUS_RESERVADO => 'Reservado',
            self::STATUS_UTILIZADO => 'Utilizado',
            self::STATUS_EXPIRADO => 'Expirado',
            self::STATUS_CANCELADO => 'Cancelado',
            default => $status,
        };
    }

    public static function statusIcon(string $status): string
    {
        return match ($status) {
            self::STATUS_DISPONIVEL => '🟢',
            self::STATUS_RESERVADO => '🟡',
            self::STATUS_UTILIZADO => '✅',
            self::STATUS_EXPIRADO => '⚫',
            self::STATUS_CANCELADO => '🔴',
            default => '❓',
        };
    }

    public static function tipoLabel(string $tipo): string
    {
        return match ($tipo) {
            self::TIPO_PERCENTUAL => 'Percentual',
            self::TIPO_VALOR_FIXO => 'Valor Fixo',
            default => $tipo,
        };
    }
}
