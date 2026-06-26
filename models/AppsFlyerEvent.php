<?php

declare(strict_types=1);

class AppsFlyerEvent extends Model
{
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO appsflyer_events 
             (usuario_id, indicacao_id, appsflyer_id, event_name, event_value, install_type, 
              media_source, campaign, campaign_id, af_status, platform, raw_payload)
             VALUES 
             (:usuario_id, :indicacao_id, :appsflyer_id, :event_name, :event_value, :install_type,
              :media_source, :campaign, :campaign_id, :af_status, :platform, :raw_payload)'
        );
        $stmt->execute([
            'usuario_id' => $data['usuario_id'] ?? null,
            'indicacao_id' => $data['indicacao_id'] ?? null,
            'appsflyer_id' => $data['appsflyer_id'] ?? null,
            'event_name' => $data['event_name'] ?? null,
            'event_value' => $data['event_value'] ?? null,
            'install_type' => $data['install_type'] ?? null,
            'media_source' => $data['media_source'] ?? null,
            'campaign' => $data['campaign'] ?? null,
            'campaign_id' => $data['campaign_id'] ?? null,
            'af_status' => $data['af_status'] ?? AppsFlyerStatus::PENDING->value,
            'platform' => $data['platform'] ?? null,
            'raw_payload' => $data['raw_payload'] ? json_encode($data['raw_payload']) : null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM appsflyer_events WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        
        if ($row && $row['raw_payload']) {
            $row['raw_payload'] = json_decode($row['raw_payload'], true);
        }
        
        return $row ?: null;
    }

    public function findByUsuario(int $usuarioId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM appsflyer_events WHERE usuario_id = :usuario_id ORDER BY created_at DESC'
        );
        $stmt->execute(['usuario_id' => $usuarioId]);
        $events = $stmt->fetchAll();
        
        foreach ($events as &$event) {
            if ($event['raw_payload']) {
                $event['raw_payload'] = json_decode($event['raw_payload'], true);
            }
        }
        
        return $events;
    }

    public function findByIndicacao(int $indicacaoId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM appsflyer_events WHERE indicacao_id = :indicacao_id ORDER BY created_at DESC'
        );
        $stmt->execute(['indicacao_id' => $indicacaoId]);
        $events = $stmt->fetchAll();
        
        foreach ($events as &$event) {
            if ($event['raw_payload']) {
                $event['raw_payload'] = json_decode($event['raw_payload'], true);
            }
        }
        
        return $events;
    }

    public function findByAppsflyerId(string $appsflyerId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM appsflyer_events WHERE appsflyer_id = :appsflyer_id LIMIT 1');
        $stmt->execute(['appsflyer_id' => $appsflyerId]);
        $row = $stmt->fetch();
        
        if ($row && $row['raw_payload']) {
            $row['raw_payload'] = json_decode($row['raw_payload'], true);
        }
        
        return $row ?: null;
    }

    public function findAll(array $filters = []): array
    {
        $sql = 'SELECT * FROM appsflyer_events WHERE 1=1';
        $params = [];

        if (!empty($filters['usuario_id'])) {
            $sql .= ' AND usuario_id = :usuario_id';
            $params['usuario_id'] = $filters['usuario_id'];
        }

        if (!empty($filters['indicacao_id'])) {
            $sql .= ' AND indicacao_id = :indicacao_id';
            $params['indicacao_id'] = $filters['indicacao_id'];
        }

        if (!empty($filters['af_status'])) {
            $sql .= ' AND af_status = :af_status';
            $params['af_status'] = $filters['af_status'];
        }

        if (!empty($filters['platform'])) {
            $sql .= ' AND platform = :platform';
            $params['platform'] = $filters['platform'];
        }

        if (!empty($filters['install_type'])) {
            $sql .= ' AND install_type = :install_type';
            $params['install_type'] = $filters['install_type'];
        }

        if (!empty($filters['data_inicio'])) {
            $sql .= ' AND created_at >= :data_inicio';
            $params['data_inicio'] = $filters['data_inicio'];
        }

        if (!empty($filters['data_fim'])) {
            $sql .= ' AND created_at <= :data_fim';
            $params['data_fim'] = $filters['data_fim'];
        }

        $sql .= ' ORDER BY created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $events = $stmt->fetchAll();
        
        foreach ($events as &$event) {
            if ($event['raw_payload']) {
                $event['raw_payload'] = json_decode($event['raw_payload'], true);
            }
        }
        
        return $events;
    }

    public function updateStatus(int $id, string $status): void
    {
        $stmt = $this->db->prepare(
            'UPDATE appsflyer_events SET af_status = :af_status WHERE id = :id'
        );
        $stmt->execute([
            'af_status' => $status,
            'id' => $id,
        ]);
    }

    public function getStats(): array
    {
        $stmt = $this->db->prepare(
            'SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN af_status = ? THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN af_status = ? THEN 1 ELSE 0 END) as received,
                SUM(CASE WHEN af_status = ? THEN 1 ELSE 0 END) as validated,
                SUM(CASE WHEN af_status = ? THEN 1 ELSE 0 END) as invalid
            FROM appsflyer_events'
        );
        $stmt->execute([
            AppsFlyerStatus::PENDING->value,
            AppsFlyerStatus::RECEIVED->value,
            AppsFlyerStatus::VALIDATED->value,
            AppsFlyerStatus::INVALID->value,
        ]);
        return $stmt->fetch();
    }

    public function countByStatus(string $status): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) as total FROM appsflyer_events WHERE af_status = :af_status'
        );
        $stmt->execute(['af_status' => $status]);
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function listRecent(int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM appsflyer_events ORDER BY created_at DESC LIMIT :limit'
        );
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $events = $stmt->fetchAll();
        
        foreach ($events as &$event) {
            if ($event['raw_payload']) {
                $event['raw_payload'] = json_decode($event['raw_payload'], true);
            }
        }
        
        return $events;
    }
}
