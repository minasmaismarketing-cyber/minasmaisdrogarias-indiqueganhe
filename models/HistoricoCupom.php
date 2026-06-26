<?php

declare(strict_types=1);

class HistoricoCupom extends Model
{
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO historico_cupons (cupom_id, status_anterior, status_novo, descricao, usuario_admin)
             VALUES (:cupom_id, :status_anterior, :status_novo, :descricao, :usuario_admin)'
        );
        $stmt->execute([
            'cupom_id' => $data['cupom_id'],
            'status_anterior' => $data['status_anterior'],
            'status_novo' => $data['status_novo'],
            'descricao' => $data['descricao'] ?? null,
            'usuario_admin' => $data['usuario_admin'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findByCupom(int $cupomId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM historico_cupons WHERE cupom_id = :cupom_id ORDER BY created_at DESC'
        );
        $stmt->execute(['cupom_id' => $cupomId]);
        return $stmt->fetchAll();
    }

    public function findAll(array $filters = []): array
    {
        $sql = 'SELECT h.*, c.codigo, u.nome as usuario_nome
                FROM historico_cupons h
                LEFT JOIN cupons c ON h.cupom_id = c.id
                LEFT JOIN usuarios u ON c.usuario_id = u.id
                WHERE 1=1';
        $params = [];

        if (!empty($filters['cupom_id'])) {
            $sql .= ' AND h.cupom_id = :cupom_id';
            $params['cupom_id'] = $filters['cupom_id'];
        }

        if (!empty($filters['usuario_admin'])) {
            $sql .= ' AND h.usuario_admin = :usuario_admin';
            $params['usuario_admin'] = $filters['usuario_admin'];
        }

        if (!empty($filters['data_inicio'])) {
            $sql .= ' AND h.created_at >= :data_inicio';
            $params['data_inicio'] = $filters['data_inicio'];
        }

        if (!empty($filters['data_fim'])) {
            $sql .= ' AND h.created_at <= :data_fim';
            $params['data_fim'] = $filters['data_fim'];
        }

        $sql .= ' ORDER BY h.created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
