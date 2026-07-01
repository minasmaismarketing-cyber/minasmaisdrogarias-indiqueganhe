<?php

declare(strict_types=1);

/**
 * Model de leitura e administração de `indicacoes`.
 *
 * Criação e atualização de indicações: use exclusivamente ReferralService.
 */
class Indicacao extends Model
{
    public const STATUS_AGUARDANDO = 'AGUARDANDO';
    public const STATUS_LINK_ACESSADO = 'LINK_ACESSADO';
    public const STATUS_CADASTRO_PENDENTE = 'CADASTRO_PENDENTE';
    public const STATUS_VALIDADO = 'VALIDADO';
    public const STATUS_PREMIO_LIBERADO = 'PREMIO_LIBERADO';
    public const STATUS_INVALIDO = 'INVALIDO';
    public const STATUS_EXPIRADO = 'EXPIRADO';

    public const FILTER_TODOS = 'todos';

    /** @var list<string> */
    public const ADMIN_STATUS_FILTERS = [
        self::FILTER_TODOS,
        ValidacaoIndicacao::STATUS_AGUARDANDO_CADASTRO,
        ValidacaoIndicacao::STATUS_AGUARDANDO_VALIDACAO,
        ValidacaoIndicacao::STATUS_EM_ANALISE,
        ValidacaoIndicacao::STATUS_APROVADO,
        ValidacaoIndicacao::STATUS_REPROVADO,
        ValidacaoIndicacao::STATUS_CANCELADO,
    ];

