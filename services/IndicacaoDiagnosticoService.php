<?php

declare(strict_types=1);

/**
 * Consolidação somente leitura do ciclo de vida de uma indicação (Sprint 3.7).
 */
final class IndicacaoDiagnosticoService
{
    private const API_KOBE_ENDPOINT = '/api/indicacao/confirmar-cadastro';

    private Indicacao $indicacaoModel;

    public function __construct()
    {
        $this->indicacaoModel = new Indicacao();
    }

    /** @return array<string, mixed>|null */
    public function buildBySearch(string $term): ?array
    {
        $indicacaoId = $this->findIndicacaoIdByTerm($term);

        if ($indicacaoId === null) {
            return null;
        }

        return $this->buildReport($indicacaoId);
    }

    /** @return array<string, mixed>|null */
    public function buildReport(int $indicacaoId): ?array
    {
        $indicacao = $this->indicacaoModel->findByIdAdmin($indicacaoId);

        if ($indicacao === null) {
            return null;
        }

        $codigoIndicador = strtoupper(trim((string) ($indicacao['codigo_indicador'] ?? '')));
        $telefoneIndicado = Validator::onlyDigits((string) ($indicacao['telefone_indicado'] ?? $indicacao['indicado_whatsapp'] ?? ''));
        $cpfIndicado = preg_replace('/\D/', '', (string) ($indicacao['indicado_cpf'] ?? '')) ?? '';

        $linkRow = $codigoIndicador !== '' ? (new LinkIndicacao())->findByCodigo($codigoIndicador) : null;
        $cliqueRow = $codigoIndicador !== '' ? $this->findFirstClique($codigoIndicador) : null;
        $cliqueLatest = $codigoIndicador !== '' ? $this->findLatestClique($codigoIndicador) : null;

        $indicadorUsuario = [
            'id' => (int) ($indicacao['usuario_id'] ?? 0),
            'nome' => (string) ($indicacao['indicador_nome'] ?? ''),
            'codigo_indicador' => $codigoIndicador,
        ];
        $oneLinkDetails = (new InviteLinkService())->buildInviteLinkDetails($indicadorUsuario);
        $oneLinkGenerated = in_array($oneLinkDetails['type'] ?? '', ['onelink', 'onelink_fallback'], true)
            || str_contains((string) ($oneLinkDetails['url'] ?? ''), 'onelink.me');

        $appsFlyerEvents = (new AppsFlyerEvent())->findByIndicacao($indicacaoId);
        $appsFlyerEvent = $appsFlyerEvents[0] ?? null;

        $apiLog = $this->findKobeApiLog($codigoIndicador, $telefoneIndicado, $cpfIndicado);

        $validacao = null;
        $validacaoHistory = [];
        if (!empty($indicacao['validacao_id'])) {
            $validacao = (new ValidacaoIndicacao())->findById((int) $indicacao['validacao_id']);
            $validacaoHistory = (new HistoricoValidacao())->findByValidacao((int) $indicacao['validacao_id']);
        }

        $cupom = null;
        $campanhaNome = '';
        if (!empty($indicacao['cupom_id'])) {
            $cupom = (new Cupom())->findById((int) $indicacao['cupom_id']);
            $campanhaNomes = $this->indicacaoModel->findCampanhaNomesByIndicacaoIds([$indicacaoId]);
            $campanhaNome = $campanhaNomes[$indicacaoId] ?? '';
        }

        $indicadoEmail = $this->resolveIndicadoEmail($validacao, $apiLog);

        $adminStatus = (string) ($indicacao['admin_status'] ?? Indicacao::resolveAdminStatus($indicacao));
        $statusIndicacao = (string) ($indicacao['status'] ?? '');

        $linkAccessed = $cliqueRow !== null
            || in_array($statusIndicacao, [
                Indicacao::STATUS_LINK_ACESSADO,
                Indicacao::STATUS_CADASTRO_PENDENTE,
                Indicacao::STATUS_VALIDADO,
                Indicacao::STATUS_PREMIO_LIBERADO,
            ], true);

        $landingAccessed = $linkAccessed;

        $cadastroConcluido = trim((string) ($indicacao['nome_indicado'] ?? '')) !== ''
            || in_array($statusIndicacao, [
                Indicacao::STATUS_CADASTRO_PENDENTE,
                Indicacao::STATUS_VALIDADO,
                Indicacao::STATUS_PREMIO_LIBERADO,
            ], true);

        $validationAdmin = $this->resolveValidationAdmin($validacaoHistory);

        $steps = [
            $this->buildStep(
                'compartilhamento',
                'Compartilhamento',
                'Concluído',
                'done',
                (string) ($indicacao['created_at'] ?? ''),
                'ReferralService',
                'Registro criado ao compartilhar ou iniciar fluxo.',
                [
                    ['label' => 'Código', 'value' => (string) ($indicacao['codigo_referencia'] ?? '—')],
                    ['label' => 'Indicador', 'value' => (string) ($indicacao['indicador_nome'] ?? '—')],
                    ['label' => 'Código indicador', 'value' => $codigoIndicador !== '' ? $codigoIndicador : '—'],
                ]
            ),
            $this->buildStep(
                'link_acessado',
                'Link acessado',
                $linkAccessed ? 'Sim' : 'Não',
                $linkAccessed ? 'done' : 'missing',
                (string) ($cliqueLatest['created_at'] ?? ($linkAccessed ? ($indicacao['updated_at'] ?? '') : '')),
                'links_indicacao',
                $linkAccessed ? 'Clique registrado na landing.' : 'Nenhum clique registrado para este código.',
                array_filter([
                    ['label' => 'Data', 'value' => $this->formatDateTime($cliqueLatest['created_at'] ?? ($indicacao['updated_at'] ?? null))],
                    $cliqueLatest !== null && !empty($cliqueLatest['ip'])
                        ? ['label' => 'IP', 'value' => (string) $cliqueLatest['ip']]
                        : null,
                    $linkRow !== null
                        ? ['label' => 'Cliques totais', 'value' => (string) ($linkRow['cliques'] ?? '0')]
                        : null,
                ])
            ),
            $this->buildStep(
                'landing',
                'Landing',
                $landingAccessed ? 'Acessada' : 'Não acessada',
                $landingAccessed ? 'done' : 'missing',
                (string) ($cliqueLatest['created_at'] ?? ($indicacao['updated_at'] ?? '')),
                '/convite',
                $landingAccessed ? 'Landing de convite acessada.' : 'Sem registro de acesso à landing.',
                [
                    ['label' => 'Data', 'value' => $this->formatDateTime($cliqueLatest['created_at'] ?? ($indicacao['updated_at'] ?? null))],
                    ['label' => 'URL', 'value' => url('/convite?ref=' . rawurlencode($codigoIndicador))],
                ]
            ),
            $this->buildStep(
                'onelink',
                'OneLink',
                $oneLinkGenerated ? 'Gerado' : 'Não gerado',
                $oneLinkGenerated ? 'done' : 'missing',
                (string) ($indicacao['created_at'] ?? ''),
                'InviteLinkService',
                $oneLinkGenerated ? 'URL OneLink disponível para o indicador.' : 'Fluxo WEB (OneLink desabilitado ou indisponível).',
                [
                    ['label' => 'URL', 'value' => (string) ($oneLinkDetails['url'] ?? '—')],
                    ['label' => 'Tipo', 'value' => (string) ($oneLinkDetails['type'] ?? '—')],
                ]
            ),
            $this->buildStep(
                'appsflyer',
                'Evento AppsFlyer',
                $appsFlyerEvent !== null ? 'Recebido' : 'Não recebido',
                $appsFlyerEvent !== null ? 'done' : 'missing',
                (string) ($appsFlyerEvent['created_at'] ?? ''),
                'appsflyer_events',
                $appsFlyerEvent !== null ? 'Metadados AppsFlyer persistidos.' : 'Nenhum evento vinculado à indicação.',
                array_filter([
                    ['label' => 'appsflyer_id', 'value' => (string) ($appsFlyerEvent['appsflyer_id'] ?? '—')],
                    ['label' => 'media source', 'value' => (string) ($appsFlyerEvent['media_source'] ?? '—')],
                    ['label' => 'campaign', 'value' => (string) ($appsFlyerEvent['campaign'] ?? '—')],
                    ['label' => 'platform', 'value' => (string) ($appsFlyerEvent['platform'] ?? '—')],
                    ['label' => 'install type', 'value' => (string) ($appsFlyerEvent['install_type'] ?? '—')],
                ])
            ),
            $this->buildStep(
                'cadastro',
                'Cadastro',
                $cadastroConcluido ? 'Concluído' : 'Pendente',
                $cadastroConcluido ? 'done' : 'pending',
                (string) ($indicacao['updated_at'] ?? ''),
                'indicacoes',
                $cadastroConcluido ? 'Dados do indicado registrados.' : 'Aguardando cadastro do indicado.',
                array_filter([
                    ['label' => 'Nome', 'value' => trim((string) ($indicacao['nome_indicado'] ?? '')) !== '' ? (string) $indicacao['nome_indicado'] : '—'],
                    ['label' => 'CPF', 'value' => $cpfIndicado !== '' ? format_cpf($cpfIndicado) : '—'],
                    ['label' => 'Telefone', 'value' => $telefoneIndicado !== '' ? format_phone($telefoneIndicado) : '—'],
                    ['label' => 'E-mail', 'value' => $indicadoEmail !== '' ? $indicadoEmail : '—'],
                ])
            ),
            $this->buildStep(
                'api_kobe',
                'API KOBE',
                $apiLog !== null ? 'Recebida' : 'Não recebida',
                $apiLog !== null ? 'done' : 'missing',
                (string) ($apiLog['created_at'] ?? ''),
                'api_logs',
                $apiLog !== null ? 'Chamada ao endpoint de confirmação registrada.' : 'Nenhuma chamada API KOBE correlacionada.',
                array_filter([
                    ['label' => 'Endpoint', 'value' => self::API_KOBE_ENDPOINT],
                    ['label' => 'Horário', 'value' => $this->formatDateTime($apiLog['created_at'] ?? null)],
                    $apiLog !== null
                        ? ['label' => 'HTTP', 'value' => (string) ($apiLog['status_code'] ?? '—')]
                        : null,
                ])
            ),
            $this->buildStep(
                'validacao',
                'Validação',
                ValidacaoIndicacao::statusLabel($adminStatus),
                in_array($adminStatus, [
                    ValidacaoIndicacao::STATUS_APROVADO,
                    ValidacaoIndicacao::STATUS_BENEFICIO_LIBERADO,
                ], true) ? 'done' : (
                    in_array($adminStatus, [
                        ValidacaoIndicacao::STATUS_REPROVADO,
                        ValidacaoIndicacao::STATUS_CANCELADO,
                    ], true) ? 'missing' : 'pending'
                ),
                (string) ($validacao['updated_at'] ?? $indicacao['updated_at'] ?? ''),
                'validacao_indicacoes',
                $validacao !== null && !empty($validacao['motivo'])
                    ? (string) $validacao['motivo']
                    : '—',
                array_filter([
                    ['label' => 'Status efetivo', 'value' => Indicacao::adminStatusLabel($adminStatus)],
                    ['label' => 'Responsável', 'value' => $validationAdmin !== '' ? $validationAdmin : '—'],
                    ['label' => 'Data', 'value' => $this->formatDateTime($validacao['updated_at'] ?? null)],
                    $validacao !== null && !empty($validacao['motivo'])
                        ? ['label' => 'Motivo', 'value' => ValidacaoIndicacao::motivoLabel((string) $validacao['motivo'])]
                        : null,
                ])
            ),
            $this->buildStep(
                'cupom',
                'Cupom',
                $cupom !== null ? Cupom::statusLabel((string) $cupom['status']) : 'Não gerado',
                $cupom !== null ? 'done' : 'missing',
                (string) ($cupom['created_at'] ?? ''),
                'cupons',
                $cupom !== null ? 'Cupom vinculado à indicação.' : 'Nenhum cupom gerado.',
                array_filter([
                    ['label' => 'Código', 'value' => mask_cupom_codigo((string) ($cupom['codigo'] ?? ''))],
                    ['label' => 'Campanha', 'value' => $campanhaNome !== '' ? $campanhaNome : '—'],
                    $cupom !== null && (string) ($cupom['status'] ?? '') === Cupom::STATUS_UTILIZADO
                        ? ['label' => 'Utilização', 'value' => $this->formatDateTime($cupom['updated_at'] ?? null)]
                        : null,
                ])
            ),
        ];

        $completedSteps = count(array_filter($steps, static fn (array $step): bool => ($step['kind'] ?? '') === 'done'));

        return [
            'indicacao' => $indicacao,
            'search_term' => null,
            'summary' => [
                'completed_steps' => $completedSteps,
                'total_steps' => count($steps),
                'current_status' => Indicacao::adminStatusLabel($adminStatus),
                'current_status_key' => $adminStatus,
                'since_created' => $this->humanSince((string) ($indicacao['created_at'] ?? '')),
                'last_update' => $this->formatDateTime($indicacao['updated_at'] ?? null),
            ],
            'side_panel' => [
                'indicador' => (string) ($indicacao['indicador_nome'] ?? '—'),
                'indicado' => (string) ($indicacao['indicado_nome'] ?? 'Aguardando cadastro'),
                'campanha' => $campanhaNome !== '' ? $campanhaNome : '—',
                'origem' => (string) ($indicacao['origem'] ?? '—'),
                'appsflyer_id' => (string) ($appsFlyerEvent['appsflyer_id'] ?? '—'),
                'codigo_indicacao' => (string) ($indicacao['codigo_referencia'] ?? $codigoIndicador ?: '—'),
                'codigo_cupom' => $cupom !== null ? mask_cupom_codigo((string) ($cupom['codigo'] ?? '')) : '—',
            ],
            'steps' => $steps,
            'technical_logs' => [
                'api_logs' => $apiLog !== null ? [$apiLog] : $this->findKobeApiLogsForDisplay($codigoIndicador, $telefoneIndicado, $cpfIndicado),
                'appsflyer_events' => $appsFlyerEvents,
            ],
        ];
    }

