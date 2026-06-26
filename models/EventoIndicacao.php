<?php

declare(strict_types=1);

class EventoIndicacao extends Model
{
    public const EVENTO_CLIQUE = 'clique';
    public const EVENTO_CADASTRO = 'cadastro';
    public const EVENTO_VALIDACAO = 'validacao';
    public const EVENTO_BENEFICIO = 'beneficio';
    public const EVENTO_SHARE = 'share';

    public function register(int $usuarioId, string $evento, ?array $dados = null): int
    {
        $dadosJson = $dados !== null ? json_encode($dados) : null;
        
        $stmt = $this->db->prepare(
            'INSERT INTO eventos_indicacao (usuario_id, evento, dados)
             VALUES (:usuario_id, :evento, :dados)'
        );
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'evento' => $evento,
            'dados' => $dadosJson,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findByUsuario(int $usuarioId, int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM eventos_indicacao
             WHERE usuario_id = :usuario_id
             ORDER BY created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue('usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countByUsuario(int $usuarioId, string $evento): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) as total 
             FROM eventos_indicacao 
             WHERE usuario_id = :usuario_id AND evento = :evento'
        );
        $stmt->execute(['usuario_id' => $usuarioId, 'evento' => $evento]);
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function countByEvento(string $evento): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) as total FROM eventos_indicacao WHERE evento = :evento'
        );
        $stmt->execute(['evento' => $evento]);
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public static function eventoLabel(string $evento): string
    {
        return match ($evento) {
            self::EVENTO_CLIQUE => 'Clique',
            self::EVENTO_CADASTRO => 'Cadastro',
            self::EVENTO_VALIDACAO => 'Validação',
            self::EVENTO_BENEFICIO => 'Benefício',
            self::EVENTO_SHARE => 'Compartilhamento',
            default => $evento,
        };
    }
}
