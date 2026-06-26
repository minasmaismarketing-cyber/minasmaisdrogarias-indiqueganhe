<?php

declare(strict_types=1);

class LinkIndicacao extends Model
{
    public function findByUsuario(int $usuarioId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM links_indicacao WHERE usuario_id = :usuario_id LIMIT 1');
        $stmt->execute(['usuario_id' => $usuarioId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByCodigo(string $codigo): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM links_indicacao WHERE codigo = :codigo LIMIT 1');
        $stmt->execute(['codigo' => strtoupper(trim($codigo))]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function createForUser(int $usuarioId, string $codigo): int
    {
        $slug = $this->generateSlug();
        $url = url('/convite?ref=' . $codigo);

        $stmt = $this->db->prepare(
            'INSERT INTO links_indicacao (usuario_id, codigo, slug, url)
             VALUES (:usuario_id, :codigo, :slug, :url)'
        );
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'codigo' => $codigo,
            'slug' => $slug,
            'url' => $url,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function incrementCliques(string $codigo): void
    {
        $stmt = $this->db->prepare(
            'UPDATE links_indicacao 
             SET cliques = cliques + 1, updated_at = NOW() 
             WHERE codigo = :codigo'
        );
        $stmt->execute(['codigo' => $codigo]);
    }

    public function getUltimoAcesso(string $codigo): ?string
    {
        $stmt = $this->db->prepare(
            'SELECT MAX(created_at) as ultimo_acesso 
             FROM cliques 
             WHERE codigo = :codigo'
        );
        $stmt->execute(['codigo' => $codigo]);
        $row = $stmt->fetch();
        return $row['ultimo_acesso'] ?? null;
    }

    public function getStatsByUsuario(int $usuarioId): array
    {
        $link = $this->findByUsuario($usuarioId);

        if ($link === null) {
            return [
                'cliques' => 0,
                'ultimo_acesso' => null,
                'ativo' => false,
            ];
        }

        return [
            'cliques' => (int) $link['cliques'],
            'ultimo_acesso' => $this->getUltimoAcesso((string) $link['codigo']),
            'ativo' => (bool) $link['ativo'],
        ];
    }

    private function generateSlug(): string
    {
        return strtolower(bin2hex(random_bytes(10)));
    }

    public function codigoExists(string $codigo): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM links_indicacao WHERE codigo = :codigo LIMIT 1');
        $stmt->execute(['codigo' => $codigo]);
        return (bool) $stmt->fetch();
    }
}
