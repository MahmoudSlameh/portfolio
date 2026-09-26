@php
    $templates = app(\App\Support\Templates\TemplateManager::class);
    $template = $templates->current();
    $settings = \App\Models\SiteSetting::current();
    $theme = in_array(request()->cookie('theme'), ['light', 'dark'], true) ? request()->cookie('theme') : null;
    $favicon = $settings->getFirstMediaUrl('favicon');
@endphp
<!DOCTYPE html>
<html lang="en" dir="ltr" data-template="{{ $template->id }}" data-theme="{{ $theme ?? 'light' }}" style="color-scheme: {{ $theme ?? 'light' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="color-scheme" content="light dark">
        <meta name="theme-color" content="#f7f5ff" media="(prefers-color-scheme: light)">
        <meta name="theme-color" content="#0b0a1a" media="(prefers-color-scheme: dark)">
        @if (! $theme)
            <script>
                (function () {
                    try {
                        var saved = localStorage.getItem('changelog:theme');
                        var theme = saved === 'dark' || saved === 'light' ? saved : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                        document.documentElement.dataset.theme = theme;
                        document.documentElement.style.colorScheme = theme;
                    } catch (error) {}
                })();
            </script>
        @endif

        @if ($favicon)
            <link rel="icon" href="{{ $favicon }}">
        @else
            <link rel="icon" href="/favicon.ico" sizes="any">
            <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        @endif
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @foreach ($template->preloadFonts as $font)
            <link rel="preload" href="{{ Vite::asset($font) }}" as="font" type="font/woff2" crossorigin>
        @endforeach

        @if ($settings->isPageEnabled('writing'))
            <link rel="alternate" type="application/rss+xml" title="{{ $settings->site_name }} — Writing" href="{{ url('/rss.xml') }}">
        @endif
        @if ($settings->google_site_verification)
            <meta name="google-site-verification" content="{{ $settings->google_site_verification }}">
        @endif
        @if ($settings->bing_site_verification)
            <meta name="msvalidate.01" content="{{ $settings->bing_site_verification }}">
        @endif

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        <x-inertia::head>
            <title>{{ $settings->site_name }}</title>
        </x-inertia::head>

        @if ($settings->analytics_snippet && ! $templates->isPreview())
            {!! $settings->analytics_snippet !!}
        @endif
    </head>
    <body class="antialiased">
        <x-inertia::app />
    </body>
</html>
