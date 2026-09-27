<?php

/*
|--------------------------------------------------------------------------
| Studio templates
|--------------------------------------------------------------------------
|
| Templates stored in the database as a Template Spec (JSON + scoped CSS) and
| rendered by the `studio` engine. See docs/12-ai-templates.md. The spec's
| sections, variants and copy keys live in App\Support\Studio\SpecCatalogue.
|
*/

return [

    /*
    | Fonts a spec may use. Only self-hosted Fontsource families that ship with
    | the app (no third-party requests, decision D20). `kinds` says which roles
    | a font can fill; `preload` is the above-the-fold file (Vite manifest key).
    */

    'fonts' => [
        'geist' => [
            'label' => 'Geist',
            'family' => "'Geist Variable', 'Geist', ui-sans-serif, system-ui, sans-serif",
            'kinds' => ['display', 'body'],
            'preload' => 'node_modules/@fontsource-variable/geist/files/geist-latin-wght-normal.woff2',
        ],
        'bricolage-grotesque' => [
            'label' => 'Bricolage Grotesque',
            'family' => "'Bricolage Grotesque Variable', 'Bricolage Grotesque', ui-sans-serif, system-ui, sans-serif",
            'kinds' => ['display', 'body'],
            'preload' => 'node_modules/@fontsource-variable/bricolage-grotesque/files/bricolage-grotesque-latin-opsz-normal.woff2',
        ],
        'space-grotesk' => [
            'label' => 'Space Grotesk',
            'family' => "'Space Grotesk Variable', 'Space Grotesk', ui-sans-serif, system-ui, sans-serif",
            'kinds' => ['display', 'body'],
            'preload' => 'node_modules/@fontsource-variable/space-grotesk/files/space-grotesk-latin-wght-normal.woff2',
        ],
        'archivo' => [
            'label' => 'Archivo',
            'family' => "'Archivo Variable', 'Archivo', ui-sans-serif, system-ui, sans-serif",
            'kinds' => ['display', 'body'],
            'preload' => 'node_modules/@fontsource-variable/archivo/files/archivo-latin-wdth-normal.woff2',
        ],
        'jetbrains-mono' => [
            'label' => 'JetBrains Mono',
            'family' => "'JetBrains Mono Variable', 'JetBrains Mono', ui-monospace, 'SFMono-Regular', monospace",
            'kinds' => ['display', 'body', 'mono'],
            'preload' => 'node_modules/@fontsource-variable/jetbrains-mono/files/jetbrains-mono-latin-wght-normal.woff2',
        ],
        'space-mono' => [
            'label' => 'Space Mono',
            'family' => "'Space Mono', ui-monospace, 'SFMono-Regular', monospace",
            'kinds' => ['display', 'body', 'mono'],
            'preload' => 'node_modules/@fontsource/space-mono/files/space-mono-latin-400-normal.woff2',
        ],
        'dm-mono' => [
            'label' => 'DM Mono',
            'family' => "'DM Mono', ui-monospace, monospace",
            'kinds' => ['display', 'body', 'mono'],
            'preload' => 'node_modules/@fontsource/dm-mono/files/dm-mono-latin-400-normal.woff2',
        ],
        'system-sans' => [
            'label' => 'System sans-serif',
            'family' => "ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif",
            'kinds' => ['display', 'body'],
            'preload' => null,
        ],
        'system-serif' => [
            'label' => 'System serif',
            'family' => "ui-serif, Georgia, 'Times New Roman', serif",
            'kinds' => ['display', 'body'],
            'preload' => null,
        ],
        'system-mono' => [
            'label' => 'System monospace',
            'family' => "ui-monospace, 'SFMono-Regular', Menlo, monospace",
            'kinds' => ['display', 'body', 'mono'],
            'preload' => null,
        ],
    ],

    'limits' => [
        'name' => 60,          // characters
        'home_sections' => 16,
        'css' => 40 * 1024,    // bytes, after sanitising (P8-02)
    ],

    /*
    | AI template builder (P9, docs/12-ai-templates.md sections 5 and 7). Requests go through the
    | Laravel AI SDK (laravel/ai); its provider keys and URLs use the SDK's own env names
    | (ANTHROPIC_API_KEY, OPENAI_API_KEY, ...; see vendor/laravel/ai/config/ai.php).
    | Settings saved in the panel (Site → AI) take precedence over these values.
    */

    'ai' => [
        'provider' => env('STUDIO_AI_PROVIDER'),       // empty = AI features off unless set in the panel
        'model' => env('STUDIO_AI_MODEL'),             // empty = the provider's default model in the SDK
        'daily_limit' => (int) env('STUDIO_AI_DAILY_LIMIT', 20),
        'max_output_tokens' => (int) env('STUDIO_AI_MAX_TOKENS', 16000),
        'timeout' => (int) env('STUDIO_AI_TIMEOUT', 240),  // seconds per request (the job allows 300)

        // Text providers the panel offers (SDK driver names). `url` = the panel asks for a base URL;
        // `model` = a model id is required (the SDK has no default for that provider).
        'providers' => [
            'anthropic' => ['label' => 'Anthropic'],
            'openai' => ['label' => 'OpenAI'],
            'gemini' => ['label' => 'Google Gemini'],
            'xai' => ['label' => 'xAI'],
            'mistral' => ['label' => 'Mistral'],
            'deepseek' => ['label' => 'DeepSeek'],
            'groq' => ['label' => 'Groq'],
            'openrouter' => ['label' => 'OpenRouter'],
            'ollama' => ['label' => 'Ollama (self-hosted)', 'url' => true],
            'openai-compatible' => ['label' => 'OpenAI-compatible endpoint', 'url' => true, 'model' => true],
        ],
    ],

];
