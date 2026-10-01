{{-- Server-rendered head tags for when Inertia SSR is unavailable; mirrors resources/js/shared/seo/SeoHead.tsx so link previews (WhatsApp, Slack, X) never depend on JavaScript. --}}
<title data-inertia="">{{ $seo['title'] }}</title>
<meta data-inertia="description" name="description" content="{{ $seo['description'] }}">
<meta data-inertia="robots" name="robots" content="{{ $seo['robots'] }}">
<link data-inertia="canonical" rel="canonical" href="{{ $seo['canonical'] }}">
<meta data-inertia="og:type" property="og:type" content="{{ $seo['type'] }}">
<meta data-inertia="og:site_name" property="og:site_name" content="{{ $seo['siteName'] }}">
<meta data-inertia="og:title" property="og:title" content="{{ $seo['title'] }}">
<meta data-inertia="og:description" property="og:description" content="{{ $seo['description'] }}">
<meta data-inertia="og:url" property="og:url" content="{{ $seo['canonical'] }}">
<meta data-inertia="og:locale" property="og:locale" content="en_US">
@if ($seo['image'])
    <meta data-inertia="og:image" property="og:image" content="{{ $seo['image']['url'] }}">
    @if ($seo['image']['width'])
        <meta data-inertia="og:image:width" property="og:image:width" content="{{ $seo['image']['width'] }}">
    @endif
    @if ($seo['image']['height'])
        <meta data-inertia="og:image:height" property="og:image:height" content="{{ $seo['image']['height'] }}">
    @endif
    <meta data-inertia="og:image:alt" property="og:image:alt" content="{{ $seo['image']['alt'] }}">
@endif
@if ($seo['publishedTime'])
    <meta data-inertia="article:published_time" property="article:published_time" content="{{ $seo['publishedTime'] }}">
@endif
@if ($seo['modifiedTime'])
    <meta data-inertia="article:modified_time" property="article:modified_time" content="{{ $seo['modifiedTime'] }}">
@endif
@foreach ($seo['tags'] as $tag)
    <meta data-inertia="article:tag:{{ $tag }}" property="article:tag" content="{{ $tag }}">
@endforeach
<meta data-inertia="twitter:card" name="twitter:card" content="{{ $seo['image'] ? 'summary_large_image' : 'summary' }}">
@if ($seo['twitterSite'])
    <meta data-inertia="twitter:site" name="twitter:site" content="{{ $seo['twitterSite'] }}">
@endif
<meta data-inertia="twitter:title" name="twitter:title" content="{{ $seo['title'] }}">
<meta data-inertia="twitter:description" name="twitter:description" content="{{ $seo['description'] }}">
@if ($seo['image'])
    <meta data-inertia="twitter:image" name="twitter:image" content="{{ $seo['image']['url'] }}">
@endif
@foreach ($seo['jsonLd'] as $index => $graph)
    <script data-inertia="jsonld-{{ $index }}" type="application/ld+json">{!! json_encode($graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endforeach
