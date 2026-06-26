<?php

declare(strict_types=1);

class Clique extends Model
{
    public function register(string $codigo, ?string $ip = null, ?string $userAgent = null): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO cliques (codigo, ip, user_agent)
             VALUES (:codigo, :ip, :user_agent)'
        );
        $stmt->execute([
            'codigo' => strtoupper(trim($codigo)),
            'ip' => $ip,
            'user_agent' => $userAgent,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function countByCodigo(string $codigo): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) as total FROM cliques WHERE codigo = :codigo');
        $stmt->execute(['codigo' => strtoupper(trim($codigo))]);
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function isSpam(string $codigo, ?string $ip = null): bool
    {
        if ($ip === null) {
            return false;
        }

        // Check if same IP clicked more than 10 times in last hour
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) as total 
             FROM cliques 
             WHERE codigo = :codigo 
               AND ip = :ip 
               AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)'
        );
        $stmt->execute([
            'codigo' => strtoupper(trim($codigo)),
            'ip' => $ip,
        ]);
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0) > 10;
    }

    public function getLastCliqueTime(string $codigo, ?string $ip = null): ?string
    {
        $sql = 'SELECT MAX(created_at) as last_click FROM cliques WHERE codigo = :codigo';
        $params = ['codigo' => strtoupper(trim($codigo))];

        if ($ip !== null) {
            $sql .= ' AND ip = :ip';
            $params['ip'] = $ip;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row['last_click'] ?? null;
    }

    public function isDuplicateClick(string $codigo, ?string $ip = null): bool
    {
        if ($ip === null) {
            return false;
        }

        $lastClick = $this->getLastCliqueTime($codigo, $ip);
        if ($lastClick === null) {
            return false;
        }

        // Block if same IP clicked in last 30 seconds
        $lastClickTime = strtotime($lastClick);
        return (time() - $lastClickTime) < 30;
    }
}
