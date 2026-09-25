<?php

namespace App\Filament\Support;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\CanUseDatabaseTransactions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Throwable;

/**
 * A panel page that edits a single-row model (profile, site settings, now page) with a sticky save bar.
 * Media fields (Spatie uploads) are saved through the form's relationships.
 *
 * @property-read Schema $form
 */
abstract class SingletonPage extends Page
{
    use CanUseDatabaseTransactions;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    abstract public function getRecord(): Model;

    public function mount(): void
    {
        $this->form->fill($this->mutateFormDataBeforeFill($this->getRecord()->attributesToArray()));
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->model($this->getRecord())
            ->operation('edit')
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')
                            ->label('Save changes')
                            ->submit('save')
                            ->keyBindings(['mod+s']),
                    ])->sticky()->key('form-actions'),
                ]),
        ]);
    }

    public function save(): void
    {
        try {
            $this->beginDatabaseTransaction();

            $data = $this->mutateFormDataBeforeSave($this->form->getState());
            $record = $this->getRecord();
            $record->update($data);
            $this->form->model($record)->saveRelationships();
            $this->afterSave();
        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction() ? $this->rollBackDatabaseTransaction() : $this->commitDatabaseTransaction();

            return;
        } catch (Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }

        $this->commitDatabaseTransaction();

        Notification::make()->success()->title('Saved')->send();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $data;
    }

    protected function afterSave(): void {}
}
