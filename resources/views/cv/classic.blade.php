{{-- Classic: serif, centred header, thin rules under the headings. --}}
@extends('cv.layout')

@section('styles')
    body { font-family: 'DejaVu Serif', 'Times New Roman', Times, serif; font-size: 10.5pt; }
    .cv-header { text-align: center; margin-bottom: 12pt; }
    .cv-name { font-size: 22pt; font-weight: bold; letter-spacing: 0.02em; }
    .cv-role { font-size: 12pt; margin-top: 2pt; }
    .cv-contact { font-size: 9.5pt; margin-top: 3pt; color: #333333; }
    .cv-section { margin-top: 11pt; }
    .cv-heading { font-size: 11.5pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.08em; border-bottom: 0.75pt solid #1a1a1a; padding-bottom: 2pt; margin-bottom: 6pt; }
    .cv-entry { margin-bottom: 8pt; }
    .cv-entry-title { font-size: 11pt; font-weight: bold; }
    .cv-entry-meta { font-size: 9.5pt; font-style: italic; color: #333333; margin-bottom: 3pt; }
    .cv-text { margin-bottom: 3pt; }
    .cv-list { margin: 2pt 0 3pt; }
    .cv-stack { font-size: 9.5pt; color: #333333; }
@endsection
