<?php

declare(strict_types=1);

/**
 * Fonte única de verdade para métricas administrativas do programa Indique e Ganhe.
 *
 * Conceitos oficiais
 * ------------------
 *
 * Indicação
 *   Um registro na tabela `indicacoes`. Toda métrica de volume parte desta entidade,
 *   independentemente da tela (Dashboard, Usuários, Indicações, Validações, Cupons ou Campanhas).
 *
 * Validação
 *   Registro em `validacao_indicacoes` vinculado a uma indicação. O status administrativo
 *   efetivo de uma indicação usa COALESCE(validacao.status, mapeamento de indicacao.status),
 *   alinhado ao filtro da listagem admin de indicações.
 *
 * Conversão (taxa oficial)
 *   (Aprovadas ÷ Total de Indicações) × 100, arredondada em 2 casas decimais.
 *   Aprovadas = status efetivo APROVADO ou BENEFICIO_LIBERADO.
 *   Denominador = total de indicações no escopo (global, usuário ou campanha).
 *
 * Cupom gerado
 *   Qualquer registro existente na tabela `cupons` no escopo considerado.
 *
 * Cupom utilizado
 *   Registro em `cupons` com status UTILIZADO.
 */
class AdminMetricsService
{
    private const EFFECTIVE_STATUS_SQL = 'COALESCE(
        v.status,
        CASE i.status
            WHEN :st_aguardando THEN :vf_aguardando_cadastro_es1
            WHEN :st_link THEN :vf_aguardando_cadastro_es2
            WHEN :st_cadastro_pendente THEN :vf_aguardando_validacao_es1
            WHEN :st_validado THEN :vf_aprovado_es1
            WHEN :st_premio THEN :vf_aprovado_es2
            WHEN :st_invalido THEN :vf_reprovado_es1
            WHEN :st_expirado THEN :vf_cancelado_es1
            ELSE :vf_aguardando_cadastro_es3
        END
    )';

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** @return array<string, int|float> */
    public function getGlobalMetrics(): array
    {
        $indicacao = $this->fetchIndicacaoMetrics('1=1', []);
        $cupons = $this->fetchCupomMetrics('1=1', []);

        return $this->composeMetricsPayload($indicacao, $cupons);
    }

    /** @return array<string, int|float> */
    public function getMetricsForUsuario(int $usuarioId): array
    {
        $indicacao = $this->fetchIndicacaoMetrics('i.usuario_id = :usuario_id', ['usuario_id' => $usuarioId]);
        $cupons = $this->fetchCupomMetrics('usuario_id = :usuario_id', ['usuario_id' => $usuarioId]);

        $payload = $this->composeMetricsPayload($indicacao, $cupons);

        return [
            'total_indicacoes' => $payload['total_indicacoes'],
            'pendentes' => $payload['pendentes'],
            'em_analise' => $payload['em_analise'],
            'aprovadas' => $payload['aprovadas'],
            'reprovadas' => $payload['reprovadas'],
            'cupons_gerados' => $payload['cupons_gerados'],
            'aguardando_cadastro' => $payload['aguardando_cadastro'],
            'aguardando_validacao' => $payload['aguardando_validacao'],
            'canceladas' => $payload['canceladas'],
            'cupons_utilizados' => $payload['cupons_utilizados'],
            'taxa_conversao' => $payload['taxa_conversao'],
        ];
    }

    /** @return array<string, int|float> */
    public function getMetricsForCampanha(int $campanhaId): array
    {
        $scopeSql = 'i.id IN (
            SELECT DISTINCT c.indicacao_id
            FROM cupons c
            WHERE c.campanha_id = :campanha_id
              AND c.indicacao_id IS NOT NULL
        )';
        $params = ['campanha_id' => $campanhaId];

        $indicacao = $this->fetchIndicacaoMetrics($scopeSql, $params);
        $cupons = $this->fetchCupomMetrics('campanha_id = :campanha_id', $params);
        $validacoes = $this->countValidacoesForCampanha($campanhaId);

        $payload = $this->composeMetricsPayload($indicacao, $cupons);

        return [
            'indicacoes' => $payload['total_indicacoes'],
            'validacoes' => $validacoes,
            'cupons_gerados' => $payload['cupons_gerados'],
            'cupons_utilizados' => $payload['cupons_utilizados'],
            'taxa_conversao' => $payload['taxa_conversao'],
            'aguardando_cadastro' => $payload['aguardando_cadastro'],
            'aguardando_validacao' => $payload['aguardando_validacao'],
            'em_analise' => $payload['em_analise'],
            'aprovadas' => $payload['aprovadas'],
            'reprovadas' => $payload['reprovadas'],
            'canceladas' => $payload['canceladas'],
            'pendentes' => $payload['pendentes'],
        ];
    }

    /**
     * Métricas de status de cupons para cards administrativos.
     *
     * @return array<string, int>
     */
    public function getCupomStatusMetrics(?int $usuarioId = null, ?int $campanhaId = null): array
    {
        $where = '1=1';
        $params = [];

        if ($usuarioId !== null) {
            $where = 'usuario_id = :usuario_id';
            $params['usuario_id'] = $usuarioId;
        } elseif ($campanhaId !== null) {
            $where = 'campanha_id = :campanha_id';
            $params['campanha_id'] = $campanhaId;
        }

        return $this->fetchCupomMetrics($where, $params);
    }

    /**
     * Cards de validação admin — mesmos conceitos de indicação, chaves legadas da view.
     *
     * @return array<string, int>
     */
    public function getValidacaoAdminCardMetrics(): array
    {
        $metrics = $this->fetchIndicacaoMetrics('1=1', []);

        return [
            'pendentes' => $metrics['pendentes'],
            'em_analise' => $metrics['em_analise'],
            'validadas' => $metrics['aprovadas'],
            'invalidadas' => $metrics['reprovadas'],
            'canceladas' => $metrics['canceladas'],
            'aguardando_cadastro' => $metrics['aguardando_cadastro'],
            'aguardando_validacao' => $metrics['aguardando_validacao'],
        ];
    }

    /**
     * Cards do dashboard admin — chaves legadas da view.
     *
     * @return array{
     *     totalIndicacoes: int,
     *     validacaoStats: array<string, int>,
     *     cuponsGerados: int,
     *     taxaConversao: float
     * }
     */
    public function getDashboardPayload(): array
    {
        $metrics = $this->getGlobalMetrics();

        return [
            'totalIndicacoes' => (int) $metrics['total_indicacoes'],
            'validacaoStats' => [
                'pendentes' => (int) $metrics['pendentes'],
                'em_analise' => (int) $metrics['em_analise'],
                'validadas' => (int) $metrics['aprovadas'],
                'invalidadas' => (int) $metrics['reprovadas'],
                'canceladas' => (int) $metrics['canceladas'],
                'aguardando_cadastro' => (int) $metrics['aguardando_cadastro'],
                'aguardando_validacao' => (int) $metrics['aguardando_validacao'],
            ],
            'cuponsGerados' => (int) $metrics['cupons_gerados'],
            'taxaConversao' => (float) $metrics['taxa_conversao'],
        ];
    }

    public static function calculateConversionRate(int $aprovadas, int $totalIndicacoes): float
    {
        if ($totalIndicacoes <= 0) {
            return 0.0;
        }

        return round(($aprovadas / $totalIndicacoes) * 100, 2);
    }

    /** @param array<string, int|float> $indicacao
     *  @param array<string, int> $cupons
     *  @return array<string, int|float>
     */
    private function composeMetricsPayload(array $indicacao, array $cupons): array
    {
        return array_merge($indicacao, [
            'cupons_gerados' => $cupons['cupons_gerados'],
            'cupons_utilizados' => $cupons['cupons_utilizados'],
            'taxa_conversao' => self::calculateConversionRate(
                (int) $indicacao['aprovadas'],
                (int) $indicacao['total_indicacoes']
            ),
        ]);
    }

    /** @return array<string, int> */
    private function fetchIndicacaoMetrics(string $whereSql, array $params): array
    {
        $statusParams = $this->statusBindParams();
        $sql = 'SELECT
                    COUNT(*) AS total_indicacoes,
                    SUM(CASE WHEN admin_status = :vf_aguardando_cadastro_m1 THEN 1 ELSE 0 END) AS aguardando_cadastro,
                    SUM(CASE WHEN admin_status IN (:vf_pendente_m1, :vf_aguardando_validacao_m2) THEN 1 ELSE 0 END) AS aguardando_validacao,
                    SUM(CASE WHEN admin_status = :vf_em_analise_m1 THEN 1 ELSE 0 END) AS em_analise,
                    SUM(CASE WHEN admin_status IN (:vf_aprovado_m3, :vf_beneficio_m1) THEN 1 ELSE 0 END) AS aprovadas,
                    SUM(CASE WHEN admin_status = :vf_reprovado_m2 THEN 1 ELSE 0 END) AS reprovadas,
                    SUM(CASE WHEN admin_status = :vf_cancelado_m2 THEN 1 ELSE 0 END) AS canceladas
                FROM (
                    SELECT ' . self::EFFECTIVE_STATUS_SQL . ' AS admin_status
                    FROM indicacoes i
                    LEFT JOIN validacao_indicacoes v ON v.indicacao_id = i.id
                    WHERE ' . $whereSql . '
                ) scoped';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge($statusParams, $params));
        $row = $stmt->fetch() ?: [];

        $aguardandoCadastro = (int) ($row['aguardando_cadastro'] ?? 0);
        $aguardandoValidacao = (int) ($row['aguardando_validacao'] ?? 0);

        return [
            'total_indicacoes' => (int) ($row['total_indicacoes'] ?? 0),
            'aguardando_cadastro' => $aguardandoCadastro,
            'aguardando_validacao' => $aguardandoValidacao,
            'pendentes' => $aguardandoCadastro + $aguardandoValidacao,
            'em_analise' => (int) ($row['em_analise'] ?? 0),
            'aprovadas' => (int) ($row['aprovadas'] ?? 0),
            'reprovadas' => (int) ($row['reprovadas'] ?? 0),
            'canceladas' => (int) ($row['canceladas'] ?? 0),
        ];
    }

    /** @return array<string, int> */
    private function fetchCupomMetrics(string $whereSql, array $params): array
    {
        $sql = 'SELECT
                    COUNT(*) AS cupons_gerados,
                    SUM(CASE WHEN status = :utilizado_m1 THEN 1 ELSE 0 END) AS cupons_utilizados,
                    SUM(CASE WHEN status = :disponivel THEN 1 ELSE 0 END) AS disponiveis,
                    SUM(CASE WHEN status = :reservado THEN 1 ELSE 0 END) AS reservados,
                    SUM(CASE WHEN status = :utilizado_m2 THEN 1 ELSE 0 END) AS utilizados,
                    SUM(CASE WHEN status = :expirado THEN 1 ELSE 0 END) AS expirados,
                    SUM(CASE WHEN status = :cancelado THEN 1 ELSE 0 END) AS cancelados
                FROM cupons
                WHERE ' . $whereSql;

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge([
            'utilizado_m1' => Cupom::STATUS_UTILIZADO,
            'utilizado_m2' => Cupom::STATUS_UTILIZADO,
            'disponivel' => Cupom::STATUS_DISPONIVEL,
            'reservado' => Cupom::STATUS_RESERVADO,
            'expirado' => Cupom::STATUS_EXPIRADO,
            'cancelado' => Cupom::STATUS_CANCELADO,
        ], $params));
        $row = $stmt->fetch() ?: [];

        return [
            'cupons_gerados' => (int) ($row['cupons_gerados'] ?? 0),
            'cupons_utilizados' => (int) ($row['cupons_utilizados'] ?? 0),
            'total' => (int) ($row['cupons_gerados'] ?? 0),
            'disponiveis' => (int) ($row['disponiveis'] ?? 0),
            'reservados' => (int) ($row['reservados'] ?? 0),
            'utilizados' => (int) ($row['utilizados'] ?? 0),
            'expirados' => (int) ($row['expirados'] ?? 0),
            'cancelados' => (int) ($row['cancelados'] ?? 0),
        ];
    }

    private function countValidacoesForCampanha(int $campanhaId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(DISTINCT v.id) AS total
             FROM validacao_indicacoes v
             INNER JOIN cupons c ON c.indicacao_id = v.indicacao_id
             WHERE c.campanha_id = :campanha_id'
        );
        $stmt->execute(['campanha_id' => $campanhaId]);
        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
    }

    /** @return array<string, string> */
    private function statusBindParams(): array
    {
        $aguardandoCadastro = ValidacaoIndicacao::STATUS_AGUARDANDO_CADASTRO;
        $aguardandoValidacao = ValidacaoIndicacao::STATUS_AGUARDANDO_VALIDACAO;
        $aprovado = ValidacaoIndicacao::STATUS_APROVADO;
        $reprovado = ValidacaoIndicacao::STATUS_REPROVADO;
        $cancelado = ValidacaoIndicacao::STATUS_CANCELADO;

        return [
            'st_aguardando' => Indicacao::STATUS_AGUARDANDO,
            'st_link' => Indicacao::STATUS_LINK_ACESSADO,
            'st_cadastro_pendente' => Indicacao::STATUS_CADASTRO_PENDENTE,
            'st_validado' => Indicacao::STATUS_VALIDADO,
            'st_premio' => Indicacao::STATUS_PREMIO_LIBERADO,
            'st_invalido' => Indicacao::STATUS_INVALIDO,
            'st_expirado' => Indicacao::STATUS_EXPIRADO,
            'vf_aguardando_cadastro_es1' => $aguardandoCadastro,
            'vf_aguardando_cadastro_es2' => $aguardandoCadastro,
            'vf_aguardando_cadastro_es3' => $aguardandoCadastro,
            'vf_aguardando_cadastro_m1' => $aguardandoCadastro,
            'vf_aguardando_validacao_es1' => $aguardandoValidacao,
            'vf_aguardando_validacao_m2' => $aguardandoValidacao,
            'vf_pendente_m1' => ValidacaoIndicacao::STATUS_PENDENTE,
            'vf_em_analise_m1' => ValidacaoIndicacao::STATUS_EM_ANALISE,
            'vf_aprovado_es1' => $aprovado,
            'vf_aprovado_es2' => $aprovado,
            'vf_aprovado_m3' => $aprovado,
            'vf_beneficio_m1' => ValidacaoIndicacao::STATUS_BENEFICIO_LIBERADO,
            'vf_reprovado_es1' => $reprovado,
            'vf_reprovado_m2' => $reprovado,
            'vf_cancelado_es1' => $cancelado,
            'vf_cancelado_m2' => $cancelado,
        ];
    }
}