    private const ADMIN_EFFECTIVE_STATUS_SQL = 'COALESCE(
        v.status,
        CASE i.status
            WHEN :st_aguardando THEN :vf_aguardando_cadastro
            WHEN :st_link THEN :vf_aguardando_cadastro
            WHEN :st_cadastro_pendente THEN :vf_aguardando_validacao
            WHEN :st_validado THEN :vf_aprovado
            WHEN :st_premio THEN :vf_aprovado
            WHEN :st_invalido THEN :vf_reprovado
            WHEN :st_expirado THEN :vf_cancelado
            ELSE :vf_aguardando_cadastro
        END
    )';

    /** @return array<string, int> */
    public function statsByUsuario(int $usuarioId): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status IN (:validado, :premio_liberado) THEN 1 ELSE 0 END) AS validadas,
                SUM(CASE WHEN status IN (:aguardando, :link, :cadastro_pendente) THEN 1 ELSE 0 END) AS pendentes,
                SUM(CASE WHEN status = :premio_liberado OR premio_liberado = 1 THEN 1 ELSE 0 END) AS liberadas
             FROM indicacoes
             WHERE usuario_id = :usuario_id'
        );
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'validado' => self::STATUS_VALIDADO,
            'premio_liberado' => self::STATUS_PREMIO_LIBERADO,
            'aguardando' => self::STATUS_AGUARDANDO,
            'link' => self::STATUS_LINK_ACESSADO,
            'cadastro_pendente' => self::STATUS_CADASTRO_PENDENTE,
        ]);

        $row = $stmt->fetch();

        return [
            'total' => (int) ($row['total'] ?? 0),
            'validadas' => (int) ($row['validadas'] ?? 0),
            'pendentes' => (int) ($row['pendentes'] ?? 0),
            'liberadas' => (int) ($row['liberadas'] ?? 0),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function listByUsuario(int $usuarioId, int $limit = 20): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM indicacoes
             WHERE usuario_id = :usuario_id
             ORDER BY updated_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue('usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function findActiveByReferrerAndPhone(int $usuarioId, string $telefone): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM indicacoes
             WHERE usuario_id = :usuario_id
               AND telefone_indicado = :telefone
               AND status NOT IN (:expirado)
             LIMIT 1'
        );
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'telefone' => $telefone,
            'expirado' => self::STATUS_EXPIRADO,
        ]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function phoneAlreadyIndicated(string $telefone, int $excludeReferrerId = 0): bool
    {
        $sql = 'SELECT id FROM indicacoes
                WHERE telefone_indicado = :telefone
                  AND status IN (:cadastro_pendente, :validado, :premio_liberado)
                LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'telefone' => $telefone,
            'cadastro_pendente' => self::STATUS_CADASTRO_PENDENTE,
            'validado' => self::STATUS_VALIDADO,
            'premio_liberado' => self::STATUS_PREMIO_LIBERADO,
        ]);

        return (bool) $stmt->fetch();
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_AGUARDANDO => 'Aguardando',
            self::STATUS_LINK_ACESSADO => 'Link acessado',
            self::STATUS_CADASTRO_PENDENTE => 'Cadastro pendente',
            self::STATUS_VALIDADO => 'Validado',
            self::STATUS_PREMIO_LIBERADO => 'Prêmio liberado',
            self::STATUS_INVALIDO => 'Inválido',
            self::STATUS_EXPIRADO => 'Expirado',
            default => $status,
        };
    }

    public static function statusIcon(string $status): string
    {
        return match ($status) {
            self::STATUS_AGUARDANDO => '⏳',
            self::STATUS_LINK_ACESSADO => '👁️',
            self::STATUS_CADASTRO_PENDENTE => '📝',
            self::STATUS_VALIDADO => '✅',
            self::STATUS_PREMIO_LIBERADO => '🎁',
            self::STATUS_INVALIDO => '❌',
            self::STATUS_EXPIRADO => '⏰',
            default => '❓',
        };
    }

    /** @return array<int, array<string, mixed>> */
    public function getTimeline(int $indicacaoId): array
    {
        $timeline = [];
        $indicacao = $this->findById($indicacaoId);

        if ($indicacao === null) {
            return $timeline;
        }

        // Link enviado (created_at)
        $timeline[] = [
            'step' => 'link_enviado',
            'label' => 'Link enviado',
            'date' => $indicacao['created_at'],
            'status' => 'completed',
            'icon' => '📤',
        ];

        // Clique registrado (updated_at when status changed to LINK_ACESSADO)
        if ($indicacao['status'] !== self::STATUS_AGUARDANDO) {
            $timeline[] = [
                'step' => 'clique_registrado',
                'label' => 'Clique registrado',
                'date' => $indicacao['updated_at'],
                'status' => 'completed',
                'icon' => '👁️',
            ];
        }

        // Cadastro (when nome_indicado is set)
        if (!empty($indicacao['nome_indicado']) || !empty($indicacao['telefone_indicado'])) {
            $timeline[] = [
                'step' => 'cadastro',
                'label' => 'Cadastro',
                'date' => $indicacao['updated_at'],
                'status' => 'completed',
                'icon' => '📝',
            ];
        }

        // Validação (when status is VALIDADO or higher)
        if (in_array($indicacao['status'], [self::STATUS_VALIDADO, self::STATUS_PREMIO_LIBERADO], true)) {
            $timeline[] = [
                'step' => 'validacao',
                'label' => 'Validação',
                'date' => $indicacao['updated_at'],
                'status' => 'completed',
                'icon' => '✅',
            ];
        }

        // Benefício (when status is PREMIO_LIBERADO or premio_liberado = 1)
        if ($indicacao['status'] === self::STATUS_PREMIO_LIBERADO || (int) $indicacao['premio_liberado'] === 1) {
            $timeline[] = [
                'step' => 'beneficio',
                'label' => 'Benefício',
                'date' => $indicacao['updated_at'],
                'status' => 'completed',
                'icon' => '🎁',
            ];
        }

        return $timeline;
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM indicacoes WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** @return array<int, array<string, mixed>> */
    public function listAll(int $limit = 100): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM indicacoes ORDER BY created_at DESC LIMIT :limit'
        );
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** @param array<string, mixed> $filters
     *  @return array<int, array<string, mixed>>
     */
    public function findAllAdmin(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $sql = $this->adminBaseSelectSql();
        $params = $this->adminStatusBindParams();

        $this->applyAdminFilters($sql, $params, $filters);

        $sql .= ' ORDER BY i.created_at DESC LIMIT :limit OFFSET :offset';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return array_map([$this, 'normalizeAdminRow'], $stmt->fetchAll());
    }

    /** @param array<string, mixed> $filters */
    public function countAllAdmin(array $filters = []): int
    {
        $sql = 'SELECT COUNT(DISTINCT i.id) AS total
                FROM indicacoes i
                INNER JOIN usuarios u_ind ON i.usuario_id = u_ind.id
                LEFT JOIN validacao_indicacoes v ON v.indicacao_id = i.id
                LEFT JOIN usuarios u_indicado ON v.usuario_indicado_id = u_indicado.id
                WHERE 1=1';
        $params = $this->adminStatusBindParams();

        $this->applyAdminFilters($sql, $params, $filters);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
    }

    /** @return array<string, mixed>|null */
    public function findByIdAdmin(int $id): ?array
    {
        $rows = $this->findAllAdmin(['id' => $id], 1, 0);

        return $rows[0] ?? null;
    }

    /** @param array<string, mixed> $row
     *  @return array<string, mixed>
     */
    public static function resolveAdminStatus(array $row): string
    {
        if (!empty($row['validacao_status'])) {
            return (string) $row['validacao_status'];
        }

        return self::mapIndicacaoStatusToAdmin((string) ($row['status'] ?? ''));
    }

    public static function mapIndicacaoStatusToAdmin(string $status): string
    {
        return match ($status) {
            self::STATUS_AGUARDANDO, self::STATUS_LINK_ACESSADO => ValidacaoIndicacao::STATUS_AGUARDANDO_CADASTRO,
            self::STATUS_CADASTRO_PENDENTE => ValidacaoIndicacao::STATUS_AGUARDANDO_VALIDACAO,
            self::STATUS_VALIDADO, self::STATUS_PREMIO_LIBERADO => ValidacaoIndicacao::STATUS_APROVADO,
            self::STATUS_INVALIDO => ValidacaoIndicacao::STATUS_REPROVADO,
            self::STATUS_EXPIRADO => ValidacaoIndicacao::STATUS_CANCELADO,
            default => ValidacaoIndicacao::STATUS_AGUARDANDO_CADASTRO,
        };
    }

    public static function adminStatusLabel(string $status): string
    {
        return match ($status) {
            ValidacaoIndicacao::STATUS_APROVADO, ValidacaoIndicacao::STATUS_BENEFICIO_LIBERADO => 'Aprovada',
            ValidacaoIndicacao::STATUS_REPROVADO => 'Reprovada',
            ValidacaoIndicacao::STATUS_CANCELADO => 'Cancelada',
            default => ValidacaoIndicacao::statusLabel($status),
        };
    }

    /** @return list<array{date: string, label: string, icon: string, description: ?string, admin: ?string}> */
    public function getAdminTimeline(int $indicacaoId, ?int $validacaoId = null, ?array $cupom = null): array
    {
        $events = [];

        foreach ($this->getTimeline($indicacaoId) as $step) {
            $events[] = [
                'date' => (string) $step['date'],
                'label' => (string) $step['label'],
                'icon' => (string) $step['icon'],
                'description' => null,
                'admin' => null,
            ];
        }

        if ($validacaoId !== null) {
            $history = (new HistoricoValidacao())->findByValidacao($validacaoId);
            foreach ($history as $item) {
                $events[] = [
                    'date' => (string) $item['created_at'],
                    'label' => ValidacaoIndicacao::statusLabel((string) $item['status_anterior'])
                        . ' → '
                        . ValidacaoIndicacao::statusLabel((string) $item['status_novo']),
                    'icon' => ValidacaoIndicacao::statusIcon((string) $item['status_novo']),
                    'description' => $item['descricao'] !== null ? (string) $item['descricao'] : null,
                    'admin' => $item['usuario_admin'] !== null ? (string) $item['usuario_admin'] : null,
                ];
            }
        }

        if ($cupom !== null) {
            $events[] = [
                'date' => (string) $cupom['created_at'],
                'label' => 'Cupom gerado',
                'icon' => Cupom::statusIcon((string) $cupom['status']),
                'description' => (string) $cupom['codigo'],
                'admin' => null,
            ];
        }

        usort($events, static function (array $a, array $b): int {
            return strtotime($a['date']) <=> strtotime($b['date']);
        });

        return $events;
    }

    /** @return array<string, mixed> */
    private function adminStatusBindParams(): array
    {
        return [
            'st_aguardando' => self::STATUS_AGUARDANDO,
            'st_link' => self::STATUS_LINK_ACESSADO,
            'st_cadastro_pendente' => self::STATUS_CADASTRO_PENDENTE,
            'st_validado' => self::STATUS_VALIDADO,
            'st_premio' => self::STATUS_PREMIO_LIBERADO,
            'st_invalido' => self::STATUS_INVALIDO,
            'st_expirado' => self::STATUS_EXPIRADO,
            'vf_aguardando_cadastro' => ValidacaoIndicacao::STATUS_AGUARDANDO_CADASTRO,
            'vf_aguardando_validacao' => ValidacaoIndicacao::STATUS_AGUARDANDO_VALIDACAO,
            'vf_aprovado' => ValidacaoIndicacao::STATUS_APROVADO,
            'vf_reprovado' => ValidacaoIndicacao::STATUS_REPROVADO,
            'vf_cancelado' => ValidacaoIndicacao::STATUS_CANCELADO,
        ];
    }

    private function adminBaseSelectSql(): string
    {
        return 'SELECT i.*,
                       u_ind.nome AS indicador_nome,
                       u_ind.cpf AS indicador_cpf,
                       u_ind.whatsapp AS indicador_whatsapp,
                       u_indicado.nome AS indicado_usuario_nome,
                       u_indicado.cpf AS indicado_cpf,
                       u_indicado.whatsapp AS indicado_usuario_whatsapp,
                       v.id AS validacao_id,
                       v.status AS validacao_status,
                       v.elegivel AS validacao_elegivel,
                       v.motivo_bloqueio AS validacao_motivo_bloqueio,
                       v.created_at AS validacao_created_at,
                       v.updated_at AS validacao_updated_at,
                       (SELECT c.id FROM cupons c WHERE c.indicacao_id = i.id ORDER BY c.created_at DESC LIMIT 1) AS cupom_id,
                       (SELECT c.codigo FROM cupons c WHERE c.indicacao_id = i.id ORDER BY c.created_at DESC LIMIT 1) AS cupom_codigo,
                       (SELECT c.status FROM cupons c WHERE c.indicacao_id = i.id ORDER BY c.created_at DESC LIMIT 1) AS cupom_status,
                       (SELECT c.created_at FROM cupons c WHERE c.indicacao_id = i.id ORDER BY c.created_at DESC LIMIT 1) AS cupom_created_at,
                       ' . self::ADMIN_EFFECTIVE_STATUS_SQL . ' AS admin_status
                FROM indicacoes i
                INNER JOIN usuarios u_ind ON i.usuario_id = u_ind.id
                LEFT JOIN validacao_indicacoes v ON v.indicacao_id = i.id
                LEFT JOIN usuarios u_indicado ON v.usuario_indicado_id = u_indicado.id
                WHERE 1=1';
    }

    /** @param array<string, mixed> $row
     *  @return array<string, mixed>
     */
    private function normalizeAdminRow(array $row): array
    {
        $row['indicado_nome'] = trim((string) ($row['nome_indicado'] ?? '')) !== ''
            ? (string) $row['nome_indicado']
            : (trim((string) ($row['indicado_usuario_nome'] ?? '')) !== ''
                ? (string) $row['indicado_usuario_nome']
                : 'Aguardando cadastro');

        $whatsapp = trim((string) ($row['telefone_indicado'] ?? ''));
        if ($whatsapp === '') {
            $whatsapp = trim((string) ($row['indicado_usuario_whatsapp'] ?? ''));
        }
        $row['indicado_whatsapp'] = $whatsapp;

        $row['admin_status'] = (string) ($row['admin_status'] ?? self::resolveAdminStatus($row));

        return $row;
    }

    /** @param array<string, mixed> $filters
     *  @param array<string, mixed> $params
     */
    private function applyAdminFilters(string &$sql, array &$params, array $filters): void
    {
        if (!empty($filters['id'])) {
            $sql .= ' AND i.id = :id';
            $params['id'] = (int) $filters['id'];
        }

        $indicador = trim((string) ($filters['indicador'] ?? ''));
        if ($indicador !== '') {
            $sql .= ' AND u_ind.nome LIKE :indicador';
            $params['indicador'] = '%' . $indicador . '%';
        }

        $indicado = trim((string) ($filters['indicado'] ?? ''));
        if ($indicado !== '') {
            $sql .= ' AND (i.nome_indicado LIKE :indicado OR u_indicado.nome LIKE :indicado_usuario)';
            $params['indicado'] = '%' . $indicado . '%';
            $params['indicado_usuario'] = '%' . $indicado . '%';
        }

        $cpf = preg_replace('/\D/', '', (string) ($filters['cpf'] ?? '')) ?? '';
        if ($cpf !== '') {
            $sql .= ' AND REPLACE(REPLACE(REPLACE(u_indicado.cpf, ".", ""), "-", ""), " ", "") LIKE :cpf';
            $params['cpf'] = '%' . $cpf . '%';
        }

        $whatsapp = preg_replace('/\D/', '', (string) ($filters['whatsapp'] ?? '')) ?? '';
        if ($whatsapp !== '') {
            $sql .= ' AND (
                REPLACE(REPLACE(REPLACE(REPLACE(i.telefone_indicado, "(", ""), ")", ""), "-", ""), " ", "") LIKE :whatsapp
                OR REPLACE(REPLACE(REPLACE(REPLACE(u_indicado.whatsapp, "(", ""), ")", ""), "-", ""), " ", "") LIKE :whatsapp_usuario
            )';
            $params['whatsapp'] = '%' . $whatsapp . '%';
            $params['whatsapp_usuario'] = '%' . $whatsapp . '%';
        }

        $codigo = trim((string) ($filters['codigo'] ?? ''));
        if ($codigo !== '') {
            $sql .= ' AND (i.codigo_referencia LIKE :codigo OR i.codigo_indicador LIKE :codigo_indicador)';
            $params['codigo'] = '%' . $codigo . '%';
            $params['codigo_indicador'] = '%' . $codigo . '%';
        }

        $statusFilter = (string) ($filters['status'] ?? self::FILTER_TODOS);
        if ($statusFilter !== self::FILTER_TODOS) {
            $this->applyAdminStatusFilter($sql, $params, $statusFilter);
        }
    }

    /** @param array<string, mixed> $params */
    private function applyAdminStatusFilter(string &$sql, array &$params, string $statusFilter): void
    {
        if ($statusFilter === ValidacaoIndicacao::STATUS_AGUARDANDO_CADASTRO) {
            $sql .= ' AND (
                v.status = :filter_aguardando_cadastro
                OR (v.status IS NULL AND i.status IN (:filter_st_aguardando, :filter_st_link))
            )';
            $params['filter_aguardando_cadastro'] = ValidacaoIndicacao::STATUS_AGUARDANDO_CADASTRO;
            $params['filter_st_aguardando'] = self::STATUS_AGUARDANDO;
            $params['filter_st_link'] = self::STATUS_LINK_ACESSADO;

            return;
        }

        if ($statusFilter === ValidacaoIndicacao::STATUS_AGUARDANDO_VALIDACAO) {
            $sql .= ' AND (
                v.status IN (:filter_pendente, :filter_aguardando_validacao)
                OR (v.status IS NULL AND i.status = :filter_st_cadastro_pendente)
            )';
            $params['filter_pendente'] = ValidacaoIndicacao::STATUS_PENDENTE;
            $params['filter_aguardando_validacao'] = ValidacaoIndicacao::STATUS_AGUARDANDO_VALIDACAO;
            $params['filter_st_cadastro_pendente'] = self::STATUS_CADASTRO_PENDENTE;

            return;
        }

        if ($statusFilter === ValidacaoIndicacao::STATUS_APROVADO) {
            $sql .= ' AND (
                v.status IN (:filter_aprovado, :filter_beneficio)
                OR (v.status IS NULL AND i.status IN (:filter_st_validado, :filter_st_premio))
            )';
            $params['filter_aprovado'] = ValidacaoIndicacao::STATUS_APROVADO;
            $params['filter_beneficio'] = ValidacaoIndicacao::STATUS_BENEFICIO_LIBERADO;
            $params['filter_st_validado'] = self::STATUS_VALIDADO;
            $params['filter_st_premio'] = self::STATUS_PREMIO_LIBERADO;

            return;
        }

        if ($statusFilter === ValidacaoIndicacao::STATUS_REPROVADO) {
            $sql .= ' AND (
                v.status = :filter_reprovado
                OR (v.status IS NULL AND i.status = :filter_st_invalido)
            )';
            $params['filter_reprovado'] = ValidacaoIndicacao::STATUS_REPROVADO;
            $params['filter_st_invalido'] = self::STATUS_INVALIDO;

            return;
        }

        if ($statusFilter === ValidacaoIndicacao::STATUS_CANCELADO) {
            $sql .= ' AND (
                v.status = :filter_cancelado
                OR (v.status IS NULL AND i.status = :filter_st_expirado)
            )';
            $params['filter_cancelado'] = ValidacaoIndicacao::STATUS_CANCELADO;
            $params['filter_st_expirado'] = self::STATUS_EXPIRADO;

            return;
        }

        $sql .= ' AND v.status = :filter_status';
        $params['filter_status'] = $statusFilter;
    }

    /** @param list<int> $ids
     *  @return array<int, array<string, mixed>>
     */
    public function findSummaryByIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return [];
        }

        $placeholders = [];
        $params = [];
        foreach ($ids as $index => $id) {
            $key = 'id_' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = $id;
        }

        $stmt = $this->db->prepare(
            'SELECT id, created_at, updated_at, codigo_referencia, codigo_indicador, nome_indicado, telefone_indicado, status
             FROM indicacoes
             WHERE id IN (' . implode(', ', $placeholders) . ')'
        );
        $stmt->execute($params);

        $map = [];
        foreach ($stmt->fetchAll() as $row) {
            $map[(int) $row['id']] = $row;
        }

        return $map;
    }
}
