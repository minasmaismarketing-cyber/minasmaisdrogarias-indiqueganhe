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
