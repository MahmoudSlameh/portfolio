<?php

namespace App\Filament\Pages;

use App\Filament\Support\SingletonPage;
use App\Models\SiteSetting;
use App\Support\Studio\AiSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Laravel\Ai\AiManager;
use Throwable;
use UnitEnum;

use function Laravel\Ai\agent;

/**
 * Site → AI: provider, model and key for the AI template builder (docs/12-ai-templates.md §7).
 * Empty fields fall back to `.env`. The saved key is never sent back to the browser.
 */
class AiSettingsPage extends SingletonPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|UnitEnum|null $navigationGroup = 'Site';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'AI';

    protected static ?string $title = 'AI';

    protected static ?string $slug = 'ai';

    public function getSubheading(): string
    {
        return 'Used by "Generate with AI" on the Appearance page. Leave a field empty to use the value from .env.';
    }

    public function getRecord(): SiteSetting
    {
        return SiteSetting::current();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('testConnection')
                ->label('Test connection')
                ->icon(Heroicon::OutlinedSignal)
                ->color('gray')
                ->action(fn () => $this->testConnection()),
        ];
    }

    public function form(Schema $schema): Schema
    {
        $providers = array_map(fn (array $provider): string => (string) $provider['label'], (array) config('studio.ai.providers'));
        $option = fn (Get $get, string $key): bool => (bool) config("studio.ai.providers.{$get('ai_provider')}.{$key}", false);

        return $schema->components([
            Section::make()->schema([
                Text::make(fn (): string => $this->status())->color(fn (): string => AiSettings::current()->enabled() ? 'success' : 'warning'),
                Toggle::make('ai_enabled')
                    ->label('Enable AI features')
                    ->helperText('When off, "Generate with AI" is hidden even if .env has a provider.'),
            ]),
            Section::make('Provider')->columns(2)->schema([
                Select::make('ai_provider')
                    ->label('Provider')
                    ->options($providers)
                    ->placeholder(fn (): string => filled(config('studio.ai.provider')) ? 'From .env ('.config('studio.ai.provider').')' : 'Choose a provider')
                    ->live(),
                TextInput::make('ai_model')
                    ->label('Model')
                    ->maxLength(120)
                    ->placeholder(fn (Get $get): string => $this->defaultModelHint($get('ai_provider')))
                    ->required(fn (Get $get): bool => $option($get, 'model'))
                    ->helperText('The provider\'s model id. Use a model that accepts images to generate from screenshots.'),
                TextInput::make('ai_api_key')
                    ->label('API key')
                    ->password()
                    ->revealable(false)
                    ->autocomplete('off')
                    ->maxLength(500)
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->placeholder(fn (): string => filled($this->getRecord()->ai_api_key) ? 'A key is saved · type to replace it' : 'Leave empty to use the key from .env')
                    ->helperText('Stored encrypted and never shown again.')
                    ->columnSpanFull(),
                Toggle::make('forget_api_key')
                    ->label('Remove the saved key')
                    ->visible(fn (): bool => filled($this->getRecord()->ai_api_key))
                    ->dehydrated(false)
                    ->columnSpanFull(),
                TextInput::make('ai_base_url')
                    ->label('Base URL')
                    ->url()
                    ->maxLength(255)
                    ->placeholder(fn (Get $get): string => $get('ai_provider') === 'ollama' ? 'http://localhost:11434' : 'https://llm.example.com/v1')
                    ->visible(fn (Get $get): bool => $option($get, 'url'))
                    ->required(fn (Get $get): bool => $get('ai_provider') === 'openai-compatible')
                    ->columnSpanFull(),
            ]),
            Section::make('Limits')->schema([
                TextInput::make('ai_daily_limit')
                    ->label('Generations per day')
                    ->integer()
                    ->minValue(1)
                    ->maxValue(1000)
                    ->placeholder((string) config('studio.ai.daily_limit'))
                    ->helperText('Protects your bill; refines count too.'),
            ]),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($this->data['forget_api_key'] ?? false) === true) {
            $data['ai_api_key'] = null;
        }

        unset($data['forget_api_key']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->data['ai_api_key'] = null;
        $this->data['forget_api_key'] = false;
    }

    /**
     * Send a tiny prompt with the settings in the form (saved or not) and report the result.
     */
    public function testConnection(): void
    {
        $settings = AiSettings::from($this->formSettings());

        if (! $settings->enabled()) {
            Notification::make()->warning()->title('AI is not ready')->body($settings->problem)->send();

            return;
        }

        $started = hrtime(true);

        try {
            $response = agent('You check that an API connection works. Reply with the single word OK.')
                ->prompt('Ping', provider: $settings->register(), model: $settings->model, timeout: 30);
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()->danger()->title('Connection failed')->body(Str::limit($this->redact($exception->getMessage()), 300))->persistent()->send();

            return;
        }

        $milliseconds = (int) round((hrtime(true) - $started) / 1_000_000);
        $model = $response->meta->model ?? $settings->effectiveModel() ?? 'default model';

        Notification::make()->success()
            ->title('Connected to '.$settings->label())
            ->body("Model {$model} answered in ".Number::format($milliseconds).' ms.')
            ->send();
    }

    /**
     * The saved settings with the form's unsaved values on top (an empty key field keeps the saved key).
     */
    private function formSettings(): SiteSetting
    {
        $record = $this->getRecord()->replicate();
        $state = $this->data ?? [];

        $record->fill(array_intersect_key($state, array_flip(['ai_enabled', 'ai_provider', 'ai_model', 'ai_base_url', 'ai_daily_limit'])));

        if (filled($state['ai_api_key'] ?? null)) {
            $record->ai_api_key = (string) $state['ai_api_key'];
        } elseif (($state['forget_api_key'] ?? false) === true) {
            $record->ai_api_key = null;
        }

        return $record;
    }

    private function status(): string
    {
        $settings = AiSettings::current();

        if (! $settings->enabled()) {
            return 'Not ready: '.$settings->problem;
        }

        $source = $settings->source === 'panel' ? 'these settings' : '.env';
        $key = $settings->keyFromPanel ? 'the saved key' : 'the key from .env';

        return "Ready: {$settings->label()} from {$source}, model ".($settings->effectiveModel() ?? 'default').", using {$key}.";
    }

    private function defaultModelHint(mixed $provider): string
    {
        if (! is_string($provider) || $provider === '') {
            return 'Default of the provider';
        }

        if (config("studio.ai.providers.{$provider}.model") === true) {
            return 'Required, e.g. llama3.1';
        }

        try {
            return 'Default: '.app(AiManager::class)->textProvider($provider)->defaultTextModel();
        } catch (Throwable) {
            return 'Default of the provider';
        }
    }

    /**
     * Provider errors sometimes echo the request; never show the key.
     */
    private function redact(string $message): string
    {
        $key = config('ai.providers.'.AiSettings::PROVIDER_NAME.'.key');

        return is_string($key) && $key !== '' ? str_replace($key, '[key]', $message) : $message;
    }
}
