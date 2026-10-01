<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

/**
 * Icon of a service card; each template maps the value to its own icon set.
 */
enum ServiceIcon: string implements HasIcon, HasLabel
{
    case Code = 'code';
    case Server = 'server';
    case Layout = 'layout';
    case Database = 'database';
    case Cloud = 'cloud';
    case Integrations = 'integrations';
    case Performance = 'performance';
    case Security = 'security';
    case Mobile = 'mobile';
    case Automation = 'automation';
    case Platform = 'platform';
    case Ai = 'ai';
    case Maintenance = 'maintenance';
    case Consulting = 'consulting';
    case Analytics = 'analytics';
    case Terminal = 'terminal';
    case Sparkles = 'sparkles';

    public function getLabel(): string
    {
        return match ($this) {
            self::Code => 'Code',
            self::Server => 'Server / Backend',
            self::Layout => 'Layout / Frontend',
            self::Database => 'Database',
            self::Cloud => 'Cloud',
            self::Integrations => 'Integrations',
            self::Performance => 'Performance',
            self::Security => 'Security',
            self::Mobile => 'Mobile',
            self::Automation => 'Automation',
            self::Platform => 'Platform / SaaS',
            self::Ai => 'AI',
            self::Maintenance => 'Maintenance',
            self::Consulting => 'Consulting',
            self::Analytics => 'Analytics',
            self::Terminal => 'Terminal / DevOps',
            self::Sparkles => 'Sparkles',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Code => Heroicon::OutlinedCodeBracket,
            self::Server => Heroicon::OutlinedServerStack,
            self::Layout => Heroicon::OutlinedWindow,
            self::Database => Heroicon::OutlinedCircleStack,
            self::Cloud => Heroicon::OutlinedCloud,
            self::Integrations => Heroicon::OutlinedPuzzlePiece,
            self::Performance => Heroicon::OutlinedBolt,
            self::Security => Heroicon::OutlinedShieldCheck,
            self::Mobile => Heroicon::OutlinedDevicePhoneMobile,
            self::Automation => Heroicon::OutlinedArrowPath,
            self::Platform => Heroicon::OutlinedRectangleStack,
            self::Ai => Heroicon::OutlinedCpuChip,
            self::Maintenance => Heroicon::OutlinedWrenchScrewdriver,
            self::Consulting => Heroicon::OutlinedChatBubbleLeftRight,
            self::Analytics => Heroicon::OutlinedChartBar,
            self::Terminal => Heroicon::OutlinedCommandLine,
            self::Sparkles => Heroicon::OutlinedSparkles,
        };
    }
}
