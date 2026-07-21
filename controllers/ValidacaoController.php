<?php

declare(strict_types=1);

class ValidacaoController extends Controller
{
    private ValidacaoIndicacao $validacaoModel;
    private HistoricoValidacao $historicoModel;

    public function __construct()
    {
        $this->validacaoModel = new ValidacaoIndicacao();
        $this->historicoModel = new HistoricoValidacao();
    }

    public function adminIndex(): void
    {
        Auth::requireAdmin();

        $filters = [
            'status' => $_GET['status'] ?? '',
            'cpf' => $_GET['cpf'] ?? '',
            'nome' => $_GET['nome'] ?? '',
            'email' => $_GET['email'] ?? '',
            'whatsapp' => $_GET['whatsapp'] ?? '',
            'data_inicio' => $_GET['data_inicio'] ?? '',
            'data_fim' => $_GET['data_fim'] ?? '',
        ];

        $validacoes = $this->enrichValidacoesForAdminList($this->validacaoModel->findAll($filters));
        $stats = (new AdminMetricsService())->getValidacaoAdminCardMetrics();

        $this->view('admin.validacoes', [
            'title' => 'Validação de Indicações',
            'validacoes' => $validacoes,
            'stats' => $stats,
            'filters' => $filters,
        ], 'admin');
    }

    public function show(int $id): void
    {
        Auth::requireAdmin();

        $validacao = $this->validacaoModel->findById($id);
        if ($validacao === null) {
            Session::flash('error', 'Validação não encontrada.');
            $this->redirect('/admin/validacoes');
        }

        $history = $this->historicoModel->findByValidacao($id);
        $indicacao = null;
        $indicador = null;
        $indicado = null;
        $cupom = null;

        $indicacaoId = (int) ($validacao['indicacao_id'] ?? 0);
        if ($indicacaoId > 0) {
            $indicacao = (new Indicacao())->findById($indicacaoId);
            $cupom = (new Cupom())->findByIndicacao($indicacaoId);
        }

        $indicadorId = (int) ($validacao['usuario_indicador_id'] ?? 0);
        if ($indicadorId > 0) {
            $indicador = (new Usuario())->findById($indicadorId);
        }

        $indicadoId = (int) ($validacao['usuario_indicado_id'] ?? 0);
        if ($indicadoId > 0) {
            $indicado = (new Usuario())->findById($indicadoId);
        }

        $campanhaNome = '—';
        if ($cupom !== null && !empty($cupom['campanha_id'])) {
            $campanha = (new Campanha())->findById((int) $cupom['campanha_id']);
            $campanhaNome = (string) ($campanha['nome'] ?? '—');
        } elseif (($campanhaAtiva = (new Campanha())->findActive()) !== null) {
            $campanhaNome = (string) ($campanhaAtiva['nome'] ?? '—');
        }

        $timeline = $this->buildAdminTimeline($validacao, $indicacao, $history, $cupom);

        $this->view('admin.validacao-view', [
            'title' => 'Detalhes da Validação',
            'validacao' => $validacao,
            'indicacao' => $indicacao,
            'indicador' => $indicador,
            'indicado' => $indicado,
            'cupom' => $cupom,
            'campanhaNome' => $campanhaNome,
            'history' => $history,
            'timeline' => $timeline,
        ], 'admin');
    }

    public function start(): void
    {
        Auth::requireAdmin();

        if (!Csrf::validateRequest()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/validacoes');
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id === 0) {
            Session::flash('error', 'ID inválido.');
            $this->redirect('/admin/validacoes');
        }

        $user = Auth::user();
        $adminEmail = $user !== null ? $user['email'] : null;

        if ($this->validacaoModel->startReview($id, $adminEmail)) {
            Session::flash('success', 'Validação iniciada com sucesso.');
        } else {
            Session::flash('error', 'Erro ao iniciar validação.');
        }

        $this->redirect('/admin/validacoes');
    }

