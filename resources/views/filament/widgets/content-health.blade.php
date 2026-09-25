@php
    $checks = $this->getChecks();
    $done = collect($checks)->where('ok', true)->count();
@endphp
<x-filament-widgets::widget>
    <x-filament::section icon="heroicon-o-heart" :heading="'Content health · '.$done.' / '.count($checks)" description="Fill these in to get the most out of the site and its SEO.">
        <ul class="divide-y divide-gray-100 dark:divide-white/5">
            @foreach ($checks as $check)
                <li class="flex items-center gap-3 py-2">
                    @if ($check['ok'])
                        <x-filament::icon icon="heroicon-s-check-circle" class="size-5 text-success-500" />
                    @else
                        <x-filament::icon icon="heroicon-o-exclamation-circle" class="size-5 text-warning-500" />
                    @endif
                    <span @class(['flex-1 text-sm', 'text-gray-500 dark:text-gray-400' => $check['ok'], 'font-medium text-gray-950 dark:text-white' => ! $check['ok']])>
                        {{ $check['label'] }}
                        @if ($check['hint'] && ! $check['ok'])
                            <span class="ms-1 text-xs text-gray-500">{{ $check['hint'] }}</span>
                        @endif
                    </span>
                    @unless ($check['ok'])
                        <x-filament::link :href="$check['url']" size="sm">Fix</x-filament::link>
                    @endunless
                </li>
            @endforeach
        </ul>
    </x-filament::section>
</x-filament-widgets::widget>
