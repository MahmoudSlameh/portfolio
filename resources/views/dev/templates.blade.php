@php
    /**
     * Developer gallery (App\Http\Controllers\Dev\TemplateGalleryController).
     *
     * Compare mode (no template chosen): one page rendered by every template.
     * Template mode: every page of one template.
     * Each frame is the real site at `<path>?_template=<id>`, rendered at 1280 × 800 (or 390 × 844)
     * and scaled down. `&_theme=` renders it light or dark on the server (no cookie is written).
     *
     * @var array<string, \App\Support\Templates\TemplateDefinition> $templates
     * @var array<string, array{0: string, 1: string}> $pages
     */
    $frames = $template
        ? collect($pages)->map(fn (array $item) => ['title' => $item[0], 'path' => $item[1], 'template' => $template])->values()
        : collect($templates)->map(fn ($definition) => ['title' => $definition->label, 'path' => $pages[$page][1], 'template' => $definition->id])->values();
    [$width, $height, $scale] = $mobile ? [390, 844, 0.6] : [1280, 800, 0.4];
    $link = fn (array $changes) => url('/dev/templates').'?'.http_build_query(array_filter([
        'template' => $template, 'page' => $page, 'theme' => $theme, 'mobile' => $mobile ? 1 : null, ...$changes,
    ], fn ($value) => $value !== null && $value !== false));
@endphp
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex,nofollow">
        <title>Template gallery</title>
        <style>
            :root { color-scheme: light; font-family: ui-sans-serif, system-ui, sans-serif; }
            body { margin: 0; background: #f4f4f5; color: #18181b; }
            header { position: sticky; top: 0; z-index: 1; display: flex; flex-wrap: wrap; gap: 1rem 2rem; align-items: center; padding: 1rem 1.5rem; background: #fff; border-bottom: 1px solid #e4e4e7; }
            h1 { margin: 0; font-size: 1rem; }
            nav { display: flex; flex-wrap: wrap; gap: 0.25rem; align-items: center; font-size: 0.8125rem; }
            nav span { color: #71717a; margin-inline-end: 0.25rem; }
            nav a { padding: 0.25rem 0.625rem; border-radius: 999px; color: inherit; text-decoration: none; border: 1px solid #e4e4e7; }
            nav a[aria-current] { background: #18181b; border-color: #18181b; color: #fff; }
            .hint { margin: 1rem 1.5rem 0; padding: 0.75rem 1rem; border-radius: 0.5rem; background: #fef9c3; font-size: 0.875rem; }
            main { display: grid; grid-template-columns: repeat(auto-fill, minmax({{ $width * $scale }}px, 1fr)); gap: 1.5rem; padding: 1.5rem; }
            figure { margin: 0; }
            figcaption { display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.8125rem; }
            figcaption a { color: #52525b; }
            .frame { width: {{ $width * $scale }}px; height: {{ $height * $scale }}px; overflow: hidden; border-radius: 0.5rem; border: 1px solid #e4e4e7; background: #fff; }
            iframe { width: {{ $width }}px; height: {{ $height }}px; border: 0; transform: scale({{ $scale }}); transform-origin: 0 0; }
        </style>
    </head>
    <body>
        <header>
            <h1>Template gallery</h1>
            <nav aria-label="Template">
                <span>Template</span>
                <a href="{{ $link(['template' => null]) }}" @if (! $template) aria-current="page" @endif>Compare all</a>
                @foreach ($templates as $definition)
                    <a href="{{ $link(['template' => $definition->id]) }}" @if ($template === $definition->id) aria-current="page" @endif>{{ $definition->label }}</a>
                @endforeach
            </nav>
            @unless ($template)
                <nav aria-label="Page">
                    <span>Page</span>
                    @foreach ($pages as $key => [$label])
                        <a href="{{ $link(['page' => $key]) }}" @if ($page === $key) aria-current="page" @endif>{{ $label }}</a>
                    @endforeach
                </nav>
            @endunless
            <nav aria-label="Display">
                <span>Theme</span>
                <a href="{{ $link(['theme' => 'light']) }}" @if ($theme === 'light') aria-current="page" @endif>Light</a>
                <a href="{{ $link(['theme' => 'dark']) }}" @if ($theme === 'dark') aria-current="page" @endif>Dark</a>
                <span>Width</span>
                <a href="{{ $link(['mobile' => null]) }}" @if (! $mobile) aria-current="page" @endif>Desktop</a>
                <a href="{{ $link(['mobile' => 1]) }}" @if ($mobile) aria-current="page" @endif>Mobile</a>
            </nav>
        </header>

        @unless ($hasContent)
            <p class="hint">There is no published content yet. Seed the demo portfolio to see every section:
                <code>php artisan db:seed --class=DemoContentSeeder</code></p>
        @endunless

        <main>
            @foreach ($frames as $frame)
                @php($src = url($frame['path']).'?'.http_build_query([$query => $frame['template'], '_theme' => $theme]))
                <figure>
                    <figcaption>
                        <strong>{{ $frame['title'] }}</strong>
                        <a href="{{ $src }}" target="_blank" rel="noopener">Open ↗</a>
                    </figcaption>
                    <div class="frame">
                        <iframe src="{{ $src }}" title="{{ $frame['title'] }}" loading="lazy"></iframe>
                    </div>
                </figure>
            @endforeach
        </main>

    </body>
</html>
