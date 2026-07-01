<?php

declare(strict_types=1);

class Campanha extends Model
{
    public const STATUS_ATIVA = 'ATIVA';
    public const STATUS_INATIVA = 'INATIVA';
    public const STATUS_AGENDADA = 'AGENDADA';
    public const STATUS_FINALIZADA = 'FINALIZADA';

    public const TIPO_DESCONTO_PERCENTUAL = 'PERCENTUAL';
    public const TIPO_DESCONTO_VALOR_FIXO = 'VALOR_FIXO';

    public const FILTER_TODOS = 'todos';
    public const FILTER_VIGENCIA_VIGENTE = 'vigente';
    public const FILTER_VIGENCIA_FUTURA = 'futura';
    public const FILTER_VIGENCIA_ENCERRADA = 'encerrada';

    /** @var list<string> */
    public const ADMIN_STATUS_FILTERS = [
        self::FILTER_TODOS,
        self::STATUS_ATIVA,
        self::STATUS_AGENDADA,
        self::STATUS_FINALIZADA,
        self::STATUS_INATIVA,
    ];

    /** @var list<string> */
    public const ADMIN_VIGENCIA_FILTERS = [
        self::FILTER_TODOS,
        self::FILTER_VIGENCIA_VIGENTE,
        self::FILTER_VIGENCIA_FUTURA,
        self::FILTER_VIGENCIA_ENCERRADA,
    ];

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO campanhas
            (nome, slug, descricao, status, desconto, tipo_desconto, valor_minimo_compra, limite_indicacoes_usuario, inicio, fim, banner, cor_primaria, cor_secundaria, texto_botao, texto_landing)
            VALUES
            (:nome, :slug, :descricao, :status, :desconto, :tipo_desconto, :valor_minimo_compra, :limite_indicacoes_usuario, :inicio, :fim, :banner, :cor_primaria, :cor_secundaria, :texto_botao, :texto_landing)'
        );

        $stmt->execute([
            'nome' => $data['nome'],
            'slug' => $this->generateSlug($data['nome']),
            'descricao' => $data['descricao'] ?? null,
            'status' => $data['status'] ?? self::STATUS_INATIVA,
            'desconto' => $data['desconto'] ?? 0.00,
            'tipo_desconto' => $data['tipo_desconto'] ?? self::TIPO_DESCONTO_PERCENTUAL,
            'valor_minimo_compra' => $data['valor_minimo_compra'] ?? 0.00,
            'limite_indicacoes_usuario' => $data['limite_indicacoes_usuario'] ?? 0,
            'inicio' => $data['inicio'],
            'fim' => $data['fim'],
            'banner' => $data['banner'] ?? null,
            'cor_primaria' => $data['cor_primaria'] ?? '#D71920',
            'cor_secundaria' => $data['cor_secundaria'] ?? '#7A7A7A',
            'texto_botao' => $data['texto_botao'] ?? 'Quero participar',
            'texto_landing' => $data['texto_landing'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findAll(): array
    {
        $stmt = $this->db->prepare('SELECT * FROM campanhas ORDER BY created_at DESC');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findActive(): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM campanhas WHERE status = :status AND CURDATE() BETWEEN inicio AND fim LIMIT 1'
        );
        $stmt->execute(['status' => self::STATUS_ATIVA]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM campanhas WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM campanhas WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function update(int $id, array $data): void
    {
        $fields = [];
        $params = ['id' => $id];

        if (isset($data['nome'])) {
            $fields[] = 'nome = :nome';
            $params['nome'] = $data['nome'];
            if (!isset($data['slug'])) {
                $fields[] = 'slug = :slug';
                $params['slug'] = $this->generateSlug($data['nome']);
            }
        }
        if (isset($data['slug'])) {
            $fields[] = 'slug = :slug';
            $params['slug'] = $data['slug'];
        }
        if (isset($data['descricao'])) {
            $fields[] = 'descricao = :descricao';
            $params['descricao'] = $data['descricao'];
        }
        if (isset($data['status'])) {
            $fields[] = 'status = :status';
            $params['status'] = $data['status'];
        }
        if (isset($data['desconto'])) {
            $fields[] = 'desconto = :desconto';
            $params['desconto'] = $data['desconto'];
        }
        if (isset($data['tipo_desconto'])) {
            $fields[] = 'tipo_desconto = :tipo_desconto';
            $params['tipo_desconto'] = $data['tipo_desconto'];
        }
        if (isset($data['valor_minimo_compra'])) {
            $fields[] = 'valor_minimo_compra = :valor_minimo_compra';
            $params['valor_minimo_compra'] = $data['valor_minimo_compra'];
        }
        if (isset($data['limite_indicacoes_usuario'])) {
            $fields[] = 'limite_indicacoes_usuario = :limite_indicacoes_usuario';
            $params['limite_indicacoes_usuario'] = $data['limite_indicacoes_usuario'];
        }
        if (isset($data['inicio'])) {
            $fields[] = 'inicio = :inicio';
            $params['inicio'] = $data['inicio'];
        }
        if (isset($data['fim'])) {
            $fields[] = 'fim = :fim';
            $params['fim'] = $data['fim'];
        }
        if (isset($data['banner'])) {
            $fields[] = 'banner = :banner';
            $params['banner'] = $data['banner'];
        }
        if (isset($data['cor_primaria'])) {
            $fields[] = 'cor_primaria = :cor_primaria';
            $params['cor_primaria'] = $data['cor_primaria'];
        }
        if (isset($data['cor_secundaria'])) {
            $fields[] = 'cor_secundaria = :cor_secundaria';
            $params['cor_secundaria'] = $data['cor_secundaria'];
        }
        if (isset($data['texto_botao'])) {
            $fields[] = 'texto_botao = :texto_botao';
            $params['texto_botao'] = $data['texto_botao'];
        }
        if (isset($data['texto_landing'])) {
            $fields[] = 'texto_landing = :texto_landing';
            $params['texto_landing'] = $data['texto_landing'];
        }

        if ($fields === []) {
            return;
        }

        $sql = 'UPDATE campanhas SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    public function activate(int $id): void
    {
        // Deactivate all campaigns first
        $this->db->exec("UPDATE campanhas SET status = '" . self::STATUS_INATIVA . "'");
        
        // Activate the specified campaign
        $stmt = $this->db->prepare(
            'UPDATE campanhas SET status = :status, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'status' => self::STATUS_ATIVA,
        ]);
    }

    public function deactivate(int $id): void
    {
        $stmt = $this->db->prepare(
            'UPDATE campanhas SET status = :status, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'status' => self::STATUS_INATIVA,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM campanhas WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function duplicate(int $id): int
    {
        $original = $this->findById($id);
        if ($original === null) {
            return 0;
        }

        $newData = [
            'nome' => $original['nome'] . ' (Cópia)',
            'descricao' => $original['descricao'],
            'status' => self::STATUS_INATIVA,
            'desconto' => $original['desconto'],
            'tipo_desconto' => $original['tipo_desconto'],
            'valor_minimo_compra' => $original['valor_minimo_compra'],
            'limite_indicacoes_usuario' => $original['limite_indicacoes_usuario'],
            'inicio' => $original['inicio'],
            'fim' => $original['fim'],
            'banner' => $original['banner'],
            'cor_primaria' => $original['cor_primaria'],
            'cor_secundaria' => $original['cor_secundaria'],
            'texto_botao' => $original['texto_botao'],
            'texto_landing' => $original['texto_landing'],
        ];

        return $this->create($newData);
    }

    public function getStats(): array
    {
        $stmt = $this->db->prepare(
            'SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as ativas,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as inativas,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as agendadas,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as finalizadas
            FROM campanhas'
        );
        $stmt->execute([
            self::STATUS_ATIVA,
            self::STATUS_INATIVA,
            self::STATUS_AGENDADA,
            self::STATUS_FINALIZADA,
        ]);
        return $stmt->fetch();
    }

    private function generateSlug(string $nome): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nome)));
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_ATIVA => 'Ativa',
            self::STATUS_INATIVA => 'Inativa',
            self::STATUS_AGENDADA => 'Agendada',
            self::STATUS_FINALIZADA => 'Finalizada',
            default => $status,
        };
    }

    public static function adminStatusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_FINALIZADA => 'Encerrada',
            default => self::statusLabel($status),
        };
    }

    public static function adminStatusIcon(string $status): string
    {
        return match ($status) {
            self::STATUS_ATIVA => '🟢',
            self::STATUS_AGENDADA => '🟡',
            self::STATUS_FINALIZADA => '⚫',
            self::STATUS_INATIVA => '⚪',
            default => '❓',
        };
    }

    public static function adminStatusBadgeClass(string $status): string
    {
        return 'badge--' . strtolower($status);
    }

    public static function formatVigencia(array $campanha): string
    {
        return date('d/m/Y', strtotime((string) $campanha['inicio']))
            . ' — '
            . date('d/m/Y', strtotime((string) $campanha['fim']));
    }

    /** @param array<string, mixed> $filters
     *  @return array<int, array<string, mixed>>
     */
    public function findAllAdmin(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $sql = 'SELECT campanhas.*,
                       ' . $this->adminMetricsSelectSql('campanhas.id') . '
                FROM campanhas
                WHERE 1=1';
        $params = [];

        $this->applyAdminFilters($sql, $params, $filters);

        $sql .= ' ORDER BY campanhas.created_at DESC LIMIT :limit OFFSET :offset';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /** @param array<string, mixed> $filters */
    public function countAllAdmin(array $filters = []): int
    {
        $sql = 'SELECT COUNT(*) AS total FROM campanhas WHERE 1=1';
        $params = [];

        $this->applyAdminFilters($sql, $params, $filters);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
    }

    /** @return array<int, array<string, mixed>> */
    public function findRecentCuponsByCampanha(int $campanhaId, int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, u.nome AS usuario_nome
             FROM cupons c
             LEFT JOIN usuarios u ON c.usuario_id = u.id
             WHERE c.campanha_id = :campanha_id
             ORDER BY c.created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue('campanha_id', $campanhaId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /** @return list<array{date: string, label: string, icon: string}> */
    public function getAdminTimeline(array $campanha): array
    {
        $events = [
            [
                'date' => (string) $campanha['created_at'],
                'label' => 'Criação',
                'icon' => '📝',
            ],
        ];

        if ((string) $campanha['status'] === self::STATUS_ATIVA) {
            $events[] = [
                'date' => (string) $campanha['updated_at'],
                'label' => 'Ativação',
                'icon' => '🟢',
            ];
        }

        $fim = (string) ($campanha['fim'] ?? '');
        $encerrada = (string) $campanha['status'] === self::STATUS_FINALIZADA
            || ($fim !== '' && $fim < date('Y-m-d') && (string) $campanha['status'] !== self::STATUS_ATIVA);

        if ($encerrada) {
            $events[] = [
                'date' => $fim !== '' ? $fim . ' 23:59:59' : (string) $campanha['updated_at'],
                'label' => 'Encerramento',
                'icon' => '⚫',
            ];
        }

        usort($events, static function (array $a, array $b): int {
            return strtotime($a['date']) <=> strtotime($b['date']);
        });

        return $events;
    }

    private function adminMetricsSelectSql(string $campanhaIdColumn): string
    {
        $utilizado = Cupom::STATUS_UTILIZADO;

        return "(SELECT COUNT(DISTINCT c.indicacao_id) FROM cupons c WHERE c.campanha_id = {$campanhaIdColumn}) AS total_indicados,
                (SELECT COUNT(*) FROM cupons c WHERE c.campanha_id = {$campanhaIdColumn}) AS cupons_gerados,
                (SELECT COUNT(*) FROM cupons c WHERE c.campanha_id = {$campanhaIdColumn} AND c.status = '{$utilizado}') AS cupons_utilizados";
    }

    /** @param array<string, mixed> $filters
     *  @param array<string, mixed> $params
     */
    private function applyAdminFilters(string &$sql, array &$params, array $filters): void
    {
        $nome = trim((string) ($filters['nome'] ?? ''));
        if ($nome !== '') {
            $sql .= ' AND campanhas.nome LIKE :nome';
            $params['nome'] = '%' . $nome . '%';
        }

        $status = (string) ($filters['status'] ?? self::FILTER_TODOS);
        if ($status !== self::FILTER_TODOS && in_array($status, self::ADMIN_STATUS_FILTERS, true)) {
            $sql .= ' AND campanhas.status = :status';
            $params['status'] = $status;
        }

        $vigencia = (string) ($filters['vigencia'] ?? self::FILTER_TODOS);
        if ($vigencia === self::FILTER_VIGENCIA_VIGENTE) {
            $sql .= ' AND CURDATE() BETWEEN campanhas.inicio AND campanhas.fim';
        } elseif ($vigencia === self::FILTER_VIGENCIA_FUTURA) {
            $sql .= ' AND campanhas.inicio > CURDATE()';
        } elseif ($vigencia === self::FILTER_VIGENCIA_ENCERRADA) {
            $sql .= ' AND campanhas.fim < CURDATE()';
        }
    }

    public static function tipoDescontoLabel(string $tipo): string
    {
        return match ($tipo) {
            self::TIPO_DESCONTO_PERCENTUAL => 'Percentual',
            self::TIPO_DESCONTO_VALOR_FIXO => 'Valor Fixo',
            default => $tipo,
        };
    }
}
