<?php

declare(strict_types=1);

/**
 * Histórico de transições de validação.
 * validacao_id referencia validacao_indicacoes.id no fluxo canônico.
 * Registros antigos (IDs de validacoes) permanecem preservados.
 */
class HistoricoValidacao extends Model
{
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO historico_validacoes (validacao_id, status_anterior, status_novo, descricao, usuario_admin)
             VALUES (:validacao_id, :status_anterior, :status_novo, :descricao, :usuario_admin)'
        );
        $stmt->execute([
            'validacao_id' => $data['validacao_id'],
            'status_anterior' => $data['status_anterior'],
            'status_novo' => $data['status_novo'],
            'descricao' => $data['descricao'] ?? null,
            'usuario_admin' => $data['usuario_admin'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findByValidacao(int $validacaoId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM historico_validacoes WHERE validacao_id = :validacao_id ORDER BY created_at DESC'
        );
        $stmt->execute(['validacao_id' => $validacaoId]);
        return $stmt->fetchAll();
    }

    public function findAll(array $filters = []): array
    {
        $sql = 'SELECT h.*, v.indicacao_id, u.nome as usuario_nome
                FROM historico_validacoes h
                LEFT JOIN validacao_indicacoes v ON h.validacao_id = v.id
                LEFT JOIN usuarios u ON v.usuario_indicador_id = u.id
                WHERE 1=1';
        $params = [];

        if (!empty($filters['validacao_id'])) {
            $sql .= ' AND h.validacao_id = :validacao_id';
            $params['validacao_id'] = $filters['validacao_id'];
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
