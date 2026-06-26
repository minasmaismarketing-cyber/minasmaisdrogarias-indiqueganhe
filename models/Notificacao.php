<?php

declare(strict_types=1);

class Notificacao extends Model
{
    public const TYPE_INFO = 'informacao';
    public const TYPE_SUCCESS = 'sucesso';
    public const TYPE_ALERT = 'alerta';

    public function create(int $usuarioId, string $tipo, string $mensagem, ?string $link = null): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO notificacoes (usuario_id, tipo, mensagem, link)
             VALUES (:usuario_id, :tipo, :mensagem, :link)'
        );
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'tipo' => $tipo,
            'mensagem' => $mensagem,
            'link' => $link,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findByUsuario(int $usuarioId, int $limit = 20): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM notificacoes
             WHERE usuario_id = :usuario_id
             ORDER BY created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue('usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function markAsRead(int $id): void
    {
        $stmt = $this->db->prepare(
            'UPDATE notificacoes SET lida = 1, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
    }

    public function markAllAsRead(int $usuarioId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE notificacoes SET lida = 1, updated_at = NOW() WHERE usuario_id = :usuario_id'
        );
        $stmt->execute(['usuario_id' => $usuarioId]);
    }

    public function countUnread(int $usuarioId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) as total FROM notificacoes WHERE usuario_id = :usuario_id AND lida = 0'
        );
        $stmt->execute(['usuario_id' => $usuarioId]);
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public static function typeLabel(string $tipo): string
    {
        return match ($tipo) {
            self::TYPE_INFO => 'Informação',
            self::TYPE_SUCCESS => 'Sucesso',
            self::TYPE_ALERT => 'Alerta',
            default => $tipo,
        };
    }

    public static function typeIcon(string $tipo): string
    {
        return match ($tipo) {
            self::TYPE_INFO => 'ℹ️',
            self::TYPE_SUCCESS => '✅',
            self::TYPE_ALERT => '⚠️',
            default => '📢',
        };
    }
}
