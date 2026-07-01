<?php

declare(strict_types=1);

/**
 * Contrato de providers de notificação.
 *
 * Stub preparado: WhatsAppNotificationProvider.
 */
interface NotificationProviderInterface
{
    /**
     * Envia notificação ao usuário.
     *
     * @param array<string, mixed> $data
     */
    public function send(int $userId, string $message, array $data = []): IntegrationResult;

    /** Indica se o provider está configurado e disponível para uso. */
    public function isAvailable(): IntegrationResult;
}
