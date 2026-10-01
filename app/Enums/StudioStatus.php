<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

/**
 * Lifecycle of a studio template. Only `ready` templates (with an active version) can be previewed
 * or activated; `queued` and `in_progress` belong to AI generation (P9).
 */
enum StudioStatus: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';
    case Queued = 'queued';
    case InProgress = 'in_progress';
    case Ready = 'ready';
    case Failed = 'failed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Queued => 'Queued',
            self::InProgress => 'Generating',
            self::Ready => 'Ready',
            self::Failed => 'Failed',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft, self::Queued => 'gray',
            self::InProgress => 'info',
            self::Ready => 'success',
            self::Failed => 'danger',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Draft => Heroicon::OutlinedPencilSquare,
            self::Queued => Heroicon::OutlinedClock,
            self::InProgress => Heroicon::OutlinedArrowPath,
            self::Ready => Heroicon::OutlinedCheckCircle,
            self::Failed => Heroicon::OutlinedExclamationTriangle,
        };
    }

    /**
     * Still being generated: the Appearance page polls while any template is in this state.
     */
    public function isWorking(): bool
    {
        return in_array($this, [self::Queued, self::InProgress], true);
    }
}
