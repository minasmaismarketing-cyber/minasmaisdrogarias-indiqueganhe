<?php

declare(strict_types=1);

class SenhaRecuperacao extends Model
{
    public function create(int $usuarioId, string $tokenHash, string $expiresAt): int
    {
        $this->invalidateByUsuario($usuarioId);

        $stmt = $this->db->prepare(
            'INSERT INTO senha_recuperacao (usuario_id, token_hash, expires_at)
             VALUES (:usuario_id, :token_hash, :expires_at)'
        );
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /** @return array<string, mixed>|null */
    public function findValidByToken(string $token): ?array
    {
        $hash = hash('sha256', $token);

        $stmt = $this->db->prepare(
            'SELECT sr.*, u.email, u.nome
             FROM senha_recuperacao sr
             INNER JOIN usuarios u ON u.id = sr.usuario_id
             WHERE sr.token_hash = :token_hash
               AND sr.used_at IS NULL
               AND sr.expires_at > NOW()
             LIMIT 1'
        );
        $stmt->execute(['token_hash' => $hash]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function markUsed(int $id): void
    {
        $stmt = $this->db->prepare(
            'UPDATE senha_recuperacao SET used_at = NOW() WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
    }

    private function invalidateByUsuario(int $usuarioId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE senha_recuperacao SET used_at = NOW()
             WHERE usuario_id = :usuario_id AND used_at IS NULL'
        );
        $stmt->execute(['usuario_id' => $usuarioId]);
    }
}
