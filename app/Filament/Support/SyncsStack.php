<?php

namespace App\Filament\Support;

use App\Models\Contracts\HasStack;

/**
 * For Create/Edit record pages whose form uses {@see Fields::stack()}: saves the selected skills in order.
 *
 * @property array<string, mixed>|null $data
 */
trait SyncsStack
{
    protected function afterCreate(): void
    {
        $this->syncStack();
    }

    protected function afterSave(): void
    {
        $this->syncStack();
    }

    protected function syncStack(): void
    {
        $record = $this->getRecord();

        if (! $record instanceof HasStack) {
            return;
        }

        $record->syncSkillsInOrder(array_values(array_map(intval(...), (array) ($this->data['stack'] ?? []))));
    }
}
