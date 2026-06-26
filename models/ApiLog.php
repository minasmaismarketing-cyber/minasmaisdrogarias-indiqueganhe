<?php

declare(strict_types=1);

class ApiLog extends Model
{
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO api_logs (endpoint, payload, response, ip, status_code)
             VALUES (:endpoint, :payload, :response, :ip, :status_code)'
        );
        $stmt->execute([
            'endpoint' => $data['endpoint'],
            'payload' => $data['payload'] ? json_encode($data['payload']) : null,
            'response' => $data['response'] ? json_encode($data['response']) : null,
            'ip' => $data['ip'] ?? null,
            'status_code' => $data['status_code'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findByEndpoint(string $endpoint, int $limit = 100): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM api_logs 
             WHERE endpoint = :endpoint 
             ORDER BY created_at DESC 
             LIMIT :limit'
        );
        $stmt->bindValue('endpoint', $endpoint);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $logs = $stmt->fetchAll();

        // Decode payload and response
        foreach ($logs as &$log) {
            if ($log['payload']) {
                $log['payload'] = json_decode($log['payload'], true);
            }
            if ($log['response']) {
                $log['response'] = json_decode($log['response'], true);
            }
        }

        return $logs;
    }

    public function findByIp(string $ip, int $limit = 100): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM api_logs 
             WHERE ip = :ip 
             ORDER BY created_at DESC 
             LIMIT :limit'
        );
        $stmt->bindValue('ip', $ip);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $logs = $stmt->fetchAll();

        // Decode payload and response
        foreach ($logs as &$log) {
            if ($log['payload']) {
                $log['payload'] = json_decode($log['payload'], true);
            }
            if ($log['response']) {
                $log['response'] = json_decode($log['response'], true);
            }
        }

        return $logs;
    }

    public function listRecent(int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM api_logs 
             ORDER BY created_at DESC 
             LIMIT :limit'
        );
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $logs = $stmt->fetchAll();

        // Decode payload and response
        foreach ($logs as &$log) {
            if ($log['payload']) {
                $log['payload'] = json_decode($log['payload'], true);
            }
            if ($log['response']) {
                $log['response'] = json_decode($log['response'], true);
            }
        }

        return $logs;
    }

    public function countByEndpoint(string $endpoint): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) as total FROM api_logs WHERE endpoint = :endpoint'
        );
        $stmt->execute(['endpoint' => $endpoint]);
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function countByIp(string $ip): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) as total FROM api_logs WHERE ip = :ip'
        );
        $stmt->execute(['ip' => $ip]);
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }
}
