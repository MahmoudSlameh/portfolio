{{--
    The CV content shared by every template (P11-02). ATS rules: one column, standard headings, real text,
    no tables, icons or images, dates on their own line. Each template styles these classes.
--}}
@php
    /** @var \App\Support\Cv\CvData $cv */
    $skillsInline = $skillsInline ?? false;
@endphp

<header class="cv-header">
    <h1 class="cv-name">{{ $cv->name }}</h1>
    @if ($cv->role)
        <p class="cv-role">{{ $cv->role }}</p>
    @endif
    <p class="cv-contact">
        {{ collect([$cv->location, $cv->email, $cv->phone, $cv->website])->filter()->implode(' | ') }}
    </p>
    @if ($cv->links !== [])
        <p class="cv-contact">
            {{ collect($cv->links)->map(fn (array $link): string => "{$link['label']}: {$link['url']}")->implode(' | ') }}
        </p>
    @endif
</header>

@if ($cv->summary)
    <section class="cv-section">
        <h2 class="cv-heading">Summary</h2>
        <p class="cv-text">{{ $cv->summary }}</p>
    </section>
@endif

@if ($cv->experience !== [])
    <section class="cv-section">
        <h2 class="cv-heading">Experience</h2>
        @foreach ($cv->experience as $role)
            <div class="cv-entry">
                <h3 class="cv-entry-title">{{ $role['role'] }}, {{ $role['organization'] }}</h3>
                <p class="cv-entry-meta">{{ $role['period'] }}@if ($role['location']) | {{ $role['location'] }}@endif</p>
                @if ($role['summary'])
                    <p class="cv-text">{{ $role['summary'] }}</p>
                @endif
                @if ($role['highlights'] !== [])
                    <ul class="cv-list">
                        @foreach ($role['highlights'] as $highlight)
                            <li>{{ $highlight }}</li>
                        @endforeach
                    </ul>
                @endif
                @if ($role['stack'] !== [])
                    <p class="cv-stack">Technologies: {{ implode(', ', $role['stack']) }}</p>
                @endif
            </div>
        @endforeach
    </section>
@endif

@if ($cv->education !== [])
    <section class="cv-section">
        <h2 class="cv-heading">Education</h2>
        @foreach ($cv->education as $study)
            <div class="cv-entry">
                <h3 class="cv-entry-title">{{ $study['degree'] }}@if ($study['field']) in {{ $study['field'] }}@endif, {{ $study['institution'] }}</h3>
                <p class="cv-entry-meta">{{ $study['period'] }}@if ($study['location']) | {{ $study['location'] }}@endif @if ($study['grade']) | Grade: {{ $study['grade'] }}@endif</p>
                @if ($study['description'])
                    <p class="cv-text">{{ $study['description'] }}</p>
                @endif
                @if ($study['achievements'] !== [])
                    <ul class="cv-list">
                        @foreach ($study['achievements'] as $achievement)
                            <li>{{ $achievement }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach
    </section>
@endif

@if ($cv->skills !== [])
    <section class="cv-section">
        <h2 class="cv-heading">Skills</h2>
        @if ($skillsInline)
            <p class="cv-text">
                @foreach ($cv->skills as $group)
                    <strong>{{ $group['category'] }}:</strong> {{ implode(', ', $group['skills']) }}@if (! $loop->last)<br>@endif
                @endforeach
            </p>
        @else
            @foreach ($cv->skills as $group)
                <p class="cv-text"><strong>{{ $group['category'] }}:</strong> {{ implode(', ', $group['skills']) }}</p>
            @endforeach
        @endif
    </section>
@endif

@if ($cv->certifications !== [])
    <section class="cv-section">
        <h2 class="cv-heading">Certifications</h2>
        <ul class="cv-list">
            @foreach ($cv->certifications as $certification)
                <li>
                    {{ $certification['name'] }}, {{ $certification['issuer'] }} ({{ $certification['issued'] }}@if ($certification['expires']) – {{ $certification['expires'] }}@endif)@if ($certification['credential']). Credential ID {{ $certification['credential'] }}@endif @if ($certification['url'])| {{ $certification['url'] }}@endif
                </li>
            @endforeach
        </ul>
    </section>
@endif

@if ($cv->projects !== [])
    <section class="cv-section">
        <h2 class="cv-heading">Projects</h2>
        @foreach ($cv->projects as $project)
            <div class="cv-entry">
                <h3 class="cv-entry-title">{{ $project['title'] }} ({{ $project['year'] }})</h3>
                @if ($project['description'])
                    <p class="cv-text">{{ $project['description'] }}</p>
                @endif
                <p class="cv-stack">
                    @if ($project['stack'] !== [])Technologies: {{ implode(', ', $project['stack']) }}@endif
                    @if ($project['url'])@if ($project['stack'] !== []) | @endif{{ $project['url'] }}@endif
                </p>
            </div>
        @endforeach
    </section>
@endif
