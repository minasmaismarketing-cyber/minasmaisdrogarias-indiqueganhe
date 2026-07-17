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
            'INSERT INTO cupons (codigo, usuario_id, indicacao_id, campanha_id, tipo, valor, status, origem, validade, atribuido_em)
             VALUES (:codigo, :usuario_id, :indicacao_id, :campanha_id, :tipo, :valor, :status, :origem, :validade, :atribuido_em)'
        );
        $stmt->execute([
            'codigo' => $data['codigo'],
            'usuario_id' => $data['usuario_id'] ?? null,
            'indicacao_id' => $data['indicacao_id'] ?? null,
            'campanha_id' => $data['campanha_id'],
            'tipo' => $data['tipo'],
            'valor' => $data['valor'],
            'status' => $data['status'] ?? self::STATUS_DISPONIVEL,
            'origem' => $data['origem'] ?? null,
            'validade' => $data['validade'] ?? null,
            'atribuido_em' => $data['atribuido_em'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Importação em lote para estoque da campanha (usuario/indicação nulos).
     *
     * @param list<string> $codigos
     */
    public function bulkInsertEstoque(
        int $campanhaId,
        array $codigos,
        string $tipo,
        float $valor,
        string $origem = 'IMPORT_CSV'
    ): int {
        if ($codigos === []) {
            return 0;
        }

        $placeholders = [];
        $params = [];
        foreach ($codigos as $i => $codigo) {
            $placeholders[] = "(:codigo_{$i}, NULL, NULL, :campanha_id_{$i}, :tipo_{$i}, :valor_{$i}, :status_{$i}, :origem_{$i}, NULL, NULL)";
            $params["codigo_{$i}"] = $codigo;
            $params["campanha_id_{$i}"] = $campanhaId;
            $params["tipo_{$i}"] = $tipo;
            $params["valor_{$i}"] = $valor;
            $params["status_{$i}"] = self::STATUS_DISPONIVEL;
            $params["origem_{$i}"] = $origem;
        }

        $sql = 'INSERT INTO cupons
                (codigo, usuario_id, indicacao_id, campanha_id, tipo, valor, status, origem, validade, atribuido_em)
                VALUES ' . implode(', ', $placeholders);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    public function findByUsuario(int $usuarioId): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, cam.nome AS campanha_nome
             FROM cupons c
             LEFT JOIN campanhas cam ON c.campanha_id = cam.id
             WHERE c.usuario_id = :usuario_id
             ORDER BY c.created_at DESC'
        );
        $stmt->execute(['usuario_id' => $usuarioId]);
        return $stmt->fetchAll();
    }

    /** Cupom de estoque (DISPONÍVEL e sem dono). Requer transação aberta (FOR UPDATE). */
    public function findDisponivelForUpdate(int $campanhaId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, codigo, status, campanha_id
             FROM cupons
             WHERE campanha_id = :campanha_id
               AND status = :status
               AND usuario_id IS NULL
               AND indicacao_id IS NULL
             ORDER BY id ASC
             LIMIT 1
             FOR UPDATE'
        );
        $stmt->execute([
            'campanha_id' => $campanhaId,
            'status' => self::STATUS_DISPONIVEL,
        ]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function assignToIndicacao(int $cupomId, int $usuarioId, int $indicacaoId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE cupons
             SET usuario_id = :usuario_id,
                 indicacao_id = :indicacao_id,
                 status = :status,
                 atribuido_em = NOW(),
                 updated_at = NOW()
             WHERE id = :id
               AND usuario_id IS NULL
               AND indicacao_id IS NULL
               AND status = :status_disp'
        );
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'indicacao_id' => $indicacaoId,
            'status' => self::STATUS_DISPONIVEL,
            'id' => $cupomId,
            'status_disp' => self::STATUS_DISPONIVEL,
        ]);

        if ($stmt->rowCount() !== 1) {
            throw new RuntimeException('Falha ao atribuir cupom (concorrência ou estado inválido).');
        }
    }

    public function countEstoqueDisponivel(int $campanhaId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) AS total
             FROM cupons
             WHERE campanha_id = :campanha_id
               AND status = :status
               AND usuario_id IS NULL
               AND indicacao_id IS NULL'
        );
        $stmt->execute([
            'campanha_id' => $campanhaId,
            'status' => self::STATUS_DISPONIVEL,
        ]);
        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
    }

    /** @return array<string, int> */
    public function getEstoqueStats(?int $campanhaId = null): array
    {
        $sql = 'SELECT
                    COUNT(*) AS total_importado,
                    SUM(CASE WHEN status = :disp AND usuario_id IS NULL THEN 1 ELSE 0 END) AS disponiveis,
                    SUM(CASE WHEN usuario_id IS NOT NULL AND status IN (:disp2, :res) THEN 1 ELSE 0 END) AS atribuidos,
                    SUM(CASE WHEN status = :util THEN 1 ELSE 0 END) AS utilizados,
                    SUM(CASE WHEN status = :canc THEN 1 ELSE 0 END) AS cancelados,
                    SUM(CASE WHEN status = :exp THEN 1 ELSE 0 END) AS expirados
                FROM cupons
                WHERE origem = :origem';
        $params = [
            'disp' => self::STATUS_DISPONIVEL,
            'disp2' => self::STATUS_DISPONIVEL,
            'res' => self::STATUS_RESERVADO,
            'util' => self::STATUS_UTILIZADO,
            'canc' => self::STATUS_CANCELADO,
            'exp' => self::STATUS_EXPIRADO,
            'origem' => 'IMPORT_CSV',
        ];

        if ($campanhaId !== null) {
            $sql .= ' AND campanha_id = :campanha_id';
            $params['campanha_id'] = $campanhaId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch() ?: [];

        return [
            'total_importado' => (int) ($row['total_importado'] ?? 0),
            'disponiveis' => (int) ($row['disponiveis'] ?? 0),
            'atribuidos' => (int) ($row['atribuidos'] ?? 0),
            'utilizados' => (int) ($row['utilizados'] ?? 0),
            'cancelados' => (int) ($row['cancelados'] ?? 0),
            'expirados' => (int) ($row['expirados'] ?? 0),
        ];
    }

    public function deleteDisponivel(int $id): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM cupons
             WHERE id = :id
               AND status = :status
               AND usuario_id IS NULL
               AND indicacao_id IS NULL'
        );
        $stmt->execute([
            'id' => $id,
            'status' => self::STATUS_DISPONIVEL,
        ]);

        return $stmt->rowCount() === 1;
    }

    /** @param list<int> $ids */
    public function deleteDisponiveisBatch(array $ids): int
    {
        $ids = array_values(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0));
        if ($ids === []) {
            return 0;
        }

        $placeholders = [];
        $params = ['status' => self::STATUS_DISPONIVEL];
        foreach ($ids as $i => $id) {
            $key = 'id_' . $i;
            $placeholders[] = ':' . $key;
            $params[$key] = $id;
        }

        $sql = 'DELETE FROM cupons
                WHERE status = :status
                  AND usuario_id IS NULL
                  AND indicacao_id IS NULL
                  AND id IN (' . implode(', ', $placeholders) . ')';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    /** @param list<string> $codigos
     *  @return list<string>
     */
    public function filterExistingCodigos(array $codigos): array
    {
        if ($codigos === []) {
            return [];
        }

        $found = [];
        foreach (array_chunk(array_values($codigos), 400) as $chunk) {
            $placeholders = [];
            $params = [];
            foreach ($chunk as $i => $codigo) {
                $key = 'c_' . $i;
                $placeholders[] = ':' . $key;
                $params[$key] = $codigo;
            }

            $stmt = $this->db->prepare(
                'SELECT codigo FROM cupons WHERE codigo IN (' . implode(', ', $placeholders) . ')'
            );
            $stmt->execute($params);
            foreach ($stmt->fetchAll() as $row) {
                $found[] = (string) $row['codigo'];
            }
        }

        return $found;
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
            'IMPORT_CSV' => 'Importação CSV',
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
        return (new Indicacao())->findCampanhaNomesByIndicacaoIds($indicacaoIds);
    }
}
