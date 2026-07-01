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

    public function findAll(array $filters = [], int $limit = 0, int $offset = 0): array
    {
        $sql = 'SELECT c.*, u.nome as usuario_nome, i.nome_indicado, cam.nome as campanha_nome
                FROM cupons c
                LEFT JOIN usuarios u ON c.usuario_id = u.id
                LEFT JOIN indicacoes i ON c.indicacao_id = i.id
                LEFT JOIN campanhas cam ON c.campanha_id = cam.id
                WHERE 1=1';
        $params = [];

        $this->applyListFilters($sql, $params, $filters);

        $sql .= ' ORDER BY c.created_at DESC';

        if ($limit > 0) {
            $sql .= ' LIMIT :limit OFFSET :offset';
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        if ($limit > 0) {
            $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /** @param array<string, mixed> $filters */
    public function countFiltered(array $filters = []): int
    {
        $sql = 'SELECT COUNT(*) AS total
                FROM cupons c
                LEFT JOIN usuarios u ON c.usuario_id = u.id
                LEFT JOIN indicacoes i ON c.indicacao_id = i.id
                LEFT JOIN campanhas cam ON c.campanha_id = cam.id
                WHERE 1=1';
        $params = [];

        $this->applyListFilters($sql, $params, $filters);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
    }

    /** @param array<string, mixed> $filters
     *  @return array<string, int>
     */
    public function getFilteredStats(array $filters = []): array
    {
        $sql = 'SELECT
                    COUNT(*) AS cupons_gerados,
                    SUM(CASE WHEN c.status = :disponivel THEN 1 ELSE 0 END) AS disponiveis,
                    SUM(CASE WHEN c.status = :reservado THEN 1 ELSE 0 END) AS reservados,
                    SUM(CASE WHEN c.status = :utilizado THEN 1 ELSE 0 END) AS utilizados,
                    SUM(CASE WHEN c.status = :expirado THEN 1 ELSE 0 END) AS expirados,
                    SUM(CASE WHEN c.status = :cancelado THEN 1 ELSE 0 END) AS cancelados
                FROM cupons c
                LEFT JOIN usuarios u ON c.usuario_id = u.id
                LEFT JOIN indicacoes i ON c.indicacao_id = i.id
                LEFT JOIN campanhas cam ON c.campanha_id = cam.id
                WHERE 1=1';
        $params = [
            'disponivel' => self::STATUS_DISPONIVEL,
            'reservado' => self::STATUS_RESERVADO,
            'utilizado' => self::STATUS_UTILIZADO,
            'expirado' => self::STATUS_EXPIRADO,
            'cancelado' => self::STATUS_CANCELADO,
        ];

        $this->applyListFilters($sql, $params, $filters);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch() ?: [];

        return [
            'cupons_gerados' => (int) ($row['cupons_gerados'] ?? 0),
            'disponiveis' => (int) ($row['disponiveis'] ?? 0),
            'reservados' => (int) ($row['reservados'] ?? 0),
            'utilizados' => (int) ($row['utilizados'] ?? 0),
            'expirados' => (int) ($row['expirados'] ?? 0),
            'cancelados' => (int) ($row['cancelados'] ?? 0),
        ];
    }

    /** @return array<string, mixed>|null */
    public function findByIdForAdmin(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*,
                    u.nome AS usuario_nome,
                    u.cpf AS usuario_cpf,
                    u.whatsapp AS usuario_whatsapp,
                    cam.nome AS campanha_nome,
                    i.codigo_referencia,
                    i.codigo_indicador,
                    i.status AS indicacao_status,
                    i.created_at AS indicacao_created_at
             FROM cupons c
             LEFT JOIN usuarios u ON c.usuario_id = u.id
             LEFT JOIN campanhas cam ON c.campanha_id = cam.id
             LEFT JOIN indicacoes i ON c.indicacao_id = i.id
             WHERE c.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /** @param array<string, mixed> $filters
     *  @param array<string, mixed> $params
     */
    private function applyListFilters(string &$sql, array &$params, array $filters): void
    {
        if (!empty($filters['codigo'])) {
            $sql .= ' AND c.codigo LIKE :codigo';
            $params['codigo'] = '%' . $filters['codigo'] . '%';
        }

        if (!empty($filters['indicador'])) {
            $sql .= ' AND u.nome LIKE :indicador';
            $params['indicador'] = '%' . $filters['indicador'] . '%';
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

    public function countByStatus(string $status): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) as total FROM cupons WHERE status = :status'
        );
        $stmt->execute(['status' => $status]);
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function countByUsuario(int $usuarioId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) as total FROM cupons WHERE usuario_id = :usuario_id'
        );
        $stmt->execute(['usuario_id' => $usuarioId]);
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

    public static function adminStatusBadgeClass(string $status): string
    {
        return 'badge--' . strtolower($status);
    }

    public static function origemLabel(?string $origem): string
    {
        return match ($origem) {
            'INDICACAO_VALIDADA' => 'Indicação validada',
            'MANUAL' => 'Manual',
            null, '' => 'Manual',
            default => $origem,
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

    /** @param list<int> $indicacaoIds
     *  @return array<int, string>
     */
    public function findCampanhaNomesByIndicacaoIds(array $indicacaoIds): array
    {
        $indicacaoIds = array_values(array_unique(array_filter(array_map('intval', $indicacaoIds))));
        if ($indicacaoIds === []) {
            return [];
        }

        $placeholders = [];
        $params = [];
        foreach ($indicacaoIds as $index => $id) {
            $key = 'id_' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = $id;
        }

        $stmt = $this->db->prepare(
            'SELECT c.indicacao_id, cam.nome AS campanha_nome
             FROM cupons c
             INNER JOIN campanhas cam ON cam.id = c.campanha_id
             WHERE c.indicacao_id IN (' . implode(', ', $placeholders) . ')'
        );
        $stmt->execute($params);

        $map = [];
        foreach ($stmt->fetchAll() as $row) {
            $map[(int) $row['indicacao_id']] = (string) $row['campanha_nome'];
        }

        return $map;
    }
}
