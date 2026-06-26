<?php

declare(strict_types=1);

enum AppsFlyerStatus: string
{
    case PENDING = 'PENDING';
    case RECEIVED = 'RECEIVED';
    case VALIDATED = 'VALIDATED';
    case INVALID = 'INVALID';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pendente',
            self::RECEIVED => 'Recebido',
            self::VALIDATED => 'Validado',
            self::INVALID => 'Inválido',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PENDING => '⏳',
            self::RECEIVED => '📥',
            self::VALIDATED => '✅',
            self::INVALID => '❌',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'yellow',
            self::RECEIVED => 'blue',
            self::VALIDATED => 'green',
            self::INVALID => 'red',
        };
    }
}
