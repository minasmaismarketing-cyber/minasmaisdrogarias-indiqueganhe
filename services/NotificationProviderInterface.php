<?php

declare(strict_types=1);

interface NotificationProviderInterface
{
    /**
     * Send notification to user
     * 
     * @param int $userId User ID
     * @param string $message Notification message
     * @param array $data Additional data (optional)
     * @return bool Success status
     */
    public function send(int $userId, string $message, array $data = []): bool;

    /**
     * Check if provider is available/configured
     * 
     * @return bool
     */
    public function isAvailable(): bool;
}
