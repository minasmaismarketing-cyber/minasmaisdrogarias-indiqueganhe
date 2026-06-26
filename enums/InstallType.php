<?php

declare(strict_types=1);

enum InstallType: string
{
    case FIRST_INSTALL = 'FIRST_INSTALL';
    case REINSTALL = 'REINSTALL';
    case REENGAGEMENT = 'REENGAGEMENT';
    case UNKNOWN = 'UNKNOWN';

    public function label(): string
    {
        return match ($this) {
            self::FIRST_INSTALL => 'Primeira Instalação',
            self::REINSTALL => 'Reinstalação',
            self::REENGAGEMENT => 'Reengajamento',
            self::UNKNOWN => 'Desconhecido',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::FIRST_INSTALL => '📱',
            self::REINSTALL => '🔄',
            self::REENGAGEMENT => '🔄',
            self::UNKNOWN => '❓',
        };
    }
}
