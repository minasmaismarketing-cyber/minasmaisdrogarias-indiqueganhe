<?php

declare(strict_types=1);

class Indicacao extends Model
{
    public const STATUS_AGUARDANDO = 'AGUARDANDO';
    public const STATUS_LINK_ACESSADO = 'LINK_ACESSADO';
    public const STATUS_CADASTRO_PENDENTE = 'CADASTRO_PENDENTE';
    public const STATUS_VALIDADO = 'VALIDADO';
    public const STATUS_PREMIO_LIBERADO = 'PREMIO_LIBERADO';
    public const STATUS_INVALIDO = 'INVALIDO';
    public const STATUS_EXPIRADO = 'EXPIRADO';

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

    public function logShare(int $usuarioId, string $codigo): int
    {
        $codigoReferencia = $this->generateCodigoReferencia();
        $stmt = $this->db->prepare(
            'INSERT INTO indicacoes (usuario_id, codigo_indicador, codigo_referencia, status, origem)
             VALUES (:usuario_id, :codigo, :codigo_ref, :status, :origem)'
        );
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'codigo' => $codigo,
            'codigo_ref' => $codigoReferencia,
            'status' => self::STATUS_AGUARDANDO,
            'origem' => 'WEB',
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function registerLinkAccess(int $usuarioId, string $codigo): int
    {
        $codigoReferencia = $this->generateCodigoReferencia();
        $stmt = $this->db->prepare(
            'INSERT INTO indicacoes (usuario_id, codigo_indicador, codigo_referencia, status, origem)
             VALUES (:usuario_id, :codigo, :codigo_ref, :status, :origem)'
        );
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'codigo' => $codigo,
            'codigo_ref' => $codigoReferencia,
            'status' => self::STATUS_LINK_ACESSADO,
            'origem' => 'WEB',
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function completeRegistration(
        int $referrerUserId,
        string $codigo,
        string $nomeIndicado,
        string $telefoneIndicado
    ): int {
        $existing = $this->findActiveByReferrerAndPhone($referrerUserId, $telefoneIndicado);

        if ($existing !== null) {
            $stmt = $this->db->prepare(
                'UPDATE indicacoes
                 SET nome_indicado = :nome,
                     telefone_indicado = :telefone,
                     status = :status,
                     updated_at = NOW()
                 WHERE id = :id'
            );
            $stmt->execute([
                'nome' => $nomeIndicado,
                'telefone' => $telefoneIndicado,
                'status' => self::STATUS_CADASTRO_PENDENTE,
                'id' => $existing['id'],
            ]);

            return (int) $existing['id'];
        }

        $codigoReferencia = $this->generateCodigoReferencia();
        $stmt = $this->db->prepare(
            'INSERT INTO indicacoes
            (usuario_id, codigo_indicador, codigo_referencia, nome_indicado, telefone_indicado, status, origem)
            VALUES (:usuario_id, :codigo, :codigo_ref, :nome, :telefone, :status, :origem)'
        );
        $stmt->execute([
            'usuario_id' => $referrerUserId,
            'codigo' => $codigo,
            'codigo_ref' => $codigoReferencia,
            'nome' => $nomeIndicado,
            'telefone' => $telefoneIndicado,
            'status' => self::STATUS_CADASTRO_PENDENTE,
            'origem' => 'WEB',
        ]);

        $id = (int) $this->db->lastInsertId();

        // criar registro de validação para esta indicação (Etapa 10)
        try {
            $validacaoModel = new ValidacaoIndicacao();
            $validacaoModel->createFromIndicacao($id, $referrerUserId, null);
        } catch (Throwable $e) {
            Logger::warning('Falha ao criar validacao para indicacao', ['error' => $e->getMessage()]);
        }

        return $id;
    }

    private function generateCodigoReferencia(): string
    {
        return strtoupper(bin2hex(random_bytes(10)));
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

    public function countAll(): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) as total FROM indicacoes');
        $stmt->execute();
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
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
}
