<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Node type in a project architecture diagram.
 */
enum ArchitectureNodeKind: string implements HasLabel
{
    case Client = 'client';
    case Service = 'service';
    case Store = 'store';
    case Queue = 'queue';
    case External = 'external';

    public function getLabel(): string
    {
        return match ($this) {
            self::Client => 'Client',
            self::Service => 'Service',
            self::Store => 'Data store',
            self::Queue => 'Queue',
            self::External => 'External',
        };
    }
}
