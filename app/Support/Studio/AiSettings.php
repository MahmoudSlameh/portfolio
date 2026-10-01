<?php

namespace App\Support\Studio;

use App\Models\SiteSetting;
use Laravel\Ai\AiManager;
use RuntimeException;
use SensitiveParameter;
use Throwable;

/**
 * The AI settings every request uses (docs/12-ai-templates.md §7), resolved in this order:
 * the panel (Site → AI) → `.env` (config/studio.php → ai) → disabled.
 *
 * The key is never exposed: it is only written into the SDK's runtime provider config by
 * `register()`, and `__debugInfo()` hides it from dumps and logs.
 */
final class AiSettings
{
    /**
     * The SDK provider name the resolved settings are registered under.
     */
    public const PROVIDER_NAME = 'studio';

    /**
     * @param  'panel'|'env'|'none'  $source
     */
    private function __construct(
        public readonly string $source,
        public readonly ?string $provider,
        public readonly ?string $model,
        public readonly ?string $baseUrl,
        public readonly int $dailyLimit,
        public readonly ?string $problem,
        #[SensitiveParameter] private readonly ?string $key,
        public readonly bool $keyFromPanel,
    ) {}

    public static function current(): self
    {
        return self::from(SiteSetting::current());
    }

    /**
     * Resolve from settings that may not be saved yet (the panel tests the form's values).
     */
    public static function from(SiteSetting $settings): self
    {
        $dailyLimit = $settings->ai_daily_limit ?? (int) config('studio.ai.daily_limit', 20);

        if (! $settings->ai_enabled) {
            return self::disabled('AI features are turned off in Site → AI.', $dailyLimit);
        }

        $panelProvider = self::knownProvider($settings->ai_provider);
        $envProvider = self::knownProvider(config('studio.ai.provider'));

        if ($panelProvider !== null) {
            $source = 'panel';
            $provider = $panelProvider;
            $model = self::filled($settings->ai_model);
            $panelKey = self::filled($settings->ai_api_key);
            $url = self::filled($settings->ai_base_url);
        } elseif ($envProvider !== null) {
            $source = 'env';
            $provider = $envProvider;
            $model = self::filled(config('studio.ai.model'));
            $panelKey = null;
            $url = null;
        } else {
            return self::disabled('Choose a provider in Site → AI, or set STUDIO_AI_PROVIDER in .env.', $dailyLimit);
        }

        $key = $panelKey ?? self::filled(config("ai.providers.{$provider}.key"));
        $url ??= self::filled(config("ai.providers.{$provider}.url"));
        $options = (array) config("studio.ai.providers.{$provider}", []);

        $problem = match (true) {
            ($options['url'] ?? false) === true && $url === null => 'This provider needs a base URL.',
            ($options['url'] ?? false) !== true && $key === null && $provider !== 'bedrock' => 'Add an API key in Site → AI, or set the provider key in .env.',
            ($options['model'] ?? false) === true && $model === null => 'This provider needs a model id.',
            default => null,
        };

        return new self($source, $provider, $model, $url, $dailyLimit, $problem, $key, $panelKey !== null);
    }

    public function enabled(): bool
    {
        return $this->provider !== null && $this->problem === null;
    }

    public function hasKey(): bool
    {
        return $this->key !== null;
    }

    /**
     * The model that will be used: the configured one, or the provider's default in the SDK.
     */
    public function effectiveModel(): ?string
    {
        if ($this->model !== null || $this->provider === null) {
            return $this->model;
        }

        try {
            return app(AiManager::class)->textProvider($this->register())->defaultTextModel();
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Write these settings into the SDK as the `studio` provider (this process only) and return its name.
     *
     * @throws RuntimeException when AI is not configured
     */
    public function register(): string
    {
        if ($this->provider === null) {
            throw new RuntimeException($this->problem ?? 'AI is not configured.');
        }

        /** @var array<string, mixed> $base */
        $base = (array) config("ai.providers.{$this->provider}", []);

        config()->set('ai.providers.'.self::PROVIDER_NAME, array_filter([
            ...$base,
            'driver' => $base['driver'] ?? $this->provider,
            'key' => $this->key ?? ($base['key'] ?? null),
            'url' => $this->baseUrl ?? ($base['url'] ?? null),
        ], fn (mixed $value): bool => $value !== null));

        app(AiManager::class)->forgetInstance(self::PROVIDER_NAME);

        return self::PROVIDER_NAME;
    }

    public function label(): string
    {
        if ($this->provider === null) {
            return 'Not configured';
        }

        return (string) (config("studio.ai.providers.{$this->provider}.label") ?? $this->provider);
    }

    /**
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return [
            'source' => $this->source,
            'provider' => $this->provider,
            'model' => $this->model,
            'baseUrl' => $this->baseUrl,
            'dailyLimit' => $this->dailyLimit,
            'problem' => $this->problem,
            'key' => $this->key === null ? null : '[hidden]',
        ];
    }

    private static function disabled(string $problem, int $dailyLimit): self
    {
        return new self('none', null, null, null, $dailyLimit, $problem, null, false);
    }

    /**
     * A provider the SDK knows (the panel list, or any text provider configured in config/ai.php for `.env`).
     */
    private static function knownProvider(mixed $provider): ?string
    {
        $provider = self::filled($provider);

        return $provider !== null && $provider !== self::PROVIDER_NAME && is_array(config("ai.providers.{$provider}")) ? $provider : null;
    }

    private static function filled(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }
}
