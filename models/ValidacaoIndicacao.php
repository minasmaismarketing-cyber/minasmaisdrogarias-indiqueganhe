<?php

declare(strict_types=1);

class ValidacaoIndicacao extends Model
{
    public const STATUS_PENDENTE = 'PENDENTE';
    public const STATUS_AGUARDANDO_CADASTRO = 'AGUARDANDO_CADASTRO';
    public const STATUS_AGUARDANDO_VALIDACAO = 'AGUARDANDO_VALIDACAO';
    public const STATUS_EM_ANALISE = 'EM_ANALISE';
    public const STATUS_APROVADO = 'APROVADO';
    public const STATUS_REPROVADO = 'REPROVADO';
    public const STATUS_BENEFICIO_LIBERADO = 'BENEFICIO_LIBERADO';
    public const STATUS_CANCELADO = 'CANCELADO';

    /** @var list<string> */
    private const STATUSES_PENDENTES = [
        self::STATUS_PENDENTE,
        self::STATUS_AGUARDANDO_CADASTRO,
        self::STATUS_AGUARDANDO_VALIDACAO,
    ];

    /** @var list<string> */
    private const STATUSES_APROVADOS = [
        self::STATUS_APROVADO,
        self::STATUS_BENEFICIO_LIBERADO,
    ];

