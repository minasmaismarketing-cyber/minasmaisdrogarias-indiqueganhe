<?php

declare(strict_types=1);

/**
 * WhatsApp Notification Provider
 * 
 * Future integration for sending notifications via WhatsApp
 * This provider will send validation status updates to users.
 */
class WhatsAppNotificationProvider implements NotificationProviderInterface
{
    public function send(int $userId, string $message, array $data = []): bool
    {
        // TODO: Implement WhatsApp API integration
        // This will:
        // 1. Get user's phone number
        // 2. Send message via WhatsApp Business API
        // 3. Handle delivery status and errors
        
        return false;
    }

    public function isAvailable(): bool
    {
        // TODO: Check if WhatsApp API credentials are configured
        return false;
    }
}