    public function approve(): void
    {
        Auth::requireAdmin();

        if (!Csrf::validateRequest()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/validacoes');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $observacao = $_POST['observacao'] ?? null;

        if ($id === 0) {
            Session::flash('error', 'ID inválido.');
            $this->redirect('/admin/validacoes');
        }

        $user = Auth::user();
        $adminEmail = $user !== null ? $user['email'] : null;

        try {
            if ($this->validacaoModel->approve($id, $observacao, $adminEmail)) {
                $validacao = $this->validacaoModel->findById($id);
                if ($validacao !== null && ValidacaoIndicacao::canReleasePendingBenefit($validacao)) {
                    Session::flash('success', 'Validação aprovada. Indicação aprovada, mas sem cupom disponível.');
                } else {
                    Session::flash('success', 'Validação aprovada com sucesso. Cupom 10% liberado ao indicador.');
                }
            } else {
                Session::flash('error', 'Erro ao aprovar validação.');
            }
        } catch (Throwable $e) {
            $message = $e->getMessage();
            if (str_contains($message, 'Não há cupons disponíveis')) {
                Session::flash('error', 'Não há cupons disponíveis para esta campanha.');
            } else {
                Session::flash('error', 'Erro ao aprovar validação: não foi possível liberar o cupom.');
            }
        }

        $this->redirect('/admin/validacoes');
    }

    public function liberarBeneficio(): void
    {
        Auth::requireAdmin();

        if (!Csrf::validateRequest()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/validacoes');
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id === 0) {
            Session::flash('error', 'ID inválido.');
            $this->redirect('/admin/validacoes');
        }

        $user = Auth::user();
        $adminEmail = $user !== null ? $user['email'] : null;

        try {
            if ($this->validacaoModel->releasePendingBenefit($id, $adminEmail)) {
                Session::flash('success', 'Cupom liberado com sucesso ao indicador.');
            } else {
                Session::flash('error', 'Não foi possível liberar o benefício pendente.');
            }
        } catch (Throwable $e) {
            $message = $e->getMessage();
            if (str_contains($message, 'Não há cupons disponíveis')) {
                Session::flash('error', 'Ainda não há cupons disponíveis para esta campanha.');
            } else {
                Session::flash('error', 'Erro ao liberar benefício: ' . $message);
            }
        }

        $this->redirect('/admin/validacoes/' . $id);
    }

    public function reject(): void
    {
        Auth::requireAdmin();

        if (!Csrf::validateRequest()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/validacoes');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? '');

        if ($id === 0) {
            Session::flash('error', 'ID inválido.');
            $this->redirect('/admin/validacoes');
        }

        if ($motivo === '') {
            Session::flash('error', 'Informe o motivo da rejeição.');
            $this->redirect('/admin/validacoes');
        }

        $user = Auth::user();
        $adminEmail = $user !== null ? $user['email'] : null;

        if ($this->validacaoModel->reject($id, $motivo, $adminEmail)) {
            Session::flash('success', 'Validação rejeitada com sucesso.');
        } else {
            Session::flash('error', 'Erro ao rejeitar validação.');
        }

        $this->redirect('/admin/validacoes');
    }

    public function cancel(): void
    {
        Auth::requireAdmin();

        if (!Csrf::validateRequest()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/validacoes');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $motivo = $_POST['motivo'] ?? null;

        if ($id === 0) {
            Session::flash('error', 'ID inválido.');
            $this->redirect('/admin/validacoes');
        }

        $user = Auth::user();
        $adminEmail = $user !== null ? $user['email'] : null;

        if ($this->validacaoModel->cancel($id, $motivo, $adminEmail)) {
            Session::flash('success', 'Validação cancelada com sucesso.');
        } else {
            Session::flash('error', 'Erro ao cancelar validação.');
        }

        $this->redirect('/admin/validacoes');
    }

    /** @param array<int, array<string, mixed>> $validacoes
     *  @return array<int, array<string, mixed>>
     */
    private function enrichValidacoesForAdminList(array $validacoes): array
    {
        if ($validacoes === []) {
            return [];
        }

        $indicacaoIds = array_values(array_unique(array_filter(array_map(
            static fn (array $row): int => (int) ($row['indicacao_id'] ?? 0),
            $validacoes
        ))));

        $indicacaoSummaries = (new Indicacao())->findSummaryByIds($indicacaoIds);
        $campanhaNomes = (new Cupom())->findCampanhaNomesByIndicacaoIds($indicacaoIds);
        $campanhaAtiva = (new Campanha())->findActive();
        $campanhaFallback = (string) ($campanhaAtiva['nome'] ?? '—');

        foreach ($validacoes as &$validacao) {
            $indicacaoId = (int) ($validacao['indicacao_id'] ?? 0);
            $indicacao = $indicacaoSummaries[$indicacaoId] ?? null;

            $validacao['indicacao_created_at'] = $indicacao['created_at'] ?? $validacao['created_at'];
            $validacao['campanha_nome'] = $campanhaNomes[$indicacaoId] ?? $campanhaFallback;
        }
        unset($validacao);

        usort($validacoes, static function (array $a, array $b): int {
            $dateA = strtotime((string) ($a['indicacao_created_at'] ?? $a['created_at']));
            $dateB = strtotime((string) ($b['indicacao_created_at'] ?? $b['created_at']));

            return $dateA <=> $dateB;
        });

        return $validacoes;
    }

    /** @param array<string, mixed>|null $indicacao
     *  @param array<int, array<string, mixed>> $history
     *  @param array<string, mixed>|null $cupom
     *  @return list<array{date: string, label: string, icon: string, description: ?string, admin: ?string}>
     */
    private function buildAdminTimeline(
        array $validacao,
        ?array $indicacao,
        array $history,
        ?array $cupom
    ): array {
        $events = [];

        if ($indicacao !== null) {
            $events[] = [
                'date' => (string) $indicacao['created_at'],
                'label' => 'Envio',
                'icon' => '📤',
                'description' => 'Indicação registrada no sistema.',
                'admin' => null,
            ];
        }

        $nomeIndicado = trim((string) ($validacao['nome_indicado'] ?? ''));
        if ($nomeIndicado === '' && $indicacao !== null) {
            $nomeIndicado = trim((string) ($indicacao['nome_indicado'] ?? ''));
        }

        if ($nomeIndicado !== '') {
            $events[] = [
                'date' => (string) ($indicacao !== null ? ($indicacao['updated_at'] ?? $validacao['created_at']) : $validacao['created_at']),
                'label' => 'Cadastro',
                'icon' => '📝',
                'description' => $nomeIndicado,
                'admin' => null,
            ];
        }

        foreach ($history as $item) {
            $statusNovo = (string) ($item['status_novo'] ?? '');

            if ($statusNovo === ValidacaoIndicacao::STATUS_EM_ANALISE) {
                $events[] = [
                    'date' => (string) $item['created_at'],
                    'label' => 'Início da análise',
                    'icon' => '🔵',
                    'description' => $item['descricao'] !== null ? (string) $item['descricao'] : null,
                    'admin' => $item['usuario_admin'] !== null ? (string) $item['usuario_admin'] : null,
                ];
                continue;
            }

            if (in_array($statusNovo, [ValidacaoIndicacao::STATUS_APROVADO, ValidacaoIndicacao::STATUS_BENEFICIO_LIBERADO], true)) {
                $events[] = [
                    'date' => (string) $item['created_at'],
                    'label' => 'Aprovação',
                    'icon' => '🟢',
                    'description' => $item['descricao'] !== null ? (string) $item['descricao'] : null,
                    'admin' => $item['usuario_admin'] !== null ? (string) $item['usuario_admin'] : null,
                ];
                continue;
            }

            if ($statusNovo === ValidacaoIndicacao::STATUS_REPROVADO) {
                $events[] = [
                    'date' => (string) $item['created_at'],
                    'label' => 'Reprovação',
                    'icon' => '🔴',
                    'description' => $item['descricao'] !== null ? (string) $item['descricao'] : null,
                    'admin' => $item['usuario_admin'] !== null ? (string) $item['usuario_admin'] : null,
                ];
            }
        }

        if ($cupom !== null) {
            $events[] = [
                'date' => (string) $cupom['created_at'],
                'label' => 'Geração do cupom',
                'icon' => '🎟️',
                'description' => (string) ($cupom['codigo'] ?? ''),
                'admin' => null,
            ];
        }

        usort($events, static function (array $a, array $b): int {
            return strtotime($a['date']) <=> strtotime($b['date']);
        });

        return $events;
    }
}
