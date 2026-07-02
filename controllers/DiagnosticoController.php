<?php

declare(strict_types=1);

class DiagnosticoController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();

        $search = trim((string) ($_GET['q'] ?? ''));
        $report = null;
        $searchError = null;

        if ($search !== '') {
            $service = new IndicacaoDiagnosticoService();
            $report = $service->buildBySearch($search);

            if ($report === null) {
                $searchError = 'Nenhuma indicação encontrada para o termo informado.';
            }
        }

        $this->view('admin.diagnostico', [
            'title' => 'Diagnóstico de Indicação',
            'search' => $search,
            'report' => $report,
            'searchError' => $searchError,
        ], 'admin');
    }
}