    private function findIndicacaoIdByTerm(string $term): ?int
    {
        $term = trim($term);

        if ($term === '') {
            return null;
        }

        if (ctype_digit($term)) {
            $row = $this->indicacaoModel->findById((int) $term);

            return $row !== null ? (int) $row['id'] : null;
        }

        $upper = strtoupper($term);
        $digits = preg_replace('/\D/', '', $term) ?? '';
        $email = strtolower($term);

        $db = Database::getConnection();

        $stmt = $db->prepare(
            'SELECT i.id
             FROM indicacoes i
             WHERE UPPER(i.codigo_referencia) = :ref
             ORDER BY i.created_at DESC
             LIMIT 1'
        );
        $stmt->execute(['ref' => $upper]);
        $id = $stmt->fetchColumn();
        if ($id !== false) {
            return (int) $id;
        }

        $stmt = $db->prepare(
            'SELECT i.id
             FROM indicacoes i
             INNER JOIN usuarios u ON u.id = i.usuario_id
             WHERE UPPER(u.codigo_indicador) = :codigo
                OR UPPER(i.codigo_indicador) = :codigo_ind
             ORDER BY i.created_at DESC
             LIMIT 1'
        );
        $stmt->execute(['codigo' => $upper, 'codigo_ind' => $upper]);
        $id = $stmt->fetchColumn();
        if ($id !== false) {
            return (int) $id;
        }

        if ($digits !== '' && strlen($digits) === 11) {
            $stmt = $db->prepare(
                'SELECT i.id
                 FROM indicacoes i
                 LEFT JOIN validacao_indicacoes v ON v.indicacao_id = i.id
                 LEFT JOIN usuarios u ON u.id = v.usuario_indicado_id
                 WHERE REPLACE(REPLACE(REPLACE(u.cpf, ".", ""), "-", ""), " ", "") = :cpf
                    OR REPLACE(REPLACE(REPLACE(i.telefone_indicado, "(", ""), ")", ""), "-", "") LIKE :tel
                 ORDER BY i.created_at DESC
                 LIMIT 1'
            );
            $stmt->execute(['cpf' => $digits, 'tel' => '%' . $digits . '%']);
            $id = $stmt->fetchColumn();
            if ($id !== false) {
                return (int) $id;
            }
        }

        if (str_contains($term, '@')) {
            $stmt = $db->prepare(
                'SELECT i.id
                 FROM indicacoes i
                 LEFT JOIN validacao_indicacoes v ON v.indicacao_id = i.id
                 LEFT JOIN usuarios u ON u.id = v.usuario_indicado_id
                 WHERE LOWER(u.email) = :email
                 ORDER BY i.created_at DESC
                 LIMIT 1'
            );
            $stmt->execute(['email' => $email]);
            $id = $stmt->fetchColumn();
            if ($id !== false) {
                return (int) $id;
            }
        }

        if ($digits !== '' && strlen($digits) >= 10) {
            $stmt = $db->prepare(
                'SELECT i.id
                 FROM indicacoes i
                 WHERE REPLACE(REPLACE(REPLACE(REPLACE(i.telefone_indicado, "(", ""), ")", ""), "-", ""), " ", "") LIKE :tel
                 ORDER BY i.created_at DESC
                 LIMIT 1'
            );
            $stmt->execute(['tel' => '%' . $digits . '%']);
            $id = $stmt->fetchColumn();
            if ($id !== false) {
                return (int) $id;
            }
        }

        $stmt = $db->prepare(
            'SELECT i.id
             FROM indicacoes i
             INNER JOIN usuarios u ON u.id = i.usuario_id
             WHERE u.nome LIKE :nome
             ORDER BY i.created_at DESC
             LIMIT 1'
        );
        $stmt->execute(['nome' => '%' . $term . '%']);
        $id = $stmt->fetchColumn();

        return $id !== false ? (int) $id : null;
    }

