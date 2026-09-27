{{-- Modern: sans-serif, the name and headings in an accent colour (dark enough to print well). --}}
@extends('cv.layout')

@section('styles')
    body { font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif; font-size: 10pt; }
    .cv-header { margin-bottom: 12pt; padding-bottom: 8pt; border-bottom: 2pt solid #1d4ed8; }
    .cv-name { font-size: 24pt; font-weight: bold; color: #1d4ed8; }
    .cv-role { font-size: 12pt; margin-top: 2pt; color: #333333; }
    .cv-contact { font-size: 9pt; margin-top: 3pt; color: #444444; }
    .cv-section { margin-top: 12pt; }
    .cv-heading { font-size: 11pt; font-weight: bold; color: #1d4ed8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6pt; }
    .cv-entry { margin-bottom: 9pt; }
    .cv-entry-title { font-size: 10.5pt; font-weight: bold; }
    .cv-entry-meta { font-size: 9pt; color: #555555; margin-bottom: 3pt; }
    .cv-text { margin-bottom: 3pt; }
    .cv-list { margin: 2pt 0 3pt; }
    .cv-stack { font-size: 9pt; color: #444444; }
@endsection
