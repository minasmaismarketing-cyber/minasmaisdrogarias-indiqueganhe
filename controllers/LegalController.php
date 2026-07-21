<?php

declare(strict_types=1);

class LegalController extends Controller
{
    public function termosDeUso(): void
    {
        $this->view('legal.termos-de-uso', [
            'title' => 'Termos de Uso',
        ]);
    }

    public function politicaDePrivacidade(): void
    {
        $this->view('legal.politica-de-privacidade', [
            'title' => 'Política de Privacidade',
        ]);
    }

    public function regulamento(): void
    {
        $this->view('legal.regulamento', [
            'title' => 'Regulamento da Campanha',
        ]);
    }
}
