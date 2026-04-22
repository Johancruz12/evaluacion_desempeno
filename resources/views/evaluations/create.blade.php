@extends('layouts.app')
@section('title', 'Nueva Evaluación')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">

    <div class="flex items-center gap-3 anim-slide-left">
        <a href="{{ route('evaluations.index') }}" class="w-8 h-8 rounded-lg bg-blue-50 border border-blue-200 hover:bg-blue-100 flex items-center justify-center text-blue-500 hover:text-blue-700 transition-all duration-200 hover:scale-110 hover:-translate-x-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-slate-800">Nueva evaluación</h1>
            <p class="text-slate-500 text-sm">Asigna una evaluación a un empleado</p>
        </div>
    </div>

    <div class="anim-fade-up bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <form method="POST" action="{{ route('evaluations.store') }}" class="space-y-5">
            @csrf

            {{-- Áreas --}}
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Áreas a evaluar</label>
                <div class="max-h-56 overflow-y-auto border border-slate-300 rounded-xl p-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($areas as $area)
                    <label class="flex items-center justify-between gap-2 text-sm text-slate-700 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-lg px-3 py-2 transition-colors">
                        <span>{{ $area->name }}</span>
                        <span class="text-xs text-slate-400">{{ $area->employees_count }}</span>
                        <input type="checkbox" name="area_ids[]" value="{{ $area->id }}" {{ collect(old('area_ids', []))->contains($area->id) ? 'checked' : '' }} class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500">
                    </label>
                    @endforeach
                </div>
                <p class="text-xs text-slate-400 mt-1">Selecciona una o varias áreas. Se creará una evaluación por cada empleado activo de esas áreas.</p>
            </div>

            {{-- Plantilla --}}
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Plantilla de evaluación</label>
                <select name="template_id" required
                        class="w-full px-3 py-2.5 border border-slate-300 rounded-xl text-slate-800 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <option value="">Seleccionar plantilla…</option>
                    @foreach($templates as $t)
                    <option value="{{ $t->id }}" {{ old('template_id') == $t->id ? 'selected' : '' }}>
                        {{ $t->name }}
                        @if($t->areas->count())
                            ({{ $t->areas->pluck('name')->join(', ') }})
                        @else
                            (Global)
                        @endif
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Tipo + Período --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Tipo de período</label>
                    <select name="period_type" id="period-type" required onchange="updatePeriods()"
                            class="w-full px-3 py-2.5 border border-slate-300 rounded-xl text-slate-800 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="">Seleccionar…</option>
                        <option value="trimestral" {{ old('period_type')=='trimestral'?'selected':'' }}>Trimestral</option>
                        <option value="semestral"  {{ old('period_type')=='semestral'?'selected':'' }}>Semestral</option>
                        <option value="anual"      {{ old('period_type')=='anual'?'selected':'' }}>Anual</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Período</label>
                    <select name="period" id="period-select" required
                            class="w-full px-3 py-2.5 border border-slate-300 rounded-xl text-slate-800 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="">— elige tipo primero —</option>
                    </select>
                </div>
            </div>

            {{-- Fecha --}}
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Fecha de evaluación <span class="text-slate-400 font-normal">(opcional)</span></label>
                <input type="date" name="evaluation_date" value="{{ old('evaluation_date', now()->toDateString()) }}"
                       class="w-full px-3 py-2.5 border border-slate-300 rounded-xl text-slate-800 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('evaluations.index') }}" class="px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Cancelar</a>
                <button type="submit" class="btn-bounce inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold px-5 py-2.5 rounded-xl transition-all text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Crear evaluación
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const currentYear = {{ now()->year }};
function updatePeriods() {
    const type = document.getElementById('period-type').value;
    const sel  = document.getElementById('period-select');
    sel.innerHTML = '';
    if (!type) { sel.innerHTML = '<option value="">— elige tipo primero —</option>'; return; }
    let opts = [];
    if (type === 'trimestral') {
        opts = [`${currentYear}-T1`,`${currentYear}-T2`,`${currentYear}-T3`,`${currentYear}-T4`];
    } else if (type === 'semestral') {
        opts = [`${currentYear}-S1`,`${currentYear}-S2`];
    } else if (type === 'anual') {
        opts = [`${currentYear}`];
    }
    opts.forEach(o => {
        const el = document.createElement('option');
        el.value = o; el.textContent = o;
        if ('{{ old('period') }}' === o) el.selected = true;
        sel.appendChild(el);
    });
}
// Trigger if there's an old period_type
if (document.getElementById('period-type').value) updatePeriods();
</script>
@endpush
@endsection
