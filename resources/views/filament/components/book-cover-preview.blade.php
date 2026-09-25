@props(['title', 'author', 'background', 'ink', 'accent', 'style'])
@php
    $ornament = match ($style) {
        'band' => "position:absolute;left:0;right:0;top:58%;height:14%;background:{$accent}",
        'block' => "position:absolute;left:0;right:0;top:0;height:42%;background:{$accent}",
        'circle' => "position:absolute;left:27%;top:46%;width:46%;aspect-ratio:1;border-radius:9999px;background:{$accent}",
        'split' => "position:absolute;top:0;bottom:0;left:0;width:22%;background:{$accent}",
        default => null,
    };
    $offset = $style === 'split' ? 'padding-left:30%;' : '';
@endphp
<div role="img" aria-label="{{ $title }} — {{ $author }}"
     style="position:relative;display:flex;flex-direction:column;justify-content:space-between;width:150px;aspect-ratio:2/3;overflow:hidden;border-radius:2px;padding:14px;background:{{ $background }};color:{{ $ink }};box-shadow:inset 4px 0 6px -4px rgb(0 0 0 / .35),0 1px 2px rgb(0 0 0 / .18);">
    @if ($ornament)
        <span style="{{ $ornament }}"></span>
    @elseif ($style === 'rule')
        <span style="position:absolute;left:9%;right:9%;top:8%;height:3px;border-top:1px solid {{ $accent }};border-bottom:1px solid {{ $accent }}"></span>
        <span style="position:absolute;left:9%;right:9%;bottom:8%;height:3px;border-top:1px solid {{ $accent }};border-bottom:1px solid {{ $accent }}"></span>
    @endif
    <span style="position:relative;{{ $offset }}font-size:15px;line-height:1.1;font-weight:600">{{ $title }}</span>
    <span style="position:relative;{{ $offset }}font-size:9px;letter-spacing:.05em;text-transform:uppercase;opacity:.8">{{ $author }}</span>
</div>
