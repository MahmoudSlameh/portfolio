{{-- Compact: smaller type, tighter spacing and inline skills, to fit more on each page. --}}
@extends('cv.layout', ['margin' => '11mm 13mm', 'skillsInline' => true])

@section('styles')
    body { font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif; font-size: 9pt; line-height: 1.2; }
    .cv-header { margin-bottom: 7pt; }
    .cv-name { font-size: 17pt; font-weight: bold; }
    .cv-role { font-size: 10pt; }
    .cv-contact { font-size: 8.5pt; color: #333333; margin-top: 1pt; }
    .cv-section { margin-top: 7pt; }
    .cv-heading { font-size: 9.5pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.06em; border-bottom: 0.5pt solid #999999; padding-bottom: 1pt; margin-bottom: 4pt; }
    .cv-entry { margin-bottom: 5pt; }
    .cv-entry-title { font-size: 9.5pt; font-weight: bold; }
    .cv-entry-meta { font-size: 8.5pt; color: #444444; margin-bottom: 1pt; }
    .cv-text { margin-bottom: 2pt; }
    .cv-list { margin: 1pt 0 2pt; }
    li { margin-bottom: 0.05em; }
    .cv-stack { font-size: 8.5pt; color: #444444; }
@endsection
