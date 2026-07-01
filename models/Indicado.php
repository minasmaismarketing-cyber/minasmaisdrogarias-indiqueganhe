<?php

declare(strict_types=1);

/**
 * Leitura legada da tabela `indicados`.
 *
 * Mantido apenas para compatibilidade do Dashboard (dados históricos).
 * Novas indicações: use ReferralService + tabela `indicacoes`.
 */
class Indicado extends Model
{
    public const STATUS_LINK_ACESSADO = 'LINK_ACESSADO';
    public const STATUS_CADASTRO_INICIADO = 'CADASTRO_INICIADO';
    public const STATUS_CADASTRO_CONCLUIDO = 'CADASTRO_CONCLUIDO';
    public const STATUS_AGUARDANDO_VALIDACAO = 'AGUARDANDO_VALIDACAO';
    public const STATUS_VALIDADO = 'VALIDADO';
    public const STATUS_INVALIDADO = 'INVALIDADO';

    /** @return array<int, array<string, mixed>> */
    public function listByIndicador(string $codigoIndicador, int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM indicados
             WHERE codigo_indicador = :codigo_indicador
             ORDER BY created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue('codigo_indicador', $codigoIndicador);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