    public function findByIndicacao(int $indicacaoId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM validacao_indicacoes WHERE indicacao_id = :indicacao_id LIMIT 1');
        $stmt->execute(['indicacao_id' => $indicacaoId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT v.*,
                    v.motivo_bloqueio AS motivo,
                    u.nome AS usuario_nome,
                    i.nome_indicado,
                    i.telefone_indicado,
                    CASE
                        WHEN v.status IN (:aprovado, :beneficio) THEN v.updated_at
                        ELSE NULL
                    END AS validado_em
             FROM validacao_indicacoes v
             LEFT JOIN usuarios u ON v.usuario_indicador_id = u.id
             LEFT JOIN indicacoes i ON v.indicacao_id = i.id
             WHERE v.id = :id
             LIMIT 1'
        );
        $stmt->execute([
            'id' => $id,
            'aprovado' => self::STATUS_APROVADO,
            'beneficio' => self::STATUS_BENEFICIO_LIBERADO,
        ]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /** @param array<string, mixed> $filters
     *  @return array<int, array<string, mixed>>
     */
    public function findAll(array $filters = []): array
    {
        $sql = 'SELECT v.*,
                       v.motivo_bloqueio AS motivo,
                       u.nome AS usuario_nome,
                       i.nome_indicado,
                       i.telefone_indicado,
                       i.created_at AS indicacao_created_at
                FROM validacao_indicacoes v
                LEFT JOIN usuarios u ON v.usuario_indicador_id = u.id
                LEFT JOIN indicacoes i ON v.indicacao_id = i.id
                WHERE 1=1';
        $params = [];

        if (!empty($filters['status'])) {
            $statusFilter = $this->resolveStatusFilter((string) $filters['status']);
            if ($statusFilter['type'] === 'in') {
                $placeholders = [];
                foreach ($statusFilter['values'] as $index => $status) {
                    $key = 'status_' . $index;
                    $placeholders[] = ':' . $key;
                    $params[$key] = $status;
                }
                $sql .= ' AND v.status IN (' . implode(', ', $placeholders) . ')';
            } else {
                $sql .= ' AND v.status = :status';
                $params['status'] = $statusFilter['values'][0];
            }
        }

        if (!empty($filters['cpf'])) {
            $sql .= ' AND u.cpf = :cpf';
            $params['cpf'] = preg_replace('/\D/', '', (string) $filters['cpf']);
        }

        if (!empty($filters['nome'])) {
            $sql .= ' AND (u.nome LIKE :nome OR i.nome_indicado LIKE :nome)';
            $params['nome'] = '%' . $filters['nome'] . '%';
        }

        if (!empty($filters['whatsapp'])) {
            $whatsapp = preg_replace('/\D/', '', (string) $filters['whatsapp']);
            $sql .= ' AND (u.whatsapp LIKE :whatsapp OR i.telefone_indicado LIKE :whatsapp)';
            $params['whatsapp'] = '%' . $whatsapp . '%';
        }

        if (!empty($filters['email'])) {
            $sql .= ' AND u.email LIKE :email';
            $params['email'] = '%' . $filters['email'] . '%';
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

    public function startReview(int $id, ?string $adminEmail = null): bool
    {
        $validacao = $this->findById($id);
        if ($validacao === null || !self::canStartReview((string) $validacao['status'])) {
            return false;
        }

        $statusAnterior = (string) $validacao['status'];
        $this->updateStatus($id, self::STATUS_EM_ANALISE);
        $this->recordHistory($id, $statusAnterior, self::STATUS_EM_ANALISE, 'Início da análise', $adminEmail);

        $usuarioId = (int) ($validacao['usuario_indicador_id'] ?? 0);
        if ($usuarioId > 0) {
            (new EventLogger())->logValidacaoIniciada($usuarioId, $id);
        }

        return true;
    }

    public function approve(int $id, ?string $observacao = null, ?string $adminEmail = null): bool
    {
        $validacao = $this->findById($id);
        if ($validacao === null || !self::canDecide((string) $validacao['status'])) {
            return false;
        }

        $statusAnterior = (string) $validacao['status'];
        $stmt = $this->db->prepare(
            'UPDATE validacao_indicacoes
             SET status = :status, elegivel = 1, updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute(['status' => self::STATUS_APROVADO, 'id' => $id]);

        $indicacaoId = (int) ($validacao['indicacao_id'] ?? 0);
        if ($indicacaoId > 0) {
            $this->syncIndicacaoStatus($indicacaoId, Indicacao::STATUS_VALIDADO);
        }

        $this->recordHistory(
            $id,
            $statusAnterior,
            self::STATUS_APROVADO,
            $observacao ?? 'Validação aprovada',
            $adminEmail
        );

        $usuarioId = (int) ($validacao['usuario_indicador_id'] ?? 0);
        if ($usuarioId > 0) {
            (new EventLogger())->logValidacaoAprovada($usuarioId, $id);
        }

        if ($indicacaoId > 0 && $usuarioId > 0) {
            try {
                (new CupomService())->generateForIndicacao($indicacaoId, $usuarioId);
            } catch (Throwable $e) {
                Logger::error('Failed to generate coupon', [
                    'validacao_id' => $id,
                    'indicacao_id' => $indicacaoId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return true;
    }

    public function reject(int $id, string $motivo, ?string $adminEmail = null): bool
    {
        $validacao = $this->findById($id);
        if ($validacao === null || !self::canDecide((string) $validacao['status'])) {
            return false;
        }

        $statusAnterior = (string) $validacao['status'];
        $this->updateStatus($id, self::STATUS_REPROVADO, $motivo);

        $indicacaoId = (int) ($validacao['indicacao_id'] ?? 0);
        if ($indicacaoId > 0) {
            $this->syncIndicacaoStatus($indicacaoId, Indicacao::STATUS_INVALIDO);
        }

        $this->recordHistory($id, $statusAnterior, self::STATUS_REPROVADO, $motivo, $adminEmail);

        $usuarioId = (int) ($validacao['usuario_indicador_id'] ?? 0);
        if ($usuarioId > 0) {
            (new EventLogger())->logValidacaoInvalidada($usuarioId, $id, $motivo);
        }

        return true;
    }

    public function cancel(int $id, ?string $motivo = null, ?string $adminEmail = null): bool
    {
        $validacao = $this->findById($id);
        if ($validacao === null || !self::canCancel((string) $validacao['status'])) {
            return false;
        }

        $statusAnterior = (string) $validacao['status'];
        $this->updateStatus($id, self::STATUS_CANCELADO, $motivo);

        $this->recordHistory(
            $id,
            $statusAnterior,
            self::STATUS_CANCELADO,
            $motivo ?? 'Validação cancelada',
            $adminEmail
        );

        $usuarioId = (int) ($validacao['usuario_indicador_id'] ?? 0);
        if ($usuarioId > 0) {
            (new EventLogger())->logValidacaoCancelada($usuarioId, $id);
        }

        return true;
    }

    public static function canStartReview(string $status): bool
    {
        return in_array($status, self::STATUSES_PENDENTES, true);
    }

    public static function canDecide(string $status): bool
    {
        return $status === self::STATUS_EM_ANALISE;
    }

    public static function canCancel(string $status): bool
    {
        return self::canStartReview($status) || $status === self::STATUS_EM_ANALISE;
    }

    /** @return array{type: string, values: list<string>} */
    private function resolveStatusFilter(string $status): array
    {
        if ($status === self::STATUS_PENDENTE) {
            return ['type' => 'in', 'values' => self::STATUSES_PENDENTES];
        }

        if ($status === self::STATUS_APROVADO) {
            return ['type' => 'in', 'values' => self::STATUSES_APROVADOS];
        }

        return ['type' => 'eq', 'values' => [$status]];
    }

    private function syncIndicacaoStatus(int $indicacaoId, string $status): void
    {
        $stmt = $this->db->prepare(
            'UPDATE indicacoes SET status = :status, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute(['status' => $status, 'id' => $indicacaoId]);
    }

    private function recordHistory(
        int $validacaoId,
        string $statusAnterior,
        string $statusNovo,
        ?string $descricao,
        ?string $adminEmail
    ): void {
        (new HistoricoValidacao())->create([
            'validacao_id' => $validacaoId,
            'status_anterior' => $statusAnterior,
            'status_novo' => $statusNovo,
            'descricao' => $descricao,
            'usuario_admin' => $adminEmail,
        ]);
    }

    public function updateStatus(int $id, string $status, ?string $motivo = null): void
    {
        $sql = 'UPDATE validacao_indicacoes SET status = :status, updated_at = NOW()';
        $params = ['id' => $id, 'status' => $status];

        if ($motivo !== null) {
            $sql .= ', motivo_bloqueio = :motivo';
            $params['motivo'] = $motivo;
        }

        $sql .= ' WHERE id = :id';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    public function markEligible(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE validacao_indicacoes SET elegivel = 1, status = :status, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id, 'status' => self::STATUS_APROVADO]);
    }

    public function listByStatus(string $status, int $limit = 100): array
    {
        $stmt = $this->db->prepare('SELECT * FROM validacao_indicacoes WHERE status = :status ORDER BY created_at DESC LIMIT :limit');
        $stmt->bindValue('status', $status);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countByUsuarioIndicador(int $usuarioId, ?string $status = null): int
    {
        if ($status !== null) {
            $stmt = $this->db->prepare('SELECT COUNT(*) as total FROM validacao_indicacoes WHERE usuario_indicador_id = :usuario_id AND status = :status');
            $stmt->execute(['usuario_id' => $usuarioId, 'status' => $status]);
        } else {
            $stmt = $this->db->prepare(
                'SELECT COUNT(*) as total FROM validacao_indicacoes WHERE usuario_indicador_id = :usuario_id'
            );
            $stmt->execute(['usuario_id' => $usuarioId]);
        }

        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public static function motivoLabel(string $motivo): string
    {
        return match ($motivo) {
            'CPF_EXISTENTE' => 'CPF já existente',
            'AUTO_INDICACAO' => 'Auto indicação',
            'JA_PARTICIPOU' => 'Já participou',
            'INVALIDO' => 'Inválido',
            default => $motivo,
        };
    }

    public function statsUsuarioIndicador(int $usuarioId): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = :aprovado THEN 1 ELSE 0 END) as aprovados,
                SUM(CASE WHEN status = :reprovado THEN 1 ELSE 0 END) as reprovados,
                SUM(CASE WHEN status = :beneficio THEN 1 ELSE 0 END) as beneficios_liberados
             FROM validacao_indicacoes
             WHERE usuario_indicador_id = :usuario_id'
        );
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'aprovado' => self::STATUS_APROVADO,
            'reprovado' => self::STATUS_REPROVADO,
            'beneficio' => self::STATUS_BENEFICIO_LIBERADO,
        ]);
        $row = $stmt->fetch();

        return [
            'total' => (int) ($row['total'] ?? 0),
            'aprovados' => (int) ($row['aprovados'] ?? 0),
            'reprovados' => (int) ($row['reprovados'] ?? 0),
            'beneficios_liberados' => (int) ($row['beneficios_liberados'] ?? 0),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function listByUsuarioIndicador(int $usuarioId, int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            'SELECT v.*, i.nome_indicado, i.telefone_indicado, i.status as indicacao_status
             FROM validacao_indicacoes v
             LEFT JOIN indicacoes i ON v.indicacao_id = i.id
             WHERE v.usuario_indicador_id = :usuario_id
             ORDER BY v.created_at DESC
             LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue('usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_PENDENTE => 'Pendente',
            self::STATUS_AGUARDANDO_CADASTRO => 'Aguardando Cadastro',
            self::STATUS_AGUARDANDO_VALIDACAO => 'Aguardando Validação',
            self::STATUS_EM_ANALISE => 'Em análise',
            self::STATUS_APROVADO => 'Aprovado',
            self::STATUS_REPROVADO => 'Reprovado',
            self::STATUS_BENEFICIO_LIBERADO => 'Benefício Liberado',
            self::STATUS_CANCELADO => 'Cancelado',
            default => $status,
        };
    }

    public static function statusIcon(string $status): string
    {
        return match ($status) {
            self::STATUS_PENDENTE => '🟡',
            self::STATUS_AGUARDANDO_CADASTRO => '📝',
            self::STATUS_AGUARDANDO_VALIDACAO => '🔍',
            self::STATUS_EM_ANALISE => '🟠',
            self::STATUS_APROVADO => '🟢',
            self::STATUS_REPROVADO => '🔴',
            self::STATUS_BENEFICIO_LIBERADO => '🎁',
            self::STATUS_CANCELADO => '⚫',
            default => '❓',
        };
    }

    public static function adminStatusIcon(string $status): string
    {
        return match ($status) {
            self::STATUS_AGUARDANDO_CADASTRO => '🟡',
            self::STATUS_PENDENTE => '🟠',
            self::STATUS_AGUARDANDO_VALIDACAO => '🟠',
            self::STATUS_EM_ANALISE => '🔵',
            self::STATUS_APROVADO, self::STATUS_BENEFICIO_LIBERADO => '🟢',
            self::STATUS_REPROVADO => '🔴',
            self::STATUS_CANCELADO => '⚫',
            default => self::statusIcon($status),
        };
    }

    public static function adminStatusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_PENDENTE => 'Aguardando Validação',
            self::STATUS_AGUARDANDO_CADASTRO => 'Aguardando Cadastro',
            self::STATUS_AGUARDANDO_VALIDACAO => 'Aguardando Validação',
            self::STATUS_EM_ANALISE => 'Em Análise',
            self::STATUS_APROVADO, self::STATUS_BENEFICIO_LIBERADO => 'Aprovada',
            self::STATUS_REPROVADO => 'Reprovada',
            self::STATUS_CANCELADO => 'Cancelada',
            default => self::statusLabel($status),
        };
    }

    public static function adminStatusBadgeClass(string $status): string
    {
        return match ($status) {
            self::STATUS_AGUARDANDO_CADASTRO => 'badge--aguardando_cadastro',
            self::STATUS_PENDENTE, self::STATUS_AGUARDANDO_VALIDACAO => 'badge--aguardando_validacao',
            self::STATUS_EM_ANALISE => 'badge--em_analise',
            self::STATUS_APROVADO, self::STATUS_BENEFICIO_LIBERADO => 'badge--aprovado',
            self::STATUS_REPROVADO => 'badge--reprovado',
            self::STATUS_CANCELADO => 'badge--cancelado',
            default => 'badge--' . strtolower($status),
        };
    }

    /** @return array{label: string, class: string, hours: float|null} */
    public static function waitTimeMeta(array $validacao): array
    {
        $status = (string) ($validacao['status'] ?? '');
        $terminalStatuses = [
            self::STATUS_APROVADO,
            self::STATUS_BENEFICIO_LIBERADO,
            self::STATUS_REPROVADO,
            self::STATUS_CANCELADO,
        ];

        if (in_array($status, $terminalStatuses, true)) {
            return [
                'label' => '—',
                'class' => 'wait-time--neutral',
                'hours' => null,
            ];
        }

        $reference = (string) ($validacao['indicacao_created_at'] ?? $validacao['created_at'] ?? '');
        if ($reference === '') {
            return [
                'label' => '—',
                'class' => 'wait-time--neutral',
                'hours' => null,
            ];
        }

        $hours = max(0, (time() - strtotime($reference)) / 3600);

        return [
            'label' => self::formatWaitDuration($hours),
            'class' => self::waitTimeClass($hours),
            'hours' => $hours,
        ];
    }

    private static function formatWaitDuration(float $hours): string
    {
        if ($hours < 1) {
            return 'Menos de 1h';
        }

        if ($hours < 24) {
            return (int) floor($hours) . 'h';
        }

        $days = (int) floor($hours / 24);
        $remainingHours = (int) floor($hours - ($days * 24));

        if ($remainingHours === 0) {
            return $days . 'd';
        }

        return $days . 'd ' . $remainingHours . 'h';
    }

    private static function waitTimeClass(float $hours): string
    {
        if ($hours <= 24) {
            return 'wait-time--ok';
        }

        if ($hours <= 72) {
            return 'wait-time--warning';
        }

        return 'wait-time--critical';
    }
}
