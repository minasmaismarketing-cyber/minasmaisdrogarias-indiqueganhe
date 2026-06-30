<?php

declare(strict_types=1);

class Usuario extends Model
{
    public const ROLE_ADMIN = 'admin';
    public const ROLE_CLIENTE = 'cliente';

    /** @param array<string, mixed>|int $userOrId */
    public function isAdmin(array|int $userOrId): bool
    {
        if (is_int($userOrId)) {
            $user = $this->findById($userOrId);

            return $user !== null && $this->isAdmin($user);
        }

        return ($userOrId['role'] ?? self::ROLE_CLIENTE) === self::ROLE_ADMIN;
    }

    /** @param array<string, mixed>|int $userOrId */
    public function isCliente(array|int $userOrId): bool
    {
        if (is_int($userOrId)) {
            $user = $this->findById($userOrId);

            return $user !== null && $this->isCliente($user);
        }

        return ($userOrId['role'] ?? self::ROLE_CLIENTE) === self::ROLE_CLIENTE;
    }

    public function setRole(int $userId, string $role): void
    {
        if (!in_array($role, [self::ROLE_ADMIN, self::ROLE_CLIENTE], true)) {
            throw new InvalidArgumentException('Role inválida.');
        }

        $stmt = $this->db->prepare(
            'UPDATE usuarios SET role = :role, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute(['role' => $role, 'id' => $userId]);
    }

    public static function roleLabel(string $role): string
    {
        return match ($role) {
            self::ROLE_ADMIN => 'Administrador',
            self::ROLE_CLIENTE => 'Cliente',
            default => $role,
        };
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO usuarios
            (nome, cpf, telefone, email, senha_hash, aceite_lgpd, codigo_indicador, cupom_recebido, whatsapp)
            VALUES
            (:nome, :cpf, :telefone, :email, :senha_hash, :aceite_lgpd, :codigo_indicador, 0, :whatsapp)'
        );

        $stmt->execute([
            'nome' => $data['nome'],
            'cpf' => $data['cpf'],
            'telefone' => $data['telefone'],
            'email' => $data['email'],
            'senha_hash' => $data['senha_hash'],
            'aceite_lgpd' => $data['aceite_lgpd'],
            'codigo_indicador' => $data['codigo_indicador'],
            'whatsapp' => $data['whatsapp'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    /** @return array<string, mixed>|null */
    public function findByCpf(string $cpf): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE cpf = :cpf LIMIT 1');
        $stmt->execute(['cpf' => $cpf]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    /** @return array<string, mixed>|null */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    /** @return array<string, mixed>|null */
    public function findByCodigo(string $codigo): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE codigo_indicador = :codigo LIMIT 1');
        $stmt->execute(['codigo' => strtoupper(trim($codigo))]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    /** @return array<string, mixed>|null */
    public function findByLogin(string $login): ?array
    {
        $login = trim($login);

        if (str_contains($login, '@')) {
            return $this->findByEmail(strtolower($login));
        }

        $cpf = Validator::onlyDigits($login);

        return $this->findByCpf($cpf);
    }

    public function cpfExists(string $cpf): bool
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM usuarios WHERE cpf = :cpf AND ativo = 1 LIMIT 1'
        );
        $stmt->execute(['cpf' => $cpf]);

        return (bool) $stmt->fetch();
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM usuarios WHERE email = :email AND ativo = 1 LIMIT 1'
        );
        $stmt->execute(['email' => strtolower($email)]);

        return (bool) $stmt->fetch();
    }

    /** @deprecated Telefone não é mais único; mantido apenas por compatibilidade de chamadas legadas. */
    public function telefoneExists(string $telefone): bool
    {
        return false;
    }

    public function codigoExists(string $codigo): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM usuarios WHERE codigo_indicador = :codigo LIMIT 1');
        $stmt->execute(['codigo' => $codigo]);

        return (bool) $stmt->fetch();
    }

    public function generateCodigoIndicador(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $code = 'MM';
            for ($i = 0; $i < 6; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while ($this->codigoExists($code));

        return $code;
    }

    public function updatePassword(int $userId, string $hash): void
    {
        $stmt = $this->db->prepare(
            'UPDATE usuarios SET senha_hash = :senha_hash, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute(['senha_hash' => $hash, 'id' => $userId]);
    }

    public function updateRememberToken(int $userId, ?string $tokenHash): void
    {
        $stmt = $this->db->prepare(
            'UPDATE usuarios SET remember_token = :remember_token, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute(['remember_token' => $tokenHash, 'id' => $userId]);
    }

    /** @param array<string, mixed> $data */
    public function updateProfile(int $userId, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE usuarios
             SET nome = :nome, telefone = :telefone, whatsapp = :whatsapp, updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            'nome' => $data['nome'],
            'telefone' => $data['telefone'],
            'whatsapp' => $data['whatsapp'],
            'id' => $userId,
        ]);
    }

    public function countAll(): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) as total FROM usuarios');
        $stmt->execute();
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public const FILTER_TODOS = 'todos';
    public const FILTER_ATIVOS = 'ativos';
    public const FILTER_BLOQUEADOS = 'bloqueados';
    public const FILTER_EXCLUIDOS = 'excluidos';

    /** @param array<string, mixed> $filters
     *  @return array<int, array<string, mixed>>
     */
    public function findAllAdmin(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $sql = 'SELECT u.*,
                       (SELECT COUNT(*) FROM indicacoes i WHERE i.usuario_id = u.id) AS total_indicacoes,
                       (SELECT COUNT(*) FROM cupons c WHERE c.usuario_id = u.id) AS total_cupons
                FROM usuarios u
                WHERE 1=1';
        $params = [];

        $this->applyAdminFilters($sql, $params, $filters);

        $sql .= ' ORDER BY u.created_at DESC LIMIT :limit OFFSET :offset';

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
        $sql = 'SELECT COUNT(*) AS total FROM usuarios u WHERE 1=1';
        $params = [];

        $this->applyAdminFilters($sql, $params, $filters);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function setAtivo(int $userId, bool $ativo): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE usuarios
             SET ativo = :ativo, updated_at = NOW()
             WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute([
            'ativo' => $ativo ? 1 : 0,
            'id' => $userId,
        ]);

        return $stmt->rowCount() > 0;
    }

    /** @param array<string, mixed> $usuario */
    public static function accountStatusLabel(array $usuario): string
    {
        if (!empty($usuario['deleted_at'])) {
            return 'Excluído';
        }

        if ((int) ($usuario['ativo'] ?? 1) === 0) {
            return 'Bloqueado';
        }

        return 'Ativo';
    }

    /** @param array<string, mixed> $usuario */
    public static function formatCpfDisplay(array $usuario): string
    {
        $cpf = (string) ($usuario['cpf'] ?? '');

        if ($cpf === '' || str_starts_with($cpf, 'DEL_')) {
            return '—';
        }

        $digits = preg_replace('/\D/', '', $cpf) ?? '';

        return strlen($digits) === 11 ? format_cpf($digits) : $cpf;
    }

    /** @param array<string, mixed> $filters
     *  @param array<string, mixed> $params
     */
    private function applyAdminFilters(string &$sql, array &$params, array $filters): void
    {
        $statusFilter = (string) ($filters['status'] ?? self::FILTER_TODOS);

        if ($statusFilter === self::FILTER_ATIVOS) {
            $sql .= ' AND u.ativo = 1 AND u.deleted_at IS NULL';
        } elseif ($statusFilter === self::FILTER_BLOQUEADOS) {
            $sql .= ' AND u.ativo = 0 AND u.deleted_at IS NULL';
        } elseif ($statusFilter === self::FILTER_EXCLUIDOS) {
            $sql .= ' AND u.deleted_at IS NOT NULL';
        }

        if (!empty($filters['nome'])) {
            $sql .= ' AND u.nome LIKE :nome';
            $params['nome'] = '%' . $filters['nome'] . '%';
        }

        if (!empty($filters['email'])) {
            $sql .= ' AND u.email LIKE :email';
            $params['email'] = '%' . strtolower((string) $filters['email']) . '%';
        }

        if (!empty($filters['cpf'])) {
            $cpfDigits = preg_replace('/\D/', '', (string) $filters['cpf']) ?? '';
            if ($cpfDigits !== '') {
                $sql .= ' AND u.cpf LIKE :cpf';
                $params['cpf'] = '%' . $cpfDigits . '%';
            }
        }
    }

    /** @return array<int, array<string, mixed>> */
    public function listAll(int $limit = 100): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, nome, email, telefone, created_at FROM usuarios ORDER BY created_at DESC LIMIT :limit'
        );
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Exclusão completa da conta: soft delete + anonimização de dados pessoais.
     * Indicações, eventos, cupons, validações e logs permanecem vinculados ao id.
     */
    public function excluirConta(int $userId): void
    {
        $timestamp = time();
        $cpfAnon = sprintf('DEL_%d_%d', $userId, $timestamp);
        $emailAnon = sprintf('deleted_%d_%d@deleted.local', $userId, $timestamp);

        $stmt = $this->db->prepare(
            'UPDATE usuarios SET
                ativo = 0,
                deleted_at = NOW(),
                nome = :nome,
                cpf = :cpf,
                email = :email,
                telefone = NULL,
                whatsapp = NULL,
                remember_token = NULL,
                codigo_indicador = NULL,
                updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            'nome' => 'Usuário Excluído',
            'cpf' => $cpfAnon,
            'email' => $emailAnon,
            'id' => $userId,
        ]);
    }
}
