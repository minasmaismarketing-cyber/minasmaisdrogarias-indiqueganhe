<?php

declare(strict_types=1);

class ValidacaoIndicacao extends Model
{
    public const STATUS_PENDENTE = 'PENDENTE';
    public const STATUS_AGUARDANDO_CADASTRO = 'AGUARDANDO_CADASTRO';
    public const STATUS_AGUARDANDO_VALIDACAO = 'AGUARDANDO_VALIDACAO';
    public const STATUS_APROVADO = 'APROVADO';
    public const STATUS_REPROVADO = 'REPROVADO';
    public const STATUS_BENEFICIO_LIBERADO = 'BENEFICIO_LIBERADO';

    public function createFromIndicacao(int $indicacaoId, ?int $usuarioIndicadorId, ?int $usuarioIndicadoId): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO validacao_indicacoes (indicacao_id, usuario_indicador_id, usuario_indicado_id, status)
             VALUES (:indicacao_id, :usuario_indicador_id, :usuario_indicado_id, :status)'
        );
        $stmt->execute([
            'indicacao_id' => $indicacaoId,
            'usuario_indicador_id' => $usuarioIndicadorId,
            'usuario_indicado_id' => $usuarioIndicadoId,
            'status' => self::STATUS_PENDENTE,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findByIndicacao(int $indicacaoId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM validacao_indicacoes WHERE indicacao_id = :indicacao_id LIMIT 1');
        $stmt->execute(['indicacao_id' => $indicacaoId]);
        $row = $stmt->fetch();
        return $row ?: null;
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

    public function countByUsuarioIndicador(int $usuarioId, string $status): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) as total FROM validacao_indicacoes WHERE usuario_indicador_id = :usuario_id AND status = :status');
        $stmt->execute(['usuario_id' => $usuarioId, 'status' => $status]);
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

    public function countByUsuarioIndicador(int $usuarioId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) as total FROM validacao_indicacoes WHERE usuario_indicador_id = :usuario_id'
        );
        $stmt->execute(['usuario_id' => $usuarioId]);
        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
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
            self::STATUS_APROVADO => 'Aprovado',
            self::STATUS_REPROVADO => 'Reprovado',
            self::STATUS_BENEFICIO_LIBERADO => 'Benefício Liberado',
            default => $status,
        };
    }

    public static function statusIcon(string $status): string
    {
        return match ($status) {
            self::STATUS_PENDENTE => '⏳',
            self::STATUS_AGUARDANDO_CADASTRO => '📝',
            self::STATUS_AGUARDANDO_VALIDACAO => '🔍',
            self::STATUS_APROVADO => '✅',
            self::STATUS_REPROVADO => '❌',
            self::STATUS_BENEFICIO_LIBERADO => '🎁',
            default => '❓',
        };
    }
}
