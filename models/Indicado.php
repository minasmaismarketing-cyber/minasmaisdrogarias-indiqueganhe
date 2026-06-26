<?php

declare(strict_types=1);

class Indicado extends Model
{
    public const STATUS_LINK_ACESSADO = 'LINK_ACESSADO';
    public const STATUS_CADASTRO_INICIADO = 'CADASTRO_INICIADO';
    public const STATUS_CADASTRO_CONCLUIDO = 'CADASTRO_CONCLUIDO';
    public const STATUS_AGUARDANDO_VALIDACAO = 'AGUARDANDO_VALIDACAO';
    public const STATUS_VALIDADO = 'VALIDADO';
    public const STATUS_INVALIDADO = 'INVALIDADO';

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO indicados
            (codigo_indicador, usuario_indicador_id, nome, cpf, telefone, email, senha_hash, status, origem, aceite_lgpd)
            VALUES
            (:codigo_indicador, :usuario_indicador_id, :nome, :cpf, :telefone, :email, :senha_hash, :status, :origem, :aceite_lgpd)'
        );

        $stmt->execute([
            'codigo_indicador' => $data['codigo_indicador'],
            'usuario_indicador_id' => $data['usuario_indicador_id'] ?? null,
            'nome' => $data['nome'],
            'cpf' => $data['cpf'],
            'telefone' => $data['telefone'],
            'email' => $data['email'],
            'senha_hash' => $data['senha_hash'] ?? null,
            'status' => $data['status'] ?? self::STATUS_LINK_ACESSADO,
            'origem' => $data['origem'] ?? null,
            'aceite_lgpd' => $data['aceite_lgpd'] ?? 0,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findByCodigo(string $codigo): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM indicados WHERE codigo_indicador = :codigo LIMIT 1');
        $stmt->execute(['codigo' => strtoupper(trim($codigo))]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByCpf(string $cpf): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM indicados WHERE cpf = :cpf LIMIT 1');
        $stmt->execute(['cpf' => $cpf]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM indicados WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => strtolower($email)]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByTelefone(string $telefone): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM indicados WHERE telefone = :telefone LIMIT 1');
        $stmt->execute(['telefone' => $telefone]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM indicados WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function updateStatus(int $id, string $status): void
    {
        $stmt = $this->db->prepare(
            'UPDATE indicados SET status = :status, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute(['id' => $id, 'status' => $status]);
    }

    public function update(int $id, array $data): void
    {
        $fields = [];
        $params = ['id' => $id];

        if (isset($data['nome'])) {
            $fields[] = 'nome = :nome';
            $params['nome'] = $data['nome'];
        }
        if (isset($data['cpf'])) {
            $fields[] = 'cpf = :cpf';
            $params['cpf'] = $data['cpf'];
        }
        if (isset($data['telefone'])) {
            $fields[] = 'telefone = :telefone';
            $params['telefone'] = $data['telefone'];
        }
        if (isset($data['email'])) {
            $fields[] = 'email = :email';
            $params['email'] = $data['email'];
        }
        if (isset($data['senha_hash'])) {
            $fields[] = 'senha_hash = :senha_hash';
            $params['senha_hash'] = $data['senha_hash'];
        }
        if (isset($data['status'])) {
            $fields[] = 'status = :status';
            $params['status'] = $data['status'];
        }
        if (isset($data['aceite_lgpd'])) {
            $fields[] = 'aceite_lgpd = :aceite_lgpd';
            $params['aceite_lgpd'] = $data['aceite_lgpd'];
        }

        if ($fields === []) {
            return;
        }

        $sql = 'UPDATE indicados SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    public function listByIndicador(string $codigoIndicador, int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM indicados 
             WHERE codigo_indicador = :codigo_indicador 
             ORDER BY created_at DESC 
             LIMIT :limit'
        );
        $stmt->bindValue('codigo_indicador', $codigoIndicador);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countByIndicador(string $codigoIndicador): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) as total FROM indicados WHERE codigo_indicador = :codigo_indicador'
        );
        $stmt->execute(['codigo_indicador' => $codigoIndicador]);
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function cpfExists(string $cpf): bool
    {
        return $this->findByCpf($cpf) !== null;
    }

    public function emailExists(string $email): bool
    {
        return $this->findByEmail($email) !== null;
    }

    public function telefoneExists(string $telefone): bool
    {
        return $this->findByTelefone($telefone) !== null;
    }

    public function codigoExists(string $codigo): bool
    {
        $usuarioModel = new Usuario();
        return $usuarioModel->codigoExists($codigo);
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_LINK_ACESSADO => 'Link Acessado',
            self::STATUS_CADASTRO_INICIADO => 'Cadastro Iniciado',
            self::STATUS_CADASTRO_CONCLUIDO => 'Cadastro Concluído',
            self::STATUS_AGUARDANDO_VALIDACAO => 'Aguardando Validação',
            self::STATUS_VALIDADO => 'Validado',
            self::STATUS_INVALIDADO => 'Invalidado',
            default => $status,
        };
    }

    public static function maskName(string $nome): string
    {
        $parts = explode(' ', trim($nome));
        if (count($parts) === 0) {
            return 'Anônimo';
        }
        
        $firstName = $parts[0];
        $initials = '';
        
        if (count($parts) > 1) {
            $lastName = $parts[count($parts) - 1];
            $initials = ' ' . mb_strtoupper(mb_substr($lastName, 0, 1)) . '.';
        }
        
        return $firstName . $initials;
    }

    public function getTimeline(int $id): array
    {
        $indicado = $this->findById($id);
        if ($indicado === null) {
            return [];
        }

        $timeline = [];

        // Link acessado
        $timeline[] = [
            'label' => 'Link acessado',
            'icon' => '👆',
            'date' => $indicado['created_at'],
            'status' => 'done',
        ];

        // Cadastro iniciado
        if ($indicado['status'] !== self::STATUS_LINK_ACESSADO) {
            $timeline[] = [
                'label' => 'Cadastro iniciado',
                'icon' => '📝',
                'date' => $indicado['created_at'],
                'status' => 'done',
            ];
        }

        // Cadastro concluído
        if (in_array($indicado['status'], [
            self::STATUS_CADASTRO_CONCLUIDO,
            self::STATUS_AGUARDANDO_VALIDACAO,
            self::STATUS_VALIDADO,
            self::STATUS_INVALIDADO,
        ])) {
            $timeline[] = [
                'label' => 'Cadastro concluído',
                'icon' => '✅',
                'date' => $indicado['updated_at'],
                'status' => 'done',
            ];
        }

        // Validação
        if (in_array($indicado['status'], [
            self::STATUS_AGUARDANDO_VALIDACAO,
            self::STATUS_VALIDADO,
            self::STATUS_INVALIDADO,
        ])) {
            $timeline[] = [
                'label' => 'Aguardando validação',
                'icon' => '⏳',
                'date' => $indicado['updated_at'],
                'status' => $indicado['status'] === self::STATUS_AGUARDANDO_VALIDACAO ? 'pending' : 'done',
            ];
        }

        // Validado
        if ($indicado['status'] === self::STATUS_VALIDADO) {
            $timeline[] = [
                'label' => 'Validado',
                'icon' => '🎉',
                'date' => $indicado['updated_at'],
                'status' => 'done',
            ];
        }

        // Invalidado
        if ($indicado['status'] === self::STATUS_INVALIDADO) {
            $timeline[] = [
                'label' => 'Invalidado',
                'icon' => '❌',
                'date' => $indicado['updated_at'],
                'status' => 'error',
            ];
        }

        return $timeline;
    }
}
