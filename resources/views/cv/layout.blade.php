{{-- Document shell shared by the CV templates: paper size, margins and the page styles slot. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $cv->name }} · CV</title>
    <style>
        @page { size: {{ $options->paper === 'letter' ? 'letter' : 'A4' }}; margin: {{ $margin ?? '16mm 17mm' }}; }
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; }
        body { color: #1a1a1a; line-height: 1.3; }
        h1, h2, h3, p, ul { margin: 0; }
        ul { padding-left: 1.1em; }
        li { margin: 0 0 0.15em; }
        .cv-section { page-break-inside: auto; }
        .cv-entry { page-break-inside: avoid; }
        .cv-heading { page-break-after: avoid; }
        @yield('styles')
    </style>
</head>
<body>
    @include('cv.partials.body', ['skillsInline' => $skillsInline ?? false])
</body>
</html>
