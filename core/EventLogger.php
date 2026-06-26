<?php

declare(strict_types=1);

class EventLogger
{
    private Evento $eventoModel;

    public function __construct()
    {
        $this->eventoModel = new Evento();
    }

    public function log(int $usuarioId, string $evento, ?string $referencia = null, ?array $payload = null): void
    {
        try {
            $this->eventoModel->create([
                'usuario_id' => $usuarioId,
                'evento' => $evento,
                'referencia' => $referencia,
                'payload' => $payload,
            ]);
        } catch (PDOException $e) {
            Logger::error('Failed to log event', [
                'usuario_id' => $usuarioId,
                'evento' => $evento,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function logLogin(int $usuarioId): void
    {
        $this->log($usuarioId, Evento::EVENTO_LOGIN);
    }

    public function logCadastro(int $usuarioId, array $userData): void
    {
        $this->log($usuarioId, Evento::EVENTO_CADASTRO, null, [
            'nome' => $userData['nome'] ?? '',
            'email' => $userData['email'] ?? '',
        ]);
    }

    public function logLinkGerado(int $usuarioId, string $codigo): void
    {
        $this->log($usuarioId, Evento::EVENTO_LINK_GERADO, $codigo);
    }

    public function logLinkCompartilhado(int $usuarioId, string $codigo): void
    {
        $this->log($usuarioId, Evento::EVENTO_LINK_COMPARTILHADO, $codigo);
    }

    public function logLinkClicado(?int $usuarioId, string $codigo): void
    {
        $this->log($usuarioId, Evento::EVENTO_LINK_CLICADO, $codigo);
    }

    public function logConviteAberto(string $codigo): void
    {
        $this->log(null, Evento::EVENTO_CONVITE_ABERTO, $codigo);
    }

    public function logPerfilEditado(int $usuarioId, array $changes): void
    {
        $this->log($usuarioId, Evento::EVENTO_PERFIL_EDITADO, null, $changes);
    }

    public function logSenhaAlterada(int $usuarioId): void
    {
        $this->log($usuarioId, Evento::EVENTO_SENHA_ALTERADA);
    }

    public function logIndicacaoCriada(int $usuarioId, string $nomeIndicado): void
    {
        $this->log($usuarioId, Evento::EVENTO_INDICACAO_CRIADA, null, [
            'nome_indicado' => $nomeIndicado,
        ]);
    }

    public function logIndicadoCadastrado(int $indicadorId, string $nomeIndicado): void
    {
        $this->log($indicadorId, Evento::EVENTO_INDICADO_CADASTRADO, null, [
            'nome_indicado' => $nomeIndicado,
        ]);
    }

    public function logValidacaoCriada(int $usuarioId, int $validacaoId): void
    {
        $this->log($usuarioId, Evento::EVENTO_VALIDACAO_CRIADA, (string) $validacaoId);
    }

    public function logValidacaoIniciada(int $usuarioId, int $validacaoId): void
    {
        $this->log($usuarioId, Evento::EVENTO_VALIDACAO_INICIADA, (string) $validacaoId);
    }

    public function logValidacaoConcluida(int $usuarioId, int $validacaoId): void
    {
        $this->log($usuarioId, Evento::EVENTO_VALIDACAO_CONCLUIDA, (string) $validacaoId);
    }

    public function logValidacaoAprovada(int $usuarioId, int $validacaoId): void
    {
        $this->log($usuarioId, Evento::EVENTO_VALIDACAO_APROVADA, (string) $validacaoId);
    }

    public function logValidacaoInvalidada(int $usuarioId, int $validacaoId, string $motivo): void
    {
        $this->log($usuarioId, Evento::EVENTO_VALIDACAO_INVALIDADA, (string) $validacaoId, [
            'motivo' => $motivo,
        ]);
    }

    public function logValidacaoCancelada(int $usuarioId, int $validacaoId): void
    {
        $this->log($usuarioId, Evento::EVENTO_VALIDACAO_CANCELADA, (string) $validacaoId);
    }

    public function logCupomCriado(int $usuarioId, int $cupomId, string $codigo): void
    {
        $this->log($usuarioId, Evento::EVENTO_CUPOM_CRIADO, (string) $cupomId, [
            'codigo' => $codigo,
        ]);
    }

    public function logCupomCancelado(int $usuarioId, int $cupomId, string $codigo): void
    {
        $this->log($usuarioId, Evento::EVENTO_CUPOM_CANCELADO, (string) $cupomId, [
            'codigo' => $codigo,
        ]);
    }

    public function logCupomExpirado(int $usuarioId, int $cupomId, string $codigo): void
    {
        $this->log($usuarioId, Evento::EVENTO_CUPOM_EXPIRADO, (string) $cupomId, [
            'codigo' => $codigo,
        ]);
    }

    public function logCupomReservado(int $usuarioId, int $cupomId, string $codigo): void
    {
        $this->log($usuarioId, Evento::EVENTO_CUPOM_RESERVADO, (string) $cupomId, [
            'codigo' => $codigo,
        ]);
    }

    public function logCupomUtilizado(int $usuarioId, int $cupomId, string $codigo): void
    {
        $this->log($usuarioId, Evento::EVENTO_CUPOM_UTILIZADO, (string) $cupomId, [
            'codigo' => $codigo,
        ]);
    }

    public function logAppsflyerEventoRecebido(int $eventId, string $eventName): void
    {
        $this->log(null, Evento::EVENTO_APPSFLYER_EVENTO_RECEBIDO, (string) $eventId, [
            'event_name' => $eventName,
        ]);
    }

    public function logAppsflyerEventoProcessado(int $eventId, string $eventName): void
    {
        $this->log(null, Evento::EVENTO_APPSFLYER_EVENTO_PROCESSADO, (string) $eventId, [
            'event_name' => $eventName,
        ]);
    }

    public function logAppsflyerEventoValidado(int $eventId, string $eventName): void
    {
        $this->log(null, Evento::EVENTO_APPSFLYER_EVENTO_VALIDADO, (string) $eventId, [
            'event_name' => $eventName,
        ]);
    }

    public function logAppsflyerEventoRejeitado(int $eventId, string $eventName, string $reason): void
    {
        $this->log(null, Evento::EVENTO_APPSFLYER_EVENTO_REJEITADO, (string) $eventId, [
            'event_name' => $eventName,
            'reason' => $reason,
        ]);
    }

    public function getRecentEvents(int $limit = 50): array
    {
        return $this->eventoModel->listRecent($limit);
    }

    public function getUserEvents(int $usuarioId, int $limit = 50): array
    {
        return $this->eventoModel->findByUsuario($usuarioId, $limit);
    }
}
