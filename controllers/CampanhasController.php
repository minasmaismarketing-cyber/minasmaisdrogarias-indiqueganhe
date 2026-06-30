<?php

declare(strict_types=1);

class CampanhasController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();

        $campanhaModel = new Campanha();
        $campanhas = $campanhaModel->findAll();

        $this->view('admin.campanhas', [
            'title' => 'Campanhas',
            'campanhas' => $campanhas,
        ], 'admin');
    }

    public function create(): void
    {
        Auth::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::validateRequest()) {
                Session::flash('error', 'Token de segurança inválido.');
                $this->redirect('/admin/campanhas');
            }

            $nome = Validator::sanitizeString($_POST['nome'] ?? '', 150);
            $descricao = Validator::sanitizeString($_POST['descricao'] ?? '', 500);
            $inicio = $_POST['inicio'] ?? '';
            $fim = $_POST['fim'] ?? '';
            $desconto = (float) ($_POST['desconto'] ?? 0);
            $tipoDesconto = $_POST['tipo_desconto'] ?? Campanha::TIPO_DESCONTO_PERCENTUAL;
            $valorMinimoCompra = (float) ($_POST['valor_minimo_compra'] ?? 0);
            $limiteIndicacoesUsuario = (int) ($_POST['limite_indicacoes_usuario'] ?? 0);
            $corPrimaria = $_POST['cor_primaria'] ?? '#D71920';
            $corSecundaria = $_POST['cor_secundaria'] ?? '#7A7A7A';
            $textoBotao = Validator::sanitizeString($_POST['texto_botao'] ?? '', 50);
            $textoLanding = Validator::sanitizeString($_POST['texto_landing'] ?? '', 500);
            $status = $_POST['status'] ?? Campanha::STATUS_INATIVA;

            $errors = [];

            if ($nome === '') {
                $errors['nome'] = 'Informe o nome da campanha.';
            }

            if ($inicio === '') {
                $errors['inicio'] = 'Informe a data de início.';
            }

            if ($fim === '') {
                $errors['fim'] = 'Informe a data de fim.';
            }

            if (strtotime($inicio) >= strtotime($fim)) {
                $errors['fim'] = 'A data de fim deve ser maior que a data de início.';
            }

            if ($desconto < 0) {
                $errors['desconto'] = 'O desconto não pode ser negativo.';
            }

            if ($valorMinimoCompra < 0) {
                $errors['valor_minimo_compra'] = 'O valor mínimo não pode ser negativo.';
            }

            if ($limiteIndicacoesUsuario < 0) {
                $errors['limite_indicacoes_usuario'] = 'O limite não pode ser negativo.';
            }

            if (!in_array($tipoDesconto, [Campanha::TIPO_DESCONTO_PERCENTUAL, Campanha::TIPO_DESCONTO_VALOR_FIXO])) {
                $errors['tipo_desconto'] = 'Tipo de desconto inválido.';
            }

            if (!in_array($status, [Campanha::STATUS_ATIVA, Campanha::STATUS_INATIVA, Campanha::STATUS_AGENDADA, Campanha::STATUS_FINALIZADA])) {
                $errors['status'] = 'Status inválido.';
            }

            if ($errors !== []) {
                Session::flash('errors', $errors);
                Session::flash('old', $_POST);
                $this->redirect('/admin/campanhas/criar');
            }

            $campanhaModel = new Campanha();
            $campanhaModel->create([
                'nome' => $nome,
                'descricao' => $descricao,
                'inicio' => $inicio,
                'fim' => $fim,
                'desconto' => $desconto,
                'tipo_desconto' => $tipoDesconto,
                'valor_minimo_compra' => $valorMinimoCompra,
                'limite_indicacoes_usuario' => $limiteIndicacoesUsuario,
                'cor_primaria' => $corPrimaria,
                'cor_secundaria' => $corSecundaria,
                'texto_botao' => $textoBotao,
                'texto_landing' => $textoLanding,
                'status' => $status,
            ]);

            Session::flash('success', 'Campanha criada com sucesso!');
            $this->redirect('/admin/campanhas');
        }

        $this->view('admin.campanha-form', [
            'title' => 'Nova Campanha',
        ], 'admin');
    }

    public function edit(int $id): void
    {
        Auth::requireAdmin();

        $campanhaModel = new Campanha();
        $campanha = $campanhaModel->findById($id);

        if ($campanha === null) {
            Session::flash('error', 'Campanha não encontrada.');
            $this->redirect('/admin/campanhas');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::validateRequest()) {
                Session::flash('error', 'Token de segurança inválido.');
                $this->redirect('/admin/campanhas');
            }

            $nome = Validator::sanitizeString($_POST['nome'] ?? '', 150);
            $descricao = Validator::sanitizeString($_POST['descricao'] ?? '', 500);
            $inicio = $_POST['inicio'] ?? '';
            $fim = $_POST['fim'] ?? '';
            $desconto = (float) ($_POST['desconto'] ?? 0);
            $tipoDesconto = $_POST['tipo_desconto'] ?? Campanha::TIPO_DESCONTO_PERCENTUAL;
            $valorMinimoCompra = (float) ($_POST['valor_minimo_compra'] ?? 0);
            $limiteIndicacoesUsuario = (int) ($_POST['limite_indicacoes_usuario'] ?? 0);
            $corPrimaria = $_POST['cor_primaria'] ?? '#D71920';
            $corSecundaria = $_POST['cor_secundaria'] ?? '#7A7A7A';
            $textoBotao = Validator::sanitizeString($_POST['texto_botao'] ?? '', 50);
            $textoLanding = Validator::sanitizeString($_POST['texto_landing'] ?? '', 500);
            $status = $_POST['status'] ?? Campanha::STATUS_INATIVA;

            $errors = [];

            if ($nome === '') {
                $errors['nome'] = 'Informe o nome da campanha.';
            }

            if ($inicio === '') {
                $errors['inicio'] = 'Informe a data de início.';
            }

            if ($fim === '') {
                $errors['fim'] = 'Informe a data de fim.';
            }

            if (strtotime($inicio) >= strtotime($fim)) {
                $errors['fim'] = 'A data de fim deve ser maior que a data de início.';
            }

            if ($desconto < 0) {
                $errors['desconto'] = 'O desconto não pode ser negativo.';
            }

            if ($valorMinimoCompra < 0) {
                $errors['valor_minimo_compra'] = 'O valor mínimo não pode ser negativo.';
            }

            if ($limiteIndicacoesUsuario < 0) {
                $errors['limite_indicacoes_usuario'] = 'O limite não pode ser negativo.';
            }

            if (!in_array($tipoDesconto, [Campanha::TIPO_DESCONTO_PERCENTUAL, Campanha::TIPO_DESCONTO_VALOR_FIXO])) {
                $errors['tipo_desconto'] = 'Tipo de desconto inválido.';
            }

            if (!in_array($status, [Campanha::STATUS_ATIVA, Campanha::STATUS_INATIVA, Campanha::STATUS_AGENDADA, Campanha::STATUS_FINALIZADA])) {
                $errors['status'] = 'Status inválido.';
            }

            if ($errors !== []) {
                Session::flash('errors', $errors);
                Session::flash('old', $_POST);
                $this->redirect('/admin/campanhas/editar/' . $id);
            }

            $campanhaModel->update($id, [
                'nome' => $nome,
                'descricao' => $descricao,
                'inicio' => $inicio,
                'fim' => $fim,
                'desconto' => $desconto,
                'tipo_desconto' => $tipoDesconto,
                'valor_minimo_compra' => $valorMinimoCompra,
                'limite_indicacoes_usuario' => $limiteIndicacoesUsuario,
                'cor_primaria' => $corPrimaria,
                'cor_secundaria' => $corSecundaria,
                'texto_botao' => $textoBotao,
                'texto_landing' => $textoLanding,
                'status' => $status,
            ]);

            Session::flash('success', 'Campanha atualizada com sucesso!');
            $this->redirect('/admin/campanhas');
        }

        $this->view('admin.campanha-form', [
            'title' => 'Editar Campanha',
            'campanha' => $campanha,
        ], 'admin');
    }

    public function duplicate(int $id): void
    {
        Auth::requireAdmin();

        if (!Csrf::validateRequest()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/campanhas');
        }

        $campanhaModel = new Campanha();
        $newId = $campanhaModel->duplicate($id);

        if ($newId > 0) {
            Session::flash('success', 'Campanha duplicada com sucesso!');
        } else {
            Session::flash('error', 'Erro ao duplicar campanha.');
        }

        $this->redirect('/admin/campanhas');
    }

    public function delete(int $id): void
    {
        Auth::requireAdmin();

        if (!Csrf::validateRequest()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/campanhas');
        }

        $campanhaModel = new Campanha();
        $campanhaModel->delete($id);

        Session::flash('success', 'Campanha excluída com sucesso!');
        $this->redirect('/admin/campanhas');
    }

    public function activate(int $id): void
    {
        Auth::requireAdmin();

        if (!Csrf::validateRequest()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/campanhas');
        }

        $campanhaModel = new Campanha();
        $campanhaModel->activate($id);

        Session::flash('success', 'Campanha ativada com sucesso!');
        $this->redirect('/admin/campanhas');
    }

    public function deactivate(int $id): void
    {
        Auth::requireAdmin();

        if (!Csrf::validateRequest()) {
            Session::flash('error', 'Token de segurança inválido.');
            $this->redirect('/admin/campanhas');
        }

        $campanhaModel = new Campanha();
        $campanhaModel->deactivate($id);

        Session::flash('success', 'Campanha desativada com sucesso!');
        $this->redirect('/admin/campanhas');
    }
}
