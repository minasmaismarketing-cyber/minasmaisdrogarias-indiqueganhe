<?php

declare(strict_types=1);

/**
 * Provider de notificação WhatsApp — Status: STUB (Integrations::STATUS_STUB).
 *
 * Preparado para envio via WhatsApp Business API quando credenciais forem configuradas.
 */
class WhatsAppNotificationProvider implements NotificationProviderInterface
{
    public function send(int $userId, string $message, array $data = []): IntegrationResult
    {
        unset($userId, $message, $data);

        return IntegrationResult::falha('Integração WhatsApp ainda não implementada.');
    }

    public function isAvailable(): IntegrationResult
    {
        return IntegrationResult::falha('Integração WhatsApp não configurada.');
    }
}