    /** @return array<string, mixed>|null */
    private function findFirstClique(string $codigo): ?array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT * FROM cliques WHERE codigo = :codigo ORDER BY created_at ASC LIMIT 1'
        );
        $stmt->execute(['codigo' => strtoupper(trim($codigo))]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /** @return array<string, mixed>|null */
    private function findLatestClique(string $codigo): ?array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT * FROM cliques WHERE codigo = :codigo ORDER BY created_at DESC LIMIT 1'
        );
        $stmt->execute(['codigo' => strtoupper(trim($codigo))]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /** @return array<string, mixed>|null */
    private function findKobeApiLog(string $codigoIndicador, string $telefone, string $cpf): ?array
    {
        $logs = $this->findKobeApiLogsForDisplay($codigoIndicador, $telefone, $cpf);

        return $logs[0] ?? null;
    }

    /** @return list<array<string, mixed>> */
    private function findKobeApiLogsForDisplay(string $codigoIndicador, string $telefone, string $cpf): array
    {
        if ($codigoIndicador === '' && $telefone === '' && $cpf === '') {
            return [];
        }

        $conditions = ['endpoint = :endpoint'];
        $params = ['endpoint' => self::API_KOBE_ENDPOINT];

        if ($codigoIndicador !== '') {
            $conditions[] = 'payload LIKE :codigo';
            $params['codigo'] = '%' . $codigoIndicador . '%';
        } elseif ($telefone !== '') {
            $conditions[] = 'payload LIKE :telefone';
            $params['telefone'] = '%' . $telefone . '%';
        } elseif ($cpf !== '') {
            $conditions[] = 'payload LIKE :cpf';
            $params['cpf'] = '%' . $cpf . '%';
        }

        $stmt = Database::getConnection()->prepare(
            'SELECT * FROM api_logs
             WHERE ' . implode(' AND ', $conditions) . '
             ORDER BY created_at DESC
             LIMIT 5'
        );
        $stmt->execute($params);
        $logs = $stmt->fetchAll();

        foreach ($logs as &$log) {
            if (!empty($log['payload'])) {
                $log['payload'] = json_decode((string) $log['payload'], true);
            }
            if (!empty($log['response'])) {
                $log['response'] = json_decode((string) $log['response'], true);
            }
        }

        return $logs;
    }

    /** @param array<string, mixed>|null $validacao
     *  @param array<string, mixed>|null $apiLog
     */
    private function resolveIndicadoEmail(?array $validacao, ?array $apiLog): string
    {
        if ($validacao !== null && !empty($validacao['usuario_indicado_id'])) {
            $usuario = (new Usuario())->findById((int) $validacao['usuario_indicado_id']);
            if ($usuario !== null && !empty($usuario['email'])) {
                return (string) $usuario['email'];
            }
        }

        if ($apiLog !== null && is_array($apiLog['payload'] ?? null)) {
            $payload = $apiLog['payload'];
            $email = trim((string) ($payload['emailIndicado'] ?? $payload['email'] ?? ''));

            if ($email !== '') {
                return $email;
            }
        }

        return '';
    }

    /** @param list<array<string, mixed>> $history */
    private function resolveValidationAdmin(array $history): string
    {
        foreach ($history as $item) {
            if (!empty($item['usuario_admin'])) {
                return (string) $item['usuario_admin'];
            }
        }

        return '';
    }

    /**
     * @param list<array{label: string, value: string}|null> $data
     * @return array<string, mixed>
     */
    private function buildStep(
        string $key,
        string $title,
        string $status,
        string $kind,
        string $datetime,
        string $origin,
        string $observations,
        array $data
    ): array {
        $data = array_values(array_filter($data));

        return [
            'key' => $key,
            'title' => $title,
            'status' => $status,
            'kind' => $kind,
            'datetime' => $this->formatDateTime($datetime !== '' ? $datetime : null),
            'origin' => $origin,
            'observations' => $observations,
            'data' => $data,
        ];
    }

    private function formatDateTime(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return '—';
        }

        $timestamp = strtotime($value);

        return $timestamp !== false ? date('d/m/Y H:i:s', $timestamp) : '—';
    }

    private function humanSince(string $createdAt): string
    {
        if ($createdAt === '') {
            return '—';
        }

        $timestamp = strtotime($createdAt);

        if ($timestamp === false) {
            return '—';
        }

        $diff = time() - $timestamp;

        if ($diff < 60) {
            return 'Há menos de 1 minuto';
        }

        if ($diff < 3600) {
            $mins = (int) floor($diff / 60);

            return 'Há ' . $mins . ' minuto' . ($mins > 1 ? 's' : '');
        }

        if ($diff < 86400) {
            $hours = (int) floor($diff / 3600);

            return 'Há ' . $hours . ' hora' . ($hours > 1 ? 's' : '');
        }

        $days = (int) floor($diff / 86400);

        return 'Há ' . $days . ' dia' . ($days > 1 ? 's' : '');
    }
}
