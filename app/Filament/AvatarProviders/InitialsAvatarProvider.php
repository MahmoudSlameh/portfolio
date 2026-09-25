<?php

namespace App\Filament\AvatarProviders;

use App\Support\Media\InitialsAvatar;
use Filament\AvatarProviders\Contracts\AvatarProvider;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;

/**
 * Local initials avatars for panel users (Filament's default calls the external ui-avatars.com).
 */
class InitialsAvatarProvider implements AvatarProvider
{
    public function get(Model $record): string
    {
        return InitialsAvatar::dataUri(Filament::getNameForDefaultAvatar($record));
    }
}
