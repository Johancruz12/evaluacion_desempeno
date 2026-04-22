<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Evaluación de Desempeño - {{ $evaluation->employee?->name }}</title>
    <style>
        @page {
            margin: 20mm 15mm 25mm 15mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 9pt;
            color: #1e293b;
            line-height: 1.4;
        }

        /* ── HEADER ── */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #3b82a0;
            margin-bottom: 16px;
        }
        .header-table td {
            vertical-align: middle;
            border: 1px solid #3b82a0;
        }
        .header-logo {
            width: 100px;
            padding: 8px 10px;
            text-align: center;
        }
        .header-logo img {
            max-width: 80px;
            max-height: 55px;
        }
        .header-title {
            text-align: center;
            padding: 8px 10px;
            font-size: 11pt;
            font-weight: bold;
            color: #1e5073;
            line-height: 1.3;
        }
        .header-meta {
            width: 140px;
            padding: 2px 8px;
            font-size: 7.5pt;
        }
        .header-meta-row {
            padding: 2px 0;
            border-bottom: 1px solid #d1e3ed;
        }
        .header-meta-row:last-child {
            border-bottom: none;
        }
        .header-meta-label {
            font-weight: bold;
            color: #1e5073;
        }

        /* ── SECTION TITLES ── */
        .section-title {
            background-color: #e8f4f8;
            border: 1.5px solid #3b82a0;
            border-bottom: 2px solid #3b82a0;
            text-align: center;
            padding: 7px 10px;
            font-weight: bold;
            font-size: 9.5pt;
            color: #1e5073;
            margin-top: 14px;
            margin-bottom: 0;
        }

        /* ── DATA TABLE (Employee Info) ── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #3b82a0;
            margin-bottom: 14px;
        }
        .data-table td {
            border: 1px solid #3b82a0;
            padding: 5px 8px;
            font-size: 8.5pt;
        }
        .data-label {
            font-weight: bold;
            color: #1e5073;
            width: 25%;
            background-color: #f0f7fa;
        }
        .data-value {
            width: 25%;
        }

        /* ── CRITERIA TABLE ── */
        .criteria-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #3b82a0;
            margin-bottom: 0;
        }
        .criteria-table th {
            background-color: #1e5073;
            color: #ffffff;
            font-weight: bold;
            font-size: 8pt;
            padding: 6px 5px;
            text-align: center;
            border: 1px solid #1e5073;
        }
        .criteria-table th.th-num { width: 30px; }
        .criteria-table th.th-type { width: 18%; }
        .criteria-table th.th-desc { }
        .criteria-table th.th-score { width: 40px; }
        .criteria-table td {
            border: 1px solid #b8d4e3;
            padding: 5px 6px;
            font-size: 8pt;
            vertical-align: top;
        }
        .criteria-table tr:nth-child(even) {
            background-color: #f7fbfd;
        }
        .criteria-table tr:hover {
            background-color: #eef6fa;
        }
        .criteria-num {
            text-align: center;
            font-weight: bold;
            color: #1e5073;
            font-size: 9pt;
        }
        .criteria-type {
            font-weight: bold;
            font-size: 8pt;
            color: #334155;
            text-transform: uppercase;
            text-align: center;
        }
        .criteria-desc {
            font-size: 8pt;
            color: #475569;
            line-height: 1.3;
        }
        .score-cell {
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
            color: #1e293b;
            vertical-align: middle;
        }
        .total-row td {
            background-color: #e8f4f8;
            font-weight: bold;
            font-size: 9pt;
            color: #1e5073;
            border-top: 2px solid #3b82a0;
        }

        /* ── OBSERVATIONS BOX ── */
        .obs-box {
            width: 100%;
            border: 1.5px solid #3b82a0;
            border-top: none;
            margin-bottom: 14px;
        }
        .obs-label {
            background-color: #f0f7fa;
            padding: 5px 8px;
            font-size: 8pt;
            font-weight: bold;
            color: #1e5073;
            border-bottom: 1px solid #b8d4e3;
        }
        .obs-content {
            padding: 8px 10px;
            min-height: 40px;
            font-size: 8pt;
            color: #475569;
            line-height: 1.4;
        }

        /* ── INSTRUCTIONS BOX ── */
        .instructions-box {
            border: 1.5px solid #3b82a0;
            margin-bottom: 14px;
            padding: 10px 12px;
            font-size: 8pt;
            color: #475569;
            line-height: 1.5;
            background-color: #fafcfe;
        }
        .instructions-box p {
            margin-bottom: 6px;
        }
        .instructions-box ul {
            margin-left: 18px;
            margin-bottom: 6px;
        }
        .instructions-box li {
            margin-bottom: 2px;
        }

        /* ── SCORING RANGES TABLE ── */
        .range-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #3b82a0;
            margin-bottom: 14px;
        }
        .range-table th {
            background-color: #1e5073;
            color: #ffffff;
            font-weight: bold;
            font-size: 8.5pt;
            padding: 6px 10px;
            text-align: center;
            border: 1px solid #1e5073;
        }
        .range-table td {
            border: 1px solid #b8d4e3;
            padding: 5px 10px;
            font-size: 8.5pt;
        }
        .range-label { font-weight: bold; text-align: center; width: 100px; }
        .range-green { color: #15803d; }
        .range-blue { color: #1d4ed8; }
        .range-yellow { color: #b45309; }
        .range-red { color: #dc2626; }



        /* ── SIGNATURES ── */
        .signatures-table {
            width: 100%;
            margin-top: 30px;
        }
        .signatures-table td {
            text-align: center;
            padding-top: 30px;
            font-size: 8.5pt;
            vertical-align: bottom;
        }
        .signature-line {
            display: inline-block;
            width: 180px;
            border-top: 1.5px solid #1e293b;
            margin-bottom: 4px;
        }
        .signature-label {
            font-weight: bold;
            color: #1e5073;
            font-size: 8pt;
        }



        /* ── SCORE HIGHLIGHT ── */
        .score-highlight {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 10pt;
        }
        .score-green { background-color: #dcfce7; color: #15803d; }
        .score-blue { background-color: #dbeafe; color: #1d4ed8; }
        .score-yellow { background-color: #fef3c7; color: #b45309; }
        .score-red { background-color: #fee2e2; color: #dc2626; }

        .page-break { page-break-before: always; }

        /* Footer */
        .footer-info {
            position: fixed;
            bottom: -15mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7pt;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }

        /* Total general highlight */
        .total-general {
            width: 100%;
            border: 2px solid #1e5073;
            padding: 8px 12px;
            margin-bottom: 14px;
            background-color: #e8f4f8;
        }
        .total-general-inner {
            width: 100%;
            border-collapse: collapse;
        }
        .total-general-inner td {
            padding: 3px 8px;
            font-size: 9pt;
        }
        .total-general-label {
            font-weight: bold;
            color: #1e5073;
            text-align: right;
            width: 70%;
        }
        .total-general-value {
            font-weight: bold;
            font-size: 11pt;
            text-align: center;
        }
    </style>
</head>
<body>

@php
    $employee = $evaluation->employee;
    $evaluator = $evaluation->evaluator;
    $template = $evaluation->template;
    $sections = $template?->sections->where('is_active', true)->sortBy('order') ?? collect();
    $responsesMap = $evaluation->responses->keyBy('criteria_id');
    $scoringRanges = $template?->scoringRanges ?? collect();

    $sectionLabels = [
        'competencias_org' => 'COMPETENCIAS ORGANIZACIONALES A EVALUAR',
        'competencias_cargo' => 'COMPETENCIAS DEL CARGO',
        'responsabilidades' => 'CUMPLIMIENTO DE RESPONSABILIDADES',
    ];
    $sectionNumbers = [
        'competencias_org' => '3',
        'competencias_cargo' => '4',
        'responsabilidades' => '5',
    ];
    $obsFields = [
        'competencias_org' => $evaluation->obs_organizacional,
        'competencias_cargo' => $evaluation->obs_cargo,
        'responsabilidades' => $evaluation->obs_responsabilidades,
    ];
    $letters = range('A', 'Z');

    $logoPath = public_path('branding/clinica-junical-logo.png');
    $logoBase64 = '';
    if (file_exists($logoPath)) {
        $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
    }

    // Calculate section totals
    $sectionTotals = [];
    $grandAutoTotal = 0;
    $grandEvalTotal = 0;
    foreach ($sections as $sec) {
        if ($sec->type === 'rango') continue;
        $autoSum = 0; $evalSum = 0;
        foreach ($sec->criteria->where('is_active', true) as $c) {
            $r = $responsesMap[$c->id] ?? null;
            if ($r) {
                $autoSum += (float)($r->auto_score ?? 0);
                $evalSum += (float)($r->evaluator_score ?? 0);
            }
        }
        $sectionTotals[$sec->id] = ['auto' => $autoSum, 'eval' => $evalSum];
        $grandAutoTotal += $autoSum;
        $grandEvalTotal += $evalSum;
    }

    $scoreClass = function($score) {
        if ($score >= 91) return 'score-green';
        if ($score >= 71) return 'score-blue';
        if ($score >= 50) return 'score-yellow';
        return 'score-red';
    };

    $periodLabels = [
        'trimestral' => 'Trimestral',
        'semestral' => 'Semestral',
        'anual' => 'Anual',
    ];

    $currentPage = 1;
    $sectionIndex = 0;
@endphp

{{-- FOOTER --}}
<div class="footer-info">
    Generado el {{ now()->format('d/m/Y H:i') }} · Sistema de Evaluación de Desempeño · Clínica Junical
</div>

{{-- ═══════════════════════════════════════════════════ --}}
{{-- PAGE 1: HEADER + INSTRUCTIONS + DATA + FIRST SECTION --}}
{{-- ═══════════════════════════════════════════════════ --}}

{{-- DOCUMENT HEADER --}}
<table class="header-table">
    <tr>
        <td class="header-logo" rowspan="4">
            @if($logoBase64)
            <img src="{{ $logoBase64 }}" alt="Logo">
            @else
            <strong style="color: #1e5073; font-size: 8pt;">CLÍNICA<br>JUNICAL</strong>
            @endif
        </td>
        <td class="header-title" rowspan="4">
            EVALUACIÓN DE DESEMPEÑO PARA:<br>
            {{ $template?->name ?? 'Evaluación de Desempeño' }}
        </td>
        <td class="header-meta">
            <div class="header-meta-row">
                <span class="header-meta-label">Cód.:</span> SP-GETH-FO-25
            </div>
        </td>
    </tr>
    <tr>
        <td class="header-meta">
            <div class="header-meta-row">
                <span class="header-meta-label">Versión:</span> 01
            </div>
        </td>
    </tr>
    <tr>
        <td class="header-meta">
            <div class="header-meta-row">
                <span class="header-meta-label">Fecha Vigente:</span> {{ $evaluation->evaluation_date?->format('d-m-Y') ?? now()->format('d-m-Y') }}
            </div>
        </td>
    </tr>
    <tr>
        <td class="header-meta">
            <div class="header-meta-row">
                <span class="header-meta-label">Página:</span> <span class="page-number"></span>
            </div>
        </td>
    </tr>
</table>

{{-- 1. INSTRUCTIONS --}}
<div class="section-title">1. INSTRUCCIONES</div>
<div class="instructions-box">
    <p>La evaluación de desempeño, tiene como finalidad calificar objetivamente el desarrollo de la labor y la adaptación, con base en la observación, los hechos, los resultados y el comportamiento del evaluado en el cargo que desempeña.</p>
    <p>Esta evaluación debe realizarse en compañía del colaborador, con un alto grado de criterio y objetividad, desde el punto de vista de la justicia y desarrollo humano; por lo cual es necesario que la calificación haga referencia a la línea de conducta del evaluado con base en las competencias en el lapso establecido y no a hechos aislados.</p>
    <p>El sistema de calificación tiene cinco alternativas las cuales se enumeran del 1 al 5:</p>
    <ul>
        <li><strong>1:</strong> Tiene deficiencias muy significativas en el cumplimiento</li>
        <li><strong>2:</strong> Tiene algunas deficiencias en el cumplimiento</li>
        <li><strong>3:</strong> Cumple</li>
        <li><strong>4:</strong> Tiende a exceder el cumplimiento</li>
        <li><strong>5:</strong> Excede frecuentemente de manera significativa el cumplimiento</li>
    </ul>
    <p>Le recordamos leer detalladamente los criterios de evaluación antes de responder.</p>
</div>

{{-- 2. EMPLOYEE DATA --}}
<div class="section-title">2. DATOS DEL EVALUADO Y DEL JEFE INMEDIATO (EVALUADOR)</div>
<table class="data-table">
    <tr>
        <td class="data-label">Nombre del Evaluado:</td>
        <td class="data-value">{{ $employee?->person?->first_name }} {{ $employee?->person?->last_name }}</td>
        <td class="data-label">Cargo:</td>
        <td class="data-value">{{ $employee?->positionType?->name ?? '—' }}</td>
    </tr>
    <tr>
        <td class="data-label">Nombre del evaluador:</td>
        <td class="data-value">{{ $evaluator?->person?->first_name ?? '—' }} {{ $evaluator?->person?->last_name ?? '' }}</td>
        <td class="data-label">Cargo:</td>
        <td class="data-value">{{ $evaluator?->positionType?->name ?? '—' }}</td>
    </tr>
    <tr>
        <td class="data-label">Fecha de ingreso:</td>
        <td class="data-value">{{ $evaluation->admission_date?->format('d/m/Y') ?? ($employee?->person?->hire_date?->format('d/m/Y') ?? '—') }}</td>
        <td class="data-label">Área:</td>
        <td class="data-value">{{ $employee?->area?->name ?? '—' }}</td>
    </tr>
    <tr>
        <td class="data-label">Período a Evaluar:</td>
        <td class="data-value">{{ $periodLabels[$evaluation->period_type] ?? ucfirst($evaluation->period_type) }}</td>
        <td class="data-label">Fecha de Evaluación:</td>
        <td class="data-value">{{ $evaluation->evaluation_date?->format('d/m/Y') ?? '—' }}</td>
    </tr>
</table>

{{-- ═══════════════════════════════════════════════════ --}}
{{-- SECTIONS: COMPETENCIAS + RESPONSABILIDADES         --}}
{{-- ═══════════════════════════════════════════════════ --}}
@foreach($sections as $section)
@if($section->type === 'rango')
    @continue
@endif
@php
    $sectionIndex++;
    $sectionNum = $sectionNumbers[$section->type] ?? $sectionIndex + 2;
    $sectionLabel = $sectionLabels[$section->type] ?? strtoupper($section->name);
    $activeCriteria = $section->criteria->where('is_active', true)->sortBy('order');
    $totals = $sectionTotals[$section->id] ?? ['auto' => 0, 'eval' => 0];
    $obsText = $obsFields[$section->type] ?? '';
@endphp

<div class="section-title">{{ $sectionNum }}. {{ $sectionLabel }}</div>

@if($section->description)
<div style="border: 1.5px solid #3b82a0; border-top: none; padding: 6px 10px; font-size: 8pt; color: #475569; background-color: #fafcfe; margin-bottom: 0;">
    {{ $section->description }}
</div>
@endif

<table class="criteria-table">
    <thead>
        <tr>
            <th class="th-num">N°.</th>
            <th class="th-type">TIPO</th>
            <th class="th-desc">DESCRIPCIÓN DE LA COMPETENCIA</th>
            <th class="th-score">Auto</th>
            <th class="th-score">Jefe</th>
        </tr>
    </thead>
    <tbody>
        @foreach($activeCriteria as $idx => $criteria)
        @php
            $resp = $responsesMap[$criteria->id] ?? null;
            $autoVal = $resp?->auto_score !== null ? number_format((float)$resp->auto_score, 0) : '';
            $evalVal = $resp?->evaluator_score !== null ? number_format((float)$resp->evaluator_score, 0) : '';
            $letter = $letters[$idx] ?? ($idx + 1);
        @endphp
        <tr>
            <td class="criteria-num">{{ $letter }}</td>
            <td class="criteria-type">{{ $criteria->name }}</td>
            <td class="criteria-desc">{{ $criteria->description ?? '' }}</td>
            <td class="score-cell">{{ $autoVal }}</td>
            <td class="score-cell">{{ $evalVal }}</td>
        </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="3" style="text-align: right; padding-right: 12px; font-size: 9pt;">TOTAL:</td>
            <td class="score-cell" style="font-size: 10pt;">{{ number_format($totals['auto'], 1) }}</td>
            <td class="score-cell" style="font-size: 10pt;">{{ number_format($totals['eval'], 1) }}</td>
        </tr>
    </tbody>
</table>

{{-- Observations --}}
<div class="obs-box">
    <div class="obs-label">{{ $sectionNum }}.1 Observaciones o Aclaraciones: (Registrar aclaraciones o hechos concretos, que expliquen el resultado de su evaluación).</div>
    <div class="obs-content">{{ $obsText ?: '' }}</div>
</div>

@endforeach

{{-- ═══════════════════════════════════════════════════ --}}
{{-- TOTAL GENERAL                                      --}}
{{-- ═══════════════════════════════════════════════════ --}}
<div class="total-general">
    <table class="total-general-inner">
        <tr>
            <td class="total-general-label">TOTAL GENERAL AUTOEVALUACIÓN:</td>
            <td class="total-general-value {{ $scoreClass((float)$evaluation->total_auto_score) }}">
                {{ number_format((float)$evaluation->total_auto_score, 1) }}
            </td>
        </tr>
        @if($evaluation->total_evaluator_score)
        <tr>
            <td class="total-general-label">TOTAL GENERAL JEFE:</td>
            <td class="total-general-value {{ $scoreClass((float)$evaluation->total_evaluator_score) }}">
                {{ number_format((float)$evaluation->total_evaluator_score, 1) }}
            </td>
        </tr>
        @endif
        <tr>
            <td class="total-general-label">PUNTAJE FINAL:</td>
            <td class="total-general-value {{ $scoreClass((float)$evaluation->final_score) }}" style="font-size: 13pt;">
                {{ number_format((float)$evaluation->final_score, 1) }}
            </td>
        </tr>
        <tr>
            <td class="total-general-label">INTERPRETACIÓN:</td>
            <td class="total-general-value {{ $scoreClass((float)$evaluation->final_score) }}" style="font-size: 9pt;">
                {{ $evaluation->getInterpretation()['label'] }}
            </td>
        </tr>
    </table>
</div>

{{-- ═══════════════════════════════════════════════════ --}}
{{-- SCORING RANGE TABLE                               --}}
{{-- ═══════════════════════════════════════════════════ --}}
<table class="range-table">
    <thead>
        <tr>
            <th>RANGO</th>
            <th>INTERPRETACIÓN</th>
        </tr>
    </thead>
    <tbody>
        @if($scoringRanges->count())
            @foreach($scoringRanges as $range)
            @php
                $rangeColorClass = match($range->color) {
                    'green' => 'range-green',
                    'blue' => 'range-blue',
                    'yellow' => 'range-yellow',
                    'red' => 'range-red',
                    default => ''
                };
            @endphp
            <tr>
                <td class="range-label">{{ number_format((float)$range->min_score, 0) }} a {{ number_format((float)$range->max_score, 0) }}</td>
                <td class="{{ $rangeColorClass }}" style="font-weight: bold;">{{ $range->label }}</td>
            </tr>
            @endforeach
        @else
            <tr>
                <td class="range-label">91 a 110</td>
                <td class="range-green" style="font-weight: bold;">Sobre pasa las expectativas</td>
            </tr>
            <tr>
                <td class="range-label">71 a 90</td>
                <td class="range-blue" style="font-weight: bold;">Se destaca por su buen desempeño</td>
            </tr>
            <tr>
                <td class="range-label">50 a 70</td>
                <td class="range-yellow" style="font-weight: bold;">Cumple con lo esperado</td>
            </tr>
            <tr>
                <td class="range-label">Inferior a 50</td>
                <td class="range-red" style="font-weight: bold;">No cumple con todos los requerimientos del cargo, requiere plan de mejoramiento de inmediato</td>
            </tr>
        @endif
    </tbody>
</table>

{{-- ═══════════════════════════════════════════════════ --}}
{{-- DEVELOPMENT PLAN                                   --}}
{{-- ═══════════════════════════════════════════════════ --}}
{{-- SIGNATURES                                         --}}
{{-- ═══════════════════════════════════════════════════ --}}
<table class="signatures-table">
    <tr>
        <td style="width: 33%;">
            <div class="signature-line"></div><br>
            <span class="signature-label">Firma del Evaluador</span>
        </td>
        <td style="width: 33%;">
            <div class="signature-line"></div><br>
            <span class="signature-label">Firma del Evaluado</span>
        </td>
        <td style="width: 33%;">
            <div class="signature-line"></div><br>
            <span class="signature-label">Vo. Bo. De Talento Humano</span>
        </td>
    </tr>
</table>

</body>
</html>
